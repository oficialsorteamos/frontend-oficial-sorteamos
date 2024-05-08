<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ServiceController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\System\GenderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\System\Address;
use App\Models\System\Role;
use App\Models\System\UserRole;

class DashboardController extends Controller
{
    private $serviceController;
    private $contactController;
    private $userController;
    private $genderController;

    public function __construct()
    {
        $this->serviceController = new ServiceController();
        $this->contactController = new ContactController();
        $this->userController = new UserController();
        $this->genderController = new GenderController();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $roles = Role::where('rol_status', 'A')
                        ->get();
        
            return response()->json([
                'roles' => $roles    
            ], 201);
        } catch (e) {

        }
        
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
    public function update(Request $request, $id)
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

    public function fetchStatistics()
    {
        try {
            $statistics = array([]);
            //Traz o total de atendimentos realizados 
            $serviceTotalAmounts = $this->serviceController->getCountServicesByStatus(null);
            //Traz o total de atendimentos abertos
            $serviceOpenTotalAmounts = $this->serviceController->getCountServicesByStatus(1);
            //Traz o total de contatos ativos
            $contactTotalAmounts = $this->contactController->getCountAllContacts();
            //Traz a quantidade de usuários online
            $usersOnlineAmounts = $this->userController->getCountUsersBySituation(1);
            //Traz a quantidade de usuários ativos
            $usersActiveAmounts = $this->userController->getCountUsersByStatus('A');

            $statistics['serviceTotalAmounts'] = $serviceTotalAmounts;
            $statistics['serviceOpenTotalAmounts'] = $serviceOpenTotalAmounts;
            $statistics['contactTotalAmounts'] = $contactTotalAmounts;
            $statistics['usersOnlineAmounts'] = $usersOnlineAmounts;
            $statistics['usersActiveAmounts'] = $usersActiveAmounts;
  
            return response()->json($statistics, 201);
        } catch (e) {

        }
    }

    //Traz os contatos por gênero
    public function fetchContactsByGender()
    {   
        //Traz os gêneros cadastrados
        $genders = json_encode($this->genderController->index());
        $genders = json_decode($genders, true);
        
        $gendersCount = [];
        $gendersName = []; 
        $gendersData = [];
        foreach ($genders['original'] as $key => $gender) {
            //Conta a quantidade de contatos que possuem esse gênero
            $gendersCount[$key] = $this->contactController->getCountContactsByGender($gender['id']);
            //Guarda o nome do gênero
            $gendersName[$key] = $gender['gen_description'];    
        }
        $gendersData['gendersCount'] = $gendersCount;
        $gendersData['gendersName']['labels'] = $gendersName;
        //$gendersData['gendersName']['legend'] = (['show' => true, 'position' => 'bottom', 'fontSize' => '14px', 'fontFamily' => 'Montserrat']);


        Log::debug('gender data');
        Log::debug($gendersData);
        return response()->json(
            $gendersData
        , 201);
    }

    public function fetchAgeGroupContacts()
    {   
        $ageGroupContactsData = [];
        $options = []; 
        //Array com a faixa etária requerida dentre os contatos
        $arrayAgeGroup = Array(
            '0' => [
                'label' => '80+',
                'start_age' => 80,
                'end_age' => null,
            ],
            '1' => [
                'label' => '61-70',
                'start_age' => 61,
                'end_age' => 70,
            ],
            '2' => [
                'label' => '51-60',
                'start_age' => 51,
                'end_age' => 60,
            ],
            '3' => [
                'label' => '41-50',
                'start_age' => 41,
                'end_age' => 50,
            ],
            '4' => [
                'label' => '31-40',
                'start_age' => 31,
                'end_age' => 40,
            ],
            '5' => [
                'label' => '21-30',
                'start_age' => 21,
                'end_age' => 30,
            ],
            '6' => [
                'label' => '10-20',
                'start_age' => 10,
                'end_age' => 20,
            ],
        );
        //Traz a quantidade de contatos por faixa etária
        $ageGroupContactsData = $this->contactController->getCountContactsByAgeGroup($arrayAgeGroup);

        //Formata a array para ser consumida no dashboard
        foreach($ageGroupContactsData as $key => $ageGroup) {
            $options['labels'][$key] = $ageGroup['label'];
            $options['dataAgeGroup'][$key] = $ageGroup['count'];
        }

        Log::debug('age group options');
        Log::debug($options);

        return response()->json(
            $options
        , 201);
    }

