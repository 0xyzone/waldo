<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV — {{ $candidate->name }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print-bar {
            position: sticky;
            top: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .no-print-bar .title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .no-print-bar h1 {
            font-size: 16px;
            font-weight: 600;
        }

        .no-print-bar .badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 500;
            border-radius: 9999px;
            background: #334155;
            color: #94a3b8;
        }

        .no-print-bar .btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-primary {
            background: #2563eb;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #334155;
            color: #f8fafc;
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .print-container {
            max-width: 900px;
            margin: 24px auto;
            padding: 0 16px;
        }

        .page-sheet {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 24px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            page-break-after: always;
            break-after: page;
        }

        .page-sheet:last-child {
            page-break-after: auto;
            break-after: auto;
            margin-bottom: 0;
        }

        .page-sheet-num {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .cv-image {
            display: block;
            width: 100%;
            height: auto;
            max-height: 98vh;
            object-fit: contain;
            margin: 0 auto;
            border-radius: 4px;
        }

        .empty-state {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 48px 24px;
            text-align: center;
            color: #64748b;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print,
            .no-print-bar {
                display: none !important;
            }

            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .page-sheet {
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                page-break-after: always !important;
                break-after: page !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .page-sheet:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            .page-sheet-num {
                display: none !important;
            }

            .cv-image {
                max-height: 100vh !important;
                max-width: 100vw !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            @page {
                size: auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar no-print">
        <div class="title-group">
            <h1>Attachments — {{ $candidate->name }}</h1>
            <span class="badge">{{ count($images) }} {{ count($images) === 1 ? 'Page' : 'Pages' }}</span>
        </div>
        <div class="btn-group">
            <button type="button" onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
            <button type="button" onclick="window.print()" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Attachments
            </button>
        </div>
    </div>

    <div class="print-container">
        @if(count($images) > 0)
            @foreach($images as $index => $imageUrl)
                <div class="page-sheet">
                    <div class="page-sheet-num no-print">
                        <span>Page {{ $index + 1 }} of {{ count($images) }}</span>
                        <a href="{{ $imageUrl }}" target="_blank" style="color: #2563eb; text-decoration: none; font-size: 11px;">View Original</a>
                    </div>
                    <img src="{{ $imageUrl }}" alt="CV Page {{ $index + 1 }}" class="cv-image" loading="eager" />
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <p style="font-size: 15px; font-weight: 600; margin-bottom: 4px;">No attachments available</p>
                <p style="font-size: 13px;">This candidate record does not have any uploaded images.</p>
            </div>
        @endif
    </div>

    @if(request()->boolean('autoprint'))
        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        </script>
    @endif
</body>
</html>
