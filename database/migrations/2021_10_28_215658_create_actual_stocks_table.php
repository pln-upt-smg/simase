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
            $table->bigInteger('sub_area_id')->unsigned();
            $table->bigInteger('material_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->string('batch')->nullable();
            $table->float('quantity', 24);
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
        Schema::dropIfExists('actual_stocks');
    }
}
