<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use App\Models\Seller;
use App\Models\SellerInfo;
use App\Models\Store;
use App\Models\Telecom;
use App\Models\SellerTelecom;
use App\Models\StoreCategory;
use App\Models\SellerSecurity;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('guest:seller');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }
    
    public function showSellerRegisterForm()
    {
        return view('seller.auth.register', ['url' => 'admin']);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    
    function validateSeller(Request $request){
        $post                   =   (object)$request->post(); $error = false;
        $validator              =   Validator::make($request->post(), [
                                        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],'phone' => 'required|numeric|digits_between:7,12',
                                    ]);
        $validator1             =   Validator::make($post->store, [
                                        'business_name' => ['required', 'string', 'max:250'],'store_name' => ['required', 'string', 'max:250'],'address' => ['required', 'string', 'max:250'],
                                        'zip_code' => ['required', 'numeric', 'min:5'],'country_id' => ['required'],'state_id' => ['required'],'city_id' => ['required'],
                                    ]);
        $validator2             =   Validator::make($post->info, ['director_name' => 'required', 'string', 'max:55']);
        $validEmail             =   SellerTelecom::ValidateUnique('email',$post->email,0); 
        $validPhone             =   SellerTelecom::ValidateUnique('phone',$post->phone,0); 
        if ($validator->fails()){   foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; } }
        if ($validator1->fails()){  foreach($validator1->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; } }
        if ($validator2->fails()){  foreach($validator2->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; } }
        if($validEmail){ $error['email']    =   $validEmail;}
        if($validPhone){ $error['phone']    =   $validPhone;}
        if($error) { return $error; }else{ return 'success'; }
    }
    
    protected function createSeller(Request $request)
    { 
        $post                   =   (object)$request->post(); $info = $post->info; $store = $post->store;
        $emailTypeId            =   Telecom::where('name','email')->first()->id; $phoneTypeId   =   Telecom::where('name','phone')->first()->id;
        $sellerId               =   Seller::create(['username'=>$post->email,'role_id'=>3,'password'=>Hash::make('123456')])->id;
        $info['seller_id']      =   $sellerId; $info['fname'] = $info['director_name']; SellerInfo::create($info); $store['seller_id'] = $sellerId;
        $storeId                =   Store::create($store)->id;
        $teleEmail              =   ['seller_id'=>$sellerId,'type_id'=>$emailTypeId,'value'=>$post->email]; 
        $telePhone              =   ['seller_id'=>$sellerId,'type_id'=>$phoneTypeId,'value'=>$post->phone]; 
        $emailId                =   SellerTelecom::create($teleEmail)->id;  SellerSecurity::create(['seller_id'=>$sellerId,'password_hash'=>Hash::make('123456')]);
        $phoneId                =   SellerTelecom::create($telePhone)->id; Seller::where('id',$sellerId)->update(['email'=>$emailId,'phone'=>$phoneId]);
        if(count($post->categories) > 0){ $this->assignStoreCategories($post->categories,$storeId,$sellerId); }
        
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
        if($sellerId){ 
            $from       = $sellerId; 
            $utype      = 2;
            $to         = 1;
            $ntype      = 'seller_request';
            $title      = 'Seller Request';
            $desc       = 'New seller request from '.$info['fname'];
            $refId      = $sellerId;
            $reflink    = 'new-sellers';
            $notify     = 'admin';
            addNotification($from,$utype,$to,$ntype,$title,$desc,$refId,$reflink,$notify);
            return back()->with('success','You are registered successfully. After approval of Admin, you can access your seller account.'); 
            
        }
        else{ return back()->with('error','Something went wrong. Please try after some time.'); }
    }
    
    public function dropdownData(Request $request) { 
        $post                   =   (object)$request->post(); 
        $data                   =   getDropdownValues($post->table,$post->field,$post->value,$post->label);
        $label                  =   $post->label;
        $options                =   '<option value="">'.$post->placeholder.'</option>';
        if($data){ foreach($data    as  $row){
            if($post->selected == $row->id){$selected = 'selected="selected"'; }else{ $selected = ''; }
            $options            .=  '<option value="'.$row->id.'" '.$selected.'>'.$row->$label.'</option>';
        } } return $options;
    }
    
    function assignStoreCategories($catIds,$storeId,$sellerId){
        StoreCategory::where('store_id',$storeId)->update(['is_deleted'=>1]);
        foreach($catIds as $cId){ 
            if(StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->count() > 0){ 
                StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->update(['is_deleted'=>0]);
            }else{ StoreCategory::create(['seller_id'=>$sellerId,'store_id'=>$storeId,'category_id'=>$cId]); }
        } return true;
    }
}
