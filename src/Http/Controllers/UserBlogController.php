<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Http\Controllers;

use Cleargoal\Blog\Actions\Blog\GetBlogAnalytics;
use Cleargoal\Blog\Contracts\BlogAuthor;
use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class UserBlogController extends Controller
{
    /**
     * Show blog analytics for the authenticated user.
     */
    public function analytics(): Factory|View
    {
        /** @var BlogAuthor|null $user */
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
    public function index(): Factory|View
    {
        /** @var BlogAuthor|null $user */
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
    public function favorites(): Factory|View
    {
        /** @var BlogAuthor|null $user */
        $user = auth()->user();

        $blogPostModel = config('blog.models.blog_post', BlogPost::class);

        $posts = $blogPostModel::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user?->getId());
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
