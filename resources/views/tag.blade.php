@php
    // Simple tag view - extend your own layout by publishing views
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Header --}}
        <div class="mb-12">
            <nav class="flex mb-4 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('blog.index') }}" class="hover:text-indigo-600">{{ __('blog::blog.blog') }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 dark:text-gray-100">{{ __('blog::blog.tags') }}</span>
                @if(isset($tag))
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 dark:text-gray-100">#{{ $tag->name }}</span>
                @endif
            </nav>

            @if(isset($tag))
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg p-8 text-white">
                    <div class="flex items-center mb-4">
                        <span class="text-6xl font-bold opacity-30 mr-4">#</span>
                        <div>
                            <h1 class="text-4xl font-extrabold">{{ $tag->name }}</h1>
                            <p class="text-indigo-100 mt-2">
                                {{ $posts->total() }} {{ __('blog::blog.posts_with_tag') }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">
                        {{ __('blog::blog.all_tags') }}
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        {{ __('blog::blog.browse_by_tag') }}
                    </p>
                </div>
            @endif
        </div>

        @if(!isset($tag) && isset($tags) && $tags->count() > 0)
            {{-- All Tags Cloud --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-8">
                <div class="flex flex-wrap justify-center gap-4">
                    @foreach($tags as $t)
                        <a href="{{ route('blog.tag', $t->slug) }}"
                           class="group inline-flex items-center px-4 py-2 rounded-lg transition-all duration-300
                                  {{ $t->usage_count > 10 ? 'bg-indigo-600 text-white text-lg' : ($t->usage_count > 5 ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}
                                  hover:shadow-md hover:scale-105">
                            <span class="font-medium">#{{ $t->name }}</span>
                            <span class="ml-2 text-sm opacity-75">({{ $t->usage_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif(isset($posts))
            {{-- Posts Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($posts as $post)
                    <article class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        {{-- Featured images require publishing views and enabling media in config --}}
                        <div class="p-6">
                            @if($post->category)
                                <a href="{{ route('blog.category', $post->category->slug) }}"
                                   class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-2 mb-2">
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-indigo-600 transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ $post->excerpt }}
                            </p>
                            <div class="flex items-center justify-between">
                                <time class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $post->published_at?->format('M d, Y') }}
                                </time>
                                <a href="{{ route('blog.show', $post->slug) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium text-sm">
                                    {{ __('blog::blog.read_more') }}
                                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('blog::blog.no_posts_with_tag') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('blog::blog.check_other_tags') }}
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
