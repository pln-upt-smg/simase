<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('book_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained();
            $table->string('batch');
            $table->bigInteger('quantity');
            $table->bigInteger('plnt');
            $table->bigInteger('sloc');
            $table->float('unrestricted');
            $table->bigInteger('qualinsp');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('book_stocks');
    }
}
