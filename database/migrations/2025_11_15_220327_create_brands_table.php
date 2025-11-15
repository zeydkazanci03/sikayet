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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('trade_registry_number')->nullable();
            $table->integer('founded_year')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->enum('subscription_type', ['free', 'basic', 'premium', 'enterprise'])->default('free');
            $table->timestamp('subscription_start')->nullable();
            $table->timestamp('subscription_end')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->index();
            $table->text('rejection_reason')->nullable();
            $table->integer('complaint_count')->default(0);
            $table->integer('resolved_complaint_count')->default(0);
            $table->decimal('resolution_rate', 5, 2)->default(0.00);
            $table->decimal('avg_resolution_time', 8, 2)->default(0.00);
            $table->decimal('avg_response_time', 8, 2)->default(0.00);
            $table->decimal('customer_satisfaction', 3, 2)->default(0.00);
            $table->integer('index_score')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index('category_id');
            $table->index('slug');
            $table->index('subscription_type');
            $table->fullText(['name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
