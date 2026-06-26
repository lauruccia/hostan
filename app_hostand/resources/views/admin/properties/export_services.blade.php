<!DOCTYPE html>
<html>
<head>
    <title>{{ __('Services Report') }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #000;
            font-size: 20pt;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed; /* Helps with rendering speed */
        }
        th {
            background-color: #f8f9fa;
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
            font-size: 9pt;
        }
        td {
            border: 1px solid #eee;
            padding: 5px;
            vertical-align: top;
            font-size: 8.5pt;
            word-wrap: break-word;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8pt;
            display: inline-block;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-in-progress { background-color: #d1ecf1; color: #0c5460; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .footer {
            text-align: center;
            font-size: 8pt;
            color: #999;
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('Services & Maintenance Report') }}</h1>
        <p>{{ __('Generated on') }}: {{ date('M d, Y H:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">{{ __('Date') }}</th>
                <th style="width: 18%;">{{ __('Property') }}</th>
                <th style="width: 20%;">{{ __('Owner') }}</th>
                <th style="width: 15%;">{{ __('Service Type') }}</th>
                <th style="width: 12%;">{{ __('Unit') }}</th>
                <th style="width: 13%;">{{ __('Maintainer') }}</th>
                <th style="width: 10%;">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($maintenanceRequests as $request)
                <tr>
                    <td>
                        @if (!empty($request->arrival_time))
                            {{ \Carbon\Carbon::parse($request->arrival_time)->format('M d, Y') }}
                        @else
                            {{ __('Not set') }}
                        @endif
                    </td>
                    <td>{{ $request->properties->name ?? 'N/A' }}</td>
                    <td>
                        @if ($request->properties && $request->properties->owner)
                            {{ $request->properties->owner->name }}<br>
                            <small>{{ $request->properties->owner->email }}</small>
                        @else
                            {{ __('Unknown') }}
                        @endif
                    </td>
                    <td>{{ $request->types->title ?? 'N/A' }}</td>
                    <td>{{ $request->units->name ?? 'N/A' }}</td>
                    <td>{{ $request->maintainers->name ?? __('Unassigned') }}</td>
                    <td>
                        @php
                            $statusClass = 'status-' . str_replace('_', '-', $request->status);
                            $statusLabel = __($request->status);
                            if ($request->status == 'pending' && (!$request->maintainers || $request->maintainers->id == 0)) {
                                $statusLabel = __('To be Assigned');
                            }
                        @endphp
                        <span class="badge {{ $statusClass }}">
                            {{ ucfirst($statusLabel) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('Hostand - Property Management System') }}</p>
    </div>
</body>
</html>
