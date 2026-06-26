<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MaintenanceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendDailyServiceReport extends Command
{
    /**
     * Comando Artisan: php artisan report:daily-services
     */
    protected $signature = 'report:daily-services
                            {--date= : Data del report (Y-m-d), default oggi}
                            {--email= : Destinatario email, default da config}';

    protected $description = 'Genera il PDF dei servizi giornalieri e lo invia per email';

    public function handle(): int
    {
        // ── Data del report ──────────────────────────────────────────────
        $dateStr = $this->option('date') ?? Carbon::tomorrow()->format('Y-m-d');
        $date    = Carbon::parse($dateStr);

        $this->info("Generazione report servizi per {$date->format('d/m/Y')}...");

        // ── Recupero servizi ─────────────────────────────────────────────
        $services = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers', 'rider'])
            ->whereDate('arrival_time', $date)
            ->orderByRaw('CASE WHEN arrival_time IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('arrival_time', 'asc')
            ->get();

        // Includi anche servizi senza arrival_time ma creati oggi
        $noDateServices = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers', 'rider'])
            ->whereNull('arrival_time')
            ->whereDate('created_at', $date)
            ->get();

        $services = $services->merge($noDateServices);

        $this->info("Trovati {$services->count()} servizi.");

        // ── Raggruppa per operatore ──────────────────────────────────────
        $byOperator = $services->groupBy(function ($s) {
            $m = $s->maintainers;
            if ($m && $m->id) {
                return trim($m->first_name . ' ' . $m->last_name);
            }
            return '— Non assegnato';
        });

        // ── Genera PDF ───────────────────────────────────────────────────
        $pdf = Pdf::loadView('admin.service-assignment.daily-pdf', [
            'date'        => $date->format('Y-m-d'),
            'services'    => $services,
            'byOperator'  => $byOperator,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $pdfContent  = $pdf->output();
        $filename    = 'servizi-' . $date->format('Y-m-d') . '.pdf';

        // ── Destinatario email ───────────────────────────────────────────
        $recipient = $this->option('email')
            ?? config('app.daily_report_email', 'gruppokosmos00@gmail.com');

        // ── Invia email ──────────────────────────────────────────────────
        Mail::send([], [], function ($message) use ($pdfContent, $filename, $recipient, $date, $services) {
            $message->to($recipient)
                ->subject('📋 Servizi ' . $date->locale('it')->isoFormat('dddd D MMMM YYYY') . ' (' . $services->count() . ' servizi)')
                ->html(
                    '<p>Ciao,</p>'
                    . '<p>In allegato trovi il report PDF dei servizi del giorno <strong>'
                    . $date->locale('it')->isoFormat('dddd D MMMM YYYY')
                    . '</strong>.</p>'
                    . '<ul>'
                    . '<li>Totale servizi: <strong>' . $services->count() . '</strong></li>'
                    . '<li>Completati: <strong>' . $services->where('status', 'completed')->count() . '</strong></li>'
                    . '<li>In corso: <strong>' . $services->where('status', 'in_progress')->count() . '</strong></li>'
                    . '<li>In attesa: <strong>' . $services->where('status', 'pending')->count() . '</strong></li>'
                    . '</ul>'
                    . '<p style="color:#999;font-size:12px;">— Hostand Property Manager</p>'
                )
                ->attachData($pdfContent, $filename, [
                    'mime' => 'application/pdf',
                ]);
        });

        $this->info("Email inviata a {$recipient} con allegato {$filename}");

        return self::SUCCESS;
    }
}
