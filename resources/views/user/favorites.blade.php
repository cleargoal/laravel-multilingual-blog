<h1>My Favorite Posts</h1>

@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->excerpt }}</p>
    </article>
@endforeach

{{ $posts->links() }}
