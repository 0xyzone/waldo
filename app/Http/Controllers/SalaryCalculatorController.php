<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SalaryCalculatorController extends Controller
{
    /**
     * Display the standalone interactive Salary & OT Calculator page.
     */
    public function index(Request $request): View
    {
        return view('salary-calculator.index');
    }
}
