<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('token_id')->nullable()->index();
            $table->string('token_name')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('method', 10);
            $table->string('path');
            $table->string('ip', 45)->nullable();
            $table->string('idempotency_key')->nullable()->index();
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('status');
            $table->json('response_meta')->nullable();
            $table->timestamps();

            $table->index(['method', 'path']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_audits');
    }
};
