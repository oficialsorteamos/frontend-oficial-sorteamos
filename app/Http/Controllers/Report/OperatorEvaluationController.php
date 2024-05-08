<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\UserController;
use App\Models\Chat\Service;
use App\Models\Contact\ContactTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;
use Excel;
use Storage;
use DB;
use Carbon\Carbon;

class OperatorEvaluationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $params)
    {
       
    }  

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($mailingData)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
               
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage. 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($mailingData)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function fetchOperatorEvaluation(Request $request)
    {
        $serviceController = new ServiceController();
        $userController = new UserController();

        Log::debug('request fetchOperatorEvaluation');
        Log::debug($request);

        //Se estiver filtrando por um usuário em específico
        if($request['user'] != '') {
            $usersOperators = $userController->getUserById($request['user']);
            $usersOperators = collect(array($usersOperators));
        }
        else {
            $usersOperators = $userController->getUsersByRole(2);
        }
        
        foreach($usersOperators as $key => $operator) {
            $servicesIdUser = Service::with('actions');
            //Traz todos os atendimentos de um determinado período
            if($request['period']) {
                $periodDivided = explode('até', $request['period']);
                //Se foi selecionada apenas a data inicial
                if(count($periodDivided) == 1) {
                    $servicesIdUser = $servicesIdUser->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', $periodDivided[0].' 23:59:59']);
                }//Se foi selecionada a data inicial e final
                else {
                    $servicesIdUser = $servicesIdUser->whereBetween('cha_services.created_at', [$periodDivided[0].' 00:00:00', trim($periodDivided[1].' 23:59:59')]);
                }
            }
            //Filtra pelos atendimentos realizados pelo usuário
            $servicesIdUser = $servicesIdUser->whereHas('actions', function($q) use($operator)
            {
                $q->where('cha_actions.user_id', $operator['id']);	
            });
            $servicesIdUser = $servicesIdUser->pluck('id');

            //Se o usuário realizou algum atendimento
            if($servicesIdUser->isnotEmpty()) {
                //Traz o nota média dos atendimentos realizados pelo usuário
                $servicesAvg = $serviceController->getAvgServicesEvaluations($servicesIdUser);
                $totalServicesEvaluations = $serviceController->getTotalServicesEvaluationsByServicesId($servicesIdUser);
                //Média das notas do atendimento realizado pelo usuário (Divide por 2 para readequar ao conceito de 5 estrelas)
                $usersOperators[$key]->setAttribute('rating', number_format($servicesAvg/2, 2));
                $usersOperators[$key]->setAttribute('countServices', count($servicesIdUser));
                $usersOperators[$key]->setAttribute('totalServicesEvaluations', $totalServicesEvaluations);
            } else {
                $usersOperators[$key]->setAttribute('rating', '-');
                $usersOperators[$key]->setAttribute('countServices', 0);
            }
        }
        
        //Ordena da maior nota para a menor
        $usersOperators = $usersOperators->sortBy([
            ['rating', 'desc'],
            ['countServices', 'desc'],
        ]);
        
        //Reordena os indexs
        $usersOperators = $usersOperators->values();


        Log::debug('operadores e suas notas');
        Log::debug($usersOperators);

        return response()->json([
            'operators' => $usersOperators,
            'total' => count($usersOperators),
            ]
        , 201);
    }
}