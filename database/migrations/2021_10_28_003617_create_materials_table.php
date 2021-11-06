<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
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
        Schema::dropIfExists('materials');
    }
}
