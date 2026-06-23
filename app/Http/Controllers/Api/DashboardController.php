<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');
        
        // 🔥 UPDATE SCHEDULED YANG SUDAH LEWAT
        $scheduledPosts = Post::where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();
        
        foreach ($scheduledPosts as $post) {
            $scheduledTime = Carbon::parse($post->scheduled_at);
            $diffInMinutes = $scheduledTime->diffInMinutes($now);
            
            if ($diffInMinutes <= 60) {
                $post->status = 'published';
                $post->published_at = $now;
            } else {
                $post->status = 'failed';
                $post->published_at = null;
            }
            $post->save();
        }

        return response()->json([
            'total_posts' => Post::where('user_id', $user->id)->count(),
            'scheduled_posts' => Post::where('user_id', $user->id)->where('status', 'scheduled')->count(),
            'published_posts' => Post::where('user_id', $user->id)->where('status', 'published')->count(),
            'draft_posts' => Post::where('user_id', $user->id)->where('status', 'draft')->count(),
            'failed_posts' => Post::where('user_id', $user->id)->where('status', 'failed')->count(),
        ]);
    }

    // ✅ PERBAIKAN: Kembalikan data dalam format yang benar
    public function postsByDate(Request $request)
    {
        $user = $request->user();
        
        // 🔥 AMBIL TOTAL POST PER TANGGAL
        $posts = Post::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // 🔥 FORMAT KE { "2024-01-15": 3, "2024-01-16": 1, ... }
        $result = [];
        foreach ($posts as $post) {
            $result[$post->date] = $post->count;
        }
        
        return response()->json($result);
    }
}