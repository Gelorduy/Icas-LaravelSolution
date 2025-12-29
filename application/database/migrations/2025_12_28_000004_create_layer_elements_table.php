<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('layer_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained('map_layers')->cascadeOnDelete();
            $table->string('element_type');
            $table->json('geometry');
            $table->json('payload')->nullable();
            $table->json('state')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('element_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layer_elements');
    }
};
