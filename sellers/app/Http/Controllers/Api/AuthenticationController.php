<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Seller;
use App\Models\SellerInfo;
use App\Models\Store;
use App\Models\Telecom;
use App\Models\SellerTelecom;
use App\Models\StoreCategory;
use App\Models\SellerSecurity;
use App\Models\SellerNotification;
use App\Models\SellerLogin;
use App\Rules\Name;
use Validator;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function register(Request $request)
    {

        $formData   =   $request->all(); 
        $rules      =   array();
        $rules['business_name']         = 'required|string|max:250';
        $rules['store_name']            = 'required|string|max:250';
        $rules['email']                 = 'required|nullable|email|max:255|unique:usr_seller_telecom,value';
        $rules['phone_number']          = 'required|nullable|numeric|digits_between:7,12|unique:usr_seller_telecom,value';
        $rules['address']               = 'required|string';
        $rules['state']                 = 'required|numeric';
        $rules['country']               = 'required|numeric';
        $rules['city']                  = 'required|numeric';
        $rules['zip_code']              = 'required|numeric';
        $rules['store_categories']      = 'required';
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
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

    public function login(Request $request)
    {

        $formData   =   $request->all(); 
        $rules      =   array();
        $rules['user_name']           = 'required|string|max:250';
        $rules['password']            = 'required|string|max:250';
        $rules['device_token']        = 'required|string|max:250';
        $rules['device_id']           = 'required|string|max:250';
        $rules['os']                  = 'required|string';
        
        $validator  =   Validator::make($request->all(), $rules);
        if ($validator->fails()) 
            {
                foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
            }
        else
            { 
                if(isset($request->device_name))
                {   
                    $deviceName = $request->device_name; 
                }
                else
                { 
                    $deviceName = NULL; 
                }

                $seller = Seller::where('username',$request->user_name)->first();
                if($seller)
                {
                    $sellerId = $seller->id;
                    $security       =   SellerSecurity::where('seller_id',$sellerId)->first();
                    if(Hash::check($request->password, $security->password_hash))
                    {
                        $info = SellerInfo::where('id',$sellerId)->first();
                        if($info)
                        {
                            if($info->is_deleted == 1)
                            {
                                return array('httpcode'=>'400','status'=>'error','message'=>'Account removed','data'=>array('errors' =>'This account has been removed. Please contact admin!'));
                            }
                            else if($info->is_active == 0)
                            {
                                return array('httpcode'=>'400','status'=>'error','message'=>'Account disabled','data'=>array('errors' =>'This account has been disabled. Please contact admin!'));
                            }
                            else if($info->is_approved == 0)
                            {
                                 return array('httpcode'=>'400','status'=>'error','message'=>'Admin Approval','data'=>array('errors' =>'Your seller account waiting for admin approval!'));
                            }
                            else if($info->is_approved == 2)
                            {
                                return array('httpcode'=>'400','status'=>'error','message'=>'Rejected','data'=>array('errors' =>'Admin rejected your seller account!'));
                            }
                            else
                            {
                                $tocken    =   $sellerId.substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),0,12);
                                $loginData =   ['seller_id'=>$sellerId,'device_id'=>$request->device_id,'device_name'=>$deviceName,'access_token'=>$tocken,'is_login'=>1,'device_token'=>$request->device_token,'os'=>$request->os,'login_at'=>date('Y-m-d H:i:s')]; 
                                $loginUser  =   SellerLogin::where('device_id',$request->device_id)->where('is_deleted',0);
                                if($loginUser->exists())   
                                {
                                    $loginData['last_login']    =   $loginUser->first()->login_at; 
                                    $update                     =   SellerLogin::where('device_id',$request->device_id)->where('is_deleted',0)->update($loginData);
                                }
                                else
                                { 
                                    $update = SellerLogin::create($loginData); 
                                    
                                }
                                if($update)
                                {
                                    $data['seller_id']      =   $sellerId;    
                                    $data['user_name']      =   $request->user_name;        
                                    $data['fname']          =   $seller->sellerInfo->fname;
                                    $data['email'] =   $data['phone'] = '';
                                    if($seller->phone         !=  NULL){ $data['phone']   =   $seller->telePhone->value; }      
                                    if($seller->email         !=  NULL){ $data['email']   =       $seller->teleEmail->value; }
                                }
                                return array('httpcode'=>'200','status'=>'success','message'=>'Login successfull!','data'=>array('access_token'=>$tocken,'user_details'=>$data));
                            }
                         }
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Invalid credientials','data'=>array('errors' =>'Invalid credientials!'));
                    }
                    
                }
                else
                {
                    return array('httpcode'=>'400','status'=>'error','message'=>'Invalid credientials','data'=>array('errors' =>'Invalid credientials!'));
                }
                
            }
    }
}
