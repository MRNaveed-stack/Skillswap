## MentorMatchingService

**File:** `app/Services/MentorMatchingService.php`
**Lines:** 45-78
**Class:** `MentorMatchingService`
**Method:** `findTopMentors()`

**Algorithm:** Weighted scoring with Top-K using SplPriorityQueue
**Time Complexity:** O(n log k) where n=total mentors, k=requested limit
**Space Complexity:** O(k) for the heap

**Integration:**
- Called by: `MentorController@recommend()` (line 32)
- Injected via: Laravel service container (registered in `AppServiceProvider`)

**Code Snippet:**
```php
$priorityQueue = new \SplPriorityQueue();
foreach ($mentors as $mentor) {
    $score = $this->calculateScore($mentor);
    $priorityQueue->insert($mentor, $score);
}
// Extract top K
$topK = [];
for ($i = 0; $i < $k && !$priorityQueue->isEmpty(); $i++) {
    $topK[] = $priorityQueue->extract();
}
