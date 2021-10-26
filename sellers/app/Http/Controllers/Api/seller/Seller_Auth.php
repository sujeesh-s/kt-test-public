<?php

namespace App\Http\Controllers\Api\seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;
use DB;
use App\Models\Admin;
use App\Models\Store;
use App\Models\Seller;
use App\Models\SellerInfo;
use App\Models\SellerSecurity;
use App\Models\SellerTelecom;
use App\Models\SellerLogin;
use App\Models\Staff;
use App\Models\StaffInfo;
use App\Models\StaffSecurity;
use App\Models\PasswordReset;
use App\Models\Email;
use App\Models\SellerOTP;
use App\Models\SellerNotification;
use App\Models\StoreCategory;
use App\Models\Telecom;
use App\Models\Category;

use App\Rules\Name;
use Validator;
use Illuminate\Support\Facades\Hash;

class Seller_Auth extends Controller
{
    public function register(Request $request)
    {

        $formData   =   $request->all(); 
        $rules      =   array();
        $rules['business_name']         = 'required|string|max:250';
        $rules['store_name']            = 'required|string|max:250';
        $rules['store_desc']            = 'required|string|max:250';
        $rules['about']                 = 'nullable|string';
        $rules['email']                 = 'required|nullable|email|max:255|unique:usr_seller_telecom,value';
        $rules['phone_number']          = 'required|nullable|numeric|digits_between:7,12|unique:usr_seller_telecom,value';
        $rules['address']               = 'required|string';
        $rules['post_office']           = 'nullable';
        $rules['commission']            = 'required|numeric';
        $rules['country']               = 'required|numeric';
        $rules['state']                 = 'required|numeric';
        $rules['city']                  = 'required|numeric';
        $rules['zip_code']              = 'required|numeric';
        $rules['store_categories']      = 'required';
        $rules['certificate']           = 'nullable';
       // $rules['certificate']           = 'nullable|mimes:jpg,bmp,png,pdf,docx';
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                $str='';
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0];
                  $pos = str_replace('.', ',', $row[0]);//strrpos($row[0], '.');
                  $str.=  $pos;//substr_replace($row[0],'.',$pos).",";
                } 
                $error_str=substr($str,0,-1);
                return array('httpcode'=>'400','status'=>'error','message'=>$error_str,'data'=>array('errors' =>(object)$error));
            }
        else
            { 
                $emailTypeId            =   Telecom::where('name','email')->first()->id;
                $phoneTypeId            =   Telecom::where('name','phone')->first()->id;
                $sellerId               =   Seller::create(['username'=>$request->email,'role_id'=>3,'password'=>Hash::make('123456')])->id;

                $info['seller_id']      =   $sellerId; 
                $info['fname']          =   $request->director_name; 
                $info['ic_number']      =   $request->director_ic_number; 
                SellerInfo::create($info);

                $store['seller_id']     =   $sellerId;
                $store['business_name'] =   $request->business_name;
                $store['store_name']    =   $request->store_name;
                $store['licence']       =   $request->store_licence;
                $store['address']       =   $request->address;
                $store['address2']      =   $request->address2;
                $store['post_office']   =   $request->post_office;
                $store['latitude']      =   $request->latitude;
                $store['longitude']     =   $request->longitude;
                $store['country_id']    =   $request->country;
                $store['state_id']      =   $request->state;
                $store['city_id']       =   $request->city;
                $store['zip_code']      =   $request->zip_code;
                $store['store_desc']    =   $request->store_desc;
                $store['commission']    =   $request->commission;
                $store['apply_tax']     =   $request->apply_tax;
                $store['incharge_name'] =   $request->incharge_name;
                $store['incharge_phone']=   $request->incharge_phone;
                $store['incharge_isd_code'] =   $request->incharge_isd_code;

                $storeId                =   Store::create($store)->id;
            
                $teleEmail              =   ['seller_id'=>$sellerId,'type_id'=>$emailTypeId,'value'=>$request->email]; 
                 $emailId                =   SellerTelecom::create($teleEmail)->id; 
                 Seller::where('id',$sellerId)->update(['email'=>$emailId]); 
            
                $telePhone              =   ['seller_id'=>$sellerId,'type_id'=>$phoneTypeId,'value'=>$request->phone_number]; 
                $phoneId                =   SellerTelecom::create($telePhone)->id;
                Seller::where('id',$sellerId)->update(['phone'=>$phoneId]); 
                
                SellerSecurity::create(['seller_id'=>$sellerId,'password_hash'=>Hash::make('123456')]);

                $cat = explode(',', $request->store_categories);
                
                if(!empty($cat)){ $this->assignStoreCategories($cat,$storeId,$sellerId); }
        
                if($request->file('logo')){ 
                    $image              =   $request->file('logo');
                    $imgName            =   'logo.'.$image->extension();
                    $path               =   '/app/public/stores/'.$storeId;
                    $destinationPath    =   storage_path($path);
                    $image->move($destinationPath, $imgName);
                    $imgData['logo']    =   $path.'/'.$imgName;
                    Store::where('id',$storeId)->update($imgData);
                    $imgUpload          =   uploadFile($path,$imgName);
                }
                if($request->file('certificate')){ 
                    $image1              =   $request->file('certificate');
                    $imgName1            =   'cert.'.$image1->extension();
                    $path1               =   '/app/public/stores_certificate/'.$storeId;
                    $destinationPath1    =   storage_path($path1);
                    $image1->move($destinationPath1, $imgName1);
                    $imgData1['certificate']  =   $path1.'/'.$imgName1;
                    Store::where('id',$storeId)->update($imgData1);
                    $imgUpload1          =   uploadFile($path1,$imgName1);
                }

                return array('httpcode'=>'200','status'=>'success','message'=>'Seller Registered','data'=>['message' =>'Seller registered Successfully! After approval of Admin, you can access your account.']);
            }
    }

     function assignStoreCategories($catIds,$storeId,$sellerId){
        StoreCategory::where('store_id',$storeId)->update(['is_deleted'=>1]);
        if(count($catIds) > 0)
        {
            foreach($catIds as $cId){ 
            if(StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->count() > 0)
            { 
                StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->update(['is_deleted'=>0]);
            }
            else
                { 
                    StoreCategory::create(['seller_id'=>$sellerId,'store_id'=>$storeId,'category_id'=>$cId]); 
                }
        }
        }
         return true;
    }
    
    public function login(Request $request){  

        $post                       =   (object)$request->post(); 
        $error = [];  
        $user = false;
        $rules                      =   ['email' => 'required|string|email|max:100',
                                         'password'=>'required',
                                         'deviceToken'=>'required',
                                         'deviceId'=>'required',
                                         'deviceName'=>'required'];
        $validator                 =   Validator::make($request->post(),$rules);
        if ($validator->fails()) {
            
         return array('httpcode'=>400,'status'=>'error','message'=>'Invalid Credential','data'=>array('errors' =>(object)['error_msg'=>'Invalid Email']));
            
        }
        else
            { 
            $seller_mst = Seller::where('username',$request->email)->first(); 
            
            if($seller_mst) {      
            $security    = SellerSecurity::where('seller_id',$seller_mst->id)->first();

            if(Hash::check($request->password, $security->password_hash)){
                // echo "dsf";die;

             }

             else{ 
             //Auth::guard('seller')->logout(); 
             return array('httpcode'=>400,'status'=>'error','message'=>'Incorrect Password','data'=>array('errors' =>(object)['error_msg'=>'Incorrect Password'])); 
              }
            if(SellerInfo::where('seller_id', $seller_mst->id)->first()) {
                if(SellerInfo::where('seller_id', $seller_mst->id)->first()->is_approved == 1){
                 $seller_data= SellerInfo::where('seller_id', $seller_mst->id)->first();   
                 
                 return $this->authenticateUserSeller($seller_data,$post);
                  }
                  else if(SellerInfo::where('seller_id', $seller_mst->id)->first()->is_approved == 2)
                            {
                                return array('httpcode'=>'400','status'=>'error','message'=>'Rejected','data'=>array('errors' =>'Admin rejected your seller account!'));
                            }
                else{
                    //Auth::guard('seller')->logout(); $request->session()->flush(); $request->session()->regenerate();
                    return array('httpcode'=>200,'status'=>'success','message'=>'Waiting for approval');
                }
            }
            else{ 
                return array('httpcode'=>400,'status'=>'error','message'=>'Invalid credientials','data'=>array('errors' =>(object)['error_msg'=>'Invalid credientials'])); 
                 }
        }
        // else if(Auth::guard('staff')->attempt(['username' => $request->email, 'password' => '123456'])) {

        //     $security       =   StaffSecurity::where('staff_id',Auth::guard('staff')->user()->id)->first();
           
        //     if(Hash::check($request->password, $security->password_hash)){ }else{   Auth::guard('staff')->logout(); return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. '); }
        //     if(StaffInfo::where('staff_id', Auth::guard('staff')->user()->id)->first()) {

        //         if(StaffInfo::where('staff_id', Auth::guard('staff')->user()->id)->first()->is_active == 1){ 
                     
        //              print_r($seller_data);
        //          die;
        //          return $this->authenticateUserStaff($seller_data->seller_id); }
        //         else{

        //             Auth::guard('staff')->logout(); $request->session()->flush(); $request->session()->regenerate();
        //             return redirect('/login')->withInput($request->only('email', 'remember'))->with('message',' The staff is not activated yet. ');
        //         }
        //     }else{  return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. '); }
        // }
        else {
          return array('httpcode'=>400,'status'=>'error','message'=>'Invalid credientials','data'=>array('errors' =>(object)['error_msg'=>'Invalid credientials'])); 
        }
        
        
    }
    }
    
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'seller_id'              =>['required','numeric']]);
        $input = $request->all();
        if ($validator->passes()) {
      
        $login = SellerLogin::where('seller_id',$input['seller_id'])->first();
        if($login){   

            $loginData = ['access_token'=>'','is_login'=>0]; 
            SellerLogin::where('seller_id',$input['seller_id'])->where('is_deleted',0)->update($loginData);
            return ['httpcode'=>200,'status'=>'success','message'=>'logout successfully','data'=>['message'=>'logout successfully']];
          }
          else
          {
            return response()->json(['httpcode'=>404,'status'=>'error','message'=>'No seller found','data'=>'No seller found']);
          }
         }
         else
        {
            return response()->json(['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]]);
        }
    }
    
    public function login_username(Request $request){  

        $post                       =   (object)$request->post(); 
        $error = [];  
        $user = false;
        $rules                      =   ['email' => 'required|string|email|max:100'];
        $validator                 =   Validator::make($request->post(),$rules);
        if ($validator->fails()) {
            
         return array('httpcode'=>400,'status'=>'error','message'=>'Invalid Credential','data'=>array('errors' =>(object)['error_msg'=>'Invalid Email']));
            
        }
        else
            { 
            $seller_mst = Seller::where('username',$request->email)->first(); 
            
            if($seller_mst) { 
                if(SellerInfo::where('seller_id', $seller_mst->id)->first()) {
                if(SellerInfo::where('seller_id', $seller_mst->id)->first()->is_approved == 1){
                 $seller_data= SellerInfo::where('seller_id', $seller_mst->id)->first();   
                 
                  return ['httpcode'=>200,'status'=>'success','message'=>'Username accepted','data'=>['status'=>'Accepted','username'=>$request->email]];
                 //$this->authenticateUserSeller($seller_data,$post);
                  }
                  else if(SellerInfo::where('seller_id', $seller_mst->id)->first()->is_approved == 2)
                            {
                                return array('httpcode'=>'400','status'=>'error','message'=>'Rejected','data'=>array('status'=>'Rejected','errors' =>'Admin rejected your seller account!'));
                            }
                else{
                    // Auth::guard('seller')->logout(); $request->session()->flush(); $request->session()->regenerate();
                    return array('httpcode'=>200,'status'=>'success','message'=>'Waiting for approval','data'=>array('status' =>'Waiting for approval'));
                }
              }
            }//if seller mst end
            else
            {
              return array('httpcode'=>400,'status'=>'error','message'=>'Invalid credientials','data'=>array('errors' =>(object)['error_msg'=>'Invalid credientials']));   
            }
        }


        }
    
    public function check_username(Request $request)
    {
        $validator = Validator::make($request->all(), ['email'=>['required','email']]);
        $input = $request->all();

        if ($validator->passes()) {
            $seller = Seller::where('username',$request->email)->first();
                if($seller)
                {
                    $sellerId = $seller->id;
                    $info = SellerInfo::where('seller_id',$sellerId)->first();
                    $data= ['seller_id'=>$info->seller_id,'seller_name'=>$info->fname];
                    return ['httpcode'=>200,'status'=>'success','message'=>'Valid username','data'=>['seller'=>$data]];
                }
                else
                {
                    return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'Username not found']];
                }
        }
        else
        {
            return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]];
        }
    }

    public function forgot_pwd(Request $request)
    {
        $validator = Validator::make($request->all(), 
            ['password'              =>['required','min:8','max:20'],
            'seller_id'              =>['required','numeric']
        ]);
        $input = $request->all();

        if ($validator->passes()) {
            $seller = Seller::where('id',$request->seller_id)->first();
                if($seller)
                {
                $sellerId = $seller->id;    
                $update=SellerSecurity::where('seller_id',$sellerId)->update(['password_hash'=>Hash::make($input['password']),'updated_at'=>date('Y-m-d H:i:s')]); 
                return ['httpcode'=>200,'status'=>'success','message'=>'Password updated successfully','data'=>['message'=>'Password updated successfully']];
                }
                else
                {
                    return response()->json(['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'Username not found']]);
                }
        }
        else
        {
            return response()->json(['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]]);
        }
    }
    
    public function generate_seller_otp(Request $request)
    {
        $validator = Validator::make($request->all(), ['email'=>['required','email']]);
        $input = $request->all();

        if ($validator->passes()) {
            $seller = Seller::where('username',$request->email)->first();
            if($seller)
            {
              $otp = $this->generateOtp($seller);
              return ['httpcode'=>200,'status'=>'success','message'=>'OTP generated','data'=>['otp'=>$otp,'seller_id'=>$seller->id]];
            }
            else
            {
                return ['httpcode'=>400,'status'=>'error','message'=>'Invalid username/email','data'=>['errors'=>'Invalid username/email']];
            }
        }
        else
        {
           return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]]; 
        }
    }

    function generateOtp($user){
        $otp = rand(1000, 9999);    //$otp = 1234;
        $sellerotp=SellerOTP::where('user_id',$user->id)->first();
        if($sellerotp){
        SellerOTP::where('user_id',$user->id)->update(['token'=>$otp,'updated_at'=>date('Y-m-d H:i:s')]); 
        }
        else
        {
           SellerOTP::create(['user_id'=>$user->id,'user_type'=>'seller','email'=>$user->username,'token'=>$otp,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]); 
        }
       // CustomerTelecom::where('id',$user->email)->update(['otp'=>$otp,'otp_sent_at'=>date('Y-m-d H:i:s')]); 
        return $otp;
    }

    public function confirm_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp'=>['required','numeric','min:4'],
            'seller_id'=>['required','numeric']]);
        $input = $request->all();

        if ($validator->passes()) {
            $seller = SellerOTP::where('user_id',$request->seller_id)->where('token',$request->otp)->first();
                if($seller)
                {
                    $sellerId = $seller->user_id;
                    $info = SellerInfo::where('seller_id',$sellerId)->first();
                    $data= ['seller_id'=>$info->seller_id,'seller_name'=>$info->fname];
                    return ['httpcode'=>200,'status'=>'success','message'=>'Valid username','data'=>['seller'=>$data]];
                }
                else
                {
                    return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'Not found/Incorrect otp']];
                }
        }
        else
        {
            return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]];
        }
    }

    function authenticateUserSeller($user,$post){
        $tocken                 =   $user->user_id.substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,12);
        if(isset($post->deviceName)){   
            $deviceName = $post->deviceName;
             }
             else{ $deviceName = NULL; }
        
        $loginData              =   ['seller_id'=>$user->seller_id,'device_id'=>$post->deviceId,'device_name'=>$deviceName,'access_token'=>$tocken,'is_login'=>1,'device_token'=>$post->deviceToken,'os'=>'APP','login_at'=>date('Y-m-d H:i:s')];
         $loginUser             =   SellerLogin::where('device_id',$post->deviceId)->where('is_deleted',0);
         if($loginUser->exists()){   $loginData['last_login']    =   $loginUser->first()->login_at; 
             SellerLogin::where('device_id',$post->deviceId)->where('is_deleted',0)->update($loginData);
         }else{ SellerLogin::create($loginData); }
        if($user){
            $mst                =   Seller::where('id',$user->seller_id)->first();
            $data['seller_id']  =   $user->seller_id;                 
            $data['fname']      =   $user->fname;
            $data['mname']      =   $user->mname;
            $data['lname']      =   $user->lname;
            $data['store_name'] =   $mst->store->store_name;
            $data['business_name']=   $mst->store->business_name;               
            $data['phone']      =   $mst->teleEmail->value;       
            $data['email']      =   $mst->telePhone->value;
            if($user->avatar){
            $data['profile_img']=   config('app.storage_url').$user->avatar;
            }
            else
            {
               $data['profile_img']='';
            }
            return ['httpcode'=>200,'status'=>'success','message'=>'Login successfull!','data'=>array('access_token'=>$tocken,'user_details'=>$data)];
        }else{ return array('httpcode'=>400,'status'=>'error','message'=>'Somthing went wrong','data'=>['errors' =>(object)['error_msg'=>'Somthing went wrong']]); }
    }
    
    
    
    
    
    /************country************/
        public function get_country(Request $request)
        {
            $country =DB::table('countries')->where('is_deleted', 0)->get();
            return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['country'=>$country]];
        }
        
        public function get_state(Request $request)
        {
            $validator=  Validator::make($request->all(),[
            'country_id' => ['required','numeric']
        ]);
        $input = $request->all();

            if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','errors'=>$validator->messages()];
            }
            else
            {
                    $country =DB::table('states')->where('country_id', $input['country_id'])->where('is_deleted', 0)->get();
                    return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['state'=>$country]];
            }
        }
        
        public function get_city(Request $request)
        {
            $validator=  Validator::make($request->all(),[
            'state_id' => ['required','numeric']
        ]);
        $input = $request->all();

            if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','errors'=>$validator->messages()];
            }
            else
            {
                    $country =DB::table('cities')->where('state_id', $input['state_id'])->where('is_deleted', 0)->get();
                    return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['city'=>$country]];
            }
        }
        
        public function get_categories()
        {
            $lang_id='';
            $category_data= Category::where('is_active',1)->where('is_deleted',0)->orderBy('sort_order')->get();
               foreach($category_data as $key)
                {    
                    $category_array=['id'=>$key->category_id,
                    'category_name'=>$key->get_content($key->cat_name_cid,$lang_id)];
                    $categories[]=$category_array;
                 }
            return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['categories'=>$categories]];     
        }
        function get_content($field_id,$lang){ 
     
        if($lang=='')
        { 
        $language =DB::table('glo_lang_lk')->where('is_active', 1)->first();
        $language_id=$language->id;
        }
        else
        {
            $language_id=$lang;
        }
        $content_table=DB::table('cms_content')->where('cnt_id', $field_id)->where('lang_id', $language_id)->first();
        if(!empty($content_table)){ 
        $return_cont = $content_table->content;
        return $return_cont;
        }
        else
            { return false; }
        }
}
