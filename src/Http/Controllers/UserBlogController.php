<?php

declare(strict_types=1);

namespace YourVendor\Blog\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use YourVendor\Blog\Actions\Blog\GetBlogAnalytics;
use YourVendor\Blog\Contracts\BlogAuthor;

class UserBlogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(config('blog.authorization.middleware.user', ['web', 'auth', 'verified'])),
        ];
    }

    /**
     * Show blog analytics for the authenticated user.
     */
    public function analytics(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = auth()->user();

        // Verify user implements BlogAuthor interface
        if (! $user instanceof BlogAuthor) {
            abort(403, 'User must implement BlogAuthor interface to access blog analytics.');
        }

        // Only users who can manage blog posts can view analytics
        if (! $user->canManageBlogPosts()) {
            abort(403, 'You do not have permission to access blog analytics.');
        }

        $analytics = app(GetBlogAnalytics::class)->execute($user);

        return view('blog::analytics', ['analytics' => $analytics]);
    }

    /**
     * Show user's blog posts management page.
     */
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = auth()->user();

        if (! $user instanceof BlogAuthor) {
            abort(403, 'User must implement BlogAuthor interface.');
        }

        // Users can always view their own blog posts
        $posts = $user->blogPosts()
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(config('blog.pagination.per_page', 15));

        return view('blog::user.index', [
            'posts' => $posts,
            'user' => $user,
        ]);
    }

    /**
     * Show user's favorited blog posts.
     */
    public function favorites(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $user = auth()->user();

        $blogPostModel = config('blog.models.blog_post', \YourVendor\Blog\Models\BlogPost::class);

        $posts = $blogPostModel::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['category', 'tags'])
            ->latest()
            ->paginate(config('blog.pagination.per_page', 15));

        return view('blog::user.favorites', [
            'posts' => $posts,
            'user' => $user,
        ]);
    }
}
