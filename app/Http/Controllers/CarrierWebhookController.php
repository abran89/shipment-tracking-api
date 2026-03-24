<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Packet;
use App\Enums\PacketStatus;
use App\Services\PacketService;

class CarrierWebhookController extends Controller
{
    public function __construct(
        private readonly PacketService $packetService,
    ) {}

    public function __invoke(CarrierWebhookRequest $request): JsonResponse
    {
        if (!$request->isValidSignature()) {
            return response()->json(['message' => 'Firma inválida.'], 401);
        }

        $packet = Packet::where('tracking_code', $request->input('tracking_code'))->first();

        if (!$packet) {
            return response()->json(['message' => 'Envío no encontrado.'], 404);
        }

        $this->packetService->updateStatus($packet, PacketStatus::Delivered);

        return response()->json(['message' => 'Estado actualizado correctamente.']);
    }
}
