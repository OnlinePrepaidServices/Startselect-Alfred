<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alfred_preferences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('owner_id')->index();
            $table->unsignedTinyInteger('type');
            $table->json('data');
            $table->timestamps();

            $table->index(['owner_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alfred_preferences');
    }
};