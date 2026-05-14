-- =============================================================
--  SkillSwap – PostgreSQL Schema
--  Normalization: 3NF throughout; selective denormalization
--  noted inline where a cache column trades writes for reads.
-- =============================================================

-- Enable pgcrypto for gen_random_uuid()
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- =============================================================
--  DOMAIN: USERS & AUTHENTICATION
-- =============================================================

CREATE TABLE users (
    id               UUID          PRIMARY KEY DEFAULT gen_random_uuid(),
    email            VARCHAR(255)  NOT NULL UNIQUE,
    password_hash    VARCHAR(255)  NOT NULL,
    email_verified_at TIMESTAMPTZ,
    role             VARCHAR(20)   NOT NULL DEFAULT 'user'
                         CHECK (role IN ('user', 'admin')),
    is_active        BOOLEAN       NOT NULL DEFAULT TRUE,
    created_at       TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);

-- Normalization note: profile data separated from auth data (SRP).
-- One-to-one enforced by UNIQUE on user_id.
CREATE TABLE profiles (
    id                           UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id                      UUID         NOT NULL UNIQUE
                                                  REFERENCES users(id) ON DELETE CASCADE,
    full_name                    VARCHAR(100) NOT NULL,
    bio                          TEXT,
    avatar_url                   VARCHAR(500),
    timezone                     VARCHAR(50)  NOT NULL DEFAULT 'UTC',
    -- Denormalized cache columns (recomputed on session completion)
    -- Avoids expensive SUM(transactions) on every profile view.
    total_credits_earned         NUMERIC(12,2) NOT NULL DEFAULT 0,
    total_credits_spent          NUMERIC(12,2) NOT NULL DEFAULT 0,
    response_rate                NUMERIC(5,2),   -- mentor response %, cached
    sessions_completed_as_mentor INTEGER       NOT NULL DEFAULT 0,
    sessions_completed_as_learner INTEGER      NOT NULL DEFAULT 0,
    created_at                   TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at                   TIMESTAMPTZ   NOT NULL DEFAULT NOW()
);


-- =============================================================
--  DOMAIN: SKILLS & MARKETPLACE
-- =============================================================

