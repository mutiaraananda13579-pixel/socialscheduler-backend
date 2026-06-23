<?php

namespace App\Console\Commands;

use App\Models\ScheduledPost;
use App\Jobs\ProcessSocialMediaPost;
use Illuminate\Console\Command;

class ProcessScheduledPosts extends Command
{
    protected $signature = 'posts:process';
    protected $description = 'Process scheduled social media posts';

    public function handle()
    {
        $posts = ScheduledPost::where('scheduled_at', '<=', now())
            ->where('status', 'pending')
            ->orWhere(function($q) {
                $q->where('status', 'failed')
                  ->where('attempts', '<', 3)
                  ->where('next_retry_at', '<=', now());
            })
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();

        if ($posts->isEmpty()) {
            $this->info('Tidak ada post yang perlu diproses.');
            return 0;
        }

        $this->info("📝 Memproses {$posts->count()} posting...");

        foreach ($posts as $post) {
            ProcessSocialMediaPost::dispatch($post);
            $this->line("  → Post ID {$post->id} di-dispatch ke queue");
        }

        $this->info('✅ Semua post telah di-dispatch!');
        return 0;
    }
}