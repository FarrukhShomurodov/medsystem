<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SisterWindowController extends Controller
{
    public function index(): View
    {
        return view('admin.sister_window.index');
    }
}
