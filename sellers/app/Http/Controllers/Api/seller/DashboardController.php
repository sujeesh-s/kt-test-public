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
use App\Models\Telecom;

use App\Models\SaleOrder;
use App\Models\Auction;

use App\Rules\Name;
use Validator;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_token'=>['required']]);
        if ($validator->passes()) {
        if(!$user = validateTokenSeller($request->post('access_token'))){ return invalidToken(); }
        $seller_id = $user->seller_id;

        $gross_sales=0;
        $earn=0;
        $graph=[];
        $sales =  SaleOrder::selectRaw('SUM(total) as sales')->selectRaw('SUM(ecom_commission) as cmm')->where('seller_id',$seller_id)->where('payment_status','success')->where('delivery_status','completed')->first();
        if(!empty($sales))
        {
            $gross_sales = $sales->sales;
            $cmm         = $sales->cmm;
            $earn        = $gross_sales - $cmm;
        }

        $order =  SaleOrder::where('seller_id',$seller_id)->count();
        $auction = Auction::where('seller_id',$seller_id)->where('is_active',1)->where('is_deleted',0)->count();
       // $order_data =  SaleOrder::select('created_at')->selectRaw('SUM(total) as sales')->selectRaw('SUM(total-ecom_commission) as earn')->where('seller_id',$seller_id)->where('payment_status','success')->where('delivery_status','completed')->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->groupBy(function($item){ return $item->created_at; })->get()->toArray();

        $date = Carbon::now()->subDays(7)->startOfDay();
        $order_data =  SaleOrder::where('seller_id',$seller_id)->where('payment_status','success')->where('delivery_status','completed')->whereDate('created_at','>=',$date)->groupBy(DB::raw('DATE(created_at)'))->get();

       if(!empty($order_data)){
       foreach($order_data as $row)
       {
         $day=date('D',strtotime($row->created_at));
         $data_list['name']=$day;
         $data_list['values']=[$this->sales($seller_id,$row->created_at),$this->earn($seller_id,$row->created_at)];
        //  $data_list['sales']=$this->sales($seller_id,$row->created_at);
        //  $data_list['earn']=$this->earn($seller_id,$row->created_at);

        //$day = date('D',strtotime($row->dates));
       // $day=date('D',strtotime($row->dates));//$this->sales_and_earn($row->created_at,$seller_id);
        // $in_list[$day]['sales']=$row->sales;
        // $in_list[$day]['earn']=$row->earn;
        //$g_list=$in_list;//$this->sales_and_earn($row->dates,$seller_id);
        // $in_list['sales']=$row->sales;
        // $in_list['earn']=$row->earn;
        // $g_list[$day] = $in_list; 
        $graph[]=$data_list;
       }
       $graph_val = $graph;
        }
        

        return ['httpcode'=>200,'status'=>'success','message'=>'Dashboard','data'=>['gross_sale'=>$gross_sales,'earnings'=>$earn,'auction'=>$auction,'graph'=>$graph_val]];
    }
  
  else
  {
    return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]];
  }
 }
 function sales($seller_id,$created_at)
 {
    $sales=SaleOrder::selectRaw('SUM(total) as sales')->where('seller_id',$seller_id)->where('payment_status','success')->where('delivery_status','completed')->whereDate('created_at',$created_at)->groupBy(DB::raw('DATE(created_at)'))->first();
    return $sales->sales;
 }
 function earn($seller_id,$created_at)
 {
    $sales=SaleOrder::selectRaw('SUM(total-ecom_commission) as earn')->where('seller_id',$seller_id)->where('payment_status','success')->where('delivery_status','completed')->whereDate('created_at',$created_at)->groupBy(DB::raw('DATE(created_at)'))->first();
    return $sales->earn;
 }

  function sales_and_earn($date,$seller_id)
  {
    $order_data =  SaleOrder::selectRaw('created_at as dates')->selectRaw('SUM(total) as sales')->selectRaw('SUM(total-ecom_commission) as earn')->where('seller_id',$seller_id)->where('payment_status','success')->where('delivery_status','completed')->whereDate('created_at',$date)->groupBy(DB::raw('DATE(created_at)'))->first();
    $list['sales']=$order_data->sales;
    $list['earn']=$order_data->earn;
    $list_g[]=$list;
    return $list_g;
  } 

}
