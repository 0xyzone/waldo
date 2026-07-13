<?php

namespace App\Http\Controllers;

use App\Models\CustomFont;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FontController extends Controller
{
    public function index()
    {
        $fonts = CustomFont::orderBy('family_name')->get();

        return view('letters.fonts', compact('fonts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'family_name' => 'required|string|max:100',
            'font_file' => 'required|file|extensions:ttf,otf,woff,woff2|max:5120',
            'style' => 'required|in:normal,italic',
            'weight' => 'required|in:100,200,300,400,500,600,700,800,900',
        ]);

        $file = $request->file('font_file');
        $originalName = $file->getClientOriginalName();
        $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();

        // Store in storage/app/public/letter-fonts
        $file->storeAs('letter-fonts', $fileName, 'public');

        CustomFont::create([
            'family_name' => $validated['family_name'],
            'file_name' => $fileName,
            'original_name' => $originalName,
            'style' => $validated['style'],
            'weight' => $validated['weight'],
        ]);

        return redirect()->route('letters.fonts')->with('success', 'Font uploaded successfully.');
    }

    public function destroy(CustomFont $font)
    {
        Storage::disk('public')->delete('letter-fonts/'.$font->file_name);
        $font->delete();

        return redirect()->route('letters.fonts')->with('success', 'Font deleted.');
    }

    /**
     * Returns all custom fonts as JSON (used by the editor toolbar).
     */
    public function apiList()
    {
        $fonts = CustomFont::orderBy('family_name')
            ->get(['id', 'family_name', 'file_name', 'style', 'weight'])
            ->map(fn ($f) => [
                'family' => $f->family_name,
                'url' => Storage::disk('public')->url('letter-fonts/'.$f->file_name),
                'style' => $f->style,
                'weight' => $f->weight,
            ]);

        return response()->json($fonts);
    }
}
