<?php

namespace App\Http\Controllers\Api\Kanban;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class KanbanInterfaceController extends Controller
{
    
    public function createCard($cardData)
    {
        $trelloController = new TrelloController();

        $response = $trelloController->createCard($cardData);

        return $response;
    }
}
