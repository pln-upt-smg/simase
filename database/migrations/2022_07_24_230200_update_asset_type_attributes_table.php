<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAssetTypeAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('asset_type_attributes', function (Blueprint $table) {
            $table->dropColumn('attributes');
            $table->text('custom_attributes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asset_type_attributes', function (Blueprint $table) {
            $table->dropColumn('custom_attributes');
            $table->text('attributes');
        });
    }
}
