<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Setting\Plan;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plan = Plan::first();

        return response()->json([
            'plan' => $plan,
        ], 200);
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
    public function update(Request $request)
    {
        $plan = Plan::find($request['id']);
        $plan->pla_total_user = $request['pla_total_user'];
        $plan->pla_total_official_channel = $request['pla_total_official_channel'];
        $plan->pla_total_unofficial_channel = $request['pla_total_unofficial_channel'];
        $plan->pla_value = $request['pla_value'];
        $plan->save();

        return response()->json([
            'plan' => $plan,
        ], 200);
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

    public function getPlanByType($typeId)
    {
        $plan = Plan::where('type_plan_id', $typeId)
                        ->first();

        return $plan;
    }

    public function updateCompanyPlan(Request $request)
    {
        $plan = Plan::first();
        $plan->pla_total_user = $request['plan']['com_total_users'];
        $plan->pla_total_official_channel = $request['plan']['com_total_official_channels'];
        $plan->pla_total_unofficial_channel = $request['plan']['com_total_unofficial_channels'];
        $plan->save();
    }

    public function getPlan()
    {
        $plan = Plan::first();

        return $plan;
    }
}
