<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Actions\Blog;

use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;

class SyncPostTags
{
    /**
     * Sync tags to a blog post and update usage counts
     */
    public function execute(BlogPost $post, array $tagNames): void
    {
        $locale = app()->getLocale();
        $tagIds = [];

        // Track old tags for usage count adjustment
        $oldTagIds = $post->tags()->pluck('post_tags.id')->toArray();

        // Find or create tags and collect their IDs
        foreach ($tagNames as $tagName) {
            if (in_array(trim((string) $tagName), ['', '0'], true)) {
                continue;
            }

            $tag = PostTag::findOrCreateByName(trim((string) $tagName), $locale);
            $tagIds[] = $tag->id;
        }

        // Sync tags (attach new, detach removed)
        $post->tags()->sync($tagIds);

        // Update usage counts for affected tags
        $this->updateUsageCounts($oldTagIds, $tagIds);
    }

    /**
     * Update usage counts for tags that were added or removed
     */
    private function updateUsageCounts(array $oldTagIds, array $newTagIds): void
    {
        // Decrement usage count for removed tags
        $removedTagIds = array_diff($oldTagIds, $newTagIds);
        if ($removedTagIds !== []) {
            PostTag::whereIn('id', $removedTagIds)->each(fn ($tag) => $tag->decrementUsage());
        }

        // Increment usage count for added tags
        $addedTagIds = array_diff($newTagIds, $oldTagIds);
        if ($addedTagIds !== []) {
            PostTag::whereIn('id', $addedTagIds)->each(fn ($tag) => $tag->incrementUsage());
        }
    }
}
