<?php

namespace App\Http\Controllers;

use App\Models\LetterGlobalVariable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LetterGlobalVariableController extends Controller
{
    /**
     * Display a listing of global variables.
     */
    public function index(Request $request): View
    {
        $query = LetterGlobalVariable::query()->orderBy('label');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $variables = $query->paginate(15)->withQueryString();

        return view('letters.variables', compact('variables'));
    }

    /**
     * Store a newly created global variable in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|alpha_dash|max:100|unique:letter_global_variables,key',
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,date,daterange,number,boolean,dropdown,richtext,calculated',
            'is_permanent' => 'nullable|boolean',
            'default_value' => 'nullable|string',
            'options' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'formulas' => 'nullable|array',
            'formulas.*.key' => 'required_with:formulas|string',
            'formulas.*.label' => 'nullable|string',
            'formulas.*.expression' => 'required_with:formulas|string',
        ]);

        $variable = LetterGlobalVariable::create([
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_permanent' => $request->boolean('is_permanent'),
            'default_value' => $validated['default_value'] ?? null,
            'options' => $validated['options'] ?? null,
            'description' => $validated['description'] ?? null,
            'formulas' => $validated['formulas'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Global variable created successfully.',
                'variable' => $variable,
            ]);
        }

        return redirect()->route('letters.variables.index')->with('success', 'Global variable created successfully.');
    }

    /**
     * Update the specified global variable in storage.
     */
    public function update(Request $request, LetterGlobalVariable $variable): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|alpha_dash|max:100|unique:letter_global_variables,key,'.$variable->id,
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,date,daterange,number,boolean,dropdown,richtext,calculated',
            'is_permanent' => 'nullable|boolean',
            'default_value' => 'nullable|string',
            'options' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'formulas' => 'nullable|array',
            'formulas.*.key' => 'required_with:formulas|string',
            'formulas.*.label' => 'nullable|string',
            'formulas.*.expression' => 'required_with:formulas|string',
        ]);

        $variable->update([
            'key' => $validated['key'],
            'label' => $validated['label'],
            'type' => $validated['type'],
            'is_permanent' => $request->boolean('is_permanent'),
            'default_value' => $validated['default_value'] ?? null,
            'options' => $validated['options'] ?? null,
            'description' => $validated['description'] ?? null,
            'formulas' => $validated['formulas'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Global variable updated successfully.',
                'variable' => $variable,
            ]);
        }

        return redirect()->route('letters.variables.index')->with('success', 'Global variable updated successfully.');
    }

    /**
     * Remove the specified global variable from storage.
     */
    public function destroy(LetterGlobalVariable $variable): RedirectResponse
    {
        $variable->delete();

        return redirect()->route('letters.variables.index')->with('success', 'Global variable deleted.');
    }

    /**
     * Return all global variables as JSON for letter studio Alpine component.
     */
    public function apiList(): JsonResponse
    {
        $variables = LetterGlobalVariable::orderBy('label')->get();

        return response()->json($variables);
    }
}
