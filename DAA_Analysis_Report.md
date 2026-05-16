# Design & Analysis of Algorithms (DAA) Implementation Report

This report outlines the complexity analysis and mathematical foundations of the algorithms implemented within the Supabase Postgres environment for the SkillSwap platform.

---

## 1. Mentor Matching Service
**Algorithm Approach:** Weighted Multi-Factor Scoring with Top-K Selection

### Algorithm Choice Justification
To find the most suitable mentors from a large dataset, a weighted scoring heuristic provides precise relevance mapping. Instead of sorting the entire table, we use `ORDER BY score DESC LIMIT K`, which PostgreSQL natively optimizes using a **Top-K Heap Sort**.

### Complexity Analysis
- **Time Complexity:** $O(N \log K)$
  - $N$ is the number of mentors matching the initial WHERE clause (filtering by role and skill overlap).
  - $K$ is the `p_limit` provided by the user.
  - The database maintains a Min-Heap of size $K$. Inserting an element takes $O(\log K)$, and doing this for $N$ records gives $O(N \log K)$.
- **Space Complexity:** $O(K)$
  - Only the $K$ top items are kept in memory during the execution of the sort algorithm.

---

## 2. Scheduling Optimization Service
**Algorithm Approach:** Greedy Interval Scheduling

### Algorithm Choice Justification
The Greedy Interval Scheduling algorithm is the mathematically optimal choice for the Activity Selection Problem. It guarantees finding the maximum number of mutually compatible, non-overlapping activities. 

### Greedy Choice Property
By always selecting the session that **ends earliest** (Earliest End Time First), we leave the maximum possible time remaining for future activities. Because time is linear, a schedule ending earlier strictly dominates a schedule ending later, assuring a globally optimal set.

### Complexity Analysis
- **Time Complexity:** $O(N \log N)$
  - $N$ is the total number of sessions in the given date range. 
  - Sorting the sessions by `scheduled_at + 1 hour` dominates the time complexity at $O(N \log N)$.
  - The subsequent linear scan to pick non-overlapping intervals runs in $O(N)$.
- **Space Complexity:** $O(1)$ (or $O(N)$ depending on Postgres sort implementation space).

---

## 3. Reputation Ranking Service
**Algorithm Approach:** Graph-Based Iterative Trust Propagation (PageRank Variant)

### Algorithm Choice Justification
Traditional 5-star rating averages are highly susceptible to review-bombing or sybil attacks. By adapting PageRank, trust "flows" through the network. A 5-star review from an expert mentor carries significantly more weight than a 5-star review from a brand-new user.

### Graph Architecture
- **Nodes ($V$):** Users (Mentors and Learners)
- **Directed Edges ($E$):** Reviews (Learner $\rightarrow$ Mentor)
- **Damping Factor ($d$):** Set to 0.85 to model random jumps and ensure mathematical convergence (prevents infinite loops in cyclic review rings).

### Complexity Analysis
- **Time Complexity:** $O(E \times k)$
  - $E$ is the number of reviews (edges) in the database.
  - $k$ is a fixed number of iterations (set to 5). Since $k$ is constant, this simplifies to $O(E)$ linear time execution.
- **Space Complexity:** $O(V)$
  - We create a temporary table (`temp_trust`) holding $V$ rows to store the $i^{th}$ and $(i+1)^{th}$ iteration values.

---

## 4. Recommendation Service
**Algorithm Approach:** History-Based Content Filtering

### Algorithm Choice Justification
When machine learning resources are unavailable, mapping a user's search history to the inverse characteristics of the mentor pool is highly effective. By joining `user_searches` against `user_skills`, we find mentors who offer exactly what the user frequently queries.

### Complexity Analysis
- **Time Complexity:** $O(M \times S)$
  - $M$ is the unique skills searched by the user.
  - $S$ is the number of mentors offering those specific skills. 
  - Because we utilize foreign keys and Postgres Hash Joins, it executes rapidly compared to a Cartesian product.
- **Space Complexity:** $O(M)$ to store the user's distinct historical search parameters in memory during the query.
