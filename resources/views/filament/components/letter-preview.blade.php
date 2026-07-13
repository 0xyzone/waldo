<?php
$employee = \App\Models\Employee::with(['department', 'designation'])->first();
if (!$employee) {
    $employee = (object)[
        'employee_code' => 'CWD001',
        'name' => 'Sarmila Bhandari',
        'email' => 'sarmila@test.com',
        'gender' => 'Female',
        'join_date' => '2024-01-01',
        'contact_number' => '9865914116',
        'department' => (object)['name' => 'HouseKeeping'],
        'designation' => (object)['name' => 'Attendant'],
        'citizenship_number' => '12-34-56-7890',
        'citizenship_issue_date' => '2020-05-12',
        'citizenship_issue_place' => 'Kathmandu',
        'ssid' => 'SSID123456',
        'dob_ad' => '1995-09-19',
        'dob_bs' => '2052-06-03',
        'marital_status' => 'Single',
        'employee_status' => 'Active',
        'tips_amount' => 150.00,
        'tips_status' => 'Paid',
        'point_value' => 1.25,
    ];
}
?>

<div x-data="{
    title: '',
    content: '',
    employee: {{ json_encode($employee) }},
    margins: {
        top: 25,
        bottom: 25,
        left: 20,
        right: 20
    },
    
    init() {
        this.$watch('$wire.data.title', val => this.title = val || 'Untitled Template');
        this.$watch('$wire.data.content', val => this.content = val || '');
        this.$watch('$wire.data.margin_top', val => this.margins.top = val || 25);
        this.$watch('$wire.data.margin_bottom', val => this.margins.bottom = val || 25);
        this.$watch('$wire.data.margin_left', val => this.margins.left = val || 20);
        this.$watch('$wire.data.margin_right', val => this.margins.right = val || 20);
        
        this.title = this.$wire.get('data.title') || 'Untitled Template';
        this.content = this.$wire.get('data.content') || '';
        this.margins.top = this.$wire.get('data.margin_top') || 25;
        this.margins.bottom = this.$wire.get('data.margin_bottom') || 25;
        this.margins.left = this.$wire.get('data.margin_left') || 20;
        this.margins.right = this.$wire.get('data.margin_right') || 20;
    },

    jsonToHtml(node) {
        if (!node) return '';
        if (typeof node === 'string') return node;
        if (Array.isArray(node)) return node.map(n => this.jsonToHtml(n)).join('');
        
        if (node.type === 'text') {
            let text = node.text || '';
            if (node.marks) {
                node.marks.forEach(mark => {
                    if (mark.type === 'bold') text = `<strong>${text}</strong>`;
                    if (mark.type === 'italic') text = `<em>${text}</em>`;
                    if (mark.type === 'strike') text = `<s>${text}</s>`;
                    if (mark.type === 'underline') text = `<u>${text}</u>`;
                    if (mark.type === 'link') text = `<a href=&quot;${mark.attrs.href}&quot; target=&quot;${mark.attrs.target}&quot;>${text}</a>`;
                });
            }
            return text;
        }
        
        let children = node.content ? this.jsonToHtml(node.content) : '';
        
        switch (node.type) {
            case 'doc':
                return children;
            case 'paragraph':
                return `<p>${children}</p>`;
            case 'heading':
                return `<h${node.attrs?.level || 1}>${children}</h${node.attrs?.level || 1}>`;
            case 'bulletList':
                return `<ul>${children}</ul>`;
            case 'orderedList':
                return `<ol>${children}</ol>`;
            case 'listItem':
                return `<li>${children}</li>`;
            case 'blockquote':
                return `<blockquote>${children}</blockquote>`;
            case 'hardBreak':
                return `<br>`;
            default:
                return children;
        }
    },

    parseTemplate(content, employee) {
        let parsed = content;
        
        // Parse Tiptap JSON Object/String if applicable
        if (typeof parsed === 'object' && parsed !== null) {
            parsed = this.jsonToHtml(parsed);
        } else if (typeof parsed === 'string' && parsed.trim().startsWith('{')) {
            try {
                parsed = this.jsonToHtml(JSON.parse(parsed));
            } catch (e) {}
        }

        if (!parsed) return '<p class=&quot;text-gray-400 italic&quot;>No content drafted yet. Use placeholders like \{\{ employee_name \}\} to design the letter.</p>';

        // Replace employee variables using underscore notation
        parsed = parsed.replace(/\{\{\s*employee_([a-zA-Z0-9_]+)\s*\}\}/g, (match, key) => {
            if (key === 'department') {
                return employee.department ? (employee.department.name || 'HouseKeeping') : 'HouseKeeping';
            }
            if (key === 'designation') {
                return employee.designation ? (employee.designation.name || 'Attendant') : 'Attendant';
            }
            if (key === 'join_date') {
                const val = employee.join_date || '2024-01-01';
                const d = new Date(val);
                return isNaN(d) ? val : d.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
            }
            if (key === 'dob_ad') {
                const val = employee.dob_ad || '1995-09-19';
                const d = new Date(val);
                return isNaN(d) ? val : d.toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
            }
            return employee[key] !== undefined && employee[key] !== null ? employee[key] : `[${key}]`;
        });

        // Replace custom variables with their configured dummy values from Repeater
        parsed = parsed.replace(/\{\{\s*custom_([a-zA-Z0-9_]+)\s*\}\}/g, (match, key) => {
            const repeater = this.$wire.get('data.variables') || [];
            const found = repeater.find(v => v.key === key);
            return found && found.dummy !== undefined && found.dummy !== null && found.dummy !== '' ? found.dummy : `[${this.formatLabel(key)}]`;
        });

        parsed = parsed.replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (match, key) => {
            if (key.startsWith('employee_')) return match;
            const cleanKey = key.startsWith('custom_') ? key.substring(7) : key;
            const repeater = this.$wire.get('data.variables') || [];
            const found = repeater.find(v => v.key === cleanKey);
            return found && found.dummy !== undefined && found.dummy !== null && found.dummy !== '' ? found.dummy : `[${this.formatLabel(cleanKey)}]`;
        });

        return parsed;
    },

    formatLabel(str) {
        return str
            .replace(/_/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    },

    get marginStyles() {
        return `
            .preview-a4-page {
                padding-top: ${this.margins.top}mm !important;
                padding-bottom: ${this.margins.bottom}mm !important;
                padding-left: ${this.margins.left}mm !important;
                padding-right: ${this.margins.right}mm !important;
            }
        `;
    }
}" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-zinc-900 transition-colors">
    <!-- Dynamic Margins Styling -->
    <style x-html="marginStyles"></style>
    <style>
        .a4-page-content ul {
            list-style-type: disc !important;
            margin-top: 0.5em !important;
            margin-bottom: 0.5em !important;
            padding-left: 2em !important;
        }
        .a4-page-content ol {
            list-style-type: decimal !important;
            margin-top: 0.5em !important;
            margin-bottom: 0.5em !important;
            padding-left: 2em !important;
        }
        .a4-page-content li {
            margin-bottom: 0.2em !important;
            display: list-item !important;
        }
        .a4-page-content h1 {
            font-size: 20pt !important;
            font-weight: bold !important;
            margin-top: 12pt !important;
            margin-bottom: 4pt !important;
            line-height: 1.2 !important;
        }
        .a4-page-content h2 {
            font-size: 16pt !important;
            font-weight: bold !important;
            margin-top: 10pt !important;
            margin-bottom: 3pt !important;
            line-height: 1.2 !important;
        }
        .a4-page-content h3 {
            font-size: 14pt !important;
            font-weight: bold !important;
            margin-top: 8pt !important;
            margin-bottom: 3pt !important;
            line-height: 1.2 !important;
        }
        .a4-page-content p {
            margin-top: 0 !important;
            margin-bottom: 8pt !important;
        }
    </style>

    <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Live Template Preview (A4 Page Guide)</h4>
    <h3 class="text-md font-bold text-gray-800 dark:text-gray-200 mb-4" x-text="title"></h3>
    
    <!-- Render as a real A4 Paper Preview scaled to fit the column width -->
    <div class="w-full overflow-hidden flex justify-center py-4 bg-slate-100 dark:bg-zinc-950 rounded-xl border border-gray-200 dark:border-zinc-800" style="min-height: 520px;">
        <div style="zoom: 0.65; -moz-transform: scale(0.65); -moz-transform-origin: top center; transform-origin: top center; width: 210mm;">
            <div class="preview-a4-page relative bg-white text-stone-900 shadow-xl border border-black/5 flex flex-col"
                 style="width: 210mm; min-height: 297mm; box-sizing: border-box;">
                
                <!-- Margin Guides -->
                <div class="absolute border-b border-dashed border-amber-500/25 w-full pointer-events-none" :style="'height: ' + margins.top + 'mm; top: 0; left: 0;'">
                    <span class="absolute left-2 bottom-1 text-[9px] text-amber-600 bg-amber-50 dark:bg-zinc-800 px-1 py-0.5 rounded font-mono">Top: <span x-text="margins.top"></span>mm</span>
                </div>
                <div class="absolute border-t border-dashed border-amber-500/25 w-full pointer-events-none" :style="'height: ' + margins.bottom + 'mm; bottom: 0; left: 0;'">
                    <span class="absolute left-2 top-1 text-[9px] text-amber-600 bg-amber-50 dark:bg-zinc-800 px-1 py-0.5 rounded font-mono">Bottom: <span x-text="margins.bottom"></span>mm</span>
                </div>
                <div class="absolute border-r border-dashed border-amber-500/25 h-full pointer-events-none" :style="'width: ' + margins.left + 'mm; top: 0; left: 0;'">
                    <span class="absolute top-2 right-1 text-[9px] text-amber-600 bg-amber-50 dark:bg-zinc-800 px-1 py-0.5 rounded font-mono">Left: <span x-text="margins.left"></span>mm</span>
                </div>
                <div class="absolute border-l border-dashed border-amber-500/25 h-full pointer-events-none" :style="'width: ' + margins.right + 'mm; top: 0; right: 0;'">
                    <span class="absolute top-2 left-1 text-[9px] text-amber-600 bg-amber-50 dark:bg-zinc-800 px-1 py-0.5 rounded font-mono">Right: <span x-text="margins.right"></span>mm</span>
                </div>

                <!-- Page Content Rendered with Georgia typography -->
                <div class="a4-page-content" style="font-family: 'Georgia', serif; font-size: 11.5pt; line-height: 1.6; color: #1c1917;" x-html="parseTemplate(content, employee)">
                </div>
                
            </div>
        </div>
    </div>
</div>
