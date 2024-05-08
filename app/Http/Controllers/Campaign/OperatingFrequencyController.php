<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign\CampaignOperatingHour;
use App\Models\Campaign\DayWeekOperation;
use App\Models\Campaign\Mailing;
use App\Models\Campaign\NumberShotFrequency;
use App\Models\Campaign\OperatingFrequency;
use App\Models\Campaign\Setting;
use App\Models\Campaign\TypeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Auth;

class OperatingFrequencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        //Traz as frequências de operação cadastradas
        $operatingFrequency = OperatingFrequency::where('ope_status', 'A')
                                                ->get();
        
        return response()->json([
            'operatingFrequency'=> $operatingFrequency
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
    public function store($campaignId)
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

    //Número de disparos por frequência
    public function fetchNumberShotsFrequency()
    {
        //Traz as frequências de operação cadastradas
        $numberShotsFrequency = NumberShotFrequency::where('num_status', 'A')
                                                ->get();
        
        return response()->json([
            'numberShotsFrequency'=> $numberShotsFrequency
        ], 201);
    }
}
