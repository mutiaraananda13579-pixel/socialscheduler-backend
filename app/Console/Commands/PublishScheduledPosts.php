<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledPost;  // ← GANTI ke ScheduledPost
use Carbon\Carbon;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish';

    protected $description = 'Update scheduled posts that have passed';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');

        $this->info("=================================");
        $this->info("Waktu sekarang : " . $now->toDateTimeString());
        $this->info("=================================");

        // Cari di tabel scheduled_posts dengan status pending
        $posts = ScheduledPost::where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $now)
            ->get();

        $this->info("Total post ditemukan: " . $posts->count());

        foreach ($posts as $post) {
            try {
                $post->update([
                    'status' => 'published',
                    'posted_at' => $now,
                ]);
                $this->info("✅ POST ID: {$post->id} => PUBLISHED!");
            } catch (\Exception $e) {
                $post->update([
                    'status' => 'failed',
                    'failed_at' => $now,
                    'error_message' => $e->getMessage()
                ]);
                $this->error("❌ POST ID: {$post->id} => FAILED: {$e->getMessage()}");
            }
        }

        $this->info("=================================");
        $this->info("Selesai. Total diproses: " . $posts->count());
        $this->info("=================================");

        return Command::SUCCESS;
    }
}