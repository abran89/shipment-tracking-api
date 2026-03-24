<?php

namespace App\Jobs;

use App\Enums\PacketStatus;
use App\Models\Packet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class SendStatusWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Packet $packet,
        private readonly PacketStatus $oldStatus,
        private readonly PacketStatus $newStatus,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookUrl = config('services.webhook_url');

        if (!$webhookUrl) {
            throw new \Exception('WEBHOOK_URL no configurada. No se puede enviar el webhook.');
        }

        try {
            $response = Http::timeout(30)->post($webhookUrl, [
                'tracking_code' => $this->packet->tracking_code,
                'old_status'    => $this->oldStatus->value,
                'new_status'    => $this->newStatus->value,
                'updated_at'    => $this->packet->updated_at?->toIso8601String() ?? now()->toIso8601String(),
            ]);

            if (!$response->successful()) {
                throw new Exception("Webhook response: {$response->status()} - {$response->body()}");
            }

            Log::info("Webhook enviado exitosamente para {$this->packet->tracking_code}", [
                'old_status' => $this->oldStatus->value,
                'new_status' => $this->newStatus->value,
            ]);

        } catch (Exception $e) {

            Log::warning("Error al enviar webhook (intento {$this->attempts()}): {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error("Job SendStatusWebhook falló definitivamente para {$this->packet->tracking_code}", [
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'error'      => $exception?->getMessage(),
            'attempts'   => $this->tries,
        ]);
    }
}
