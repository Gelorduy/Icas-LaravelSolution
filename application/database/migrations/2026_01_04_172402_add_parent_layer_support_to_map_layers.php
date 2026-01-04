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
        Schema::table('map_layers', function (Blueprint $table) {
            $table->foreignId('parent_layer_id')->nullable()->after('map_id')->constrained('map_layers')->nullOnDelete();
            $table->json('element_types')->nullable()->after('layer_type')->comment('Types of elements this layer contains: svg_icon, text, marker, polygon, etc');
            $table->json('related_layers')->nullable()->after('element_types')->comment('Layer IDs that this layer depends on or controls');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_layers', function (Blueprint $table) {
            $table->dropForeign(['parent_layer_id']);
            $table->dropColumn(['parent_layer_id', 'element_types', 'related_layers']);
        });
    }
};
