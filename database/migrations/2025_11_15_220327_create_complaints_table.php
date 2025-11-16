<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('title');
            $table->longText('content');
            $table->text('resolution_expectation')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam', 'in_progress', 'resolved', 'closed'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            $table->foreignId('moderator_id')->nullable()->constrained('users');
            $table->timestamp('moderated_at')->nullable();
            $table->timestamp('brand_notified_at')->nullable();
            $table->timestamp('brand_first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            $table->integer('spam_score')->default(0);
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('neutral');
            $table->decimal('sentiment_score', 3, 2)->default(0.00);
            $table->boolean('is_resolved')->default(false);
            $table->integer('customer_satisfaction_rating')->nullable();
            $table->text('customer_satisfaction_comment')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
