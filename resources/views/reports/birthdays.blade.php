<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday List - {{ $monthName }} {{ $year }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #e8eaed;
        }

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            background: white;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            border: 2px solid #000;
        }

        th, td {
            border: 1px solid #000;
            padding: 10px 6px;
            text-align: left;
            vertical-align: middle;
            color: #000;
        }

        tr {
            page-break-inside: avoid;
        }

        tbody td {
            height: 45px;
        }

        th {
            font-weight: bold;
        }

        /* Specific column widths and alignments */
        th:nth-child(1), td:nth-child(1) { width: 5%; white-space: nowrap; text-align: center; }
        th:nth-child(2), td:nth-child(2) { width: 8%; text-align: center; }
        th:nth-child(3), td:nth-child(3) { width: 20%; }
        th:nth-child(4), td:nth-child(4) { width: 10%; text-align: center; }
        th:nth-child(5), td:nth-child(5) { width: 15%; }
        th:nth-child(6), td:nth-child(6) { width: 17%; }
        th:nth-child(7), td:nth-child(7) { width: 25%; }

        /* Header row formatting */
        .title-row th {
            padding: 14px 6px;
            font-size: 15px;
            text-transform: uppercase;
        }
        
        .title-row .main-title {
            font-weight: 800;
        }
        
        .title-row .month-cell, .title-row .year-cell {
            font-weight: 700;
            text-align: center;
        }

        /* The header below title */
        .headers th {
            font-size: 11px;
            padding: 10px 6px;
            text-transform: uppercase;
            background-color: #fcfcfc;
        }

        @media print {
            body {
                background: none;
                padding: 0;
                margin: 0;
            }
            .a4-page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: 0;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            
            /* Prevent the header from repeating on every printed page */
            thead {
                display: table-row-group;
            }
        }
    </style>
</head>
<body>
    <div class="a4-page">
        <table>
            <thead>
                <tr class="title-row">
                    <th colspan="4" class="main-title">Casino Staff - Birthday List for the month:</th>
                    <th colspan="2" class="month-cell">{{ $monthName }}</th>
                    <th class="year-cell">{{ $year }}</th>
                </tr>
                <tr class="headers">
                    <th>S No</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $employee)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $employee->employee_code }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->dob_ad ? $employee->dob_ad->format('d F') : '' }}</td>
                    <td>{{ $employee->department ? $employee->department->name : '' }}</td>
                    <td>{{ $employee->designation ? $employee->designation->name : '' }}</td>
                    <td></td>
                </tr>
                @endforeach
                
                @if($employees->isEmpty())
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No active employees found with birthdays in this month matching the criteria.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <script>
        // Trigger print automatically when loaded
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
