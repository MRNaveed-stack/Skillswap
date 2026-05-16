
CREATE TABLE IF NOT EXISTS user_searches (
  id BIGSERIAL PRIMARY KEY,
  user_id UUID NOT NULL REFERENCES auth.users(id) ON DELETE CASCADE,
  search_term VARCHAR(255),
  skill_id BIGINT REFERENCES skills(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT NOW()
);

-- Performance Indexes to prevent O(N) full table scans
CREATE INDEX idx_user_searches_user_id ON user_searches(user_id);
CREATE INDEX idx_sessions_mentor_learner ON sessions(mentor_id, learner_id);
CREATE INDEX idx_sessions_scheduled_at ON sessions(scheduled_at) WHERE status = 'scheduled';


-- ==============================================================================
-- 2. DAA ALGORITHMS
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- ALGORITHM 1: Mentor Matching Service (Weighted Scoring & Top-K)
-- Complexity: O(n log k) using PostgREST's Heap-based limit sort
-- ------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION match_mentors(p_skill_ids BIGINT[], p_limit INT, p_user_id UUID)
RETURNS TABLE (
  mentor_id UUID,
  full_name VARCHAR,
  avatar_url TEXT,
  total_score FLOAT,
  rating_score FLOAT,
  experience_score FLOAT,
  skill_score FLOAT,
  availability_score FLOAT,
  response_score FLOAT
) AS $$
/*
 * DAA Algorithm: Weighted Scoring with Top-K Selection
 * Time Complexity: O(n log k) where n is matching mentors.
 * We use an ORDER BY ... LIMIT clause which Postgres optimizes into a Top-K Heap Sort.
 * Space Complexity: O(k) for the heap.
 */
BEGIN
  RETURN QUERY
  WITH mentor_stats AS (
    -- Group mentor skills to calculate matching subsets
    SELECT 
      us.user_id,
      COUNT(us.skill_id) FILTER (WHERE us.skill_id = ANY(p_skill_ids)) AS matching_skills,
      MAX(CASE WHEN us.experience_level = 'Expert' THEN 5 WHEN us.experience_level = 'Intermediate' THEN 3 ELSE 1 END) AS exp_level
    FROM user_skills us
    WHERE us.skill_id = ANY(p_skill_ids)
    GROUP BY us.user_id
  ),
  availability_stats AS (
    SELECT mentor_id, COUNT(id) AS available_slots FROM availability_slots GROUP BY mentor_id
  )
  SELECT 
    p.id AS mentor_id,
    p.full_name,
    p.avatar_url,
    (
      (COALESCE(p.rating, 0) / 5.0 * 0.35) + 
      (LEAST(COALESCE(ms.exp_level, 0), 5) / 5.0 * 0.25) + 
      (LEAST(ms.matching_skills, array_length(p_skill_ids, 1))::FLOAT / GREATEST(array_length(p_skill_ids, 1), 1) * 0.20) +
      (LEAST(COALESCE(a.available_slots, 0), 10) / 10.0 * 0.10) +
      (1.0 * 0.10) -- Default response rate to 100% since it is not in the schema currently
    )::FLOAT AS total_score,
    (COALESCE(p.rating, 0) / 5.0 * 0.35)::FLOAT AS rating_score,
    (LEAST(COALESCE(ms.exp_level, 0), 5) / 5.0 * 0.25)::FLOAT AS experience_score,
    (LEAST(ms.matching_skills, array_length(p_skill_ids, 1))::FLOAT / GREATEST(array_length(p_skill_ids, 1), 1) * 0.20)::FLOAT AS skill_score,
    (LEAST(COALESCE(a.available_slots, 0), 10) / 10.0 * 0.10)::FLOAT AS availability_score,
    (0.10)::FLOAT AS response_score
  FROM profiles p
  JOIN mentor_stats ms ON p.id = ms.user_id
  LEFT JOIN availability_stats a ON p.id = a.mentor_id
  WHERE p.role = 'mentor' AND p.id != p_user_id
  ORDER BY total_score DESC
  LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;


-- ------------------------------------------------------------------------------
-- ALGORITHM 2: Scheduling Optimization Service (Greedy Interval Scheduling)
-- Complexity: O(n log n) sorting
-- ------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION optimize_schedule(p_user_id UUID, p_start_date TIMESTAMP, p_end_date TIMESTAMP)
RETURNS TABLE (
  start_time TIMESTAMP,
  end_time TIMESTAMP
) AS $$
/*
 * DAA Algorithm: Greedy Interval Scheduling
 * Time Complexity: O(n log n) due to sorting by end_time.
 * Greedy Choice Property: Always picking the session that ends earliest leaves the maximum
 * possible time for remaining non-overlapping sessions, ensuring an optimal subset.
 */
DECLARE
  v_rec RECORD;
  v_last_end TIMESTAMP := '-infinity';
BEGIN
  -- Iterate through sessions sorted by Earliest End Time First
  FOR v_rec IN 
    SELECT 
      scheduled_at AS s_start, 
      scheduled_at + INTERVAL '1 hour' AS s_end
    FROM sessions 
    WHERE (mentor_id = p_user_id OR learner_id = p_user_id)
      AND status = 'scheduled'
      AND scheduled_at >= p_start_date
      AND scheduled_at <= p_end_date
    ORDER BY (scheduled_at + INTERVAL '1 hour') ASC
  LOOP
    -- Greedy choice: If it doesn't overlap with the last selected interval, select it
    IF v_rec.s_start >= v_last_end THEN
      start_time := v_rec.s_start;
      end_time := v_rec.s_end;
      v_last_end := v_rec.s_end;
      RETURN NEXT;
    END IF;
  END LOOP;
END;
$$ LANGUAGE plpgsql;


-- ------------------------------------------------------------------------------
-- ALGORITHM 3: Reputation Ranking Service (PageRank inspired)
-- Complexity: O(E * iterations)
-- ------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION calculate_trust_score()
RETURNS TABLE (
  mentor_id UUID,
  trust_score FLOAT,
  converged BOOLEAN
) AS $$
/*
 * DAA Algorithm: Iterative Trust Propagation (PageRank inspired)
 * Time Complexity: O(E * k) where E is the number of reviews and k is iterations (5).
 * Space Complexity: O(V) to store temporary trust scores.
 */
DECLARE
  v_iter INT;
  v_damping FLOAT := 0.85;
BEGIN
  -- Temporary table for iteration memory
  CREATE TEMP TABLE IF NOT EXISTS temp_trust (
    t_mentor_id UUID PRIMARY KEY,
    current_score FLOAT DEFAULT 1.0,
    next_score FLOAT DEFAULT 0.0
  ) ON COMMIT DROP;

  INSERT INTO temp_trust (t_mentor_id, current_score)
  SELECT id, 1.0 FROM profiles WHERE role = 'mentor'
  ON CONFLICT DO NOTHING;

  -- 5 Iterations for trust flow
  FOR v_iter IN 1..5 LOOP
    UPDATE temp_trust t
    SET next_score = (1.0 - v_damping) + v_damping * COALESCE((
      SELECT SUM(
        (r.rating / 5.0 * 0.7) + (COALESCE(tt_reviewer.current_score, 1.0) * 0.3)
      ) / GREATEST(COUNT(r.id), 1)
      FROM reviews r
      LEFT JOIN temp_trust tt_reviewer ON r.learner_id = tt_reviewer.t_mentor_id
      WHERE r.mentor_id = t.t_mentor_id
    ), 0);

    UPDATE temp_trust SET current_score = next_score;
  END LOOP;

  RETURN QUERY SELECT t_mentor_id, current_score, TRUE FROM temp_trust;
END;
$$ LANGUAGE plpgsql;


-- ------------------------------------------------------------------------------
-- ALGORITHM 4: Recommendation Service
-- Complexity: O(m * n)
-- ------------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION recommend_mentors(p_user_id UUID, p_limit INT)
RETURNS TABLE (
  mentor_id UUID,
  full_name VARCHAR,
  recommendation_reason TEXT,
  score FLOAT
) AS $$
/*
 * DAA Algorithm: Weighted Recommendation (Collaborative Filtering Proxy)
 * Time Complexity: O(m * n) where m is user search history and n is mentors.
 * Space: O(1) beyond query results.
 */
BEGIN
  RETURN QUERY
  WITH user_history AS (
    SELECT DISTINCT skill_id FROM user_searches WHERE user_id = p_user_id
  ),
  mentor_scores AS (
    SELECT 
      p.id,
      p.full_name,
      COUNT(us.skill_id) * 10.0 + COALESCE(p.rating, 0) * 2.0 AS calc_score
    FROM profiles p
    JOIN user_skills us ON p.id = us.user_id
    JOIN user_history uh ON us.skill_id = uh.skill_id
    WHERE p.role = 'mentor' AND p.id != p_user_id
    GROUP BY p.id, p.full_name, p.rating
  )
  SELECT 
    ms.id,
    ms.full_name,
    'Based on your past skill searches'::TEXT,
    ms.calc_score::FLOAT
  FROM mentor_scores ms
  ORDER BY ms.calc_score DESC
  LIMIT p_limit;
END;
$$ LANGUAGE plpgsql;
