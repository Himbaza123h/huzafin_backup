<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\XeroService;
use Webfox\Xero\OauthCredentialManager;

class XeroController extends Controller
{
    private $xeroService;
    public function __construct()
    {
        $this->xeroService = new XeroService();
    }
    public function index(Request $request)
    {

        $data = $this->xeroService->index();
        return view('xero', $data);
    }
    public function getInvoices(Request $request)
    {
        return $this->xeroService->getInvoices();
    }
}
