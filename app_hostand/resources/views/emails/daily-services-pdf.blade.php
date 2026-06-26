<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Riepilogo Servizi Giornalieri</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f9f9f9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 6px;
                     border: 1px solid #e0e0e0; overflow: hidden; }
        .header { background: #6d1f3e; color: #fff; padding: 20px 24px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p  { margin: 4px 0 0; font-size: 12px; opacity: 0.85; }
        .body { padding: 24px; }
        .body p { margin: 0 0 12px; line-height: 1.6; }
        .stat-box { background: #f0e8ec; border-left: 4px solid #6d1f3e; padding: 12px 16px;
                    border-radius: 0 4px 4px 0; margin: 16px 0; }
        .stat-box strong { font-size: 22px; color: #6d1f3e; }
        .footer { background: #f5f5f5; padding: 14px 24px; font-size: 11px; color: #888;
                  border-top: 1px solid #e0e0e0; text-align: center; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>📋 Riepilogo Servizi Giornalieri</h1>
        <p>{{ $date->locale('it')->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>

    <div class="body">
        <p>Buonasera,</p>
        <p>
            In allegato trovi il riepilogo completo dei servizi programmati per oggi,
            <strong>{{ $date->locale('it')->isoFormat('D MMMM YYYY') }}</strong>,
            comprensivi di servizi assegnati e non assegnati.
        </p>

        <div class="stat-box">
            Totale servizi del giorno: <strong>{{ $totalCount }}</strong>
        </div>

        <p>
            Il documento PDF allegato (<em>{{ $filename }}</em>) contiene:
        </p>
        <ul>
            <li>Vista cronologica di tutti i servizi</li>
            <li>Vista raggruppata per operatore</li>
            <li>Stato di ogni servizio (completato, in corso, in attesa, non assegnato)</li>
        </ul>

        <p style="margin-top:20px; color:#555; font-size:13px;">
            Questo messaggio è generato automaticamente ogni sera alle 18:35.<br>
            Per scaricare il PDF manualmente:
            <a href="{{ config('app.url') }}/admin/service-assignment/daily-pdf?date={{ $date->format('Y-m-d') }}"
               style="color:#6d1f3e;">Apri PDF online</a>
        </p>
    </div>

    <div class="footer">
        Hostand Property Manager &nbsp;·&nbsp; Documento ad uso interno &nbsp;·&nbsp;
        Generato automaticamente il {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</body>
</html>
