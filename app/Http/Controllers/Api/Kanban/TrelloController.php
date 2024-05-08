<?php

namespace App\Http\Controllers\Api\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class TrelloController extends Controller
{

    const BASE_URL = 'https://api.trello.com/1/';
    
    public function createCard($cardData)
    {
        $endPoint = self::BASE_URL.'card';

        $bodyData = [
            'name' => $cardData['name'],
            'desc' => $cardData['description'],
            'pos' => 'top',
            'idList' => '64dd2be255c01d4630588ca3', //Nome da Lista: Agendado Apresentação
            'key' => 'a47f66aa2f41eb8d719b9879df164243',
            'token' => 'ATTA1dbb1511e0b683784dd30c6ae05c0ac9f089ab94c83f8520ef24c01f90da01ed78BF99BF'
        ];

        //Envia os dados
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
        ->timeout(60)
        //->asForm()
        ->post($endPoint, $bodyData);
        
        Log::debug('createCard Trello response');
        Log::debug($response);
    }
}
