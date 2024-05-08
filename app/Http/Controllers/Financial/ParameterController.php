<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use DateTime;


class ParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
    public function store(Request $request)
    {
        $parameter = new Parameter();
        $parameter->type_parameter_id = $request['type_parameter_id'];
        $parameter->par_value = $request['par_value'];
        $parameter->save();
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

    //Traz os parâmetros pelo seu tipo
    public function getParameterByType($parameterTypeId)
    {
        $parameter = Parameter::where('type_parameter_id', $parameterTypeId)
                                ->where('par_status', 'A')
                                ->first();
        return $parameter;
    }

    //Traz todos os parâmetros de pagamento
    public function fetchParameters()
    {
        $parameters = Parameter::where('par_status', 'A')->get();

        return $parameters;
    }

    //Traz os parâmetros de cobrança
    public function fetchParametersCharge()
    {
        $parametersCharge = Parameter::whereIn('type_parameter_id', [5, 6, 7, 8 ,9, 10, 11, 12])
                                    ->where('par_status', 'A')
                                    ->orderBy('type_parameter_id', 'ASC')
                                    ->get();

        return response()->json([
            'parametersCharge'=> $parametersCharge,
        ], 200);
    }

    //Traz os parâmetros gerais
    public function fetchParametersGeneral()
    {
        $parametersGeneral = Parameter::whereIn('type_parameter_id', [4])
                                    ->where('par_status', 'A')
                                    ->orderBy('type_parameter_id', 'ASC')
                                    ->get();

        return response()->json([
            'parametersGeneral'=> $parametersGeneral,
        ], 200);
    }

    public function updateParametersCharge(Request $request)
    {
        Log::debug('updateParametersCharge $request');
        Log::debug($request);
        $parameters = $request->all();
        //Para cada parâmetro
        foreach($parameters as $parameter) {
            
            Log::debug('parameter');
            Log::debug($parameter);
            //Atualiza o parâmetro
            $parameterData = Parameter::find($parameter['id']);
            $parameterData->par_value = $parameter['par_value']; 
            $parameterData->par_proportional_charge = $parameter['par_proportional_charge'];
            $parameterData->save();
        }
    }

    //Traz os parâmetros pelo seu tipo
    public function fetchParameterByType($parameterTypeId)
    {
        $parameter = Parameter::where('type_parameter_id', $parameterTypeId)
                                ->where('par_status', 'A')
                                ->first();
        
        return response()->json([
            'parameter'=> $parameter,
        ], 200);
    }

    //Atualiza os parâmetros gerais da empresa
    public function updateParametersGeneral(Request $request)
    {
        Log::debug('updateParametersGeneral $request');
        Log::debug($request);
        $parameters = $request->all();
        //Para cada parâmetro
        foreach($parameters as $parameter) {
            
            Log::debug('parameter');
            Log::debug($parameter);
            //Atualiza o parâmetro
            $parameterData = Parameter::find($parameter['id']);
            //Se for a data de início de operação
            if($parameter['type_parameter_id'] == 4) {
                $date = str_replace('/', '-', $parameter['par_value']);
                $dateFormatted = date("Y-m-d", strtotime($date));
                //$dateFormatted = DateTime::createFromFormat('d-m-Y', $parameter['par_value']);
                Log::debug('$dateFormatted');
                Log::debug($dateFormatted);
                $parameterData->par_value = $dateFormatted;
            }
            else {
                $parameterData->par_value = $parameter['par_value'];
            }
             
            $parameterData->par_proportional_charge = $parameter['par_proportional_charge'];
            $parameterData->save();
        }

        return response()->json([
            'generalData'=> $parameters,
        ], 200);
    }

    public function updateCompanyCharges(Request $request)
    {
        Log::debug('updateCompanyCharges request');
        Log::debug($request);

        foreach($request['charges'] AS $key => $chargeValue) {
            //Começa o tipo de cobrança pelo ID 1
            if($key > 0) {
                $proportionalCharge = null;
                $typeParameterId = null;
                // Se o parâmetro for a MENSALIDADE
                if($key == 1) {
                    $typeParameterId = 6;
                    //Pega o parâmetro de COBRANÇA PROPORCIONAL da MENSALIDADE
                    $proportionalCharge = $request['charges'][$key+1];
                } 
                //Se o parâmetro for a cobrança por USUÁRIO
                else if ($key == 3) {
                    $typeParameterId = 7;
                    //Pega o parâmetro de COBRANÇA PROPORCIONAL por USUÁRIO
                    $proportionalCharge = $request['charges'][$key+1];
                }//Se a cobraça for por CANAL OFICIAL
                else if ($key == 5) {
                    $typeParameterId = 8;
                    //Pega o parâmetro for de COBRANÇA PROPORCIONAL por CANAL OFICIAL
                    $proportionalCharge = $request['charges'][$key+1];
                }//Se a cobraça for por CANAL NÃO OFICIAL
                else if ($key == 7) {
                    $typeParameterId = 9;
                    //Pega o parâmetro for de COBRANÇA PROPORCIONAL por CANAL NÃO OFICIAL
                    $proportionalCharge = $request['charges'][$key+1];
                }//Se a cobrança for por ENVIO DE MENSAGEM VIA WHATSAPP EM UMA CAMPANHA
                else if ($key == 9) {
                    $typeParameterId = 5;
                } //Se a cobrança for por ENVIO DE SMS
                else if ($key == 10) {
                    $typeParameterId = 10;
                } //Se a cobrança for por RETORNO DE SMS
                else if ($key == 11) {
                    $typeParameterId = 11;
                } //Se a cobrança for por LIGAÇÃO VIA WHATSAPP
                else if ($key == 12) {
                    $typeParameterId = 12;
                }

                //Se houver algum parâmetro correspodente
                if($typeParameterId) {
                    $hasCharge = null;
                    $hasCharge = self::getParameterByType($typeParameterId);
                    //Se a cobrança já foi cadastrada em algum momento
                    if($hasCharge) {
                        //Atualiza a cobrança existente
                        $hasCharge->par_value = $chargeValue;
                        //Se for um tipo de cobrança que possui cobrança proporcional, caso essa cobrança esteja desabilitada, também desabilita a cobrança proporcional
                        if($typeParameterId == 6 || $typeParameterId == 7 || $typeParameterId == 8 || $typeParameterId == 9) {
                            $hasCharge->par_proportional_charge = $chargeValue == '1'? $proportionalCharge : '0';
                        }
                        $hasCharge->save();
                    }
                }
            }
        }
    }
}
