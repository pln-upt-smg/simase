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
            $table->bigInteger('area_id')->unsigned();
            $table->bigInteger('material_id')->unsigned();
            $table->string('batch')->nullable();
            $table->float('quantity', 24);
            $table->float('unrestricted', 24);
            $table->bigInteger('plnt');
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
