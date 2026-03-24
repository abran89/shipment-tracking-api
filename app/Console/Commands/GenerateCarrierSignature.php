<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateCarrierSignature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carrier:signature
                            {tracking_code : Código de seguimiento}
                            {status : Estado del envío}
                            {timestamp : Fecha y hora en formato ISO8601}';

   

    /**
     * The console command description.
     *
     * @var string
     */
     protected $description = 'Genera una firma HMAC para probar el webhook del transportista';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payload = [
            'tracking_code' => $this->argument('tracking_code'),
            'status'        => $this->argument('status'),
            'timestamp'     => $this->argument('timestamp'),
        ];

        $signature = 'sha256=' . hash_hmac(
            'sha256',
            json_encode($payload),
            config('services.carrier_webhook_secret')
        );

        $this->info('Payload:');
        $this->line(json_encode([...$payload, 'signature' => $signature], JSON_PRETTY_PRINT));
    }
}
