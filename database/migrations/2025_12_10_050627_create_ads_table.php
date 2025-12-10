<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['image', 'html'])->default('image');
            $table->string('position');
            $table->enum('page', ['all', 'homepage', 'article', 'category'])->default('all');

            // Image ad fields
            $table->string('image_path')->nullable();
            $table->string('click_url')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('open_in_new_tab')->default(true);

            // HTML ad fields
            $table->text('html_content')->nullable();

            // Display settings
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes for performance
            $table->index(['position', 'page', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
