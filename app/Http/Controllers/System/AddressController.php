<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\System\Address;

class AddressController extends Controller
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
    public function store(Request $request, $csvFile=false)
    {  
        try {
            //Log::debug("store endereço");
            //Log::debug($request);

            if($csvFile) {
                $newAddress = new Address();
                $newAddress->user_id = $request['userId'];
                $newAddress->type_user_id = $request['typeUserId'];
                $newAddress->add_zip_code = preg_replace('/[^0-9]/', '', $request['cep']); //Deixa apenas os números 
                $newAddress->add_street = mb_convert_encoding($request['street'] , 'UTF-8' , 'ASCII');
                $newAddress->add_district = mb_convert_encoding($request['district'] , 'UTF-8' , 'ASCII');
                $newAddress->add_number = $request['number'];
                $newAddress->add_address_complement = mb_convert_encoding($request['address_complement'] , 'UTF-8' , 'ASCII');
                $newAddress->add_city = mb_convert_encoding($request['city'] , 'UTF-8' , 'ASCII');
                $newAddress->state_id = $request->state['id'];
                $newAddress->country_id = $request->country['id'];
                
                $newAddress->save();
            }
            else {
                $newAddress = new Address();
                $newAddress->user_id = $request['userId'];
                $newAddress->type_user_id = $request['typeUserId'];
                $newAddress->add_zip_code = preg_replace('/[^0-9]/', '', $request['cep']); //Deixa apenas os números 
                $newAddress->add_street = $request['street'];
                $newAddress->add_district = $request['district'];
                $newAddress->add_number = $request['number'];
                $newAddress->add_address_complement = $request['address_complement'];
                $newAddress->add_city = $request['city'];
                $newAddress->state_id = $request->state['id'];
                $newAddress->country_id = $request->country['id'];
                
                if($newAddress->save()) {
                    $addressData[0]['id'] = $newAddress->id;
                    $addressData[0]['cep'] = $request['cep'];
                    $addressData[0]['street'] = $request['street'];
                    $addressData[0]['district'] = $request['district'];
                    $addressData[0]['number'] = $request['number'];
                    $addressData[0]['address_complement'] = $request['address_complement'];
                    $addressData[0]['city'] = $request['city'];
                    $addressData[0]['state'] = $request['state'];
                    $addressData[0]['country'] = $request['country'];
                }
                
            }
            
            if(isset($addressData)) {
                return response()->json([
                    'address' => $addressData    
                ], 201);
            }
            else {
                return response()->json(
                    ''    
                , 201);
            }   
        }
        catch(e) {

        }
        
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
        try {
            //Log::debug('atualizar endereço');
            //Log::debug($request);
            $address = Address::find($id);
            $address->add_zip_code = preg_replace('/[^0-9]/', '', $request['cep']); //Deixa apenas os números
            $address->add_street = $request['street'];
            $address->add_district = $request['district'];
            $address->add_number = $request['number'];
            $address->add_address_complement = $request['address_complement'];
            $address->add_city = $request['city'];
            $address->state_id = $request['state']['id'];
            $address->country_id = $request['country']['id'];
            $address->save();

            $addressData[0]['id'] = $address->id;
            $addressData[0]['cep'] = $request['cep'];
            $addressData[0]['street'] = $request['street'];
            $addressData[0]['district'] = $request['district'];
            $addressData[0]['number'] = $request['number'];
            $addressData[0]['address_complement'] = $request['address_complement'];
            $addressData[0]['city'] = $request['city'];
            $addressData[0]['state'] = $request['state'];
            $addressData[0]['country'] = $request['country'];

            return response()->json([
                'address' => $addressData    
            ], 200);
        } catch(e) {

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Deleta o bloco
        Address::find($id)->delete();

        return response()->json([], 200);
    }

    public function fetchAddressesUser(Request $request)
    {
        try {

            //Traz o endereço de acordo com o id do usuário e o tipo (contato, operador, etc)
            $addresses = Address::with('state', 'country')
                                ->select('id', 'add_zip_code as cep', 'add_number as number', 'add_street as street', 'add_address_complement as address_complement', 'add_district as district', 'add_city as city', 'state_id', 'country_id')
                                ->where('user_id', $request['userId'])
                                ->where('type_user_id', $request['typeUserId'])
                                ->get();
            
            Log::debug('Endereços');
            Log::debug($addresses);
            return response()->json(
                $addresses    
            , 201);
        } catch(e) {

        }
    }

    //Quantidade de endereços cadastrados para um determinado contato
    public function countAddressesUser($userId, $typeContactId)
    {
        $amountAddresses = Address::where('user_id', $userId)
                                ->where('type_user_id', $typeContactId)
                                ->count();
        
        return $amountAddresses;
    }

    public function getAddressesUser($userId, $typeUserId)
    {
        try {

            //Traz o endereço de acordo com o id do usuário e o tipo (contato, operador, etc)
            $addresses = Address::with('state', 'country')
                                ->select('id', 'add_zip_code as cep', 'add_number as number', 'add_street as street', 'add_address_complement as address_complement', 'add_district as district', 'add_city as city', 'state_id', 'country_id')
                                ->where('user_id', $userId)
                                ->where('type_user_id', $typeUserId)
                                ->get();
            
            return $addresses;
        } catch(e) {

        }
    }
}
