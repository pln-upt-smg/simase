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
            $table->foreignId('sub_area_id')->constrained();
            $table->foreignId('material_id')->constrained();
            $table->foreignId('user_id')->constrained();
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
