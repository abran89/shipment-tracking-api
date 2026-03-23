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

class SendStatusWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;


    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Packet $packet,
        private readonly PacketStatus $oldStatus,
        private readonly PacketStatus $newStatus,)
    {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookUrl = config('services.webhook_url');

        if (!$webhookUrl) {
            Log::warning('WEBHOOK_URL no definida, se omite notificación.');
            return;
        }

        Http::post($webhookUrl, [
            'tracking_code' => $this->packet->tracking_code,
            'old_status'    => $this->oldStatus->value,
            'new_status'    => $this->newStatus->value,
            'updated_at'    => $this->packet->updated_at->toIso8601String(),
        ]);
    }
}
