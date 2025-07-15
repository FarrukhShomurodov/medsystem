<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\ServiceImportValidationException;
use App\Http\Controllers\Controller;
use App\Imports\DangerousCombinationImport;
use App\Models\DangerousCombination;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DangerousCombinationController extends Controller
{
    public function showImportServices(): View
    {
        $combinations = DangerousCombination::query()->get();

        return view('admin.medications.import', compact('combinations'));
    }

    public function importServices(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new DangerousCombinationImport(), $request->file('file'));
        } catch (ServiceImportValidationException $e) {
            return back()->withErrors(['import_error' => $e->getMessage()]);
        }

        return redirect()->route('medications.index')->with('success', 'Успешно импортированы.');
    }
}
