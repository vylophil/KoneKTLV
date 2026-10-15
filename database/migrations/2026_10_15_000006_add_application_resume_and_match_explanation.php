<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            $table->string('resume_path', 500)->nullable();
        });
        Schema::table('job_matches', function (Blueprint $table): void {
            $table->json('matched_terms')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', fn (Blueprint $table) => $table->dropColumn('resume_path'));
        Schema::table('job_matches', fn (Blueprint $table) => $table->dropColumn('matched_terms'));
    }
};
