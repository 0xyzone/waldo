<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\GeneratedLetter;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

class LetterController extends Controller
{
    public function index()
    {
        $templates = LetterTemplate::orderBy('title')->get();

        return view('letters.index', compact('templates'));
    }

    public function history(Request $request)
    {
        $query = GeneratedLetter::with(['employee', 'template'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%")
                    ->orWhere('template_title', 'like', "%{$search}%");
            });
        }

        if ($templateId = $request->input('template_id')) {
            $query->where('letter_template_id', $templateId);
        }

        $letters = $query->paginate(12)->withQueryString();
        $templates = LetterTemplate::orderBy('title')->get();

        return view('letters.generated.index', compact('letters', 'templates'));
    }

    public function saveGenerated(Request $request)
    {
        $validated = $request->validate([
            'letters' => 'required|array',
            'letters.*.letter_template_id' => 'nullable|integer|exists:letter_templates,id',
            'letters.*.employee_code' => 'required|string|exists:employees,employee_code',
            'letters.*.template_title' => 'required|string',
            'letters.*.employee_name' => 'required|string',
            'letters.*.content' => 'required|string',
            'letters.*.custom_values' => 'nullable|array',
            'letters.*.margins' => 'nullable|array',
        ]);

        $createdIds = [];
        foreach ($validated['letters'] as $item) {
            $record = GeneratedLetter::create([
                'letter_template_id' => $item['letter_template_id'] ?? null,
                'employee_code' => $item['employee_code'],
                'template_title' => $item['template_title'],
                'employee_name' => $item['employee_name'],
                'content' => $item['content'],
                'custom_values' => $item['custom_values'] ?? [],
                'margins' => $item['margins'] ?? [],
            ]);
            $createdIds[] = $record->id;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => count($createdIds).' letter(s) saved to history successfully.',
                'ids' => $createdIds,
            ]);
        }

        return redirect()->route('letters.history')->with('success', count($createdIds).' letter(s) saved to history.');
    }

    public function showGenerated($id)
    {
        $letter = GeneratedLetter::with(['employee', 'template'])->findOrFail($id);

        return view('letters.generated.show', compact('letter'));
    }

    public function editGenerated($id)
    {
        $letter = GeneratedLetter::with(['employee.department', 'employee.designation', 'template'])->findOrFail($id);

        return view('letters.generated.edit', compact('letter'));
    }

    public function updateGenerated(Request $request, $id)
    {
        $letter = GeneratedLetter::findOrFail($id);

        $validated = $request->validate([
            'template_title' => 'required|string|max:255',
            'employee_name' => 'required|string|max:255',
            'content' => 'required|string',
            'custom_values' => 'nullable|array',
            'margin_top' => 'required|integer|min:0|max:100',
            'margin_bottom' => 'required|integer|min:0|max:100',
            'margin_left' => 'required|integer|min:0|max:100',
            'margin_right' => 'required|integer|min:0|max:100',
        ]);

        $letter->update([
            'template_title' => $validated['template_title'],
            'employee_name' => $validated['employee_name'],
            'content' => $validated['content'],
            'custom_values' => $validated['custom_values'] ?? $letter->custom_values,
            'margins' => [
                'top' => (int) $validated['margin_top'],
                'bottom' => (int) $validated['margin_bottom'],
                'left' => (int) $validated['margin_left'],
                'right' => (int) $validated['margin_right'],
            ],
        ]);

        return redirect()->route('letters.history')->with('success', 'Generated letter updated successfully.');
    }

    public function destroyGenerated($id)
    {
        $letter = GeneratedLetter::findOrFail($id);
        $letter->delete();

        return redirect()->route('letters.history')->with('success', 'Generated letter removed from history.');
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
