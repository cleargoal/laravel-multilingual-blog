<h1>My Blog Posts</h1>

@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>Status: {{ $post->status }}</p>
    </article>
@endforeach

{{ $posts->links() }}
