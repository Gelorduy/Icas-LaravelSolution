<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('map_viewports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_root')->default(false);
            $table->json('bounds');
            $table->decimal('default_zoom', 8, 4)->default(1.0);
            $table->json('default_pan')->nullable();
            $table->json('layer_overrides')->nullable();
            $table->unsignedInteger('refresh_interval')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['map_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_viewports');
    }
};
