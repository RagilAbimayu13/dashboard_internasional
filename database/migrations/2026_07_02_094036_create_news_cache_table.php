<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_cache', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('source_url');
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->string('category')->nullable(); // logistics, trade, geopolitics
            $table->string('sentiment')->nullable(); // positive, neutral, negative
            $table->integer('positive_score')->default(0);
            $table->integer('negative_score')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_cache');
    }
};