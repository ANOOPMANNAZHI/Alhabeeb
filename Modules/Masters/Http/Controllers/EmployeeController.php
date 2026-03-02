<?php


namespace Modules\Masters\Http\Controllers;

use Modules\Masters\Entities\Employee;
use Modules\Masters\Entities\Designation;
use Spatie\Permission\Models\Role;
use Modules\Masters\Entities\JobCategory;
use App\User;
use Image;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\Masters\Emails\EmployeeLoginEmail;
use Modules\Masters\Emails\EmployeeResetEmail;
use Dynamics;

class EmployeeController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth'); 
    $this->middleware('permission:add_employee', ['only' => ['create']]);   
    $this->middleware('permission:edit_employee', ['only' => ['edit','update']]);  
    $this->middleware('permission:delete_employee', ['only' => ['destroy']]);    
    $this->middleware('permission:view_employee', ['only' => ['index','show']]); 
    $this->middleware('permission:change_status_employee', ['only' => ['changeStatus']]); 
    $this->middleware('permission:reset_password_employee', ['only' => ['resetPassword']]);
  }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request) 
    {

      $employee_fields = [
      'employee_code' => 'Code',
      'employee_name' => 'Name',
      'employee_contact_no' => 'Mobile', 
      'username' => 'Username',
      'email' => 'Email'
      ];

      $fieldName  = $request->fieldName;
      $fieldValue = $request->fieldValue;

      $request->flash();   
      $noOfRecord  = prefixData('no_of_records_in_list_grid')->configuration_value; 
      $employees = Employee::whereHas('user', function ($query) use($fieldValue,$fieldName) {
        $query->where('user_type', '=', 'employee')
        ->when($fieldValue, function ($query) use($fieldValue,$fieldName){
          return $query->where($fieldName,'ilike', '%'.$fieldValue.'%');
        });                     
      })->sortable()->paginate($noOfRecord);

        //dd($employees);
      return view('masters::Employee.list', compact('employees','employee_fields','request'));

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

        // Role list
      $roleList = Role::get();
      $jobs = JobCategory::get();

        // Designation
      $designationList = Designation::where('designation_status','=', 1)->get();
      
      $emplLatest = Employee::withTrashed()->orderBy('created_at','DESC')->first();

      $prefix  = prefixData('employee_code_prefix')->configuration_value;    

      $empl_code = isset($emplLatest->id)? $prefix.str_pad($emplLatest->id + 1, 4, "0", STR_PAD_LEFT):$prefix.'001';

      return view('masters::Employee.add_edit', compact(['empl_code','roleList', 'designationList','jobs']));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
      $img_path = null;
      $this->validate($request, [
        'employee_code' => 'required|unique:employees',
        'employee_name' => 'required',
        'role_name'=> 'required', 
        'employee_contact_no' =>   'required|numeric', 
        'employee_dob'=> 'required', 
        'designation_id'=> 'required',
        'employee_contact_address'=> 'required',
        'employee_picture'  => 'dimensions:min_width=100,min_height=100|nullable|mimes:jpeg,png,jpg|max:2000',
        'email'=>'required|unique:users',
        'username'=>'required|unique:users',
        ]);

      if(!empty($request->file('employee_picture'))){

        $path = public_path('img');

        $imageName = time().'.'.$request->file('employee_picture')->getClientOriginalExtension();


        $large_img = Image::make($request->file('employee_picture')->getRealPath());
        $large_img->resize(100, 100);
        $large_img->save($path.'/'.$imageName,100);

        $img_path =    Storage::putFile('public/employees', new File($path.'/'.$imageName), 'public');
                        //move idproof to  public path
      } 

      $empl =  Employee::create([
        'employee_code'       => $request['employee_code'],
        'employee_name' => $request['employee_name'],
        'employee_contact_address' => $request['employee_contact_address'],
        'employee_contact_no' => $request['employee_contact_no'],
        'employee_secondary_address' => $request['employee_secondary_address'],
        'employee_secondary_no' => $request['employee_secondary_no'],
        'employee_dob' => $request['employee_dob'],
        'employee_picture' => $img_path,
        'designation_id' => $request['designation_id'],
        'head_role' => $request['head_role'],
        'head_user' => $request['head_user'],
        'job_category_id' => $request['job_category_id'],
        'created_by' => \Auth::user()->id,
        ]);

      $role_name   = $request['role_name'] ;
      $empInsertId = $empl->id ;

      $role = Role::where('name',$request['default_role'])->first();


      $user =  User::create([
        'username'        => $request['username'],
        'email'           => $request['email'],
        'password'        => Hash::make($request['username'].$empInsertId),
          'user_type'       => 'employee',           // employee
          'user_type_id'    => $empInsertId, // employee_id
          'user_type_status'=> 1,
          'default_role'    => $role->id,
          'created_by'      => \Auth::user()->id,
          ]);

		if($empl->id && $user->id){	
			$employeeInfo  = Employee::where('id',$empl->id)->first();
			
			if(Dynamics::EmployeeAxPushData('AXEmployee', $employeeInfo)=='Error'){
				Employee::destroy($empl->id);
				User::destroy($user->id);
				return Redirect::back()->withMessage('error', 'Microsoft Dynamics API Service Error');
			}
		
		}
      activity('Add Employee')
      ->performedOn($user)
      ->causedBy(\Auth::user()->id)
      ->withProperties($user)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

      $user->assignRole($role_name);
      $user->new_password = $request['username'].$empInsertId;

      Mail::to($request['email'])->send(new EmployeeLoginEmail($user));

      session()->flash('success', 'Employee created successfully');
      return redirect()->route('employee.index');
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
      $employee = Employee::where('id',$id)->first();
      return view('masters::Employee.view',compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(Employee $employee)
    {
		// Role list
      $roleList = Role::get();
      $jobs = JobCategory::get();

      $default_role_name =  Role::select('name')->find($employee->user->default_role);
      $default_role_name = $default_role_name->name;  
		// Designation
      $designationList = Designation::where('designation_status','=', 1)->get();
		//  dd($employee->user);
      $headUsers = User::where('default_role',$employee->head_role)->get();
		//dd($headUsers);
      return view('masters::Employee.add_edit', compact(['employee','roleList','designationList','default_role_name','jobs','headUsers']));
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, Employee $employee)
    {

      $this->validate($request, [
        'employee_name' => 'required',
        'role_name'=> 'required', 
        'employee_contact_no' =>   'required|numeric', 
        'employee_dob'=> 'required', 
        'designation_id'=> 'required',
        'employee_contact_address'=> 'required',
        'employee_picture'  => 'dimensions:min_width=100,min_height=100|nullable|mimes:jpeg,png,jpg|max:2000',
        'email'=>'required|unique:users,email,'.$employee->user->id,
        ]);

      if(!empty($request->file('employee_picture'))){

           // $empl = Employee::where('id',$employee->id);
        if($employee->employee_picture){

          Storage::delete($employee->employee_picture);

        }
        $path = public_path('img');

        $imageName = time().'.'.$request->file('employee_picture')->getClientOriginalExtension();         

        $large_img = Image::make($request->file('employee_picture')->getRealPath());
        $large_img->resize(100, 100);
        $large_img->save($path.'/'.$imageName,100);

        $img_path =    Storage::putFile('public/employees', new File($path.'/'.$imageName), 'public');
                        //move idproof to  public path
      }else {
        $img_path = $employee->employee_picture;
      } 
      
      $empl =  $employee->update([
        'employee_name'               => $request['employee_name'],
        'employee_contact_address'    => trim($request['employee_contact_address']),
        'employee_contact_no'         => $request['employee_contact_no'],
        'employee_secondary_address'  => trim($request['employee_secondary_address']),
        'employee_secondary_no'       => $request['employee_secondary_no'],
        'employee_dob'                => $request['employee_dob'],
        'employee_picture'            => $img_path,
        'designation_id'              => $request['designation_id'],
        'head_role'                   => $request['head_role'],
        'head_user'                   => $request['head_user'],
        'job_category_id' => $request['job_category_id'],
        'updated_by'                  => \Auth::user()->id,
        ]);

      $role_name   = $request['role_name'];
      $role = Role::where('name',$request['default_role'])->first();

      $user =  User::where('user_type_id', $employee->id)->update([
        'username'        => $request['username'],
        'email'           => $request['email'],
        'default_role'    => $role->id,
        'updated_by' => \Auth::user()->id,
        ]);

      $employee->user->syncRoles($role_name);
      $user_update = User::where('user_type_id', $employee->id)->
      where('user_type', 'employee')->first();
      activity('Update Employee')
      ->performedOn($user_update)
      ->causedBy(\Auth::user()->id)
      ->withProperties($user_update)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);
        //$user->assignRole($role_name);

      session()->flash('success', 'Employee successfully updated');
      return redirect()->route('employee.index');
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy(Employee $employee)
    {
      $id = Employee::find($employee->id);

      try {

       $id->delete();

       session()->flash('success', 'Employee Deleted Successfully');
     }
     catch (\Exception $e) {
      session()->flash('error', 'Please delete related records before');
    }



    $user_update = User::where('user_type_id', $employee->id)->
    where('user_type', 'employee')->first();
    activity('Deleted Employee')
    ->causedBy(\Auth::user()->id)
    ->withProperties($user_update)
    ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

    activity('Deleted Employee')
    ->causedBy(\Auth::user()->id)
    ->withProperties($user_update)
    ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

        //session()->flash('success', 'Employee Deleted Successfully');
    return redirect()->route('employee.index');

  }
    /**
     * Change the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id,Employee $employee)
    {
      $status = Employee::find($id);

      if($status['employee_status']==1) {
        $status->employee_status = 0;
        User::where('user_type_id', $id)->update([
         'user_type_status' => FALSE,
         'updated_by' => \Auth::user()->id
         ]);
      } else {
        $status->employee_status = 1;
        User::where('user_type_id', $id)->update([
         'user_type_status' => TRUE,
         'updated_by' => \Auth::user()->id
         ]);
      } 
      $status->save();    

      $user_update = User::where('user_type_id', $id)->
      where('user_type', 'employee')->first();

      activity('Change Employee Status')
      ->causedBy(\Auth::user()->id)
      ->withProperties($user_update)
      ->log("Created Name :".\Auth::user()->username.", Created Email :".\Auth::user()->email);

      session()->flash('success', 'Status Changed Successfully');
      return redirect()->route('employee.index');
    }
    /*
     *    Reset password
     */
    public function resetPassword($id,Employee $employee)
    {
      $user = User::where('user_type_id',$id)->where('user_type','employee')->first();

      if( $user->password){
        User::where('id', $user->id)->update([
         'password'   => Hash::make($user->username.$user->id),
         'updated_by' => \Auth::user()->id
         ]);

      }
      $user->password = $user->username.$user->id;
       /* $userinfo =  [
          'username'        => $user->username,
          'email'           => $user->email,
          'password'        => Hash::make($user->username.$user->id)
          ];
        */
        //  dd($user);
          Mail::to($user->email)->send(new EmployeeResetEmail($user));
          session()->flash('success', 'Password reset successfully');
          return redirect()->route('employee.index');

        }
        public function deleteEmployeeImage(Request $request)
        {
          $employee_id = $request->employeeId;
          $imageName = Employee::where('id',$employee_id)->first()->employee_picture;
         // unlink($imageName);
          Employee::where('id',$employee_id)
          ->update(['employee_picture' => null,
            'updated_by' => \Auth::user()->id
            ]);
          session()->flash('success', 'Profile Picture Deleted successfully');
          return redirect()->route('employee.index');
        }
      }
