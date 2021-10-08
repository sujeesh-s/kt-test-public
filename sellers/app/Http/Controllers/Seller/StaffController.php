<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic as Image;

use Session;
use DB;
use App\Models\Modules;
// use App\Models\UserRoles;
use App\Models\Seller;
use App\Models\UserRole;
use App\Models\SellerUserRole;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\customer\CustomerMaster;
use App\Models\SellerInfo;
use App\Models\UserVisit;
use App\Rules\Name;
use Validator;


use App\Models\Staff;
use App\Models\StaffInfo;
use App\Models\StaffSecurity;
use App\Models\StaffTelecom;
use App\Models\Telecom;
use App\Models\SellerTelecom;
use App\Models\SellerSecurity;

class StaffController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:seller');
    }
    
    
        
        //staffs list
        public function staffs()
        { 
        $data['title']              =   'Staffs';
        $data['menu']               =   'staffs-list';
        $data['admins']              =   StaffInfo::where("is_active",1)->whereIn('staff_id',function($query) {
   $query->select('id')->from('usr_staff_mst')->where('seller_id',auth()->user()->id);})->orderBy('id', 'DESC')->get();

        // dd($data);
        return view('seller.staffs.list',$data);
        }
  public function createStaff()
        { 
        $data['title']              =   'Create Staff';
        $data['menu']               =   'create-Staff';
        $data['roles']              =   SellerUserRole::where("seller_id",auth()->user()->id)->where('is_deleted',0)->get();
        $data['seller']               =  auth()->user()->id;
        return view('seller.staffs.create',$data);
        }

        public function editStaff($role_id)
        { 
        $data['title']              =   'Edit Staff';
        $data['menu']               =   'edit-Staff';
        $data['seller']              =   StaffInfo::where("id",$role_id)->first();
      
        $data['roles']              =   SellerUserRole::where("seller_id",auth()->user()->id)->where('is_deleted',0)->get();
        // dd($data);
        return view('seller.staffs.edit',$data);
        }
    
        public function viewStaff($role_id)
        { 
        $data['title']              =   'View Staff';
        $data['menu']               =   'view-Staff';
        $data['seller']              =   StaffInfo::where('id',$role_id)->first();

        return view('seller.staffs.view',$data);
        }

        public function staffSave(Request $request){
        $post           =   (object)$request->post();
        // dd($post);
        $user           =   $post->user; 
        if($post->id    >   0){

            $staffId      =   $post->staff_id;
             $validEmail             =   StaffTelecom::ValidateUnique('email',$post->user['email'],$staffId);
        $validPhone             =   StaffTelecom::ValidateUnique('phone',$post->user['phone'],$staffId);
      
        if($validEmail){ Session::flash('message', ['text'=>"Email already exist.",'type'=>'danger']);   return back()->withErrors(['email'=>'Email already exist.'])->withInput($request->all()); }
        if($validPhone){ Session::flash('message', ['text'=>"Phone number already exist.",'type'=>'danger']);   return back()->withErrors(['phone'=>'Phone number already exist.'])->withInput($request->all()); }
        
            $sell_arr = array();
            if($post->user['password']      ==  ''){ unset($post->user['password']); }
        else{ 
        StaffSecurity::where('staff_id',$staffId)->update(['password_hash'=>Hash::make($post->user['password'])]);
         }
        $sell_arr['updated_at']       =   date('Y-m-d H:i:s');
        $sell_arr['username']       =$post->user['email'];
        Staff::where('id',$staffId)->update($sell_arr); 


       $emailTypeId            =   Telecom::where('name','email')->first()->id; $phoneTypeId   =   Telecom::where('name','phone')->first()->id;
      
      $info['fname'] = $post->user['fname'];
      $info['lname'] = $post->user['lname']; 
       $info['staff_role_id'] = $post->user['role_id']; 
      StaffInfo::where('id',$post->id)->update($info); 
        $teleEmail              =   ['value'=>$post->user['email']]; 
        $telePhone              =   ['value'=>$post->user['phone']]; 
        $emailId                =   StaffTelecom::where('staff_id',$staffId)->where('type_id',$emailTypeId)->update($teleEmail);  
        $phoneId                =   StaffTelecom::where('staff_id',$staffId)->where('type_id',$phoneTypeId)->update($telePhone);  
        
        $msg        =   'Staff details updated successfully!';
        }else{
        
        $rules                  =   [
        
        'email'                 =>  'required|unique:usr_staff_mst|email|max:100',
        'phone'                 =>  'required|numeric',
        'role_id'                 =>  'required',
        ];
        $validator              =   Validator::make($user,$rules);
        if ($validator->fails()) {
        foreach($validator->messages()->getMessages() as $k=>$row){  $error[$k] = $row[0];
        Session::flash('message', ['text'=>$row[0],'type'=>'danger']); }
        
        return back()->withErrors($validator)->withInput($request->all());
        }
         $validEmail             =   StaffTelecom::ValidateUnique('email',$post->user['email'],0);
        $validPhone             =   StaffTelecom::ValidateUnique('phone',$post->user['phone'],0);
      
        if($validEmail){ Session::flash('message', ['text'=>"Email already exist.",'type'=>'danger']);   return back()->withErrors(['email'=>'Email already exist.'])->withInput($request->all()); }
        if($validPhone){ Session::flash('message', ['text'=>"Phone number already exist.",'type'=>'danger']);   return back()->withErrors(['phone'=>'Phone number already exist.'])->withInput($request->all()); }
        

        $post->user['created_at']       =   date('Y-m-d H:i:s');
       

        $emailTypeId            =   Telecom::where('name','email')->first()->id; $phoneTypeId   =   Telecom::where('name','phone')->first()->id;
        $staffId               =   Staff::create(['username'=>$post->user['email'],'role_id'=>7,'seller_id'=>$post->user['parent_id'],'password'=>Hash::make('123456')])->id;
        $info['staff_id']      =   $staffId; $info['fname'] = $post->user['fname'];$info['lname'] = $post->user['lname'];
        $info['is_approved'] = 1; $info['staff_role_id'] = $post->user['role_id']; StaffInfo::create($info); 
        $teleEmail              =   ['staff_id'=>$staffId,'type_id'=>$emailTypeId,'value'=>$post->user['email']]; 
        $telePhone              =   ['staff_id'=>$staffId,'type_id'=>$phoneTypeId,'value'=>$post->user['phone']]; 
        $emailId                =   StaffTelecom::create($teleEmail)->id; 
         StaffSecurity::create(['staff_id'=>$staffId,'password_hash'=>Hash::make($post->user['password'])]);
        $phoneId                =   StaffTelecom::create($telePhone)->id; 
        Staff::where('id',$staffId)->update(['email'=>$emailId,'phone'=>$phoneId]);

        $msg        =   'Staff details added successfully!';
        }
        if($staffId){
        
        if($request->file('avatar') && $request->file('avatar') != ''){
        $image = $request->file('avatar');
        $input['imagename'] = 'avatar.'.$image->extension();
        $path               =   '/app/public/user/'.$staffId;
        $destinationPath = storage_path($path.'/thumbnail');
        $img = Image::make($image->path());
        if (!file_exists($destinationPath)) { mkdir($destinationPath, 755, true);}
        $img->resize(150, 150, function ($constraint) {
        $constraint->aspectRatio();
        })->save($destinationPath.'/'.$input['imagename']);
        $destinationPath = storage_path($path);
        $image->move($destinationPath, $input['imagename']);
        StaffInfo::where('staff_id',$staffId)->update(['avatar'=>$path.'/'.$input['imagename']]); 
        }
      
        // dd($data);
        Session::flash('message', ['text'=>$msg,'type'=>'success']);
        return redirect(route('seller.staffs'));
        
        }else{ 
        
        Session::flash('message', ['text'=>"Failed to save details.",'type'=>'danger']);
        
        return redirect(route('seller.staffs'));
        }
        }
    
         
        public function staffStatus(Request $request)
        {
        $input = $request->all();
        
        if($input['id']>0) {
        $deleted =  StaffInfo::where('id',$input['id'])->update(array('is_active'=>$input['status']));
        
        return '1';
        }else {
        
        return '0';
        }
        
        }
        
          public function staffDelete(Request $request)
        {
        $input = $request->all();

        if($input['id']>0) {
        $deleted =  StaffInfo::where('id',$input['id'])->update(array('is_deleted'=>1,'is_active'=>0));
        Session::flash('message', ['text'=>'Staff deleted successfully.','type'=>'success']);
        return true;
        }else {
        Session::flash('message', ['text'=>'Staff failed to delete.','type'=>'danger']);
        return false;
        }

        }
    
}
