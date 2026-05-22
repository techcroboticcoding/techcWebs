<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $fillable = ['type', 'total'];
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('todos', function (Blueprint $table) {
    $table->id();
    $table->string('type'); // tagihan, penggantian, dll
    $table->integer('total')->default(0);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
