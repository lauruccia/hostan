<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Operator Performance Report') }}</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.5; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #28a745; font-size: 22pt; }
        .header p { margin: 5px 0; color: #666; }
        
        .info-section { margin-bottom: 25px; width: 100%; border-collapse: collapse; }
        .info-section td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #555; }
        
        .stats-container { margin-bottom: 30px; }
        .stats-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .stats-table td { padding: 15px; border: 1px solid #eee; text-align: center; background-color: #fcfcfc; }
        .stats-value { font-size: 16pt; font-weight: bold; color: #28a745; display: block; }
        .stats-label { font-size: 9pt; color: #777; text-transform: uppercase; }
        
        h2 { border-left: 4px solid #28a745; padding-left: 10px; color: #333; font-size: 14pt; margin-top: 30px; margin-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th { background-color: #f8f9fa; border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 9pt; color: #555; }
        td { border: 1px solid #eee; padding: 6px; vertical-align: top; font-size: 8.5pt; word-wrap: break-word; }
        
        .text-end { text-align: right; }
        .badge { padding: 2px 5px; font-size: 8pt; border-radius: 3px; color: #fff; }
        .bg-success { background-color: #28a745; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-info { background-color: #17a2b8; }
        .bg-primary { background-color: #007bff; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HOSTAND</h1>
        <p>{{ __('Operator Performance Report') }}</p>
    </div>

    <table class="info-section">
        <tr>
            <td width="50%">
                <span class="label">{{ __('Operator') }}:</span> {{ $operator->first_name }} {{ $operator->last_name }}<br>
                <span class="label">{{ __('Email') }}:</span> {{ $operator->email }}
            </td>
            <td width="50%" class="text-end">
                <span class="label">{{ __('Period') }}:</span> {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}<br>
                <span class="label">{{ __('Generated') }}:</span> {{ date('M d, Y H:i') }}
            </td>
        </tr>
    </table>

    <div class="stats-container">
        <h2>{{ __('Performance Summary') }}</h2>
        <table class="stats-table">
            <tr>
                <td>
                    <span class="stats-value">{{ $totalAllServices }}</span>
                    <span class="stats-label">{{ __('All Services') }}</span>
                </td>
                <td>
                    <span class="stats-value text-success">{{ $totalServices }}</span>
                    <span class="stats-label">{{ __('Completed') }}</span>
                </td>
                <td>
                    <span class="stats-value text-info">{{ number_format($totalHours, 1) }}</span>
                    <span class="stats-label">{{ __('Total Hours') }}</span>
                </td>
                <td>
                    <span class="stats-value text-warning">${{ number_format($totalAmount, 2) }}</span>
                    <span class="stats-label">{{ __('Amount') }}</span>
                </td>
            </tr>
        </table>
    </div>

    @if(isset($servicesByDay) && $servicesByDay->count() > 0)
    <h2>{{ __('Daily Breakdown') }}</h2>
    <table>
        <thead>
            <tr>
                <th width="30%">{{ __('Date') }}</th>
                <th width="20%">{{ __('Services') }}</th>
                <th width="20%">{{ __('Completed') }}</th>
                <th width="30%">{{ __('Hours Worked') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicesByDay->sortKeys() as $dayKey => $services)
                @php
                    $dayCompleted = $services->where('status', 'completed');
                    $dayHours = $dayCompleted->sum('hours_worked') ?? 0;
                    $dayDate = \Carbon\Carbon::parse($dayKey);
                @endphp
                <tr>
                    <td>{{ $dayDate->format('M d, Y (l)') }}</td>
                    <td>{{ $services->count() }}</td>
                    <td>{{ $dayCompleted->count() }}</td>
                    <td>{{ number_format($dayHours, 1) }}h</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(isset($servicesByMonth) && $servicesByMonth->count() > 0)
    <h2>{{ __('Monthly Breakdown') }}</h2>
    <table>
        <thead>
            <tr>
                <th width="40%">{{ __('Month') }}</th>
                <th width="20%">{{ __('Services') }}</th>
                <th width="20%">{{ __('Completed') }}</th>
                <th width="20%">{{ __('Hours') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicesByMonth->sortKeys()->reverse() as $monthKey => $services)
                @php
                    $monthCompleted = $services->where('status', 'completed');
                    $monthHours = $monthCompleted->sum('hours_worked') ?? 0;
                    $monthDate = \Carbon\Carbon::parse($monthKey . '-01');
                @endphp
                <tr>
                    <td>{{ $monthDate->format('F Y') }}</td>
                    <td>{{ $services->count() }}</td>
                    <td>{{ $monthCompleted->count() }}</td>
                    <td>{{ number_format($monthHours, 1) }}h</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="page-break"></div>

    <h2>{{ __('Detailed Service List') }}</h2>
    <table>
        <thead>
            <tr>
                <th width="15%">{{ __('Date') }}</th>
                <th width="25%">{{ __('Property / Unit') }}</th>
                <th width="25%">{{ __('Service Type') }}</th>
                <th width="15%">{{ __('Status') }}</th>
                <th width="10%">{{ __('Hours') }}</th>
                <th width="10%">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allServices as $service)
            <tr>
                <td>{{ !empty($service->arrival_time) ? \Carbon\Carbon::parse($service->arrival_time)->format('M d, Y') : '-' }}</td>
                <td>
                    {{ $service->properties->name ?? 'N/A' }}<br>
                    <small>{{ $service->units->name ?? 'N/A' }}</small>
                </td>
                <td>{{ $service->types->title ?? 'N/A' }}</td>
                <td>
                    @if($service->status == 'completed') {{ __('Completed') }}
                    @elseif($service->status == 'in_progress') {{ __('In Progress') }}
                    @else {{ __('Pending') }}
                    @endif
                </td>
                <td class="text-end">{{ number_format($service->hours_worked ?? 0, 1) }}h</td>
                <td class="text-end">${{ number_format($service->amount ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ __('Generated by Hostand Management System') }} - {{ date('Y') }}
    </div>
</body>
</html>
