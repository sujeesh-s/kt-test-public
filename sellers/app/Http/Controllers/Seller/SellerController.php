<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Crypt;
use Session;
use DB;
use App\Models\Modules;
use App\Models\Category;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Telecom;
use App\Models\Seller;
use App\Models\UserRole;
use App\Models\SellerInfo;
use App\Models\SellerSecurity;
use App\Models\SellerTelecom;
use App\Models\StoreCategory;
use App\Models\Store;
use App\Models\SellerAddress;
use App\Models\SlotDelySlot;
use App\Models\SpotDelySlot;
use App\Models\AssignedSlot;
use App\Models\SellerBank;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Rules\Name;
use Validator;

class SellerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:seller,staff');
    }
    public function index()
    { 
        return view('seller.seller');
    }
     public function dashboard()
    { 
      
      $data['orders'] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->orderBy('id','desc')->get(); 
        if($data['orders']){ $data['orders_c'] = count($data['orders']); }else { $data['orders_c'] =0; }
         $data['orders_pending'] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->where('order_status',"pending")->orderBy('id','desc')->get(); 
        if($data['orders_pending']){ $data['orders_pending_c'] = count($data['orders_pending']); }else { $data['orders_pending_c'] =0; }
        $data['total_ord_amt'] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->where('order_status',"success")->sum('g_total'); 



         //ecom counts
        $data['revenue'] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))->where('order_status',"success")->sum('g_total'); 
        if(! $data['revenue']){ $data['revenue'] =0;  }
        $data['ord_processing'] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))->where(function ($query) { $query->where('order_status', '=', "processing")->orWhere('order_status', '=', "pending");})->get();
        if($data['ord_processing']){ $data['ord_processing_c'] = count($data['ord_processing']); }else { $data['ord_processing_c'] =0; }
        $data['ord_completed'] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->whereMonth('created_at', date('m'))
        ->whereYear('created_at', date('Y'))->where('order_status',"success")->get(); 
        if($data['ord_completed']){ $data['ord_completed_c'] = count($data['ord_completed']); }else { $data['ord_completed_c'] =0; }


         $data['products'] = Product::where('is_approved',1)->where('seller_id',auth()->user()->id)->where('is_active',1)->where('is_deleted',0)->orderBy('id','desc')->get();
         if($data['products']){ $data['products_c'] = count($data['products']); }else { $data['products_c'] =0; }
        $prd_out_stock_soon = array();
        foreach($data['products'] as $row){
        if(($row->prdStock($row->id) <10) && ($row->prdStock($row->id) >= 0)){
        $prd_out_stock_soon[] = $row;    
        }
        }
        $data['prd_out_stock_soon'] = $prd_out_stock_soon;
        if($data['prd_out_stock_soon']){ $data['prd_out_stock_soon_c'] = count($data['prd_out_stock_soon']); }else { $data['prd_out_stock_soon_c'] =0; }
        
       

            $sales_graph_revenue = $sales_graph_profit = array();

            for($i = 1; $i < 13; $i++) {
            $sales_graph_revenue[$i] = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->whereMonth('created_at', $i)->where('order_status',"success")->sum('g_total');
            $sales_graph_profit[$i] = $sales_graph_revenue[$i] - SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->whereMonth('created_at', $i)->where('order_status',"success")->sum('ecom_commission');
            }

            $data['sales_graph_revenue'] = $sales_graph_revenue;
            $data['sales_graph_profit'] = $sales_graph_profit;
            
        // dd($data);
        return view('seller.dashboard',$data);
    }
    function sale_ord_cnt($date){
            $cnt = 0;
            $orders = SalesOrder::where('org_id',1)->where('seller_id',auth()->user()->id)->where('order_status', '!=', "cancelled")->whereDate('created_at', '=', date('Y-m-d',strtotime($date)))->sum('g_total');
            if($orders){
                $cnt = $orders;
            }
            return $cnt;
        }
    function profile(){
        return view('admin.profile');
    }
       function sellerLogout(){ 
        Auth::logout(); return redirect('/login')->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0');
    }

    function validateUser(Request $request){
        $profile                =   $request->post('profile');
        $rules                  =   [
                                        'fname'                 =>  ['required', 'string','max:100', new Name],
                                        'email'                 =>  'required|string|email|max:100',
                                        'phone'                 =>  'required|numeric|digits_between:7,12',
                                    ];
        if($profile['lname']   !=  ''){ $rules['lname']        =   ['required', 'string','max:100', new Name]; }
        $validator              =   Validator::make($profile,$rules);
        $validEmail             =   Admin::ValidateUnique('email',(object)$profile,auth()->user()->id);
        $validPhone             =   Admin::ValidatePhone('phone',(object)$profile,auth()->user()->id);
        if ($validator->fails()) {
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }
        if($validEmail){ $error['email']    =   $validEmail; }
        if($validPhone){ $error['phone']    =   $validPhone; }
        if(isset($error)) { return $error; }else{ return 'success'; }
    }
    
    function saveProfile(Request $request){
        $profile                =   Admin::where('id',auth()->user()->id)->update($request->post('profile'));
        if($profile){   return      back()->with('success',' Profile updated successfully! '); }else{ return back(); }
    }
    
    function validatePassword(Request $request){
        $post                   =   (object)$request->post();
        $validator              =   Validator::make($request->post(),['curr_password' => 'required|string|regex:/^\S*$/u','password' => 'required|string|min:6|regex:/^\S*$/u|confirmed']);
        $user                   =   Admin::where('id',auth()->user()->id)->first();
        if ($validator->fails()) {
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }
        if (Hash::check($request->curr_password, $user->getOriginal('password'))) {
        }else{ $error['curr_password'] = 'Invalid current password'; }
        if(isset($error)) { return $error; }else{ return 'success';  }
    }
    
    function savePassword(Request $request){
        $res        =   Admin::where('id',auth()->user()->id)->update(['password' => Hash::make($request->post('password'))]);
        if($res){ return 'success'; }else{ return 'error'; }
    }
    
    function adminLogout(){ 
        Auth::logout(); return redirect('admin/login');
    }
    
    
    
        
    
        
        //admins list
        public function admins()
        { 
        $data['title']              =   'Admins';
        $data['menu']               =   'admin-list';
        $data['admins']              =   Admin::where('role_id',2)->where(function ($query) {
    $query->where('is_deleted', '=', NULL)
          ->orWhere('is_deleted', '=', 0);})->get();
        // dd($data);
        return view('admin.admins.list',$data);
        }

              public function createAdmin()
        { 
        $data['title']              =   'Create Admin';
        $data['menu']               =   'create-admin';
        $data['modules']              =   Admin::where('is_deleted',NULL)->orWhere('is_deleted',0)->get();
        return view('admin.admins.create',$data);
        }

        public function editAdmin($role_id)
        { 
        $data['title']              =   'Edit Admin';
        $data['menu']               =   'edit-admin';
        $data['admin']              =   Admin::where('id',$role_id)->first();

        return view('admin.admins.edit',$data);
        }
        public function viewAdmin($role_id)
        { 
        $data['title']              =   'View Admin';
        $data['menu']               =   'view-admin';
        $data['admin']              =   Admin::where('id',$role_id)->first();

        return view('admin.admins.view',$data);
        }
    

        public function adminSave(Request $request){
        $post           =   (object)$request->post();
        // dd($post);
        $user           =   $post->user; 
        if($post->id    >   0){
        if($post->user['password']      ==  ''){ unset($post->user['password']); }
        else{ $post->user['password']   =   Hash::make($post->user['password']); }
        $post->user['updated_at']       =   date('Y-m-d H:i:s');
        $insId      =   $post->id; Admin::where('id',$post->id)->update($post->user);   
        $msg        =   'Admin details updated successfully!';
        }else{
        
        $rules                  =   [
        
        'email'                 =>  'required|unique:admins|email|max:100',
        'phone'                 =>  'required|numeric|unique:admins',
        ];
        $validator              =   Validator::make($user,$rules);
        if ($validator->fails()) {
        foreach($validator->messages()->getMessages() as $k=>$row){  $error[$k] = $row[0];
        Session::flash('message', ['text'=>$row[0],'type'=>'danger']); }
        
        return redirect(route('admin.admins'));
        }
        
        $post->user['password']         =   Hash::make($post->user['password']);
        $post->user['created_at']       =   date('Y-m-d H:i:s');
        $insId      =   Admin::create($post->user)->id;
        $msg        =   'Admin details added successfully!';
        }
        if($insId){
        
        if($request->file('avatar') && $request->file('avatar') != ''){
        $image = $request->file('avatar');
        $input['imagename'] = 'avatar.'.$image->extension();
        $path               =   '/app/public/user/'.$insId;
        $destinationPath = storage_path($path.'/thumbnail');
        $img = Image::make($image->path());
        if (!file_exists($destinationPath)) { mkdir($destinationPath, 755, true);}
        $img->resize(150, 150, function ($constraint) {
        $constraint->aspectRatio();
        })->save($destinationPath.'/'.$input['imagename']);
        $destinationPath = storage_path($path);
        $image->move($destinationPath, $input['imagename']);
        Admin::where('id',$insId)->update(['avatar'=>$path.'/'.$input['imagename']]); 
        }
        $data['title']              =   'Admins';
        $data['menu']               =   'admin-list';
        $data['admins']              =   Admin::where('role_id',2)->where(function ($query) {
        $query->where('is_deleted', '=', NULL)
        ->orWhere('is_deleted', '=', 0);})->get();
        // dd($data);
        Session::flash('message', ['text'=>$msg,'type'=>'success']);
        return redirect(route('admin.admins'));
        
        }else{ 
        
        Session::flash('message', ['text'=>"Failed to save details.",'type'=>'danger']);
        
        return redirect(route('admin.admins'));
        }
        }
    
         
        public function adminStatus(Request $request)
        {
        $input = $request->all();
        
        if($input['id']>0) {
        $deleted =  Admin::where('id',$input['id'])->update(array('is_active'=>$input['status']));
        
        return '1';
        }else {
        
        return '0';
        }
        
        }
        
         // seller management

        function seller(Request $request, $id=0,$type=''){
        $id = auth()->user()->id;
        $data['title']              =   'View Seller Profile'; 
        $data['menuGroup']          =   'userGroup';
        $data['menu']               =   'seller';
        $stateId = $countryId       =   0;
        $data['seller']             =   Seller::where('id',$id)->first();
        $data['info']               =   SellerInfo::where('id',$id)->first();
        $store                      =   Store::where('seller_id',$id)->where('is_deleted',0)->first();
        $data['store']              =   $store;
        $data['bank']               =   SellerBank::getBankData($id);
        if($store){ $countryId      =  (int)$store->country_id; $stateId = (int)$store->state_id; }
        $data['categories']         =   getDropdownData(Category::where('is_deleted',0)->get(),'category_id','cat_name');
        $data['c_code']              =   getDropdownData(Country::where('is_deleted',0)->get(),'id','phonecode');
        $data['countries']          =   getDropdownData(Country::where('is_deleted',0)->get(),'id','country_name');
        $data['states']             =   getDropdownData(State::where('country_id',$countryId)->where('is_deleted',0)->get(),'id','state_name');
        $data['cities']             =   getDropdownData(City::where('state_id',$stateId)->where('is_deleted',0)->get(),'id','city_name');
        $data['slots']              =   getDropdownData(SlotDelySlot::where('is_deleted',0)->get(),'id','slot_name');
        $data['spots']              =   getDropdownData(SpotDelySlot::where('is_deleted',0)->get(),'id','slot_name');
        $data['assSlots']           =   AssignedSlot::where('seller_id',$id)->where('slot_type','slot')->where('is_deleted',0)->get();
        $data['assSpots']           =   AssignedSlot::where('seller_id',$id)->where('slot_type','spot')->where('is_deleted',0)->get();
        $data['assCategories']      =   StoreCategory::where('seller_id',$id)->where('is_deleted',0)->get(['category_id']);
        $data['filters']            =   $request->post();
   // dd($data);
        // return view('seller.profile.details',$data);
        return view('seller.profile.page',$data);
    }

    function validateSeller(Request $request){
        $post                   =   (object)$request->post(); $error = false;
        $info                   =   $request->post('info'); $store = $request->post('store'); $storeSet = $request->post('storeSet'); 

        $bank_arr = array();
        $bank_arr['acc_number'] = $post->acc_number;
        $bank_arr['acc_holder'] = $post->acc_holder;
        $bank_arr['bank_name'] = $post->bank_name;
        $bank_arr['ifsc'] = $post->ifsc;
        $bank_arr['branch_name'] = $post->branch_name;


        $rules                  =   [
                                        'email'                 =>  'required|string|email|max:100',
                                        'phone'                 =>  'required|numeric|digits_between:7,12',
                                        'business_name'         =>  'required|string','store_name'  =>  'required|string',
                                        'director_name'         =>  ['required', 'string','max:100', new Name],
                                    ];
        $validator              =   Validator::make($post->info,$rules);
        $validEmail             =   SellerTelecom::ValidateUnique('email',$info['email'],$post->id);
        $validPhone             =   SellerTelecom::ValidateUnique('phone',$info['phone'],$post->id);
        if ($validator->fails()){
            $error['error']     =   'info';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }
        if($validEmail){ $error['email']    =   $validEmail; $error['error']     =   'info';}
        if($validPhone){ $error['phone']    =   $validPhone; $error['error']     =   'info';}
        if($error) { return $error; }
        $rules                  =   ['address'  => 'required|string|max:250','country_id' =>  'required','state_id' =>  'required', 'city_id' =>  'required',];   
        $validator              =   Validator::make($store,$rules);
        if ($validator->fails()){
            $error['error']     =   'store';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        } 
        $rules                  =   ['categories'  => 'required','commission' =>  'required|numeric|min:1'];   
        $validator              =   Validator::make($storeSet,$rules);
        if ($validator->fails()){
            $error['error']     =   'storeSet';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        } 
         $rules                  =   ['acc_number'  => 'required|max:20','acc_holder' =>'required','bank_name'  => 'required','ifsc'  => 'required|max:10','branch_name'  => 'required'];   

        $validator              =   Validator::make($bank_arr,$rules);
        if ($validator->fails()){
            $error['error']     =   'bank';
         
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        } 
        if($error) { return $error; }else{ return 'success'; }
    }   

    function saveSeller(Request $request){
        $post                   =   (object)$request->post(); 
        // dd($post);
        $info                   =   (object)$post->info;
        $store                  =   $post->store; 
        $security                  =   $post->security; 
        $storeSet               =   (object)$post->storeSet; 
        $images                 =   $request->file('image');
        $selInfo                   =   ['fname'=>$info->director_name,'ic_number'=>$info->ic_number,'is_active'=>$storeSet->is_active];
     //   $store['pack_charge']   =   $storeSet->pack_charge;
        $store['business_name'] =   $info->business_name;       $store['store_name']    =   $info->store_name;      $store['licence']    =   $info->licence;
             $store['is_active']     =   $storeSet->is_active; $store['incharge_name']     =   $info->incharge_name; $store['incharge_phone']     =   $info->incharge_phone;
        $emailTypeId            =   Telecom::where('name','email')->first()->id; $phoneTypeId   =   Telecom::where('name','phone')->first()->id;
        if($post->id            >   0){ 
            $seller             =   Seller::where('id',$post->id)->first();
            if($info->email     !=  $seller->username){ Seller::where('id',$post->id)->update(['username'=>$info->email]); }
            $sellerId           =   $post->id; $storeId = $post->storeId;
            Seller::where('id',$post->id)->update(['isd_code'=>$info->isd_code]); $store['incharge_isd_code']     =   $info->incharge_isd_code;
                                    SellerTelecom::where('id',$seller->email)->update(['value'=>$info->email]);
                                    SellerTelecom::where('id',$seller->phone)->update(['value'=>$info->phone]);
                                    SellerInfo::where('seller_id',$sellerId)->update($selInfo); Store::where('id',$storeId)->update($store);
            $seller_addr = array();
            $addr_field = array('address','address2','city_id','state_id','country_id','zip_code','land_mark','latitude','longitude');
            foreach($addr_field as $field){
                if(isset($store["$field"])){ $seller_addr["$field"] = $store["$field"]; }else{
                    $seller_addr["$field"] = "";
                }
            }
            
 
            SellerAddress::where('seller_id',$sellerId)->update($seller_addr);
        if($security['password'] !="" && $security['password'] == $security['c_password'] ){
           Seller::where('id',auth()->user()->id)->update(['password' => Hash::make($security['password'])]); 
        }    

     $post               =   (object)$request->post(); 
        $encrypted = Crypt::encryptString($post->acc_number);
        // $decrypt= Crypt::decryptString($encrypted);
        $bank_arr = array();
        $bank_arr['seller_id'] = $sellerId;
        $bank_arr['ac_no'] = $encrypted;
        $bank_arr['ac_holder'] = $post->acc_holder;
        $bank_arr['bank'] = $post->bank_name;
        $bank_arr['acc_type'] = $post->acc_type;
        $bank_arr['ifsc'] = $post->ifsc;
        $bank_arr['branch'] = $post->branch_name;
        $bank_arr['is_active'] = 1;
        $bank_arr['is_deleted'] = 0;

        if($post->bank_id >0){
            $bank_arr['updated_at'] = date('Y-m-d H:i:s');
           SellerBank::where('seller_id',$sellerId)->update($bank_arr);
          
        }else {
            $bank_arr['created_at'] = date('Y-m-d H:i:s');
        $bank_arr['updated_at'] = date('Y-m-d H:i:s');
        $insId      =   SellerBank::create($bank_arr)->id;
     
        }

            $msg                =   'Seller updated successfully!';
        }
        $this->assignStoreCategories($storeSet->categories,$storeId,$sellerId);
        if($images){ foreach($images as $k=>$image){
            $imgName            =   $k.'logo.'.$image->extension();
            $path               =   '/app/public/stores/'.$storeId;
            $destinationPath    =   storage_path($path);
            $image->move($destinationPath, $imgName);
            $imgData[$k]        =   $path.'/'.$imgName;
            $imgUpload          =   uploadFile($path,$imgName);
            Store::where('id',$storeId)->update($imgData);
        } }
         if($request->file('certificate')){ 
            $certificate              =   $request->file('certificate');
            $crtName            =   'certificate.'.$certificate->extension();
            $path               =   '/app/public/stores/'.$storeId.'/docs';
            $destinationPath    =   storage_path($path);
            $certificate->move($destinationPath, $crtName);
            $crtData['certificate']    =   $path.'/'.$crtName;
            Store::where('id',$storeId)->update($crtData);
            $imgUpload          =   uploadFile($path,$crtName);
        }
      
        $data['title']          =   'Profile';
        $data['active']         =   "";
        $sellers                =   SellerInfo::where('is_approved',1)->where('is_deleted',0);
        if(isset($post->active) &&  $post->active != ''){ 
            $attributes         =   $sellers->where('is_active',$post->active); 
            $data['active']     =   $post->active;
        }
      
       
        return redirect(route('profile'));
    }
    
    function assignStoreCategories($catIds,$storeId,$sellerId){
        StoreCategory::where('store_id',$storeId)->update(['is_deleted'=>1]);
        foreach($catIds as $cId){ 
            if(StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->count() > 0){ 
                StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->update(['is_deleted'=>0]);
            }else{ StoreCategory::create(['seller_id'=>$sellerId,'store_id'=>$storeId,'category_id'=>$cId]); }
        } return true;
    }
    
    function updateStatus(Request $request){
        $post               =   (object)$request->post(); 
        $result             =   SellerInfo::where('id',$post->id)->update([$post->field => $post->value]);
        if($post->page      !=  'new_seller'){  Store::where('seller_id',$post->id)->update([$post->field => $post->value]); }
        if($post->field     ==  'is_approved'){
            Session::flash('success', $post->msg);
        }else{
            if($result){ return ['type'=>'success','id'=>$post->id]; }else{  return ['type'=>'warning','id'=>$post->id]; } 
        }
    }
    
    
}
