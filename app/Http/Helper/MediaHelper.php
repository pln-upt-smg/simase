<?php

namespace App\Http\Helper;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

abstract class MediaHelper
{
    public const SPREADSHEET_MAX_SIZE = '51200'; // 50MB

    /**
     * @throws Throwable
     */
    public static function importSpreadsheet(Request $request, mixed $importable = null, string $attr = 'file', bool $required = true): void
    {
        $attr = trim($attr);
        Validator::make($request->all(), [
            $attr => [$required ? 'required' : 'nullable', 'mimes:xls,xlsx,csv', 'max:' . self::SPREADSHEET_MAX_SIZE]
        ])->validate();
        if (!is_null($importable)) {
            Excel::import($importable, $request->file($attr));
        }
    }

    /**
     * @throws Throwable
     */
    public static function exportSpreadsheet(mixed $exportable, Model|string $filename, string $format = 'xlsx'): BinaryFileResponse
    {
        if ($filename instanceof Model) {
            $filename = $filename->getTable();
        }
        $filename = trim($filename);
        $format = Str::lower(trim($format));
        $contentType = match ($format) {
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            default => null
        };
        return Excel::download($exportable, $filename . ".$format", headers: is_null($contentType) ? [] : ["Content-Type: $contentType"]);
    }
}
