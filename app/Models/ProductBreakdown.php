<?php

namespace App\Models;

use Based\Fluent\Fluent;
use Based\Fluent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Throwable;

class ProductBreakdown extends Model
{
	use Fluent, HasFactory, SoftDeletes;

	#[BelongsTo]
	public SubArea $subArea;

	#[BelongsTo]
	public ProductMaterial $productMaterial;

	#[BelongsTo]
	public User $user;

	#[BelongsTo]
	public ActualStock $actualStock;

	public ?string $batch;
	public float $quantity;

	protected $fillable = [
		'sub_area_id',
		'product_material_id',
		'user_id',
		'actual_stock_id',
		'batch'
	];

	/**
	 * @throws Throwable
	 */
	public function convertAsActualStock(string $batch): ActualStock
	{
		if ($this->isConvertedAsActualStock()) {
			return $this->load('actualStock')->actualStock;
		}
		$this->load(['subArea', 'productMaterial', 'user']);
		$actualStock = ActualStock::create([
			'sub_area_id' => $this->subArea->id,
			'material_id' => $this->productMaterial->load('material')->material->id,
			'user_id' => $this->user->id,
			'batch' => Str::upper($batch),
			'quantity' => $this->productMaterial->material_quantity
		]);
		$this->updateOrFail([
			'actual_stock_id' => $actualStock->id,
			'batch' => Str::upper($batch)
		]);
		return $actualStock;
	}

	public function isConvertedAsActualStock(): bool
	{
		return !is_null($this->actual_stock_id);
	}
}
