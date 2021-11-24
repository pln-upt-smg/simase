<?php

namespace App\Imports\Helper;

use App\Notifications\DataImported;
use App\Notifications\DataImportFailed;
use App\Notifications\DataImportRequested;
use Closure;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;

trait HasDefaultEvents
{
	/**
	 * @return Closure[]
	 */
	public function registerEvents(): array
	{
		return [
			BeforeImport::class => function () {
				if (config('excel.import_overwrite')) {
					$this->overwrite();
				}
				$this->importedBy()?->notify(new DataImportRequested($this->name()));
			},
			AfterImport::class => function () {
				sleep(1);
				$this->importedBy()?->notify(new DataImported($this->name()));
			},
			ImportFailed::class => function (ImportFailed $event) {
				sleep(1);
				$this->importedBy()?->notify(new DataImportFailed($this->name(), $event));
			}
		];
	}
}
