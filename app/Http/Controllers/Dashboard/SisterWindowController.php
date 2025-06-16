<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Contracts\View\View;

class SisterWindowController extends Controller
{
    public function index(): View
    {
        $patients = Patient::query()->paginate(10);

        return view('admin.sister_window.index', compact('patients'));
    }
}
