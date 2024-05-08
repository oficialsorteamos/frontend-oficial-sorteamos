<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Jobs\SendCompanyNotification;
use App\Models\Administration\Notification\Notification;
use App\Models\Administration\Notification\NotificationCompany;
use App\Models\Administration\Notification\NotificationTypeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AdmNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::debug('notification $request');
        Log::debug($request);

        //Se o usuário não digitou nada no campo de pesquisa
        $skip = (($request['page']-1) * $request['perPage']);

        $notifications = Notification::with('typeUsers', 'companies');
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($request['q'] != '') {
            $notifications = $notifications->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('adm_notifications.not_title', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('adm_notifications.not_message', 'like', '%'.trim($request['q']).'%');
            });
        }
        //Se as notificações foram filtradas por alguma EMPRESA
        if($request['company'] != '') {
            $company = json_decode($request['company'], true);
            $notifications = $notifications->whereHas('companies', function($q) use($company)
            {
                $q->where('adm_companies.id', $company['id']);	
            });
        }
        $notifications = $notifications->orderBy('adm_notifications.created_at', 'DESC');
        $total = $notifications->count();
        $notifications = $notifications->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        //Log::debug('notifications');
        //Log::debug($notifications);

        return response()->json([
            'notifications'=> $notifications,
            'total'=> $total,
        ], 201);
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
    public function store(Request $request)
    {   
        //Log::debug('Dados parceiro');
        //Log::debug($request);
        
        $newNotification = new Notification();
        $newNotification->type_notification_id = 1; //COLOCA SEMPRE COMO PUSH, POR ENQUANTO
        $newNotification->not_title = $request['not_title'];
        $newNotification->not_message = $request['not_message'];
        
        $newNotification->save();

        return $newNotification;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request)
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
        
    }

    public function sendNotification(Request $request)
    {
        $companyController = new CompanyController();
        
        //Se a mensagem foi salva
        if($notification = self::store($request)) {
            //Para cada tipo de usuário que receberá a mensagem
            foreach($request['typeUsers'] AS $typeUser) {
                self::storeNotificationTypeUser($notification['id'], $typeUser['id']);
            }

            //Se o usuário selecionou para enviar notificação para TODAS as empresas
            if($request['companies'][0]['id'] == 0) {
                //Traz todas as empresas ativas
                $companiesData = $companyController->getCompaniesByStatus(1);
                $companiesData = json_encode($companiesData);
                $companiesData = json_decode($companiesData, true);
                //Para cada empresa ativa
                foreach($companiesData['original']['companies'] AS $company) {
                    $endPoint = $company['com_url'].'/api/administration/notification/webhook-notification';
                    self::storeNotificationCompany($notification['id'], $company['id']);

                    $notificationData = [
                        'title' => $request['not_title'],
                        'message' => $request['not_message'],
                        'typeUsers' => $request['typeUsers'],
                        'api_token' => env('CHATX_API_TOKEN'),
                        'endPoint' => $endPoint,
                    ];

                    SendCompanyNotification::dispatch($notificationData)->onQueue('send_company_notification');
                }
            }
            else {
                foreach($request['companies'] AS $company) {
                    $endPoint = $company['com_url'].'/api/administration/notification/webhook-notification';
                    self::storeNotificationCompany($notification['id'], $company['id']);

                    $notificationData = [
                        'title' => $request['not_title'],
                        'message' => $request['not_message'],
                        'typeUsers' => $request['typeUsers'],
                        'api_token' => env('CHATX_API_TOKEN'),
                        'endPoint' => $endPoint,
                    ];

                    //Cria o job na fila
                    SendCompanyNotification::dispatch($notificationData)->onQueue('send_company_notification');

                }
            }
        }
    }

    //Envia a notificação para a empresa
    public function sendNotificationRoutine($notificationData)
    {
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Content-Type' => 'application/json; charset=utf-8'
        ])
        ->timeout(10)
        //->asForm()
        ->post($notificationData['endPoint'], $notificationData);
    }

    //Salva o tipo de usuário para quem a notificação foi enviada
    public function storeNotificationTypeUser($notificationId, $typeUserId)
    {
        $notiifcationTypeUser = new NotificationTypeUser();
        $notiifcationTypeUser->notification_id = $notificationId;
        $notiifcationTypeUser->role_id = $typeUserId;

        $notiifcationTypeUser->save();
    }

    //Salva a empresa para quem a notificação foi enviada
    public function storeNotificationCompany($notificationId, $companyId)
    {
        $notiifcationCompany = new NotificationCompany();
        $notiifcationCompany->notification_id = $notificationId;
        $notiifcationCompany->company_id = $companyId;

        $notiifcationCompany->save();
    }

    //Traz as empresas que receberam uma determinada notificação
    public function fetchCompaniesByNotification(Request $request)
    {
        Log::debug('fetchCompaniesByNotification request');
        Log::debug($request);

        //Se o usuário não digitou nada no campo de pesquisa
        $skip = (($request['page']-1) * $request['perPage']);

        $notifiedCompanies = NotificationCompany::join('adm_companies', 'adm_notifications_companies.company_id', 'adm_companies.id');
                            //->select('con_contacts.id as id' ,'con_name', 'gender_id', 'pipeline_id', 'status_id', 'con_phone', 'con_avatar');
        //Se o usuário fez alguma busca pelo campo de texto livre
        if($request['q'] != '') {
            $notifiedCompanies = $notifiedCompanies->where(function ($query) use($request) {
                //Verifica se a busca coincide com o nome de algum usuário
                $query->where('adm_companies.com_name', 'like', '%'.trim($request['q']).'%')
                        //Verifica se busca coincide com o telefone de algum usuário
                        ->orWhere('adm_companies.com_cnpj', 'like', '%'.trim($request['q']).'%')
                        ->orWhere('adm_companies.com_cpf', 'like', '%'.trim($request['q']).'%');
            });
        }
        $notifiedCompanies = $notifiedCompanies->where('adm_notifications_companies.notification_id', $request['notificationId']);
        $notifiedCompanies = $notifiedCompanies->orderBy('adm_notifications_companies.created_at', 'DESC');
        $total = $notifiedCompanies->count();
        $notifiedCompanies = $notifiedCompanies->skip($skip) //Quantidade de itens que serão desconsiderados por já terem sido carregados 
                    ->take($request['perPage']) //Quantidade de itens trazidos
                    ->get();

        Log::debug('notifications');
        Log::debug($notifiedCompanies);

        return response()->json([
            'companies'=> $notifiedCompanies,
            'total'=> $total,
        ], 201);
    }
}
