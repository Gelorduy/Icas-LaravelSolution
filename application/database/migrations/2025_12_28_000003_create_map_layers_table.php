<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('map_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('display_name');
            $table->string('layer_type');
            $table->integer('z_index')->default(0);
            $table->boolean('default_visible')->default(true);
            $table->json('style_preset')->nullable();
            $table->json('data_source')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['map_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_layers');
    }
};
