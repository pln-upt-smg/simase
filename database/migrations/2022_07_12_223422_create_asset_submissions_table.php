<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_submissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('asset_id')->unsigned();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('quantity');
            $table->text('note');
            $table->tinyInteger('priority');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_submissions');
    }
}
