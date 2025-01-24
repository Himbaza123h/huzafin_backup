<?php

namespace App\Services;

use Webfox\Xero\OauthCredentialManager;
use XeroAPI\XeroPHP\Api\AccountingApi;
use Illuminate\Support\Facades\Log;

class XeroService
{
   private $xero;
   private $xeroCredentials;

   public function __construct()
   {
      $this->xero = resolve(AccountingApi::class);
      $this->xeroCredentials = resolve(OauthCredentialManager::class);
   }
   public function index()
   {
      try {
         // Check if we've got any stored credentials
         if ($this->xeroCredentials->exists()) {
            /*
                 * We have stored credentials so we can resolve the AccountingApi,
                 * If we were sure we already had some stored credentials then we could just resolve this through the controller
                 * But since we use this route for the initial authentication we cannot be sure!
                 */
            $organisationName = $this->xero->getOrganisations($this->xeroCredentials->getTenantId())->getOrganisations()[0]->getName();
            $user = $this->xeroCredentials->getUser();
            $username         = "{$user['given_name']} {$user['family_name']} ({$user['username']})";
         }
      } catch (\throwable $e) {
         // This can happen if the credentials have been revoked or there is an error with the organisation (e.g. it's expired)
         $error = $e->getMessage();
      }

      return [
         'connected'        => $this->xeroCredentials->exists(),
         'error'            => $error ?? null,
         'organisationName' => $organisationName ?? null,
         'username'         => $username ?? null
      ];
   }
   public function getInvoices()
   {
      if ($this->xeroCredentials->exists()) {

         $invoices =
            $this->xero->getInvoices($this->xeroCredentials->getTenantId());
         return response()->success($invoices, '');
      }
   }
   public function getCompanyDetail(): array
   {
      if ($this->xeroCredentials->exists()) {
         $company = [
            "logo" => $this->xero->getBrandingThemes($this->xeroCredentials->getTenantId())[0]->getLogoUrl(),
            "name" => $this->xero->getOrganisations($this->xeroCredentials->getTenantId())->getOrganisations()[0]->getName()
         ];
         return $company;
      }
   }
   public function getTaxRate(string $taxType)
   {
      $taxRate = 0;
      if ($this->xeroCredentials->exists()) {
         $rates = $this->xero->getTaxRates($this->xeroCredentials->getTenantId());
         $taxRatesArray = $rates->getTaxRates(); 
         foreach ($taxRatesArray as $rate) {
               Log::channel('xerowebhooks')->info("Each tax type --> " . json_encode($rate));
               Log::channel('xerowebhooks')->info("Each tax type -- " . $rate->getTaxType() . " -- " . $rate->getEffectiveRate());
               if ($rate->getTaxType() === $taxType) {
                  $taxRate = $rate->getEffectiveRate();
                  break;
               }
         }
      }
      return $taxRate;
   }
   public function getTaxName(string $taxType)
   {
      $name = 0;
      if ($this->xeroCredentials->exists()) {
         $rates = $this->xero->getTaxRates($this->xeroCredentials->getTenantId());
         $taxRatesArray = $rates->getTaxRates();
         foreach ($taxRatesArray as $rate) {
               Log::channel('xerowebhooks')->info("Each tax type -- " . $rate->getTaxType() . " -- " . $rate->getEffectiveRate());
               if ($rate->getTaxType() === $taxType) {
                  $name = $rate->getName();
                  break;
               }
         }
      }
      return $name;
   }
}
