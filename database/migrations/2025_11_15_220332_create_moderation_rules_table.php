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
        Schema::create('moderation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['keyword', 'regex', 'spam', 'profanity'])->default('keyword');
            $table->text('pattern');
            $table->enum('action', ['flag', 'block', 'auto_reject', 'quarantine'])->default('flag');
            $table->enum('scope', ['complaints', 'comments', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index(['scope', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderation_rules');
    }
};
