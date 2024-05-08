<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign\CampaignOperatingHour;
use App\Models\Campaign\DayWeekOperation;
use App\Models\Campaign\Mailing;
use App\Models\Campaign\Setting;
use App\Models\Campaign\TypeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Auth;

class SettingController extends Controller
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
    public function store($campaignId)
    {
        try {
            $settings = new Setting();
            $settings->campaign_id = $campaignId;
            //Seta que a operação do canal irá ocorrer entre 1 à 2 minutos
            $settings->operation_frequency_id = 1;
            $settings->save();

            $daysWeekOperation = DayWeekOperation::all();

            //Para cada dia da semana, define o horário de operação
            foreach($daysWeekOperation as $dayWeekOperation) {
                $operationHour = new CampaignOperatingHour();
                $operationHour->campaign_id = $campaignId;
                $operationHour->day_week_id = $dayWeekOperation->id; //Dia da semana
                $operationHour->ope_hr_start = '00:00';
                $operationHour->ope_hr_end = '00:00';
           
                $operationHour->save();
            }
            
        } catch(e) {

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
}
