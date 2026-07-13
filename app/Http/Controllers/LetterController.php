<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function index()
    {
        $templates = LetterTemplate::orderBy('title')->get();

        return view('letters.index', compact('templates'));
    }

    public function create()
    {
        return view('letters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'variables.*.key' => 'required|string|alpha_dash',
            'variables.*.type' => 'required|string|in:text,date,number,boolean,dropdown',
            'variables.*.dummy' => 'nullable|string',
            'variables.*.options' => 'nullable|string',
            'margin_top' => 'required|integer|min:0|max:100',
            'margin_bottom' => 'required|integer|min:0|max:100',
            'margin_left' => 'required|integer|min:0|max:100',
            'margin_right' => 'required|integer|min:0|max:100',
        ]);

        LetterTemplate::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'variables' => $validated['variables'] ?? [],
            'margin_top' => $validated['margin_top'],
            'margin_bottom' => $validated['margin_bottom'],
            'margin_left' => $validated['margin_left'],
            'margin_right' => $validated['margin_right'],
        ]);

        return redirect()->route('letters.index')->with('success', 'Template created successfully.');
    }

    public function edit($id)
    {
        $template = LetterTemplate::findOrFail($id);

        return view('letters.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = LetterTemplate::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'variables.*.key' => 'required|string|alpha_dash',
            'variables.*.type' => 'required|string|in:text,date,number,boolean,dropdown',
            'variables.*.dummy' => 'nullable|string',
            'variables.*.options' => 'nullable|string',
            'margin_top' => 'required|integer|min:0|max:100',
            'margin_bottom' => 'required|integer|min:0|max:100',
            'margin_left' => 'required|integer|min:0|max:100',
            'margin_right' => 'required|integer|min:0|max:100',
        ]);

        $template->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'variables' => $validated['variables'] ?? [],
            'margin_top' => $validated['margin_top'],
            'margin_bottom' => $validated['margin_bottom'],
            'margin_left' => $validated['margin_left'],
            'margin_right' => $validated['margin_right'],
        ]);

        return redirect()->route('letters.index')->with('success', 'Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = LetterTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('letters.index')->with('success', 'Template deleted successfully.');
    }

    public function generate()
    {
        $query = Employee::with(['department', 'designation']);
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $query->orderByRaw('CAST(SUBSTR(employee_code, 4) AS INTEGER) asc');
        } else {
            $query->orderByRaw('CAST(SUBSTR(employee_code, 4) AS UNSIGNED) asc');
        }

        $employees = $query->get();
        $templates = LetterTemplate::orderBy('title')->get();
        $selectedTemplateId = request('template_id', '');

        return view('letters.generate', compact('employees', 'templates', 'selectedTemplateId'));
    }
}
