<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\MaintenanceRequest;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SendDailyServicesPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hostand:send-daily-pdf
                            {--date= : Data nel formato YYYY-MM-DD (default: oggi)}
                            {--email= : Destinatario override (default: sistema@hostand.eu)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera il PDF riepilogativo dei servizi giornalieri e lo invia via email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date      = $this->option('date') ?? Carbon::tomorrow()->format('Y-m-d');
        $recipient = $this->option('email') ? [$this->option('email')] : [
    'servizi.atman@gmail.com',
    'knmkosmos@gmail.com',
    'portale@hostand.eu',
];

        try {
            $carbon = Carbon::parse($date);
        } catch (\Exception $e) {
            $this->error("Data non valida: {$date}");
            return Command::FAILURE;
        }

        $this->info("Generazione PDF servizi del {$carbon->format('d/m/Y')}...");

        // Recupera tutti i servizi del giorno
        $services = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers', 'rider'])
            ->whereDate('arrival_time', $carbon)
            ->orderByRaw('CASE WHEN arrival_time IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('arrival_time', 'asc')
            ->get();

        if ($services->isEmpty()) {
            $this->warn("Nessun servizio trovato per il {$carbon->format('d/m/Y')} — invio email comunque.");
        }

        // Raggruppa per operatore
        $byOperator = $services->groupBy(function ($s) {
            $m = $s->maintainers;
            return ($m && $m->id) ? trim($m->first_name . ' ' . $m->last_name) : '— Non assegnato';
        });

        $generatedAt = Carbon::now()->format('d/m/Y H:i');

        // Genera il PDF in memoria ($date è la stringa YYYY-MM-DD usata dal blade)
        $pdf = Pdf::loadView('admin.service-assignment.daily-pdf', compact(
            'services', 'byOperator', 'date', 'carbon', 'generatedAt'
        ))->setPaper('a4', 'landscape');

        $pdfContent = $pdf->output();
        $filename   = 'servizi-' . $carbon->format('Y-m-d') . '.pdf';

        $recipientList = implode(', ', (array) $recipient);
$this->info("PDF generato ({$services->count()} servizi). Invio a {$recipientList}...");

        // Invia email con allegato
$dateLabel  = $carbon->locale('it')->isoFormat('dddd D MMMM YYYY');
$totalCount = $services->count();
Mail::send([], [], function ($message) use ($pdfContent, $filename, $recipient, $dateLabel, $totalCount) {
    $message->to($recipient)
        ->subject("📋 Servizi del {$dateLabel} — Riepilogo Hostand")
        ->html("<p>Buonasera,</p><p>In allegato il riepilogo dei servizi del <strong>{$dateLabel}</strong>.<br>Totale: <strong>{$totalCount}</strong> servizi.</p>")
        ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
});

        $this->info("✓ Email inviata con successo a {$recipientList}");

        return Command::SUCCESS;
    }
}
