@php
    // Simple blog post view - extend your own layout by publishing views
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Article Header --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="flex mb-8 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('blog.index') }}" class="hover:text-indigo-600">{{ __('blog::blog.blog') }}</a>
            @if($post->category)
                <span class="mx-2">/</span>
                <a href="{{ route('blog.category', $post->category->slug) }}" class="hover:text-indigo-600">{{ $post->category->name }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="text-gray-900 dark:text-gray-100 truncate">{{ Str::limit($post->title, 40) }}</span>
        </nav>

        {{-- Article Header --}}
        <div class="mb-8">
            <div class="flex items-center space-x-2 mb-4">
                @if($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}" 
                       class="inline-block px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded-full">
                        {{ $post->category->name }}
                    </a>
                @endif
                @if($post->is_featured)
                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        {{ __('blog::blog.featured') }}
                    </span>
                @endif
            </div>
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">{{ $post->title }}</h1>
        </div>

        {{-- Author & Meta --}}
        <div class="flex items-center justify-between py-6 border-y border-gray-200 dark:border-gray-700 mb-8">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full bg-indigo-600 flex items-center justify-center text-white text-lg font-bold">
                        {{ substr($post->author->name ?? 'A', 0, 1) }}
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $post->author->name ?? __('blog::blog.anonymous') }}
                    </p>
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                        <time datetime="{{ $post->published_at }}">
                            {{ $post->published_at?->format('F d, Y') }}
                        </time>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ $post->views_count }} {{ __('blog::blog.views') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Article Content --}}
        <article class="prose prose-lg prose-indigo dark:prose-invert max-w-none mb-12">
            {!! $post->content !!}
        </article>

        {{-- Tags --}}
        @if($post->tags->count() > 0)
            <div class="flex flex-wrap gap-2 mb-12">
                @foreach($post->tags as $tag)
                    <a href="{{ route('blog.tag', $tag->slug) }}" 
                       class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-indigo-900 dark:hover:text-indigo-300 transition-colors">
                        #{{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Related Posts --}}
        @if(isset($relatedPosts) && $relatedPosts->count() > 0)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-12 mb-12">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ __('blog::blog.related_posts') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedPosts as $related)
                        <article class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="p-4">
                                <h4 class="font-bold text-gray-900 dark:text-white mb-2">
                                    <a href="{{ route('blog.show', $related->slug) }}" class="hover:text-indigo-600 transition-colors">
                                        {{ Str::limit($related->title, 50) }}
                                    </a>
                                </h4>
                                <time class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $related->published_at?->format('M d, Y') }}
                                </time>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Comments Section --}}
        <div class="border-t border-gray-200 dark:border-gray-700 pt-12">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                {{ __('blog::blog.comments') }} 
                <span class="ml-2 text-sm font-normal text-gray-500">({{ $comments->count() ?? 0 }})</span>
            </h3>

            @auth
                <form action="{{ route('blog.comment', $post) }}" method="POST" class="mb-8">
                    @csrf
                    <div class="mb-4">
                        <textarea name="content" rows="4" 
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                  placeholder="{{ __('blog::blog.write_comment') }}" required></textarea>
                    </div>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        {{ __('blog::blog.post_comment') }}
                    </button>
                </form>
            @else
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 mb-8 text-center">
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('blog::blog.login_to_comment') }}
                    </p>
                </div>
            @endauth

            {{-- Comments List --}}
            @if(isset($comments) && $comments->count() > 0)
                <div class="space-y-6">
                    @foreach($comments as $comment)
                        <div class="flex space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold">
                                    {{ substr($comment->author->name ?? 'A', 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-1 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-white">
                                        {{ $comment->author->name ?? __('blog::blog.anonymous') }}
                                    </h4>
                                    <time class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $comment->created_at->diffForHumans() }}
                                    </time>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300">{{ $comment->content }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                    {{ __('blog::blog.no_comments') }}
                </p>
            @endif
        </div>
    </div>
</div>
