<?php

namespace YourVendor\Blog\Http\Controllers;

use YourVendor\Blog\Actions\Blog\GetBlogIndexData;
use YourVendor\Blog\Actions\Blog\GetBlogPostForShow;
use YourVendor\Blog\Actions\Blog\SyncPostTags;

use YourVendor\Blog\Jobs\TranslateBlogPostJob;
use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BlogController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $search = $request->get('search');
        $categorySlug = null;
        $tagSlug = null;

        // Get data using the action
        ['posts' => $posts, 'categories' => $categories, 'popularTags' => $popularTags] =
            app(GetBlogIndexData::class)->execute($categorySlug, $tagSlug, $search);

        return view('blog::index', compact('posts', 'categories', 'popularTags'));
    }

    public function show(Request $request, $slug): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        // Get post data using the action
        $data = app(GetBlogPostForShow::class)->execute($slug);

        if (!$data) {
            abort(404);
        }

        return view('blog::show', $data);
    }

    public function category($slug): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        ['posts' => $posts, 'categories' => $categories, 'popularTags' => $popularTags] =
            app(GetBlogIndexData::class)->execute($slug, null, null);

        return view('blog::category', compact('posts', 'categories', 'popularTags'));
    }

    public function myPosts(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $posts = BlogPost::where('user_id', auth()->id())
            ->with(['category'])
            ->latest()
            ->paginate(20);

        return view('blog.my-posts', ['posts' => $posts]);
    }

    public function create(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $categories = BlogCategory::orderBy('sort_order')->get();

        return view('blog.form', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $currentLocale = app()->getLocale();

        $validated = $request->validate([
            "title.{$currentLocale}" => 'required|string|max:255',
            "excerpt.{$currentLocale}" => 'nullable|string|max:500',
            "content.{$currentLocale}" => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
            'service_links' => 'nullable|array',
            'service_links.*' => 'exists:service_offerings,id',
            'tags' => 'nullable|string',
        ]);

        $post = BlogPost::create([
            'user_id' => auth()->id(),
            'category_id' => $validated['category_id'],
            'title' => [
                $currentLocale => $validated['title'][$currentLocale],
            ],
            'excerpt' => [
                $currentLocale => $validated['excerpt'][$currentLocale] ?? '',
            ],
            'content' => [
                $currentLocale => $validated['content'][$currentLocale],
            ],
            'status' => $validated['status'],
            'original_locale' => $currentLocale,
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $validated['status'] === 'published' ? ($validated['published_at'] ?? now()) : null,
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $post->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        // Sync tags
        $tags = $request->filled('tags')
            ? array_filter(array_map(trim(...), explode(',', (string) $request->input('tags'))))
            : [];
        app(SyncPostTags::class)->execute($post, $tags);

        // Dispatch translation job
        TranslateBlogPostJob::dispatch($post)->afterResponse();

        return redirect()->route('my.blog.index')->with('success', __('Blog post created successfully!'));
    }

    public function edit(BlogPost $post): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        // Authorization check
        if ($post->author_id !== auth()->id() && (! auth()->user()->isAdmin() && ! auth()->user()->isModerator())) {
            abort(403);
        }

        $categories = BlogCategory::orderBy('sort_order')->get();

        return view('blog.form', ['post' => $post, 'categories' => $categories]);
    }

    public function update(Request $request, BlogPost $post)
    {
        // Authorization check
        if ($post->author_id !== auth()->id() && (! auth()->user()->isAdmin() && ! auth()->user()->isModerator())) {
            abort(403);
        }

        $currentLocale = app()->getLocale();

        $validated = $request->validate([
            "title.{$currentLocale}" => 'required|string|max:255',
            "excerpt.{$currentLocale}" => 'nullable|string|max:500',
            "content.{$currentLocale}" => 'required|string',
            'category_id' => 'required|exists:blog_categories,id',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
            'service_links' => 'nullable|array',
            'service_links.*' => 'exists:service_offerings,id',
            'tags' => 'nullable|string',
        ]);

        // Use setTranslation() to update only the current locale — preserves other language translations
        $titleChanged = $post->getTranslation('title', $currentLocale) !== $validated['title'][$currentLocale];
        $contentChanged = $post->getTranslation('content', $currentLocale) !== $validated['content'][$currentLocale];
        $newExcerpt = $validated['excerpt'][$currentLocale] ?? '';
        $excerptChanged = $post->getTranslation('excerpt', $currentLocale) !== $newExcerpt;

        $post->setTranslation('title', $currentLocale, $validated['title'][$currentLocale]);
        $post->setTranslation('excerpt', $currentLocale, $newExcerpt);
        $post->setTranslation('content', $currentLocale, $validated['content'][$currentLocale]);
        $post->category_id = $validated['category_id'];
        $post->status = $validated['status'];
        $post->is_featured = $request->boolean('is_featured');
        $post->published_at = $validated['status'] === 'published' ? ($validated['published_at'] ?? $post->published_at ?? now()) : null;
        $post->save();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $post->clearMediaCollection('featured_image');
            $post->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        // Sync tags
        $tags = $request->filled('tags')
            ? array_filter(array_map(trim(...), explode(',', (string) $request->input('tags'))))
            : [];
        app(SyncPostTags::class)->execute($post, $tags);

        // Re-translate if content changed in the original source locale
        if (($titleChanged || $contentChanged || $excerptChanged) && $currentLocale === $post->original_locale) {
            TranslateBlogPostJob::dispatch($post)->afterResponse();
        }

        return redirect()->route('my.blog.index')->with('success', __('Blog post updated successfully!'));
    }

    public function rate(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Check if post is published
        if (! $post->isPublished()) {
            return back()->with('error', __('You can only rate published posts.'));
        }

        // Update or create rating
        $blogPostRatingModel = config('blog.models.blog_post_rating', \YourVendor\Blog\Models\BlogPostRating::class);
        $blogPostRatingModel::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'blog_post_id' => $post->id,
            ],
            [
                'rating' => $validated['rating'],
            ]
        );

        return back()->with('success', __('Thank you for rating this article!'));
    }

    public function toggleFavorite(BlogPost $post)
    {
        // Check if post is published
        if (! $post->isPublished()) {
            return back()->with('error', __('You can only favorite published posts.'));
        }

        $user = auth()->user();

        if ($post->isFavoritedByUser($user->id)) {
            // Unfavorite
            $post->favoritedBy()->detach($user->id);
            $message = __('Article removed from your favorites.');
        } else {
            // Favorite
            $post->favoritedBy()->attach($user->id);
            $message = __('Article added to your favorites!');
        }

        return back()->with('success', $message);
    }

    public function destroy(BlogPost $post)
    {
        // Authorization check
        if ($post->author_id !== auth()->id() && (! auth()->user()->isAdmin() && ! auth()->user()->isModerator())) {
            abort(403);
        }

        $post->delete();

        return redirect()->route('my.blog.index')->with('success', __('Blog post deleted successfully!'));
    }

    public function storeComment(\Illuminate\Http\Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        // Check if post is published
        if (! $post->isPublished()) {
            return back()->with('error', __('You can only comment on published posts.'));
        }

        $blogCommentModel = config('blog.models.blog_comment', \YourVendor\Blog\Models\BlogComment::class);

        $comment = $blogCommentModel::create([
            'blog_post_id' => $post->id,
            'author_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'status' => config('blog.comments.auto_approve', false) ? 'approved' : 'pending',
            'approved_at' => config('blog.comments.auto_approve', false) ? now() : null,
        ]);

        return back()->with('success', __('Comment posted successfully!'));
    }

    public function destroyComment(\YourVendor\Blog\Models\BlogComment $comment)
    {
        // Authorization check - only comment author or admins/moderators can delete
        if ($comment->author_id !== auth()->id() && (! auth()->user()->isAdmin() && ! auth()->user()->isModerator())) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', __('Comment deleted successfully!'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // 5MB max
        ]);

        $file = $request->file('file');

        // Store in public disk under blog-content-images
        $path = $file->store('blog-content-images', 'public');

        // Generate public URL
        $url = asset('storage/'.$path);

        return response()->json([
            'url' => $url,
        ]);
    }

    public function trackServiceClick(BlogPost $post, \App\Models\ServiceOffering $offering)
    {
        // Verify this service belongs to the blog post author
        if ($post->author_id !== $offering->freelancer_id) {
            abort(404);
        }

        // Track the click
        app(\App\Actions\Blog\TrackServiceLinkClick::class)->execute(
            $post,
            $offering,
            auth()->id(),
            request()->ip()
        );

        // Redirect to offering page
        return redirect()->route('offerings.show', $offering->id);
    }

    /**
     * Search tags for autocomplete (AJAX endpoint)
     */
    public function searchTags(Request $request)
    {
        $query = $request->get('q', '');
        $locale = app()->getLocale();

        $tags = PostTag::query()
            ->whereRaw("name->>'$locale' ILIKE ?", ["%{$query}%"])
            ->orWhereRaw("name->>'en' ILIKE ?", ["%{$query}%"])
            ->orderBy('usage_count', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($tag): array => [
                'name' => $tag->getTranslation('name', $locale),
                'slug' => $tag->slug,
                'count' => $tag->usage_count,
            ]);

        return response()->json($tags);
    }

    /**
     * Show posts with a specific tag
     */
    public function tag(string $slug): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        ['posts' => $posts, 'categories' => $categories, 'popularTags' => $popularTags] =
            app(GetBlogIndexData::class)->execute(null, $slug, null);

        return view('blog::tag', compact('posts', 'categories', 'popularTags'));
    }
}
