@php
    // Simple blog index view - extend your own layout by publishing views
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Blog Header --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl mb-4">
                {{ __('blog::blog.blog_title') }}
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ __('blog::blog.blog_subtitle') }}
            </p>
        </div>

        {{-- Featured Posts Section --}}
        @if(isset($featuredPosts) && $featuredPosts->count() > 0)
            <div class="mb-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    {{ __('blog::blog.featured_posts') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($featuredPosts as $post)
                        <article class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            {{-- Featured images require publishing views and enabling media in config --}}
                            <div class="p-6">
                                <div class="flex items-center mb-2">
                                    @if($post->category)
                                        <a href="{{ route('blog.category', $post->category->slug) }}" 
                                           class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                            {{ $post->category->name }}
                                        </a>
                                    @endif
                                    <span class="mx-2 text-gray-400">•</span>
                                    <time class="text-sm text-gray-500 dark:text-gray-400" datetime="{{ $post->published_at }}">
                                        {{ $post->published_at?->format('M d, Y') }}
                                    </time>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-indigo-600 transition-colors">
                                        {{ $post->title }}
                                    </a>
                                </h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $post->excerpt }}</p>
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('blog.show', $post->slug) }}" 
                                       class="inline-flex items-center text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">
                                        {{ __('blog::blog.read_more') }}
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ $post->views_count }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- All Posts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Content --}}
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ __('blog::blog.latest_posts') }}
                    </h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('blog.index') }}" 
                           class="px-3 py-1 text-sm font-medium rounded-full {{ !request('sort') || request('sort') == 'latest' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('blog::blog.sort_latest') }}
                        </a>
                        <a href="{{ route('blog.index', ['sort' => 'popular']) }}" 
                           class="px-3 py-1 text-sm font-medium rounded-full {{ request('sort') == 'popular' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ __('blog::blog.sort_popular') }}
                        </a>
                    </div>
                </div>

                <div class="space-y-6">
                    @forelse($posts as $post)
                        <article class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <div class="md:flex">
                                <div class="p-6 md:w-full">
                                    <div class="flex items-center mb-3">
                                        @if($post->category)
                                            <a href="{{ route('blog.category', $post->category->slug) }}" 
                                               class="inline-block px-2 py-1 text-xs font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded">
                                                {{ $post->category->name }}
                                            </a>
                                        @endif
                                        <span class="mx-2 text-gray-400">•</span>
                                        <time class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $post->published_at?->format('M d, Y') }}
                                        </time>
                                    </div>
                                    
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                        <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-indigo-600 transition-colors">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                    
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $post->excerpt }}</p>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                                                {{ substr($post->author->name ?? 'A', 0, 1) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $post->author->name ?? __('blog::blog.anonymous') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                {{ $post->views_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('blog::blog.no_posts') }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('blog::blog.check_back_later') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="lg:col-span-1">
                {{-- Categories --}}
                @if(isset($categories) && $categories->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            {{ __('blog::blog.categories') }}
                        </h3>
                        <ul class="space-y-2">
                            @foreach($categories as $category)
                                <li>
                                    <a href="{{ route('blog.category', $category->slug) }}" 
                                       class="flex items-center justify-between group">
                                        <span class="text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors">
                                            {{ $category->name }}
                                        </span>
                                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-full">
                                            {{ $category->posts_count }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Popular Tags --}}
                @if(isset($popularTags) && $popularTags->count() > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            {{ __('blog::blog.popular_tags') }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($popularTags as $tag)
                                <a href="{{ route('blog.tag', $tag->slug) }}" 
                                   class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 hover:bg-indigo-100 hover:text-indigo-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-indigo-900 dark:hover:text-indigo-300 transition-colors">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Newsletter / CTA --}}
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-xl shadow-md p-6 text-white">
                    <h3 class="text-lg font-bold mb-2">{{ __('blog::blog.stay_updated') }}</h3>
                    <p class="text-indigo-100 text-sm mb-4">{{ __('blog::blog.newsletter_desc') }}</p>
                    <a href="#" class="block w-full text-center px-4 py-2 bg-white text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition-colors">
                        {{ __('blog::blog.subscribe') }}
                    </a>
                </div>
            </aside>
        </div>
    </div>
</div>
