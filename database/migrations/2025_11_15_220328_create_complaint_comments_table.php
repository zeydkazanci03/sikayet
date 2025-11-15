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
        Schema::create('complaint_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->enum('user_type', ['customer', 'company', 'admin'])->default('customer');
            $table->boolean('is_official_response')->default(false);
            $table->boolean('is_solution')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->index('complaint_id');
            $table->index('user_id');
            $table->index(['is_official_response', 'is_approved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_comments');
    }
};
