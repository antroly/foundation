<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index();
            $table->text('message');
            $table->json('context')->nullable();
            $table->string('url')->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
