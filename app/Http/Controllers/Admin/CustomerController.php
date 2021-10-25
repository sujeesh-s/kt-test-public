<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Modules;
use App\Models\UserRoles;
use App\Models\Admin;
use App\Models\UserRole;
use App\Models\SaleOrder;
use App\Models\SaleorderItems;
use App\Models\SellerTelecom;
use App\Models\SellerAddress;
use App\Models\customer\CustomerMaster;
use App\Models\customer\CustomerInfo;
use App\Models\customer\CustomerSecurity;
use App\Models\customer\CustomerTelecom;
use App\Models\customer\CustomerAddress;
use App\Rules\Name;
use Validator;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    public function index()
    {
        $data['title']              =   'Customer';
        $data['menu']               =   'Customer List';
        $data['role']               =    UserRole::where('is_deleted',NULL)->orWhere('is_deleted',0)->where('usr_role_name','Customer')->where('is_active',1)->get();
        $data['customer']           =    CustomerMaster::where('is_deleted',NULL)->orWhere('is_deleted',0)->orderBy('id','DESC')->get();
        return view('admin.customer.customer_list', $data);
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:usr_mst,username',
            'password' => 'required|min:8',
            'status'=>'required',
            'number'=>'required|min:10'
        ]);

        $input = $request->all();

        if ($validator->passes()) {

          $master =  CustomerMaster::create(['org_id' => 1,
                'username' => $request->email,
                'ref_code' => $request->ref_code,
                'is_active'=>$request->status,
                'is_deleted'=>0,
                'created_by'=>auth()->user()->id,
                'updated_by'=>auth()->user()->id,
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
           'usr_role_id' => $request->role,
           'profile_image'=>$filename,
           'is_active'=>$request->status,
           'is_deleted'=>0,
           'created_by'=>auth()->user()->id,
           'updated_by'=>auth()->user()->id,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);

           $security = CustomerSecurity::create(['org_id' => 1,
           'password_hash' => Hash::make($request->password),
           'user_id' => $masterId,
           'is_active'=>$request->status,
           'is_deleted'=>0,
           'created_by'=>auth()->user()->id,
           'updated_by'=>auth()->user()->id,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);

           $telecom_email = CustomerTelecom::create(['org_id' => 1,
           'user_id' => $masterId,
           'usr_telecom_typ_id'=>1,
           'usr_telecom_value'=>$request->email,
           'is_active'=>$request->status,
           'is_deleted'=>0,
           'created_by'=>auth()->user()->id,
           'updated_by'=>auth()->user()->id,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);
           $email_tele=$telecom_email->id;

           $telecom_ph = CustomerTelecom::create(['org_id' => 1,
           'user_id' => $masterId,
           'usr_telecom_typ_id'=>2,
           'usr_telecom_value'=>$request->number,
           'is_active'=>$request->status,
           'is_deleted'=>0,
           'created_by'=>auth()->user()->id,
           'updated_by'=>auth()->user()->id,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);
           $ph_tele=$telecom_ph->id;

           CustomerMaster::where('id',$masterId)->update([
               'email'=>$email_tele,
               'phone'=>$ph_tele
           ]);

           return response()->json(['success'=>'success']);

        }
        return response()->json(['errors'=>$validator->errors()->all()]);
    }

    public function view_customer($user_id)
    {
        $data['title']              =   'Customer Info';
        $data['menu']               =   'Customer Details';
        $data['role']               =    UserRole::where('is_deleted',NULL)->orWhere('is_deleted',0)->where('usr_role_name','Customer')->where('is_active',1)->get();
        $data['customer_mst']       =    CustomerMaster::where('is_deleted',0)->where('id',$user_id)->first();
        $data['telecom']            =    CustomerTelecom::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->get();
        $data['customer_addr']       =    CustomerAddress::where('is_deleted',0)->where('user_id',$user_id)->get();
        $data['info']               =    CustomerInfo::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->first();
        $data['wallet']             =    DB::table("usr_cust_wallet")->select(DB::raw("SUM(credit)-SUM(debit) as wallet"))->where("is_deleted",0)->where("user_id",$user_id)->first();
        $data['order']              =    SaleOrder::where('cust_id',$user_id)->get();
        $data['tot_order']          =    SaleOrder::where('cust_id',$user_id)->count();
        $data['sale_amt']           =    SaleOrder::where('order_status','delivered')->where('cust_id',$user_id)->sum('total'); 
        $data['order_cancel']       =    SaleOrder::where('cust_id',$user_id)->where('order_status','cancelled')->count();
        $data['order_refund']       =    SaleOrder::where('cust_id',$user_id)->where('order_status','refund')->count();
        // dd($data);
        return view('admin.customer.view_customer', $data);
    }

    public function update_profile(Request $request,$user_id)
    {  
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required',
            'number'=>'required',
            'status'=>'required',
            'number'=>'required|min:10'
        ]);

        if ($validator->passes()) {
            
            if($request->hasFile('profile_img'))
            {
            $file=$request->file('profile_img');
            $extention=$file->getClientOriginalExtension();
            $filename=time().'.'.$extention;
            $file->move(('storage/app/public/customer_profile/'),$filename);
            
            CustomerInfo::where('user_id',$user_id)->where('is_active',1)->update([
                'profile_image'=>$filename,
                'updated_by'=>auth()->user()->id,
                'updated_at'=>date("Y-m-d H:i:s")]);
           // dd($filename);
            }

            CustomerMaster::where('id',$user_id)->update([
                'is_active' => $request->status,
                'updated_by'=>auth()->user()->id,
                'updated_at'=>date("Y-m-d H:i:s")]);

            CustomerInfo::where('user_id',$user_id)->where('is_active',1)->update([
                'first_name' => $request->first_name,
                'last_name' =>$request->last_name,
                'updated_by'=>auth()->user()->id,
                'updated_at'=>date("Y-m-d H:i:s")]);

            CustomerTelecom::where('user_id',$user_id)->where('is_active',1)->where('usr_telecom_typ_id',1)->update([
                    'usr_telecom_value' => $request->email,
                    'updated_by'=>auth()->user()->id,
                    'updated_at'=>date("Y-m-d H:i:s")]);  
             
            $teledata=CustomerTelecom::where('user_id',$user_id)->where('is_active',1)->where('usr_telecom_typ_id',2)->first();
            if(is_null($teledata)){
                CustomerTelecom::create(['org_id' => 1,
           'user_id' => $user_id,
           'usr_telecom_typ_id'=>2,
           'usr_telecom_value'=>$request->number,
           'is_active'=>$request->status,
           'is_deleted'=>0,
           'created_by'=>auth()->user()->id,
           'updated_by'=>auth()->user()->id,
           'created_at'=>date("Y-m-d H:i:s"),
           'updated_at'=>date("Y-m-d H:i:s")]);  
            
            }
            else
            {
             CustomerTelecom::where('user_id',$user_id)->where('is_active',1)->where('usr_telecom_typ_id',2)->update([
                        'usr_telecom_typ_id'=>2,
                        'usr_telecom_value' => $request->number,
                        'is_active'=>1,
                        'updated_by'=>auth()->user()->id,
                        'updated_at'=>date("Y-m-d H:i:s")]); 
            }
             Session::flash('message', ['text'=>'Customer updated successfully.','type'=>'success']);
                        
                        return redirect(url('admin/customer/view/'.$user_id));           
        }

    }
    
     public function invoice($id)
    {
        $data['title']              =   'Invoice';
        $data['menu']               =   'Invoice';
        $data['order']              =    SaleOrder::where('id',$id)->first();
        $user_id = $data['order']->cust_id; 
        $data['user_id']               =   $user_id;
        
        $data['customer_mst']       =    CustomerMaster::where('is_deleted',0)->where('id',$user_id)->first();
        $data['telecom']            =    CustomerTelecom::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->get();
        $data['info']               =    CustomerInfo::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->first();
        $data['wallet']             =    DB::table("usr_cust_wallet")->select(DB::raw("SUM(credit)-SUM(debit) as wallet"))->where("is_deleted",0)->where("user_id",$user_id)->first();
        
       $data['seller_address']  = SellerAddress::where('seller_id',auth()->user()->id)->where('is_deleted',0)->first();
       if($data['seller_address']) {
        $data['seller_address_city']  = getCities($data['seller_address']->city_id);
       }
        $data['order_items']             = SaleorderItems::where('sales_id',$id)->get();
       
        // dd($data);
        return view('admin.customer.invoice', $data);
    }

}
