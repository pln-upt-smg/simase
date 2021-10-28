<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActualStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('actual_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained();
            $table->string('batch');
            $table->integer('plnt');
            $table->integer('sloc');
            $table->integer('qualinsp');
            $table->float('unrestricted');
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
        Schema::dropIfExists('actual_stocks');
    }
}