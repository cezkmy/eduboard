<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Comment;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementInteractionController extends Controller
{
    /**
     * Get paginated comments.
     */
    public function comments(Announcement $announcement)
    {
        $comments = $announcement->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(10);

        // Transform for human-readable time
        $comments->getCollection()->transform(function ($comment) {
            $comment->human_time = $comment->created_at->diffForHumans();
            if ($comment->replies) {
                $comment->replies->transform(function ($reply) {
                    $reply->human_time = $reply->created_at->diffForHumans();
                    return $reply;
                });
            }
            return $comment;
        });

        return response()->json($comments);
    }

    /**
     * Store a new comment.
     */
    public function storeComment(Request $request, Announcement $announcement)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = $announcement->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
            'message' => 'Comment added successfully'
        ]);
    }

    /**
     * Handle reaction (like, heart, fire, sad).
     */
    public function toggleReaction(Request $request, Announcement $announcement)
    {
        $request->validate([
            'type' => 'required|string|in:heart,like,fire,sad'
        ]);

        $type = $request->type;
        $userId = Auth::id();

        // Check if the user already has this specific reaction
        $existingReaction = Reaction::where('announcement_id', $announcement->id)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($existingReaction) {
            // Remove the reaction
            $existingReaction->delete();
            $this->updateReactionCount($announcement, $type, -1);
            $active = false;
        } else {
            // Add the reaction
            Reaction::create([
                'announcement_id' => $announcement->id,
                'user_id' => $userId,
                'type' => $type
            ]);
            $this->updateReactionCount($announcement, $type, 1);
            $active = true;
        }

        return response()->json([
            'success' => true,
            'active' => $active,
            'count' => $announcement->refresh()->{$type . '_count'}
        ]);
    }

    /**
     * Update the reaction count in the announcements table.
     */
    protected function updateReactionCount(Announcement $announcement, string $type, int $increment)
    {
        $column = $type . '_count';
        $announcement->increment($column, $increment);
    }
}
