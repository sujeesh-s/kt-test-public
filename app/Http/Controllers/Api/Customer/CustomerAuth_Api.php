<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Admin;
use App\Models\CustomerMaster;
use App\Models\CustomerInfo;
use App\Models\CustomerSecurity;
use App\Models\CustomerTelecom;
use App\Models\CustomerLogin;
use App\Models\CustomerAddress;
use App\Models\CustomerRegisterotp;

use App\Rules\Name;
use Validator;
use Illuminate\Support\Facades\Hash;

class CustomerAuth_Api extends Controller
{
    public function register(Request $request)
    {
        $ph = ['usr_telecom_value'=>[$request->phone_number]];
        $validator = Validator::make($request->all(), [
            'first_name' => ['required','max:255'],
            'last_name' => ['required','max:255'],
            'country_code' => ['required','max:20'],
            'email' => ['required','nullable','email','max:255','unique:usr_mst,username'],
            'phone_number'=>['required','nullable','min:7,12'],
            'password' => ['required','min:8','required_with:password_confirmation','confirmed'],
        ]);


        $input = $request->all();

        if ($validator->passes()) {

          $master =  CustomerMaster::create(['org_id' => 1,
                'username' => $request->email,
                'ref_code' => $request->ref_code,
                'is_active'=>1,
                'is_deleted'=>0,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
          $masterId = $master->id;

          if($request->hasFile('profile_img'))
            {
            $file=$request->file('profile_img');
            $extention=$file->getClientOriginalExtension();
            $filename=time().'.'.$extention;
            $file->move(('storage/app/public/customer_profile/'),$filename);
            }
            else
            {
                $filename='';
            }

           $info = CustomerInfo::create(['org_id' => 1,
           'first_name' => $request->first_name,
           'last_name' =>$request->last_name,
           'user_id' => $masterId,
           'usr_role_id' => 5,
           'profile_image'=>$filename,
           'is_active'=>1,
           'is_deleted'=>0,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);

           $security = CustomerSecurity::create(['org_id' => 1,
           'password_hash' => Hash::make($request->password),
           'user_id' => $masterId,
           'is_active'=>1,
           'is_deleted'=>0,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);

           if($request->email)
           {
           $telecom_email = CustomerTelecom::create(['org_id' => 1,
           'user_id' => $masterId,
           'usr_telecom_typ_id'=>1,
           'country_code'=>'',
           'usr_telecom_value'=>$request->email,
           'is_active'=>1,
           'is_deleted'=>0,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);
           $email_tele=$telecom_email->id;

           CustomerMaster::where('id',$masterId)->update([
               'email'=>$email_tele
           ]);
          }
          if($request->phone_number)
           {
           $telecom_ph = CustomerTelecom::create(['org_id' => 1,
           'user_id' => $masterId,
           'usr_telecom_typ_id'=>2,
           'country_code'=>$request->country_code,
           'usr_telecom_value'=>$request->phone_number,
           'is_active'=>1,
           'is_deleted'=>0,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);
           $ph_tele=$telecom_ph->id;

           CustomerMaster::where('id',$masterId)->update([
               'phone'=>$ph_tele
           ]);

           }

        //   $address= CustomerAddress::create(['org_id'=>1,
        //     'user_id'=>$masterId,
        //     'usr_addr_typ_id'=>1,
        //     'city_id'=>$request->city,
        //     'address_1'=>$request->address,
        //     'is_active'=>1,
        //     'is_default'=>1,
        //     'is_deleted'=>0,
        //     'created_at'=>date("Y-m-d H:i:s"),
        //     'updated_at'=>date("Y-m-d H:i:s")
        //     ]);
           
           CustomerRegisterotp::where('country_code',$request->country_code)->where('phone_number',$request->phone_number)->where('is_active',1)->where('is_deleted',0)->update(['status'=>0]);
           return response()->json(['httpcode'=>200,'success'=>'successfully registered']);

        }
        return response()->json(['httpcode'=>'400','status'=>'error','error'=>$validator->errors()->all()]);
    }
    
    public function login(Request $request){  
        $post                       =   (object)$request->post(); $error = [];  $user = false;
        $rules                      =   ['input' => 'required|string|email|max:100','password'=>'required','deviceToken'=> 'required|string|max:200', 'os' => 'required|string|max:20',
                                            'deviceId' => 'required|string|max:200'];
        $validator              =   Validator::make($request->post(),$rules);
        if ($validator->fails()) {
           $rules                   =   ['input' => 'required|numeric|digits_between:7,12'];
            $validator              =   Validator::make($request->post(),$rules);
            if ($validator->fails()){   
                return array('httpcode'=>400,'status'=>'error','message'=>'Invalid Credential','data'=>array('errors' =>(object)['error_msg'=>'Invalid Email or Phone']));
            }else{ $input           =   'phone'; $inputId = 2; }
        }else{ $input               =   'email'; $inputId = 1; }
        $telecom                    =   CustomerTelecom::whereIn('usr_telecom_typ_id',[1,2])->where('usr_telecom_value',$post->input)->first();
        if($telecom){ $user         =   CustomerInfo::where('user_id',$telecom->user_id)->where('is_deleted',0)->first(); }
        if($user){
            // $otp                    =   $this->validateOtp($user,$post->otp);
            // if(!$otp){ return ['httpcode'=>400,'status'=>'error','message'=>'Invalid OTP','data'=>['errors' =>['error'=>'Invalid OTP']]]; }
        //**    $info                   =   CustomerInfo::where('user_id',$user->id)->first();
            $security               =   CustomerSecurity::where('user_id',$user->user_id)->first(); 
            // print_r($security);
            // die();
            if(Hash::check($post->password, $security->password_hash)){ 
                
            if($user->is_active == 0){ $error  =   'This account has been disabled. Please contact Admin'; }
            if($error){
                return ['httpcode'=>400,'status'=>'error','message'=>$error,'data'=>['errors' =>['error'=>$error]]];
            }else{ return $this->authenticateUser($user,$post); }
        }//pwd
        else
        {
            return array('httpcode'=>400,'status'=>'error','message'=>'Incorrect password ','data'=>array('errors' =>(object)['error_msg'=>'Incorrect password']));
        }
        }
        
        else{ return array('httpcode'=>400,'status'=>'error','message'=>'Invalid Account','data'=>array('errors' =>(object)['error_msg'=>'Invalid Account'])); }
        return ['httpcode'=>400,'status'=>'error','redirect'=>'login','message'=>'Invalid credential','data'=>['errors' =>(object)['error_msg'=>'Invalid credential']]];
        
    }
    
    //****old
    public function login1(Request $request){  
        $post                       =   (object)$request->post(); $error = [];  $user = false;
        $rules                      =   ['input' => 'required|string|email|max:100'];
        $validator              =   Validator::make($request->post(),$rules);
        if ($validator->fails()) {
           $rules                   =   ['input' => 'required|numeric|digits_between:7,12'];
            $validator              =   Validator::make($request->post(),$rules);
            if ($validator->fails()){   
                return array('httpcode'=>400,'status'=>'error','message'=>'Invalid Credential','data'=>array('errors' =>(object)['error_msg'=>'Invalid Email or Phone']));
            }else{ $input           =   'phone'; $inputId = 2; }
        }else{ $input               =   'email'; $inputId = 1; }
        $telecom                    =   CustomerTelecom::where('usr_telecom_typ_id',$inputId)->where('usr_telecom_value',$post->input)->first();
        if($telecom){ $user         =   CustomerMaster::where('id',$telecom->user_id)->first(); }
        if($user){ 
            $security               =   CustomerSecurity::where('user_id',$user->id)->first(); 
            if(Hash::check($post->password, $security->password_hash)){ 
                $otp                =   $this->generateOtp($user);
                return ['httpcode'=>200,'status'=>'success','message'=>'OTP hes been sent to phone and email','data'=>['otp' =>$otp,'input'=>$post->input]];
            }
        }
        return ['httpcode'=>400,'status'=>'error','redirect'=>'login','message'=>'Invalid credential','data'=>['errors' =>(object)['error_msg'=>'Invalid credential']]];
        
    }
    
    function generateOtp($user){
        $otp = rand(1000, 9999);    $otp = 1234;
        CustomerTelecom::where('id',$user->phone)->update(['otp'=>$otp,'otp_sent_at'=>date('Y-m-d H:i:s')]); 
        CustomerTelecom::where('id',$user->email)->update(['otp'=>$otp,'otp_sent_at'=>date('Y-m-d H:i:s')]); 
        return $otp;
    }
    
    public function verifyOtp(Request $request){
        $post                       =   (object)$request->post(); $error = [];  $user = false;
        $rules                      =   [
                                            'input' => 'required|string|max:100', 'otp' => 'required|string|max:100',
                                            'deviceToken' => 'required|string|max:200', 'os' => 'required|string|max:20',
                                            'deviceId' => 'required|string|max:200'
                                        ];
        $validator                  =   Validator::make($request->post(), $rules);
        if(isset($post->deviceName)){   $deviceName = $post->deviceName; }else{ $deviceName = NULL; }
        if ($validator->fails()){
            foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0];}  
            return array('httpcode'=>400,'status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
        }
        $telecom                    =   CustomerTelecom::whereIn('usr_telecom_typ_id',[1,2])->where('usr_telecom_value',$post->input)->first();
        if($telecom){ $user         =   CustomerInfo::where('user_id',$telecom->user_id)->where('is_deleted',0)->first(); }
        if($user){
            $otp                    =   $this->validateOtp($user,$post->otp);
            if(!$otp){ return ['httpcode'=>400,'status'=>'error','message'=>'Invalid OTP','data'=>['errors' =>['error'=>'Invalid OTP']]]; }
        //**    $info                   =   CustomerInfo::where('user_id',$user->id)->first();
            
            if($user->is_active == 0){ $error  =   'This account has been disabled. Please contact Admin'; }
            if($error){
                return ['httpcode'=>400,'status'=>'error','message'=>$error,'data'=>['errors' =>['error'=>$error]]];
            }else{ return $this->authenticateUser($user,$post); }
        }else{ return array('httpcode'=>400,'status'=>'error','message'=>'Invalid OTP','data'=>array('errors' =>(object)['error_msg'=>'Invalid OTP'])); }
    }
    
    function validateOtp($user,$otp){
        if(CustomerTelecom::where('user_id',$user->user_id)->where('otp',$otp)->where('otp','!=',NULL)->count() > 0){
            CustomerTelecom::where('user_id',$user->user_id)->where('otp',$otp)->update(['otp'=>NULL,'otp_verified_at'=>date('Y-m-d H:i:s')]); return true;
        }else{ return false; }
    }
    
    
    function socialLogin(Request $request){
        $post                       =   (object)$request->post(); $error = false; $sField = ''; $user = false;
        if(!isset($post->email))    {   $post->email = ''; }if(!isset($post->phone)){ $post->phone = ''; }if(!isset($post->lname)){ $post->lname = ''; }
        $rules                      =   [
                                            'social_media'      => 'required|string|max:255', 'fname'   => 'required|string|max:255',
                                            'login_id'          => 'required|string|max:255', 'os' => 'required|string|max:55',
                                            'deviceId'         => 'required|string|max:255', 'deviceToken' => 'required|string|max:255',
                                        ];
        if($post->email != '')  {   $rules['email']         =   'string|email|max:255'; }
        $validator                  =   Validator::make($request->post(),$rules);
        if ($validator->fails())    {   foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; } }
        if($post->social_media  ==  'facebook'){ $sField =  'fb_id'; }else if($post->social_media == 'google'){ $sField =  'google_id'; }else if($post->social_media == 'apple'){ $sField =  'apple_id'; } 
        else{ $errorMag[]       =   $error['social_media'] = 'Invalid social media name'; }
        if($error)  {   return      ['httpcode' =>  '400','status'=>'error','message'=>$errorMag[0],'data'=>['errors' =>$error]]; }
        $str_result             =   '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        
        $security               =   CustomerSecurity::where($sField,$post->login_id)->first(); 
        if($security){ $user    =   $this->getUser($security->user_id); }
        if(!$user){
            if($post->email     !=  ''){
                $telecom        =   CustomerTelecom::where('usr_telecom_value',$post->email)->where('usr_telecom_typ_id',1)->first();
                if($telecom)    {   $user   =   $this->getUser($telecom->user_id); }
                if($user)       {   CustomerSecurity::where('user_id',$telecom->user_id)->update([$sField=>$post->login_id]); }
            }
        }
        if(!$user){
            if($post->phone     !=  ''){
                $telecom        =   CustomerTelecom::where('usr_telecom_value',$post->phone)->where('usr_telecom_typ_id',2)->first();
                if($telecom)    {   $user   =   $this->getUser($telecom->user_id); }
                if($user)       {   CustomerSecurity::where('user_id',$telecom->user_id)->update([$sField=>$post->login_id]); }
            }
        }
        
        if($user){ 
            if($user->is_active    ==  0){ $error  =   'This account has been disabled. Please contact Admin'; }
         //   else if($user->is_approved  ==  0){ $error  =   'This account did not approved. Please contact admin.'; }
            if($error){                 return ['httpcode'=>400,'status'=>'error','message'=>$error,'data'=>['errors' =>(object)['error_msg'=>$error]]]; }
            else{  return      $this->authenticateUser($user,$post); }
        }else{
            $userId             =   CustomerMaster::create(['username'=>$post->email])->id;
            if($post->email     !=  ''){ $email = $post->email; }else{ $email   =   'user'.$userId.'@kangtao.com'; }
            if($post->phone     !=  ''){ $phone = $post->phone; }else{ $phone   =   $userId; }
                                    CustomerInfo::create(['user_id'=>$userId,'first_name'=>$post->fname,'last_name'=>$post->lname]);
            $emailId            =   CustomerTelecom::create(['user_id'=>$userId,'usr_telecom_typ_id'=>1,'usr_telecom_value'=>$email])->id;
            $phoneId            =   CustomerTelecom::create(['user_id'=>$userId,'usr_telecom_typ_id'=>2,'usr_telecom_value'=>$phone]);
                                    CustomerSecurity::create(['user_id'=>$userId,'password_hash'=>Hash::make($str_result),$sField=>$post->login_id]);
                                    CustomerMaster::where('id',$userId)->update(['email'=>$emailId,'phone'=>$phoneId]);
            $user               =   $this->getUser($userId); return $this->authenticateUser($user,$post);
        }
    }
    
    function authenticateUser($user,$post){
        $tocken                 =   $user->user_id.substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,12);
        if(isset($post->deviceName)){   $deviceName = $post->deviceName; }else{ $deviceName = NULL; }
        if(!isset($post->os))   {   $post->os = 'web'; }
        //    $update                 =    User::where('id',$user->id)->update(['is_login'=>1,'otp'=>NULL,'access_token'=>$tocken,'deviceToken'=>$post->deviceToken,'os'=>$post->os]);
        $loginData              =   ['user_id'=>$user->user_id,'device_id'=>$post->deviceId,'device_name'=>$deviceName,'access_token'=>$tocken,'is_login'=>1,'device_token'=>$post->deviceToken,'os'=>$post->os,'login_at'=>date('Y-m-d H:i:s')];
         $loginUser             =   CustomerLogin::where('device_id',$post->deviceId)->where('is_deleted',0);
         if($loginUser->exists()){   $loginData['last_login']    =   $loginUser->first()->login_at; 
             CustomerLogin::where('device_id',$post->deviceId)->where('is_deleted',0)->update($loginData);
         }else{ CustomerLogin::create($loginData); }
        if($user){
            $mst                =   CustomerMaster::where('id',$user->user_id)->first();
            $data['user_id']    =   $user->user_id;                 $data['fname']          =   $user->first_name;
            $data['lname']      =   $user->last_name;               $data['phone']          =   $mst->custphone($mst->phone);       
            $data['email']      =   $mst->custEmail($mst->email);
            return ['httpcode'=>200,'status'=>'success','message'=>'Login successfull!','data'=>array('access_token'=>$tocken,'user_details'=>$data)];
        }else{ return array('httpcode'=>400,'status'=>'error','message'=>'Somthing went wrong','data'=>['errors' =>(object)['error_msg'=>'Somthing went wrong']]); }
    }
    
    function getUser($userId){ 
        $query                  =   CustomerInfo::where('user_id',$userId)->where('is_deleted',0)->first();
        if($query->exists)  {       return $query; }else{ return false; }
    }
    public function regSendotp(Request $request)
    {
        $formData   =   $request->all(); 
        $rules      =   array();
        $rules['country_code']    = 'required|string';
        $rules['phone_number']    = 'required|numeric|min:7,12';
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
            }
        else
            { 
              $regexist = CustomerTelecom::where('country_code',$request->country_code)->where('usr_telecom_value',$request->phone_number)->where('is_active',1)->where('is_deleted',0);
              if($regexist->count() > 0)
              {
                return array('httpcode'=>'400','status'=>'error','message'=>'Already Exist','data'=>['message' =>'Phone number already exist!']);
              }
              else
              {
                $otp = rand(1000, 9999); $otp = 1234;
                $exisit = CustomerRegisterotp::where('country_code',$request->country_code)->where('phone_number',$request->phone_number)->where('is_active',1)->where('is_deleted',0);
                if($exisit->count() > 0)
                {
                    CustomerRegisterotp::where('country_code',$request->country_code)->where('phone_number',$request->phone_number)->where('is_active',1)->where('is_deleted',0)->update(['otp'=>$otp]);
                }
                else
                {
                    CustomerRegisterotp::create(['country_code'=>$request->country_code,'phone_number'=>$request->phone_number,'otp'=>$otp,'created_at'=>date('Y-m-d H:i:s')]);
                }
               
                return ['httpcode'=>200,'status'=>'success','message'=>'OTP hes been sent to phone','data'=>['otp' =>$otp,'country_code'=>$request->country_code,'phone_number'=>$request->phone_number]];
              }
              
            }
    }
    
    public function regVerifyotp(Request $request)
    {
        $formData   =   $request->all(); 
        $rules      =   array();
        $rules['country_code']    = 'required|string';
        $rules['phone_number']    = 'required|numeric|min:7,12';
        $rules['otp']             = 'required|numeric';
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
            }
        else
            { 
                $exisit = CustomerRegisterotp::where('country_code',$request->country_code)->where('phone_number',$request->phone_number)->where('is_active',1)->where('is_deleted',0);
                if($exisit->count() > 0)
                {
                     $exist = CustomerRegisterotp::where('country_code',$request->country_code)->where('phone_number',$request->phone_number)->where('otp',$request->otp)->where('is_active',1)->where('is_deleted',0);
                     if($exist->count() > 0)
                     {
                        CustomerRegisterotp::where('country_code',$request->country_code)->where('phone_number',$request->phone_number)->where('otp',$request->otp)->where('is_active',1)->where('is_deleted',0)->update(['status'=>1]);
                    return ['httpcode'=>200,'status'=>'success','message'=>'OTP verified','data'=>['redirect' =>'registeration','country_code'=>$request->country_code,'phone_number'=>$request->phone_number]];
                     }
                     else
                     {
                         return array('httpcode'=>'400','status'=>'error','message'=>'Invalid OTP','data'=>['message' =>'Entered OTP is Invalid!']);
                     }
                    
                }
                else
                {
                   return array('httpcode'=>'400','status'=>'error','message'=>'Phone number does not exist','data'=>['message' =>'Phone number does not exist!']);
                }
              
            }
    }
    
     public function loginSendotp(Request $request)
    {
        $formData   =   $request->all(); 
        $rules      =   array();
        $rules['country_code']    = 'required|string';
        $rules['phone_number']    = 'required|numeric|min:7,12';
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
            }
        else
            { 
              $regexist = CustomerTelecom::where('country_code',$request->country_code)->where('usr_telecom_value',$request->phone_number)->where('is_active',1)->where('is_deleted',0);
              if($regexist->count() > 0)
              {
                $otp = rand(1000, 9999); $otp = 1234;
                CustomerTelecom::where('country_code',$request->country_code)->where('usr_telecom_value',$request->phone_number)->update(['otp'=>$otp,'otp_sent_at'=>date('Y-m-d H:i:s')]);
                return ['httpcode'=>200,'status'=>'success','message'=>'OTP hes been sent to phone','data'=>['otp' =>$otp,'country_code'=>$request->country_code,'phone_number'=>$request->phone_number]];
              }
              else
              {
                return array('httpcode'=>'400','status'=>'error','message'=>'Phone number does not exist','data'=>['message' =>'Phone number does not exist!']);
              }
              
            }
    }

