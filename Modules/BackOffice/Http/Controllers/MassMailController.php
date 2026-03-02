<?php

namespace Modules\BackOffice\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
//use Modules\BackOffice\Emails\SendMassEmail;
use Modules\BackOffice\Jobs\sendMassMailJob;
use Modules\BackOffice\Entities\MassMails;
use Modules\BackOffice\Entities\PreferredMails;
use Modules\Sales\Entities\Tenant;
use Modules\Masters\Entities\Vendor;
use App\User;
use Log;
use DB;

class MassMailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {

        $noOfRecord     = prefixData('no_of_records_in_list_grid')->configuration_value; 
        $massMails      = massMails::paginate($noOfRecord);
       
        return view('backoffice::MassMail.list',compact('massMails'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('backoffice::MassMail.mass_mail');
        //return view('backoffice::MassMail.composer_form');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request,
            ['user_id'    => 'required',
            'subject'   => 'required',
            'content'   => 'required',
        ]);
        $cc = null;
        $cc_mail = array();
           
        $mailPara   = new \stdClass();
        $subject    = $request->subject;
        $content    = $request->content;

        $mails =  MassMails::create([
            'subject' => $subject,
            'content' => $content,
            'created_by' => \Auth::user()->id,
        ]);
        if(!empty(is_array($request['users_id'])) && count($request['users_id'])>0){
            for($i =0; $i<count($request['users_id']);$i++){

                    $usersId = $request['users_id'][$i];
                    $receipantInfo =  User::where('id','=',$usersId)->first();
                    $cc_mail[]     =  $receipantInfo->email;
            }
            $cc = implode(',',$cc_mail);
        }  
        
        if(count($request['user_id'])>0){
            for($i =0; $i<count($request['user_id']);$i++){
                
                $userStr = $request['user_id'][$i];
                $userId  = explode('-',$userStr);
                if($userId[1] == 'L'){
                     $id = intval($userId[0]);
                     $receipantInfo =  Vendor::where('id','=',$id)->first();

                     $name  = $receipantInfo->vendor_name;
                     $email = $receipantInfo->vendor_contact_email;
                     $userType = 1; // 'landlord'
                }
                else{
                    $id = intval($userId[0]);
                    $receipantTenantInfo =  Tenant::where('id','=',$id)->first();

                    $name  = $receipantTenantInfo->tenant_name;
                    $email = $receipantTenantInfo->tenant_contact_email;
                    $userType = 2; //'tenant'
                }
                //$mailPara->id       = $itm->id;
                $mailPara->username = $name;
                $mailPara->email    = $email;
                $mailPara->subject  = $subject;
                $mailPara->content  = $content;
                $mailPara->cc       = $cc;

                $preferredMassMail = PreferredMails::create([
                'mass_mail_id'=>$mails->id,
                'user_id' =>intval($userId[0]),
                'mail_type' =>1,
                'user_type'=>$userType,
                'created_by' => \Auth::user()->id,
                ]);
                $mailPara->statusId     = $preferredMassMail->id;

                sendMassMailJob::dispatch($mailPara);
            }
        }
        
        $mailPara_cc   = new \stdClass();
        if(!empty($request['users_id'])){
            if(count($request['users_id'])>0){
                for($i =0; $i<count($request['users_id']);$i++){

                    $usersId = $request['users_id'][$i];
                    $receipantInfo =  User::where('id','=',$usersId)->first();

                    $mailPara_cc->username = $receipantInfo->employee->employee_name;
                    $mailPara_cc->email    = $receipantInfo->email;
                    $mailPara_cc->subject  = $subject;
                    $mailPara_cc->content  = $content;
                    $mailPara_cc->cc       = $cc;
                    
                    $preferred_cc = PreferredMails::create([
                    'mass_mail_id'=>$mails->id,
                    'user_id' =>$request['users_id'][$i],
                    'mail_type' =>0,
                    'user_type'=>3, // Employee
                    'created_by' => \Auth::user()->id,
                    ]);
                    $mailPara_cc->statusId     = $preferred_cc->id;
                    sendMassMailJob::dispatch($mailPara_cc);
                } 
            }
        } 
 
        session()->flash('success', 'Mail Send Successfully');
        return redirect()->route('massMail.index');

        
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
         $toList       = null;
        $ccList       = null;
 
        $massMail = massMails::where('id','=',$id)->first();

        foreach($massMail->preferredMailsTo as $key=>$to){
			
            if($to->user_type == 2 ){ // Tenant
              
              $tenantInfo = Tenant::where('id','=',$to->user_id)->first()->toArray();
              $tenantInfo['mailstatus'] = $to->status;
              $tenantInfo['usertype']   = $to->user_type;
              
              $toList[] = $tenantInfo;

             }elseif($to->user_type == 1){
                $vendInfo = Vendor::where('id','=',$to->user_id)->first()->toArray();
                $vendInfo['mailstatus'] = $to->status;
                $vendInfo['usertype']   = $to->user_type;
                
                
                $toList[] = $vendInfo;
            }
           
        }
        //dd($toList);
        $cc_users = array();
        if(count($massMail->preferredMailsCc)> 0){
            foreach($massMail->preferredMailsCc as $key_sub=>$cc){
                $userInfo = user::where('id','=',$cc->user_id)->first();
                $cc_users[$key_sub]['emailUser'] = $userInfo->email;
                $cc_users[$key_sub]['employee_name'] = $userInfo->employee->employee_name ;
                 $cc_users[$key_sub]['mailstatus'] = $cc->status ;
                
            }
        }
        //dd($cc_users);
        return view('backoffice::MassMail.view',compact('massMail','toList','cc_users'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('backoffice::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

   /**
    *
    * user email Autocomplete
    *
    **/
    public function emailAutocomplete(Request $request){
        $key = $request->search;
        $tenant = Tenant::where('tenant_contact_email', 'ILIKE', '%'.$key.'%')->select(DB::raw("CONCAT(id,'-T') AS value"),'tenant_contact_email AS text as type')->get();

        $vendor =  Vendor::where('vendor_contact_email', 'ILIKE', '%'.$key.'%')->where('vendor_type_id','=',2)->select(DB::raw("CONCAT(id,'-L') AS value"),'vendor_contact_email AS text')->get();

        $result = $tenant->merge($vendor);
  
        return $result ;
  
   }
   /**
    *
    * CC email Autocomplete
    *
    **/
    public function emailCcAutocomplete(Request $request){
        $key = $request->search;
        $user = User::where('email', 'ILIKE', '%'.$key.'%')
                ->select('id AS value','email AS text as type')->get();
  
        return $user ;
  
   }
    /**
    *
    * user email Autocomplete
    *
    **/
    public function massEmailAutocomplete(Request $request){
         
      $key = $request->term;

      $tenant = Tenant::where('tenant_contact_email', 'ILIKE', '%'.$key.'%')->select(DB::raw("CONCAT(id,'-T') AS ids"),'tenant_contact_email AS value')->get();

      $vendor =  Vendor::where('vendor_contact_email', 'ILIKE', '%'.$key.'%')->where('vendor_type_id','=',2)->select(DB::raw("CONCAT(id,'-L') AS ids"),'vendor_contact_email AS value')->get();

      $result = $tenant->merge($vendor);

      return $result ;
   }


   public function massMailList(Request $request){

    $massMails   = null;
    $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 

    $email    = $request->email;
    $user_id  = $request->user_id;
    $userId   = explode('-',$user_id);


    if(!empty($email) && (!empty($user_id))){
      // 1 - Landlord, 2 - Tenant
      $userType = ($userId[1]=='L')?1:2;
      $massMails=massMails::leftJoin('preferred_mails','mass_mails.id', '=', 'preferred_mails.mass_mail_id')
      ->where('user_id', $userId[0])
      ->where('mail_type',1)
      ->where('user_type',$userType)
      ->orderBy('mass_mails.id','asc')
      ->paginate($noOfRecord);  
      return view('backoffice::MassMail.list',compact('massMails','email','user_id','request'));


    }
    return view('backoffice::MassMail.list',compact('massMails','request'));
  }

}
