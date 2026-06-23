<?php

namespace App\Jobs;

use App\Models\ScheduledPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessSocialMediaPost implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [300, 600, 900];

    public function __construct(
        protected ScheduledPost $post
    ) {}

    public function handle(): void
    {
        try {
            // Update status ke processing
            $this->post->update([
                'status' => 'processing',
                'attempts' => $this->post->attempts + 1,
            ]);

            // Proses sesuai platform
            $response = match($this->post->platform) {
                'facebook' => $this->postToFacebook(),
                'instagram' => $this->postToInstagram(),
                'twitter' => $this->postToTwitter(),
                'linkedin' => $this->postToLinkedin(),
                default => throw new \Exception("Platform tidak didukung"),
            };

            if ($response->successful()) {
                $this->post->update([
                    'status' => 'posted',
                    'posted_at' => now(),
                    'response_data' => $response->json(),
                ]);

                Log::info("✅ Posting berhasil ke {$this->post->platform}", [
                    'post_id' => $this->post->id,
                    'user_id' => $this->post->user_id,
                ]);
            } else {
                throw new \Exception('API Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->handleFailure($e);
        }
    }

    protected function postToFacebook()
    {
        // TODO: Implementasi Facebook API
        // Simulasi dulu
        return Http::fake()->post('https://graph.facebook.com/v18.0/me/feed', [
            'access_token' => config('services.facebook.access_token'),
            'message' => $this->post->content,
        ]);
    }

    protected function postToTwitter()
    {
        return Http::fake()->post('https://api.twitter.com/2/tweets', [
            'text' => $this->post->content,
        ]);
    }

    protected function postToInstagram()
    {
        return Http::fake()->post('https://graph.instagram.com/me/media', [
            'access_token' => config('services.instagram.access_token'),
            'caption' => $this->post->content,
        ]);
    }

    protected function postToLinkedin()
    {
        return Http::fake()->post('https://api.linkedin.com/v2/ugcPosts', [
            'author' => 'urn:li:person:xxxxx',
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $this->post->content,
                    ],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ]);
    }

    protected function handleFailure(\Exception $e): void
    {
        $attempts = $this->post->attempts;

        if ($attempts < 3) {
            $this->post->update([
                'status' => 'pending',
                'next_retry_at' => now()->addMinutes(5 * $attempts),
                'error_message' => $e->getMessage(),
            ]);

            $this->release(300 * $attempts);

            Log::warning("🔄 Retry {$attempts}/3 untuk post {$this->post->id}", [
                'error' => $e->getMessage(),
            ]);
        } else {
            $this->post->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error("❌ Posting gagal total untuk post {$this->post->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}