-- Normalized category lookup; avoids repeating category strings in skills.
CREATE TABLE skill_categories (
    id         SERIAL       PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    slug       VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

CREATE TABLE skills (
    id          UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    category_id INTEGER      NOT NULL REFERENCES skill_categories(id),
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    -- Unique slug enables SEO-friendly URLs without full-text search on title.
    slug        VARCHAR(200) NOT NULL UNIQUE,
    is_active   BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at  TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- Junction between users (as mentors) and skills.
-- Stores mentor-specific metadata per skill (experience, price).
CREATE TABLE user_skills (
    id               UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id          UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    skill_id         UUID         NOT NULL REFERENCES skills(id) ON DELETE CASCADE,
    experience_level VARCHAR(20)  NOT NULL DEFAULT 'intermediate'
                         CHECK (experience_level IN ('beginner','intermediate','advanced','expert')),
    credits_per_hour NUMERIC(6,2) NOT NULL DEFAULT 1.00
                         CHECK (credits_per_hour > 0),
    description      TEXT,
    is_active        BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    -- One active listing per (user, skill) pair.
    UNIQUE (user_id, skill_id)
);

-- User interest tags for the recommendation engine.
CREATE TABLE user_interests (
    user_id    UUID        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    skill_id   UUID        NOT NULL REFERENCES skills(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, skill_id)
);


-- =============================================================
--  DOMAIN: AVAILABILITY & SCHEDULING
-- =============================================================

-- Recurring weekly availability windows.
CREATE TABLE availability_slots (
    id           UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id      UUID        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    day_of_week  SMALLINT    NOT NULL CHECK (day_of_week BETWEEN 0 AND 6), -- 0=Sunday
    start_time   TIME        NOT NULL,
    end_time     TIME        NOT NULL,
    is_recurring BOOLEAN     NOT NULL DEFAULT TRUE,
    valid_from   DATE,
    valid_until  DATE,
    created_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_slot_window  CHECK (end_time > start_time),
    CONSTRAINT chk_slot_dates   CHECK (valid_until IS NULL OR valid_until >= valid_from)
);

-- Session requests raised by learners.
-- Decoupled from sessions; not every request becomes a session.
CREATE TABLE session_requests (
    id                UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    learner_id        UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    mentor_id         UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    user_skill_id     UUID         NOT NULL REFERENCES user_skills(id),
    proposed_start    TIMESTAMPTZ  NOT NULL,
    proposed_end      TIMESTAMPTZ  NOT NULL,
    learner_message   TEXT,
    status            VARCHAR(20)  NOT NULL DEFAULT 'pending'
                          CHECK (status IN ('pending','accepted','rejected','cancelled','expired')),
    credits_reserved  NUMERIC(8,2),   -- held in wallet on request
    rejection_reason  TEXT,
    created_at        TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at        TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_request_no_self CHECK (learner_id <> mentor_id),
    CONSTRAINT chk_request_times   CHECK (proposed_end > proposed_start)
);

-- Confirmed sessions (created from accepted session_requests).
CREATE TABLE sessions (
    id               UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    -- One session per request (1:1 enforced by UNIQUE).
    request_id       UUID         NOT NULL UNIQUE REFERENCES session_requests(id),
    learner_id       UUID         NOT NULL REFERENCES users(id),
    mentor_id        UUID         NOT NULL REFERENCES users(id),
    user_skill_id    UUID         NOT NULL REFERENCES user_skills(id),
    scheduled_start  TIMESTAMPTZ  NOT NULL,
    scheduled_end    TIMESTAMPTZ  NOT NULL,
    actual_start     TIMESTAMPTZ,
    actual_end       TIMESTAMPTZ,
    status           VARCHAR(20)  NOT NULL DEFAULT 'scheduled'
                                CHECK (status IN ('scheduled','in_progress','completed','cancelled','no_show')),
    credits_charged  NUMERIC(8,2),
    meeting_url      VARCHAR(500),
    notes            TEXT,
    created_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_session_times CHECK (scheduled_end > scheduled_start)
);


-- =============================================================
--  DOMAIN: CREDIT WALLET
-- =============================================================

-- One wallet per user. Balance enforced non-negative at DB level.
CREATE TABLE wallets (
    id           UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id      UUID         NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    balance      NUMERIC(12,2) NOT NULL DEFAULT 10.00,  -- signup bonus
    total_earned NUMERIC(12,2) NOT NULL DEFAULT 0,
    total_spent  NUMERIC(12,2) NOT NULL DEFAULT 0,
    created_at   TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    updated_at   TIMESTAMPTZ   NOT NULL DEFAULT NOW(),
    CONSTRAINT chk_wallet_balance     CHECK (balance >= 0),
    CONSTRAINT chk_wallet_earned      CHECK (total_earned >= 0),
    CONSTRAINT chk_wallet_spent       CHECK (total_spent >= 0)
);

-- Append-only ledger. Never update or delete rows; only INSERT.
-- balance_after is denormalized for O(1) current-balance reads from history.
CREATE TABLE transactions (
    id            UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    wallet_id     UUID         NOT NULL REFERENCES wallets(id),
    session_id    UUID                     REFERENCES sessions(id),
    type          VARCHAR(30)  NOT NULL
                      CHECK (type IN (
                          'credit_earned','credit_spent',
                          'refund','signup_bonus','adjustment','credit_reserved','credit_released'
                      )),
    -- Positive = money in, negative = money out.
    amount        NUMERIC(10,2) NOT NULL,
    balance_after NUMERIC(12,2) NOT NULL,
    description   VARCHAR(255),
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);


-- =============================================================
--  DOMAIN: MESSAGING
-- =============================================================

CREATE TABLE conversations (
    id                  UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    session_request_id  UUID                    REFERENCES session_requests(id),
    last_message_at     TIMESTAMPTZ,            -- denormalized for inbox sort
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Many-to-many: one conversation can have multiple participants.
CREATE TABLE conversation_participants (
    conversation_id UUID        NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    user_id         UUID        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    joined_at       TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    last_read_at    TIMESTAMPTZ,
    PRIMARY KEY (conversation_id, user_id)
);

CREATE TABLE messages (
    id              UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    conversation_id UUID        NOT NULL REFERENCES conversations(id) ON DELETE CASCADE,
    sender_id       UUID        NOT NULL REFERENCES users(id),
    content         TEXT        NOT NULL,
    is_edited       BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- =============================================================
--  DOMAIN: REPUTATION & RANKING
-- =============================================================

-- Post-session reviews. Both learner -> mentor and mentor -> learner allowed.
CREATE TABLE reviews (
    id          UUID       PRIMARY KEY DEFAULT gen_random_uuid(),
    session_id  UUID       NOT NULL REFERENCES sessions(id),
    reviewer_id UUID       NOT NULL REFERENCES users(id),
    reviewee_id UUID       NOT NULL REFERENCES users(id),
    rating      SMALLINT   NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    -- One review per direction per session.
    UNIQUE (session_id, reviewer_id),
    CONSTRAINT chk_review_no_self CHECK (reviewer_id <> reviewee_id)
);

-- Materialized PageRank-style trust scores. Recomputed by a background job.
-- Stored separately from profiles to allow versioned re-runs.
CREATE TABLE trust_scores (
    id                UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id           UUID         NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    score             NUMERIC(10,8) NOT NULL DEFAULT 0.15000000, -- PageRank init
    rank_position     INTEGER,
    total_reviews     INTEGER      NOT NULL DEFAULT 0,
    average_rating    NUMERIC(3,2),
    algorithm_version SMALLINT     NOT NULL DEFAULT 1,
    last_computed_at  TIMESTAMPTZ,
    created_at        TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    updated_at        TIMESTAMPTZ  NOT NULL DEFAULT NOW()
);

-- Graph edge table used by the PageRank computation.
-- Represents trust relationships between users derived from reviews.
CREATE TABLE trust_edges (
    from_user_id UUID        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    to_user_id   UUID        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    weight       NUMERIC(6,4) NOT NULL DEFAULT 1.0,  -- aggregated normalized rating
    review_count INTEGER     NOT NULL DEFAULT 1,
    updated_at   TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (from_user_id, to_user_id),
    CONSTRAINT chk_edge_no_self CHECK (from_user_id <> to_user_id)
);


-- =============================================================
--  DOMAIN: RECOMMENDATIONS
-- =============================================================

-- Precomputed recommendation scores; refreshed periodically.
CREATE TABLE skill_recommendations (
    id         UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id    UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    skill_id   UUID         NOT NULL REFERENCES skills(id) ON DELETE CASCADE,
    score      NUMERIC(8,6) NOT NULL,
    reason     VARCHAR(30)  NOT NULL
                   CHECK (reason IN ('collaborative_filter','popularity','similar_users','trending')),
    created_at TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    UNIQUE (user_id, skill_id)
);

CREATE TABLE mentor_recommendations (
    id            UUID         PRIMARY KEY DEFAULT gen_random_uuid(),
    learner_id    UUID         NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    user_skill_id UUID         NOT NULL REFERENCES user_skills(id) ON DELETE CASCADE,
    score         NUMERIC(8,6) NOT NULL,
    reason        VARCHAR(30),
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT NOW(),
    UNIQUE (learner_id, user_skill_id)
);


-- =============================================================
--  DOMAIN: NOTIFICATIONS
-- =============================================================

CREATE TABLE notifications (
    id                  UUID        PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id             UUID        NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type                VARCHAR(50) NOT NULL,   -- e.g. 'session_accepted', 'new_message'
    title               VARCHAR(255) NOT NULL,
    body                TEXT,
    is_read             BOOLEAN     NOT NULL DEFAULT FALSE,
    -- Polymorphic reference (no FK; resolved by application layer).
    related_entity_type VARCHAR(50),
    related_entity_id   UUID,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
);


-- =============================================================
--  INDEXES
-- =============================================================

-- users
CREATE INDEX idx_users_email          ON users(email);
CREATE INDEX idx_users_role_active    ON users(role, is_active);

-- profiles
CREATE INDEX idx_profiles_user_id     ON profiles(user_id);

-- skills
CREATE INDEX idx_skills_category      ON skills(category_id);
CREATE INDEX idx_skills_slug          ON skills(slug);
CREATE INDEX idx_skills_active        ON skills(is_active) WHERE is_active = TRUE;
-- Full-text search on skill title + description
CREATE INDEX idx_skills_fts           ON skills
    USING GIN (to_tsvector('english', title || ' ' || COALESCE(description, '')));

-- user_skills (mentor listings)
CREATE INDEX idx_user_skills_user     ON user_skills(user_id);
CREATE INDEX idx_user_skills_skill    ON user_skills(skill_id);
CREATE INDEX idx_user_skills_active   ON user_skills(skill_id) WHERE is_active = TRUE;
CREATE INDEX idx_user_skills_level    ON user_skills(skill_id, experience_level);

-- availability_slots
CREATE INDEX idx_avail_user           ON availability_slots(user_id);
CREATE INDEX idx_avail_day_user       ON availability_slots(day_of_week, user_id);

-- session_requests
CREATE INDEX idx_sreq_learner         ON session_requests(learner_id);
CREATE INDEX idx_sreq_mentor          ON session_requests(mentor_id);
CREATE INDEX idx_sreq_status          ON session_requests(status);
CREATE INDEX idx_sreq_mentor_status   ON session_requests(mentor_id, status)
    WHERE status = 'pending';
CREATE INDEX idx_sreq_proposed_start  ON session_requests(proposed_start);

-- sessions
CREATE INDEX idx_sessions_learner     ON sessions(learner_id);
CREATE INDEX idx_sessions_mentor      ON sessions(mentor_id);
CREATE INDEX idx_sessions_status      ON sessions(status);
CREATE INDEX idx_sessions_scheduled   ON sessions(scheduled_start);
CREATE INDEX idx_sessions_skill       ON sessions(user_skill_id);

-- wallets
CREATE INDEX idx_wallets_user         ON wallets(user_id);

-- transactions
CREATE INDEX idx_tx_wallet            ON transactions(wallet_id);
CREATE INDEX idx_tx_session           ON transactions(session_id) WHERE session_id IS NOT NULL;
CREATE INDEX idx_tx_created           ON transactions(created_at DESC);
CREATE INDEX idx_tx_type              ON transactions(wallet_id, type);

-- conversations
CREATE INDEX idx_conv_request         ON conversations(session_request_id)
    WHERE session_request_id IS NOT NULL;
CREATE INDEX idx_conv_last_msg        ON conversations(last_message_at DESC);

-- conversation_participants
CREATE INDEX idx_cp_user              ON conversation_participants(user_id);

-- messages
CREATE INDEX idx_msg_conversation     ON messages(conversation_id, created_at DESC);
CREATE INDEX idx_msg_sender           ON messages(sender_id);

-- reviews
CREATE INDEX idx_reviews_session      ON reviews(session_id);
CREATE INDEX idx_reviews_reviewee     ON reviews(reviewee_id);
CREATE INDEX idx_reviews_reviewer     ON reviews(reviewer_id);

-- trust_scores
CREATE INDEX idx_trust_score_rank     ON trust_scores(score DESC);
CREATE INDEX idx_trust_user           ON trust_scores(user_id);

-- trust_edges
CREATE INDEX idx_tedge_to             ON trust_edges(to_user_id);
CREATE INDEX idx_tedge_from           ON trust_edges(from_user_id);

-- skill_recommendations
CREATE INDEX idx_srec_user_score      ON skill_recommendations(user_id, score DESC);

-- mentor_recommendations
CREATE INDEX idx_mrec_learner_score   ON mentor_recommendations(learner_id, score DESC);

-- notifications
CREATE INDEX idx_notif_user_unread    ON notifications(user_id, created_at DESC)
    WHERE is_read = FALSE;
CREATE INDEX idx_notif_entity         ON notifications(related_entity_type, related_entity_id)
    WHERE related_entity_id IS NOT NULL;


-- =============================================================
--  HELPER: updated_at auto-trigger
-- =============================================================

CREATE OR REPLACE FUNCTION set_updated_at()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;

-- Apply to all tables with updated_at
DO $$
DECLARE
    tname TEXT;
BEGIN
    FOREACH tname IN ARRAY ARRAY[
        'users','profiles','skills','user_skills',
        'session_requests','sessions','wallets',
        'messages','trust_scores','trust_edges'
    ] LOOP
        EXECUTE format(
            'CREATE TRIGGER trg_%s_updated_at
             BEFORE UPDATE ON %I
             FOR EACH ROW EXECUTE FUNCTION set_updated_at();',
            tname, tname
        );
    END LOOP;
END;
$$;
