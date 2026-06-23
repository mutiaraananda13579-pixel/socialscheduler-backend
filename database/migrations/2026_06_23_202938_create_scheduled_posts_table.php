<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // facebook, twitter, instagram, linkedin
            $table->text('content');
            $table->json('media')->nullable();
            $table->datetime('scheduled_at');
            
            // Status tracking
            $table->string('status')->default('pending');
            $table->datetime('posted_at')->nullable();
            $table->datetime('failed_at')->nullable();
            
            // Response & error
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            
            // Retry
            $table->integer('attempts')->default(0);
            $table->datetime('next_retry_at')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['scheduled_at', 'status']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_posts');
    }
};