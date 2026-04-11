<h1>Blog Analytics</h1>

<div>
    <p>Total Posts: {{ $analytics['totalPosts'] ?? 0 }}</p>
    <p>Published Posts: {{ $analytics['publishedPosts'] ?? 0 }}</p>
    <p>Draft Posts: {{ $analytics['draftPosts'] ?? 0 }}</p>
    <p>Total Views: {{ $analytics['totalViews'] ?? 0 }}</p>
    <p>Total Comments: {{ $analytics['totalComments'] ?? 0 }}</p>
</div>
