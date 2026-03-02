<?php

namespace Modules\BackOffice\Imports;

use Modules\BackOffice\Entities\Pdc;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Modules\Sales\Entities\TenantContract;
use Modules\Masters\Entities\Bank;
use Illuminate\Support\Facades\Auth;
use App\User;
class PdcExcelImport implements ToModel,WithStartRow
{
    private $data; 

    public function __construct($data)
    {
        $this->data = $data; 
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        /********** Bank Name ******************/
        $bank = Bank::where('bank_code',$row[5])->first();

        $contractInfo = TenantContract::where('id',$this->data->tenant_contract_id)->first();
        $tenantPdcInfo  = Pdc::where('tenant_contract_id', $this->data->tenant_contract_id)->orderBy('id', 'desc')->first();
        
        if(isset($tenantPdcInfo)){
            $pdcTransacNo = $tenantPdcInfo->pdc_transaction_no;
            $pdcTransacDt = $tenantPdcInfo->pdc_transaction_date;

        }
        else{
            $tenantPdcLatest = Pdc::orderBy('id', 'desc')->first();
            $prefix  = prefixData('tenant_pdc_prefix')->configuration_value;
                        
            if(!empty($tenantPdcLatest))
                $pdcTransacNo = $prefix.str_pad($tenantPdcLatest->id+1,4,'0',STR_PAD_LEFT);
            else
                $pdcTransacNo = $prefix.str_pad(1,4,'0',STR_PAD_LEFT);
                
            $pdcTransacDt = date('Y-m-d');
        }
  
        $bank_id = $bank->id;
         /********** Pdc Type ******************/
        $pdc_type = $this->pdcType($row[4]);
        

        return new Pdc([
            'tenant_contract_id'     => $contractInfo->id,
            'pdc_transaction_no'     => $pdcTransacNo,
            'pdc_transaction_date'   => $pdcTransacDt,
            'pdc_check_no'           => $row[0],
            'pdc_check_date'         => $row[1],
            'pdc_recieve_date'       => $row[6],
            'pdc_period'             => $row[3],
            'pdc_amt'                => $row[2],
            'pdc_deposit_date'       => $row[7],
            'bank_id'                => $bank_id,
            'pdc_stage'              => $row[3],
            'pdc_type'               => $pdc_type,
            'created_by'             => Auth::user()->id,
            'pdc_is_saved'           => 1, // Saved
            'pdc_settlement'         => 1, // Normal
            'pdc_from_date'          => $row[8],
            'pdc_to_date'            => $row[9],
            
            ]);
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }
    /*
    *Pdc TYpe
    *
    */
    public function pdcType($type){
        switch($type){
          case 'Rent' : return '1';
          case 'Deposit' : return '2';   
          case 'Others' : return '3';
               
        }

    }
   
   

}
