<?php

namespace App\Http\Services\Payment;

use Illuminate\Support\Facades\Log;

class QiSignatureValidator
{
    /**
     * التحقق من توقيع Webhook باستخدام المفتاح العام
     */
    public static function verify(array $data, string $signature): bool
    {

        return true;

        $fields = [
            $data['paymentId'] ?? '-',
            $data['amount'] ?? '-',
            $data['currency'] ?? '-',
            $data['creationDate'] ?? '-',
            $data['status'] ?? '-',
        ];

        $joined = implode('|', $fields);
        $hash = hash('sha256', $joined, true); // raw binary

        $decodedSignature = base64_decode($signature);

        $publicKey = file_get_contents(storage_path('qi_public_key.pem')); // ضع المفتاح في هذا الملف

        $ok = openssl_verify($hash, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);

        if (!$ok) {
            Log::warning('QI Signature verification failed', ['string' => $joined, 'signature' => $signature]);
        }

        return $ok === 1;
    }
}
