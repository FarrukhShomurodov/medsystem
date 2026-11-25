<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportSpreadsheetRequest;
use App\Imports\DangerousCombinationImport;
use App\Models\DangerousCombination;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;

/**
 * The drug interaction list behind the prescription warnings, imported from Excel.
 */
class DangerousCombinationController extends Controller
{
    /** More than the catalogues' ten: the real list has about 2 450 rows. */
    private const int PER_PAGE = 50;

    public function showImport(): View
    {
        $combinations = DangerousCombination::query()->orderBy('id')->paginate(self::PER_PAGE);

        return view('admin.medications.import', compact('combinations'));
    }

    public function import(ImportSpreadsheetRequest $request): RedirectResponse
    {
        $import = new DangerousCombinationImport;
        Excel::import($import, $request->file('file'));

        return $import->report()->flashTo(to_route('medications.index'));
    }
}
