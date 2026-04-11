<article>
    <h1>{{ $post->title }}</h1>
    <div>{!! $post->content !!}</div>
</article>

<section>
    <h2>Comments</h2>
    @foreach($comments as $comment)
        <div class="comment">
            <p>{{ $comment->content }}</p>
        </div>
    @endforeach
</section>

@if($relatedPosts->count() > 0)
    <aside>
        <h3>Related Posts</h3>
        @foreach($relatedPosts as $related)
            <div>{{ $related->title }}</div>
        @endforeach
    </aside>
@endif
