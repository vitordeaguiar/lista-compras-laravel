<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('user_settings', function (Blueprint $table) {
            // 'insertion' = ordem em que os itens foram adicionados; 'alphabetical' = A→Z
            $table->string('list_item_order', 20)->default('insertion')->after('layout_density');
        });
    }

    public function down(): void {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn('list_item_order');
        });
    }
};
