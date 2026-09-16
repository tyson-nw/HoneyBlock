<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honeyblock_requests', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip');
            $table->string('trap');
            $table->index('ip');
            $table->timestamps();
        });

        Schema::create('honeyblock_whitelist', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honeyblock_requests');
        Schema::dropIfExists('honeyblock_whitelist');
    }
};
