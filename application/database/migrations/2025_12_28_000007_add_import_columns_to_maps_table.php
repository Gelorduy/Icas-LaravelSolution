<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->string('source_dxf_path')->nullable()->after('svg_asset_path');
            $table->string('conversion_status')->default('pending')->after('source_dxf_path');
            $table->text('conversion_notes')->nullable()->after('conversion_status');
        });
    }

    public function down(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropColumn(['source_dxf_path', 'conversion_status', 'conversion_notes']);
        });
    }
};
