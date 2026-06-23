<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');
        
        // 🔥 UPDATE SCHEDULED YANG SUDAH LEWAT
        $scheduledPosts = $user->posts()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();
        
        foreach ($scheduledPosts as $post) {
            $post->status = 'failed';
            $post->published_at = null;
            $post->save();
        }
        
        return response()->json(
            $user->posts()
                ->latest()
                ->get()
        );
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');
        
        // 🔥 UPDATE SCHEDULED YANG SUDAH LEWAT
        $scheduledPosts = $user->posts()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();
        
        foreach ($scheduledPosts as $post) {
            $post->status = 'failed';
            $post->published_at = null;
            $post->save();
        }

        return response()->json([
            'total_posts' => $user->posts()->count(),
            'scheduled_posts' => $user->posts()->where('status', 'scheduled')->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'draft_posts' => $user->posts()->where('status', 'draft')->count(),
            'failed_posts' => $user->posts()->where('status', 'failed')->count(),
        ]);
    }

    public function show(string $id)
    {
        $post = Post::findOrFail($id);
        $now = Carbon::now('Asia/Jakarta');
        
        // 🔥 UPDATE SCHEDULED YANG SUDAH LEWAT
        if ($post->status === 'scheduled' && $post->scheduled_at <= $now) {
            $post->status = 'failed';
            $post->published_at = null;
            $post->save();
        }

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'status' => 'required|in:draft,scheduled,published,failed',
            'scheduled_at' => 'nullable|date',
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'status' => $validated['status'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Post berhasil dibuat',
            'post' => $post
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'status' => 'required|in:draft,scheduled,published,failed',
            'scheduled_at' => 'nullable|date',
        ]);

        $post->update($validated);

        return response()->json([
            'message' => 'Post berhasil diupdate',
            'post' => $post
        ]);
    }

    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'message' => 'Post berhasil dihapus'
        ]);
    }
}