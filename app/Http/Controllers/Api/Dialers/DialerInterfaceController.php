<?php

namespace App\Http\Controllers\Api\Dialers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;



class DialerInterfaceController extends Controller
{
    
    public function callPhone($phoneData)
    {
        $ipboxController = new IpBoxController();

        $response = $ipboxController->callPhone($phoneData);

        return $response;
    }
}
