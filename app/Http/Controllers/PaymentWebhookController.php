<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGateway;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\OrderService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Penerima webhook pembayaran.
 * Payload TIDAK dipercaya sebelum signature HMAC diverifikasi; event duplikat diabaikan.
 *
 * Format payload (JSON): {"event_id": "...", "reference": "...", "status": "paid|failed", "amount": 12345}
 * Header: X-Signature = hex(hmac_sha256(body_mentah, PAYMENT_WEBHOOK_SECRET))
 */
class PaymentWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGateway $gateway, OrderService $orders): JsonResponse
    {
        $raw = $request->getContent();

        if (! $gateway->verifyWebhookSignature($raw, $request->header('X-Signature'))) {
            return response()->json(['message' => 'Signature tidak valid.'], 401);
        }

        $data = json_decode($raw, true);
        $validator = Validator::make(is_array($data) ? $data : [], [
            'event_id' => ['required', 'string', 'max:100'],
            'reference' => ['required', 'string', 'max:64'],
            'status' => ['required', 'in:paid,failed'],
            'amount' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Payload tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $payment = Payment::where('reference', $data['reference'])->first();
        if (! $payment) {
            return response()->json(['message' => 'Referensi pembayaran tidak ditemukan.'], 404);
        }

        if (isset($data['amount']) && (int) $data['amount'] !== (int) $payment->amount) {
            return response()->json(['message' => 'Nominal tidak sesuai.'], 422);
        }

        try {
            $event = WebhookEvent::create([
                'event_id' => $data['event_id'],
                'provider' => $gateway->name(),
                'payload' => ['reference' => $data['reference'], 'status' => $data['status']],
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json(['message' => 'Event sudah diproses.', 'duplicate' => true]);
        }

        try {
            $meta = ['webhook_event_id' => $data['event_id']];
            $data['status'] === 'paid'
                ? $orders->markPaymentPaid($payment, $meta)
                : $orders->failPayment($payment, $meta);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 409);
        }

        $event->update(['processed_at' => now()]);

        return response()->json(['message' => 'OK']);
    }
}
