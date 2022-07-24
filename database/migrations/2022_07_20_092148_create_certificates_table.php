<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('urban_village_id')->unsigned();
            $table->bigInteger('sub_district_id')->unsigned();
            $table->bigInteger('district_id')->unsigned();
            $table->bigInteger('province_id')->unsigned();
            $table->bigInteger('holder_id')->unsigned();
            $table->string('name');
            $table->string('area_code');
            $table->string('certificate_type');
            $table->string('certificate_number');
            $table->string('certificate_print_number');
            $table->timestamp('certificate_bookkeeping_date')->useCurrent();
            $table->timestamp('certificate_publishing_date')->useCurrent();
            $table->timestamp('certificate_final_date')->useCurrent();
            $table->string('nib');
            $table->string('origin_right_category');
            $table->string('base_registration_decree_number');
            $table->string('base_registration_date');
            $table->string('measuring_letter_number');
            $table->timestamp('measuring_letter_date')->useCurrent();
            $table->boolean('measuring_letter_status');
            $table->boolean('field_map_status');
            $table->decimal('wide', 20, 10);
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
        Schema::dropIfExists('certificates');
    }
}
