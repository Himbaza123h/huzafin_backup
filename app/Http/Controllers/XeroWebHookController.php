<?php

namespace App\Http\Controllers;

use App\Enums\ActionEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webfox\Xero\Webhook;
use App\Services\XeroWebHookService;
use Illuminate\Support\Facades\Log;

class XeroWebHookController extends Controller
{
    protected $XeroWebHookService;

    public function __construct(XeroWebHookService $service)
    {
        $this->XeroWebHookService = $service;
    }

    public function __invoke(Request $request, Webhook $webhook)
    {
        Log::info("Xero Call-->");
        // Validate Xero webhook signature
        if (!$webhook->validate($request->header('x-xero-signature'))) {
            Log::error("Xero Call Unauthorized");
            return response('', Response::HTTP_UNAUTHORIZED);
        }

        // Process webhook events
        foreach ($webhook->getEvents() as $event) {
            Log::info("Xero Callback Event");
            $this->processEvent($request, $event);
        }

        return response('', Response::HTTP_OK);
    }

    protected function processEvent(Request $request, $event)
    {
        // Dispatch event to the corresponding handler in the service
        if ($event->getEventType() === 'CREATE' && $event->getEventCategory() === 'INVOICE') {
            $this->XeroWebHookService->handle($request, $event->getResource(), ActionEnum::SAVE);
        } elseif ($event->getEventType() === 'UPDATE' && $event->getEventCategory() === 'INVOICE') {
            $this->XeroWebHookService->handle($request, $event->getResource(), ActionEnum::UPDATE);
        }
    }
}
