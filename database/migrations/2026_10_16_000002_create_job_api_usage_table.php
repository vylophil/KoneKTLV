<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_api_usage', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 30);
            $table->string('period', 7);
            $table->unsignedInteger('requests')->default(0);
            $table->unique(['provider', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_api_usage');
    }
};
