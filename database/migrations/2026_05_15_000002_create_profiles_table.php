<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('profiles')) {
            Schema::create('profiles', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('full_name', 100);
                $table->text('bio')->nullable();
                $table->string('avatar_url', 500)->nullable();
                $table->string('resume_url', 500)->nullable();
                $table->string('linkedin_url', 500)->nullable();
                $table->string('portfolio_url', 500)->nullable();
                $table->string('timezone', 50)->default('UTC');
                $table->decimal('total_credits_earned', 12, 2)->default(0);
                $table->decimal('total_credits_spent', 12, 2)->default(0);
                $table->decimal('response_rate', 5, 2)->nullable();
                $table->unsignedInteger('sessions_completed_as_mentor')->default(0);
                $table->unsignedInteger('sessions_completed_as_learner')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
