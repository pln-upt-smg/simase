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
			$table->foreignId('sub_area_id')->constrained();
			$table->foreignId('product_material_id')->constrained();
			$table->foreignId('user_id')->constrained();
			$table->foreignId('actual_stock_id')->nullable()->index();
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
