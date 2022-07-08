<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBreakdownsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		Schema::create('product_breakdowns', function (Blueprint $table) {
			$table->id();
			$table->bigInteger('sub_area_id')->unsigned();
			$table->bigInteger('product_material_id')->unsigned();
			$table->bigInteger('user_id')->unsigned();
			$table->bigInteger('actual_stock_id')->unsigned()->nullable();
			$table->string('batch')->nullable();
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
		Schema::dropIfExists('product_breakdowns');
	}
}
