<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CandidatePrintController extends Controller
{
    /**
     * Display a printable view of candidate CV images.
     */
    public function print(Candidate $candidate, Request $request): View
    {
        $candidate->loadMissing('department');
        $images = $candidate->getCvImageUrls();
        $includeHeader = $request->boolean('header', true);

        return view('candidates.print', [
            'candidate' => $candidate,
            'images' => $images,
            'includeHeader' => $includeHeader,
        ]);
    }
}
