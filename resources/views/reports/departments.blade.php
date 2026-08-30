<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Employee Counts by Department & Designation</title>
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
            padding: 10px 8px;
            text-align: left;
            vertical-align: middle;
            color: #000;
        }

        tr {
            page-break-inside: avoid;
        }

        th {
            font-weight: bold;
        }

        /* Specific column widths and alignments */
        th:nth-child(1), td:nth-child(1) { width: 6%; text-align: center; }
        th:nth-child(2), td:nth-child(2) { width: 22%; }
        th:nth-child(3), td:nth-child(3) { width: 22%; }
        th:nth-child(4), td:nth-child(4) { width: 18%; }
        th:nth-child(5), td:nth-child(5) { width: 16%; text-align: center; }
        th:nth-child(6), td:nth-child(6) { width: 16%; text-align: center; }

        /* Header row formatting */
        .title-row th {
            padding: 14px 8px;
            font-size: 15px;
            text-transform: uppercase;
            text-align: center;
        }
        
        /* The header below title */
        .headers th {
            font-size: 11px;
            padding: 10px 8px;
            text-transform: uppercase;
            background-color: #fcfcfc;
        }

        .dept-row {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .desig-row td:nth-child(3) {
            padding-left: 20px;
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
                    <th colspan="6">Casino Staff - Active Employee Counts (Department &amp; Designation)</th>
                </tr>
                <tr class="headers">
                    <th>S No</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Manager Name</th>
                    <th>Mobile No</th>
                    <th>Active Count</th>
                </tr>
            </thead>
            <tbody>
                @php $sno = 1; @endphp
                @foreach($departments as $dept)
                    <!-- Department Row -->
                    <tr class="dept-row">
                        <td style="text-align: center;">{{ $sno++ }}</td>
                        <td>{{ $dept['name'] }}</td>
                        <td>All Designations</td>
                        <td>{{ $dept['manager'] ? $dept['manager']['name'] : 'N/A' }}</td>
                        <td style="text-align: center;">{{ $dept['manager'] ? $dept['manager']['mobile'] : 'N/A' }}</td>
                        <td style="text-align: center;">{{ $dept['count'] }}</td>
                    </tr>
                    <!-- Designation Rows (sorted by rank) -->
                    @foreach($dept['designations'] as $desig)
                        <tr class="desig-row">
                            <td></td>
                            <td></td>
                            <td>{{ $desig['name'] }}</td>
                            <td></td>
                            <td></td>
                            <td style="text-align: center;">{{ $desig['count'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
                
                @if($departments->isEmpty())
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">No active departments/employees found.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
