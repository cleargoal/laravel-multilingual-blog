@php
    // Simple category view - extend your own layout by publishing views
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Header --}}
        <div class="mb-12">
            <nav class="flex mb-4 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('blog.index') }}" class="hover:text-indigo-600">{{ __('blog::blog.blog') }}</a>
                <span class="mx-2">/</span>
                <a href="{{ route('blog.index') }}" class="hover:text-indigo-600">{{ __('blog::blog.categories') }}</a>
                @if(isset($category))
                    <span class="mx-2">/</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $category->name }}</span>
                @endif
            </nav>

            @if(isset($category))
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
                    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">
                        {{ $category->name }}
                    </h1>
                    @if($category->description)
                        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-3xl">
                            {{ $category->description }}
                        </p>
                    @endif
                    <div class="mt-6 flex items-center text-sm text-gray-500">
                        <span class="flex items-center mr-6">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ $posts->total() }} {{ __('blog::blog.posts') }}
                        </span>
                    </div>
                </div>
            @else
                <div class="text-center">
                    <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-4">
                        {{ __('blog::blog.all_categories') }}
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        {{ __('blog::blog.browse_by_category') }}
                    </p>
                </div>
            @endif
        </div>

        @if(isset($category) && $category->children->count() > 0)
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('blog::blog.subcategories') }}
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($category->children as $child)
                        <a href="{{ route('blog.category', $child->slug) }}"
                           class="inline-flex items-center px-4 py-2 rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow text-gray-700 dark:text-gray-300">
                            {{ $child->name }}
                            <span class="ml-2 text-sm text-gray-500">({{ $child->posts_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!isset($category) && isset($categories) && $categories->count() > 0)
            {{-- All Categories Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $cat)
                    <a href="{{ route('blog.category', $cat->slug) }}"
                       class="group bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 transition-colors">
                                {{ $cat->name }}
                            </h3>
                            <span class="text-2xl font-bold text-indigo-600">
                                {{ $cat->posts_count }}
                            </span>
                        </div>
                        @if($cat->description)
                            <p class="text-gray-600 dark:text-gray-400 line-clamp-2">
                                {{ $cat->description }}
                            </p>
                        @endif
                        <div class="mt-4 flex items-center text-indigo-600 font-medium">
                            {{ __('blog::blog.view_posts') }}
                            <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @elseif(isset($posts))
            {{-- Posts Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($posts as $post)
                    <article class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center mb-2">
                                <time class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $post->published_at?->format('M d, Y') }}
                                </time>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-indigo-600 transition-colors">
                                    {{ $post->title }}
                                </a>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ $post->excerpt }}
                            </p>
                            <div class="flex items-center justify-between">
                                <a href="{{ route('blog.show', $post->slug) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium text-sm">
                                    {{ __('blog::blog.read_more') }}
                                    <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                <span class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    {{ $post->views_count }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ __('blog::blog.no_posts_in_category') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('blog::blog.check_other_categories') }}
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
