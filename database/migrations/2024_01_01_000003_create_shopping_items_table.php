<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shopping_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->nullable();
            $table->decimal('qty', 8, 3)->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('purchased')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shopping_items'); }
};
