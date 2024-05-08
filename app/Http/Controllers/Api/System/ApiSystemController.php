<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;



class ApiSystemController extends Controller
{
    //Traz a empresa de acordo com o CPF ou CNPJ
    public function getCompanyByDocumentId($urlBase, $documentId)
    {
        $endPoint = $urlBase.'/api/administration/company/get-company-by-document-id';

        $companyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'documentId' => $documentId,
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $companyData);

        return $response;
    }

    public function updateCompany($urlBase, $companyData)
    {
        $endPoint = $urlBase.'/api/administration/company/update-company-api';

        $bodyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'companyData' => $companyData,
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $bodyData);

        return $response;
    }

    public function addCompany($urlBase, $companyData)
    {
        $endPoint = $urlBase.'/api/administration/company/add-company-api';

        $bodyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'companyData' => $companyData,
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $bodyData);

        return $response;
    }

    //Traz a empresa de acordo com o CPF ou CNPJ
    public function getPartnerByDocumentId($urlBase, $documentId)
    {
        $endPoint = $urlBase.'/api/administration/partner/get-partner-by-document-id';

        $companyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'documentId' => $documentId,
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $companyData);

        return $response;
    }

    //Atualiza os dados do da empresa no ambiente (servidor) da mesma
    public function updateCustomer($urlBase, $companyData)
    {
        $endPoint = $urlBase.'/api/setting/company/update-company-api';

        $bodyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'companyData' => $companyData,
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $bodyData);

        return $response;
    }

    //Traz as faturas com um determinado status de uma empresa
    public function getInvoicesByStatus($urlBase, $invoiceData)
    {
        $endPoint = $urlBase.'/api/financial/invoice/get-invoices-by-status';

        $bodyData = [
            'api_token' => env('CHATX_API_TOKEN'),
            'invoiceData' => $invoiceData,
        ];

        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($endPoint, $bodyData);

        return $response;
    }

    
}
