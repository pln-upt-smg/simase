<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained();
            $table->foreignId('period_id')->constrained();
            $table->string('code');
            $table->string('description');
            $table->string('uom');
            $table->string('mtyp');
            $table->string('crcy');
            $table->bigInteger('price');
            $table->bigInteger('per');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
}