    public function fetchBestOperators()
    {   //Traz os usuários com perfil de operador
        $usersOperators = $this->userController->getUsersByRole(2);

        foreach($usersOperators as $key => $operator) {
            //Traz os id's dos atendimentos já realizados pelo usuário
            $servicesIdUser = $this->serviceController->getServicesIdUser($operator['id']);
            //Se o usuário realizou algum atendimento
            if($servicesIdUser->isnotEmpty()) {
                //Traz o nota média dos atendimentos realizados pelo usuário
                $servicesAvg = $this->serviceController->getAvgServicesEvaluations($servicesIdUser);
                //Média das notas do atendimento realizado pelo usuário (Divide por 2 para readequar ao conceito de 5 estrelas)
                $usersOperators[$key]->setAttribute('rating', number_format($servicesAvg/2, 2));
                $usersOperators[$key]->setAttribute('countServices', count($servicesIdUser));
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

        //Log::debug('operadores');
        //Log::debug($usersOperators);

        return response()->json(
            $usersOperators
        , 201);
    }

    public function fetchServicesLastMonths()
    {
        try {
            $servicesLastMonths = [];
            //Traz a quantidade de atendimentos realizados nos últimos meses
            $servicesLastMonthsData = $this->serviceController->getCountServicesLastMonths(6);
            
            foreach($servicesLastMonthsData as $key => $lastMonths) {
                $servicesLastMonths['labels'][$key] = $lastMonths['month'].'/'.$lastMonths['year'];
                $servicesLastMonths['countServices'][$key] = $lastMonths['countServices'];
            }
            $servicesLastMonths['maxCountService'] = max($servicesLastMonths['countServices']);

            Log::debug('count services months');
            Log::debug($servicesLastMonths);

            return response()->json(
                $servicesLastMonths
            , 200);

        } catch(e) {

        }
    }

    //Traz a quantidade de contatos por Estado
    public function fetchContactsState()
    {
        $amountContactsPerState = [];

        //Conta a quantidade de contatos por estado
        $amountContactsPerState = $this->contactController->getCountContactsPerStates();

        return response()->json(
            $amountContactsPerState
        , 200);
    }

    //Traz a quantidade de atendimentos aberto x fechados
    public function fetchCompareServiceStatus()
    {
        $amountServicesStatus = [];
        //Traz os tipos de status de serviços
        $typesStatusServices = $this->serviceController->getTypesStatusServices();

        //Traz o total de atendimentos realizados 
        $serviceTotalAmounts = $this->serviceController->getCountServicesByStatus(null);
        //Se houve alguma atendimento registrado no sistema
        if($serviceTotalAmounts > 0) {
            foreach($typesStatusServices as $key => $typeStatus) {
                $amountServicesStatus['labels'][$key] = $typeStatus->typ_description;
                //Traz a quantidade de serviço de acordo com o seu status
                $amountServicesStatus['series'][$key] = $this->serviceController->getCountServicesByStatus($typeStatus->id);
                //Se for o status FECHADO 
                if($typeStatus->id == 3) {
                    //Calcula a porcentagem de atendimentos fechados
                    $amountServicesStatus['percentageClosed'] = number_format(($amountServicesStatus['series'][$key] / $serviceTotalAmounts)*100, 2);
                }
            }            
        }
        else {
            $amountServicesStatus['percentageClosed'] = 0.00;
        }
        
        
        return response()->json(
            $amountServicesStatus
        , 200);
        
    }

    /*
    public function fetchAgeGroupContacts()
    {   
        $ageGroupContactsData = [];
        $options = []; 
        //Array com a faixa etária requerida dentre os contatos
        $arrayAgeGroup = Array(
            '0' => [
                'label' => '10-20',
                'start_age' => 10,
                'end_age' => 20,
            ],
            '1' => [
                'label' => '21-30',
                'start_age' => 21,
                'end_age' => 30,
            ],
            '2' => [
                'label' => '31-40',
                'start_age' => 31,
                'end_age' => 40,
            ],
            '3' => [
                'label' => '41-50',
                'start_age' => 41,
                'end_age' => 50,
            ],
            '4' => [
                'label' => '51-60',
                'start_age' => 51,
                'end_age' => 60,
            ],
            '5' => [
                'label' => '61-70',
                'start_age' => 61,
                'end_age' => 70,
            ],
            '6' => [
                'label' => '80+',
                'start_age' => 80,
                'end_age' => null,
            ], 
        );
        //Traz a quantidade de contatos por faixa etária
        $ageGroupContactsData = $this->contactController->getCountContactsByAgeGroup($arrayAgeGroup);

        //Formata a array para ser consumida no dashboard
        foreach($ageGroupContactsData as $key => $ageGroup) {
            $options['legend']['data'][$key] = $ageGroup['label'];
            $options['series']['data'][$key]['value'] = $ageGroup['count'];
            $options['series']['data'][$key]['name'] = $ageGroup['label'];
        }
        //Pega a faixa etária com o maior número de contatos
        $maxCountAgeGroup = max($options['series']['data']);
        $options['maxAgeGroup'] = $maxCountAgeGroup['value'];

        return response()->json(
            $options
        , 201);
    }*/
}
