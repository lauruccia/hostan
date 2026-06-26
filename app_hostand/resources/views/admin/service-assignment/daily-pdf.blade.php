<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Servizi del {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; background: #fff; }

        .header { background: #6d1f3e; color: #fff; padding: 10px 14px; margin-bottom: 12px; }
        .header h1 { font-size: 16px; font-weight: bold; }
        .header .sub { font-size: 10px; margin-top: 3px; opacity: 0.85; }

        h2.section-title {
            font-size: 11px; font-weight: bold; color: #6d1f3e;
            border-bottom: 1.5px solid #6d1f3e; padding-bottom: 3px;
            margin: 14px 0 6px;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #6d1f3e; color: #fff; padding: 5px 6px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 4px 6px; border-bottom: 1px solid #e0e0e0; vertical-align: top; font-size: 9.5px; }
        tr:nth-child(even) td { background: #faf5f7; }

        .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 8.5px; font-weight: bold; }
        .badge-pending   { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .badge-progress  { background: #cff4fc; color: #055160; border: 1px solid #0dcaf0; }
        .badge-completed { background: #d1e7dd; color: #0a3622; border: 1px solid #198754; }
        .badge-unassigned{ background: #f8d7da; color: #842029; border: 1px solid #dc3545; }

        .operator-block  { margin-bottom: 16px; page-break-inside: avoid; }
        .operator-name   { font-size: 11px; font-weight: bold; color: #444; background: #f0e8ec;
                           padding: 4px 8px; border-left: 4px solid #6d1f3e; margin-bottom: 5px; }

        .footer { margin-top: 18px; font-size: 8px; color: #888; border-top: 1px solid #ddd; padding-top: 5px;
                  display: flex; justify-content: space-between; }

        .totals { font-size: 9px; color: #555; margin-bottom: 6px; }
        .totals span { margin-right: 18px; }
        .totals .num  { font-weight: bold; color: #6d1f3e; }
    </style>
</head>
<body>

{{-- ── INTESTAZIONE ── --}}
<div class="header">
    <h1>📋 Riepilogo Servizi Giornalieri — {{ \Carbon\Carbon::parse($date)->locale('it')->isoFormat('dddd D MMMM YYYY') }}</h1>
    <div class="sub">Generato il {{ $generatedAt }} &nbsp;|&nbsp; Totale servizi: {{ $services->count() }}</div>
</div>

{{-- ── TOTALI RAPIDI ── --}}
<div class="totals">
    <span>Totale: <span class="num">{{ $services->count() }}</span></span>
    <span>Assegnati: <span class="num">{{ $services->filter(fn($s) => $s->maintainer_id > 0)->count() }}</span></span>
    <span>Non assegnati: <span class="num">{{ $services->filter(fn($s) => !$s->maintainer_id || $s->maintainer_id == 0)->count() }}</span></span>
    <span>Completati: <span class="num">{{ $services->where('status','completed')->count() }}</span></span>
    <span>In corso: <span class="num">{{ $services->where('status','in_progress')->count() }}</span></span>
    <span>In attesa: <span class="num">{{ $services->where('status','pending')->count() }}</span></span>
</div>

{{-- ══════════════════════════════════════════════ --}}
{{-- SEZIONE 1 — VISTA COMPLETA CRONOLOGICA        --}}
{{-- ══════════════════════════════════════════════ --}}
<h2 class="section-title">1. Vista cronologica — tutti i servizi</h2>
<table>
    <thead>
        <tr>
            <th style="width:6%">Ora</th>
            <th style="width:20%">Proprietà</th>
            <th style="width:18%">Unità</th>
            <th style="width:14%">Tipo servizio</th>
            <th style="width:13%">Operatore</th>
            <th style="width:8%">Rider</th>
            <th style="width:8%">Stato</th>
            <th style="width:13%">Note</th>
        </tr>
    </thead>
    <tbody>
        @forelse($services as $s)
            @php
                $ora       = $s->arrival_time ? \Carbon\Carbon::parse($s->arrival_time)->format('H:i') : '—';
                $property  = optional($s->properties)->name ?? '—';
                $unit      = optional($s->units)->name     ?? '—';
                $tipo      = optional($s->types)->title    ?? ($s->service_type ?? '—');
                $m         = $s->maintainers;
                $operatore = ($m && $m->id) ? trim($m->first_name . ' ' . $m->last_name) : '—';
                $rider     = $s->rider ? trim($s->rider->first_name . ' ' . $s->rider->last_name) : '—';
                $stato     = $s->status ?? 'pending';
                $badgeClass= match($stato) {
                    'completed'   => 'badge-completed',
                    'in_progress' => 'badge-progress',
                    default       => ($operatore === '—' ? 'badge-unassigned' : 'badge-pending'),
                };
                $statoLabel = match($stato) {
                    'completed'   => 'Completato',
                    'in_progress' => 'In corso',
                    'pending'     => ($operatore === '—' ? 'Non assegnato' : 'In attesa'),
                    default       => ucfirst($stato),
                };
            @endphp
            <tr>
                <td>{{ $ora }}</td>
                <td>{{ $property }}</td>
                <td>{{ $unit }}</td>
                <td>{{ $tipo }}</td>
                <td>{{ $operatore }}</td>
                <td>{{ $rider }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $statoLabel }}</span></td>
                <td>{{ Str::limit($s->notes ?? '', 60) }}</td>
            </tr>
        @empty
            <tr><td colspan="8" style="text-align:center;color:#999;padding:12px;">Nessun servizio programmato per questa data.</td></tr>
        @endforelse
    </tbody>
</table>

{{-- ══════════════════════════════════════════════ --}}
{{-- SEZIONE 2 — VISTA PER OPERATORE               --}}
{{-- ══════════════════════════════════════════════ --}}
<h2 class="section-title" style="margin-top:20px;">2. Vista per operatore</h2>

@foreach($byOperator->sortKeys() as $operatoreName => $lista)
<div class="operator-block">
    <div class="operator-name">
        👤 {{ $operatoreName }}
        &nbsp;<span style="font-weight:normal;font-size:9.5px;color:#666;">({{ $lista->count() }} {{ $lista->count() == 1 ? 'servizio' : 'servizi' }})</span>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:7%">Ora</th>
                <th style="width:22%">Proprietà</th>
                <th style="width:20%">Unità</th>
                <th style="width:16%">Tipo servizio</th>
                <th style="width:9%">Rider</th>
                <th style="width:9%">Stato</th>
                <th style="width:17%">Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lista as $s)
                @php
                    $ora       = $s->arrival_time ? \Carbon\Carbon::parse($s->arrival_time)->format('H:i') : '—';
                    $property  = optional($s->properties)->name ?? '—';
                    $unit      = optional($s->units)->name     ?? '—';
                    $tipo      = optional($s->types)->title    ?? ($s->service_type ?? '—');
                    $rider     = $s->rider ? trim($s->rider->first_name . ' ' . $s->rider->last_name) : '—';
                    $stato     = $s->status ?? 'pending';
                    $badgeClass= match($stato) {
                        'completed'   => 'badge-completed',
                        'in_progress' => 'badge-progress',
                        default       => 'badge-pending',
                    };
                    $statoLabel = match($stato) {
                        'completed'   => 'Completato',
                        'in_progress' => 'In corso',
                        default       => 'In attesa',
                    };
                @endphp
                <tr>
                    <td>{{ $ora }}</td>
                    <td>{{ $property }}</td>
                    <td>{{ $unit }}</td>
                    <td>{{ $tipo }}</td>
                    <td>{{ $rider }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statoLabel }}</span></td>
                    <td>{{ Str::limit($s->notes ?? '', 80) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

{{-- ── PIE' DI PAGINA ── --}}
<div class="footer">
    <span>Hostand Property Manager</span>
    <span>Documento generato il {{ $generatedAt }} — uso interno</span>
</div>

</body>
</html>
