<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experience', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('company_name', 255);
            $table->string('job_title', 255);
            $table->string('location', 255)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->nullable()->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
        });

        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('institution', 255);
            $table->enum('degree', ['high_school', 'associate', 'bachelors', 'masters', 'doctorate', 'certification', 'other']);
            $table->string('field_of_study', 255)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->nullable()->default(0);
            $table->string('grade', 50)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('industry', 255)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('company_size', 50)->nullable();
            $table->integer('founded_year')->nullable();
            $table->timestamps();
            $table->index(['user_id']);
        });

        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->string('location', 255)->nullable();
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship', 'freelance']);
            $table->enum('work_arrangement', ['on_site', 'remote', 'hybrid'])->nullable()->default('on_site');
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->nullable()->default('PHP');
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'executive'])->nullable()->default('entry');
            $table->integer('min_experience_years')->nullable()->default(0);
            $table->enum('education_requirement', ['none', 'high_school', 'associate', 'bachelors', 'masters', 'doctorate'])->nullable()->default('none');
            $table->boolean('is_active')->nullable()->default(1);
            $table->date('deadline')->nullable();
            $table->timestamps();
            $table->index(['company_id']);
            $table->index(['employer_id']);
            $table->index(['is_active']);
            $table->index(['job_type']);
            $table->index(['work_arrangement']);
        });

        Schema::create('job_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->enum('importance', ['required', 'preferred', 'nice_to_have'])->nullable()->default('required');
            $table->timestamps();
            $table->unique(['job_id', 'skill_id']);
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('cover_letter')->nullable();
            $table->string('resume_url', 500)->nullable();
            $table->enum('status', ['pending', 'reviewing', 'shortlisted', 'interview', 'offered', 'accepted', 'rejected', 'withdrawn'])->nullable()->default('pending');
            $table->timestamp('applied_at')->nullable()->useCurrent();
            $table->timestamps();
            $table->unique(['job_id', 'user_id']);
            $table->index(['status']);
        });

        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->timestamp('saved_at')->nullable()->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'job_id']);
        });

        Schema::create('job_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->decimal('match_score', 5, 2)->default(0.00);
            $table->decimal('skill_score', 5, 2)->nullable()->default(0.00);
            $table->decimal('experience_score', 5, 2)->nullable()->default(0.00);
            $table->decimal('education_score', 5, 2)->nullable()->default(0.00);
            $table->timestamp('computed_at')->nullable()->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'job_id']);
            $table->index(['match_score']);
            $table->index(['user_id', 'match_score']);
            $table->index(['job_id', 'match_score']);
        });

        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->nullable()->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();
            $table->unique(['requester_id', 'receiver_id']);
            $table->index(['receiver_id', 'status']);
            $table->index(['requester_id', 'status']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_read')->nullable()->default(0);
            $table->timestamp('sent_at')->nullable()->useCurrent();
            $table->timestamps();
            $table->index(['sender_id', 'receiver_id', 'sent_at']);
            $table->index(['receiver_id', 'is_read']);
        });

        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endorser_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('endorsed_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['endorser_id', 'endorsed_user_id', 'skill_id']);
        });

        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('viewed_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('viewed_at')->nullable()->useCurrent();
            $table->timestamps();
            $table->index(['viewed_id', 'viewed_at']);
            $table->index(['viewer_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
        Schema::dropIfExists('endorsements');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('connections');
        Schema::dropIfExists('job_matches');
        Schema::dropIfExists('saved_jobs');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_skills');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('education');
        Schema::dropIfExists('experience');
    }
};
