<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial\Fee;
use App\Models\Financial\InvoiceFee;
use App\Models\Financial\TypeFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;


class FeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fees = Fee::with('typeFee')
                    ->where('fee_status', 'A');
        $total = $fees->count(); 
        $fees = $fees->get();
        
        return response()->json([
            'fees' => $fees,
            'total' => $total,
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
        $fee = Fee::find($request['id']);
        $fee->fee_value = $request['fee_value'];
        $fee->save();

        return response()->json([
            'fee' => $fee,
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

    //Traz a taxa pelo seu tipo
    public function getFeeByType($feeTypeId)
    {
        $fee = Fee::where('type_fee_id', $feeTypeId)
                    ->where('fee_status', 'A')
                    ->first();
        return $fee;
    }

    //Traz a taxa pelo seu tipo
    public function fetchFeeByType($feeTypeId)
    {
        $fee = Fee::where('type_fee_id', $feeTypeId)
                    ->where('fee_status', 'A')
                    ->first();
                    
        return response()->json([
            'fee' => $fee,
        ], 200);
    }

    //Traz todas as taxas associadas a uma fatura
    public function getFeeByInvoice($invoiceId)
    {
        $fees = InvoiceFee::where('invoice_id', $invoiceId)
                        ->where('inv_status', 'A')
                        ->get();

        return $fees;
    }

    //Atualiza as taxas enviadas via módulo de GESTÃO
    public function updateCompanyFees(Request $request)
    {
        //Para cada taxa preenchida pelo usuário
        foreach($request['fees'] AS $typeFeeId => $feeValue) {
            //Começa o tipo de taxa pelo ID 1
            if($typeFeeId > 0) {
                $hasFee = null;
                $hasFee = self::getFeeByType($typeFeeId);
                //Se a taxa já foi cadastrada em algum momento
                if($hasFee) {
                    //Atualiza a taxa existente
                    $hasFee->fee_value = $feeValue;
                    $hasFee->save();
                }
            }
        }
    }

    //Traz todas as taxas ativas
    public function getFees()
    {
        $fees = Fee::where('fee_status', 'A')
                    ->get();
        
        return $fees;
    }

    //Traz os tipos de taxas ativas
    public function getTypeFees()
    {
        $typeFees = TypeFee::where('typ_status', 'A')
                    ->get();
        
        return $typeFees;
    }

}
