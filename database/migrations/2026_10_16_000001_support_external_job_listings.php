<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_postings', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->change();
            $table->foreignId('employer_id')->nullable()->change();
            $table->string('job_type')->nullable()->change();
            $table->string('source', 30)->default('local')->index();
            $table->string('source_key', 64)->nullable()->unique();
            $table->text('external_id')->nullable();
            $table->string('external_company_name')->nullable();
            $table->text('apply_url')->nullable();
            $table->string('publisher')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('salary_period', 10)->nullable();
            $table->text('salary_text')->nullable();
            $table->timestamp('source_posted_at')->nullable();
            $table->timestamp('fetched_at')->nullable();
        });
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('status', 30)->nullable()->default('pending')->change();
            $table->timestamp('opened_at')->nullable();
            $table->boolean('self_reported')->default(false);
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        // Preserve nullable ownership and expanded statuses so imported/history rows remain valid.
        Schema::table('job_applications', fn (Blueprint $table) => $table->dropColumn(['opened_at', 'self_reported', 'notes']));
        Schema::table('job_postings', function (Blueprint $table): void {
            $table->dropUnique(['source_key']);
            $table->dropIndex(['source']);
            $table->dropColumn([
                'source', 'source_key', 'external_id', 'external_company_name', 'apply_url', 'publisher', 'country',
                'salary_period', 'salary_text', 'source_posted_at', 'fetched_at',
            ]);
        });
    }
};
