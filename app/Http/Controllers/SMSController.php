<?php

namespace App\Http\Controllers;

use App\Services\SMSService;
use Illuminate\Http\Request;

class SMSController extends Controller
{
    private $smsService;

    public function __construct(SMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    // Handle send SMS request
    public function sendSMS(Request $request)
    {
        $smsData = [
            'msisdn' => $request->input('msisdn'),
            'message' => $request->input('message'),
            'msgRef' => $request->input('msgRef'),
            'sender_id' => $request->input('sender_id', 'FDI'),
        ];

        $response = $this->smsService->sendSMS($smsData);

        if (isset($response['error'])) {
            return response()->json([
                'message' => 'SMS sending failed',
                'error' => $response['error'],
            ], 500);
        }

        return response()->json([
            'success' => $response['success'] ?? false,
            'message' => $response['message'] ?? 'Message queued successfully',
            'cost' => $response['cost'] ?? '',
            'msgRef' => $response['msgRef'] ?? '',
            'gatewayRef' => $response['gatewayRef'] ?? '',
        ]);
    }
}