    public function loginVerifyotp(Request $request)
    {
        $formData   =   $request->all(); 
        $post       =   (object)$request->post();
        $rules      =   array();
        $rules['country_code']    = 'required|string';
        $rules['phone_number']    = 'required|numeric|min:7,12';
        $rules['otp']             = 'required|numeric';
        $rules['deviceToken']     = 'required|string|max:200';
        $rules['os']              = 'required|string|max:20';
        $rules['deviceId']        = 'required|string|max:200';
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
            }
        else
            { 
                $exisit = CustomerTelecom::where('country_code',$request->country_code)->where('usr_telecom_value',$request->phone_number)->where('is_deleted',0)->first();
                if($exisit)
                {
                    if($exisit->is_active == 0)
                    {
                         return array('httpcode'=>'400','status'=>'error','message'=>'Account Disabled','data'=>['message' =>'This account has been disabled. Please contact Admin!']);
                    }
                    else
                    {
                        $ext = CustomerTelecom::where('country_code',$request->country_code)->where('usr_telecom_value',$request->phone_number)->where('otp',$request->otp)->where('is_deleted',0)->where('is_active',1);
                         if($ext->count() > 0)
                         {
                              $user = $ext->first();
                              return $this->authenticateUser($user,$post);
                         }
                         else
                         {
                             return array('httpcode'=>'400','status'=>'error','message'=>'Invalid OTP','data'=>['message' =>'Entered OTP is Invalid!']);
                         }
                    }
                  
                    
                }
                else
                {
                   return array('httpcode'=>'400','status'=>'error','message'=>'Phone number does not exist','data'=>['message' =>'Phone number does not exist!']);
                }
              
            }
    }
}
