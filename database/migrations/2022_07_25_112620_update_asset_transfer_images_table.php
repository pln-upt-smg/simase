<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssetTransferImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_transfer_images', function (Blueprint $table) {
            $table->dropColumn('asset_transfer_image_id');
            $table->bigInteger('asset_transfer_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_transfer_images', function (Blueprint $table) {
            $table->dropColumn('asset_transfer_id');
            $table->bigInteger('asset_transfer_image_id')->unsigned();
        });
    }
}
