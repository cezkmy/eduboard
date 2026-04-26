<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        // Increase execution time and memory for large video uploads
        if ($request->hasFile('media')) {
            ini_set('max_execution_time', '300'); // 5 minutes
            ini_set('memory_limit', '512M');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'nullable|string|in:published,draft',
            'template_id' => 'nullable',
            'bg_color' => 'nullable|string',
            'layout_type' => 'nullable|string',
            'border_radius' => 'nullable|integer',
            'media_layout' => 'nullable|string',
            'font_style' => 'nullable|string',
            'title_color' => 'nullable|string',
            'content_color' => 'nullable|string',
            'category_color' => 'nullable|string',
            'border_color' => 'nullable|string',
            'target_college' => 'nullable|array',
            'target_program' => 'nullable|array',
            'target_year' => 'nullable|array',
            'target_grade_level' => 'nullable|array',
            'target_strand' => 'nullable|array',
            'target_section' => 'nullable|array',
            'target_roles' => 'nullable|array',
        ]);

        $mediaRule = tenant()->hasFeature('video_upload') 
            ? 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:102400' 
            : 'nullable|file|mimes:jpg,jpeg,png,gif|max:10240';

        $request->validate([
            'media.*' => $mediaRule,
        ]);

        if ($request->hasFile('media')) {
            if (tenant()->isStorageFull()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have exceeded your storage limit. Image uploads are temporarily disabled. Please upgrade your storage plan.',
                    'errors' => ['media' => ['Storage limit exceeded.']]
                ], 422);
            }
        }

        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('announcements', 'public');
                $mediaPaths[] = $path;
            }
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('announcements/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension()
                ];
            }
        }

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'General',
            'status' => $validated['status'] ?? 'published',
            'template_id' => $request->template_id,
            'bg_color' => $validated['bg_color'] ?? '#ffffff',
            'layout_type' => $validated['layout_type'] ?? 'landscape',
            'border_radius' => $validated['border_radius'] ?? 24,
            'media_layout' => $validated['media_layout'] ?? 'landscape',
            'font_style' => $validated['font_style'] ?? 'font-sans',
            'title_color' => $validated['title_color'] ?? '#111827',
            'content_color' => $validated['content_color'] ?? '#4b5563',
            'category_color' => $validated['category_color'] ?? '#4b5563',
            'border_color' => $validated['border_color'] ?? 'transparent',
            'posted_by' => auth()->id(),
            'is_pinned' => $request->has('is_pinned'),
            'pinned_at' => $request->has('is_pinned') ? now() : null,
            'target_college' => $request->target_college ?: null,
            'target_program' => $request->target_program ?: null,
            'target_year' => $request->target_year ?: null,
            'target_grade_level' => $request->target_grade_level ?: null,
            'target_strand' => $request->target_strand ?: null,
            'target_section' => $request->target_section ?: null,
            'target_roles' => $request->target_roles ?: null,
            'media_paths' => $mediaPaths,
            'attachments' => $attachments,
        ]);

        $totalBytes = 0;
        if (count($mediaPaths) > 0) {
            foreach ($request->file('media') as $file) {
                $totalBytes += $file->getSize();
            }
        }

        if (count($attachments) > 0) {
            foreach ($request->file('attachments') as $file) {
                $totalBytes += $file->getSize();
            }
        }

        if ($totalBytes > 0) {
            // Efficiently update storage and bandwidth usage
            tenant()->incrementStorageUsage($totalBytes);
            
            $uploadSizeGB = round($totalBytes / 1073741824, 6);
            
            \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('tenants')
                ->where('id', tenant('id'))
                ->increment('bandwidth_used_gb', $uploadSizeGB);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement published successfully!',
                'announcement' => $announcement
            ]);
        }

        return back()->with('success', 'Announcement posted!');
    }

    public function update(Request $request, Announcement $announcement)
    {
        // Increase execution time and memory for large video uploads
        if ($request->hasFile('media')) {
            ini_set('max_execution_time', '300'); // 5 minutes
            ini_set('memory_limit', '512M');
        }

        // Ensure only the author can update
        if ($announcement->posted_by !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'status' => 'nullable|string|in:published,draft',
            'template_id' => 'nullable',
            'bg_color' => 'nullable|string',
            'layout_type' => 'nullable|string',
            'border_radius' => 'nullable|integer',
            'media_layout' => 'nullable|string',
            'font_style' => 'nullable|string',
            'title_color' => 'nullable|string',
            'content_color' => 'nullable|string',
            'category_color' => 'nullable|string',
            'border_color' => 'nullable|string',
            'target_college' => 'nullable|array',
            'target_program' => 'nullable|array',
            'target_year' => 'nullable|array',
            'target_grade_level' => 'nullable|array',
            'target_strand' => 'nullable|array',
            'target_section' => 'nullable|array',
            'target_roles' => 'nullable|array',
        ]);

        $mediaRule = tenant()->hasFeature('video_upload') 
            ? 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:102400' 
            : 'nullable|file|mimes:jpg,jpeg,png,gif|max:10240';

        $request->validate([
            'media.*' => $mediaRule,
        ]);

        if ($request->hasFile('media')) {
            if (tenant()->isStorageFull()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have exceeded your storage limit. Image uploads are temporarily disabled. Please upgrade your storage plan.',
                    'errors' => ['media' => ['Storage limit exceeded.']]
                ], 422);
            }
        }

        $mediaPaths = $announcement->media_paths ?? [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('announcements', 'public');
                $mediaPaths[] = $path;
            }
        }

        $attachments = $announcement->attachments ?? [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('announcements/attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension()
                ];
            }
        }

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'] ?? 'General',
            'status' => $validated['status'] ?? 'published',
            'template_id' => $request->template_id,
            'bg_color' => $validated['bg_color'] ?? '#ffffff',
            'layout_type' => $validated['layout_type'] ?? 'landscape',
            'border_radius' => $validated['border_radius'] ?? 24,
            'media_layout' => $validated['media_layout'] ?? 'landscape',
            'font_style' => $validated['font_style'] ?? 'font-sans',
            'title_color' => $validated['title_color'] ?? '#111827',
            'content_color' => $validated['content_color'] ?? '#4b5563',
            'category_color' => $validated['category_color'] ?? '#4b5563',
            'border_color' => $validated['border_color'] ?? 'transparent',
            'is_pinned' => $request->has('is_pinned'),
            'pinned_at' => $request->has('is_pinned') ? ($announcement->pinned_at ?? now()) : null,
            'target_college' => $request->target_college ?: null,
            'target_program' => $request->target_program ?: null,
            'target_year' => $request->target_year ?: null,
            'target_grade_level' => $request->target_grade_level ?: null,
            'target_strand' => $request->target_strand ?: null,
            'target_section' => $request->target_section ?: null,
            'target_roles' => $request->target_roles ?: null,
            'media_paths' => $mediaPaths,
            'attachments' => $attachments,
        ]);

        $totalBytes = 0;
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $totalBytes += $file->getSize();
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $totalBytes += $file->getSize();
            }
        }

        if ($totalBytes > 0) {
            tenant()->incrementStorageUsage($totalBytes);
            
            $uploadSizeGB = round($totalBytes / 1073741824, 6);
            
            \Illuminate\Support\Facades\DB::connection('mysql')
                ->table('tenants')
                ->where('id', tenant('id'))
                ->increment('bandwidth_used_gb', $uploadSizeGB);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully!',
                'announcement' => $announcement
            ]);
        }

        return back()->with('success', 'Announcement updated!');
    }

    public function destroy(Announcement $announcement)
    {
        // Ensure only the author can delete
        if ($announcement->posted_by !== auth()->id()) {
            abort(403);
        }

        // Delete media files from storage
        if (!empty($announcement->media_paths)) {
            foreach ($announcement->media_paths as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        $announcement->delete();

        // Recalculate usage after deleting files
        tenant()->updateStorageUsage();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully!'
        ]);
    }

    public function comments(Announcement $announcement)
    {        $comments = $announcement->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(10);

        // Transform for human-readable time
        $comments->getCollection()->transform(function ($comment) {
            $comment->human_time = $comment->created_at->diffForHumans();
            $comment->replies->transform(function ($reply) {
                $reply->human_time = $reply->created_at->diffForHumans();
                return $reply;
            });
            return $comment;
        });

        return response()->json($comments);
    }
}
