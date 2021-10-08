<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Session;
use DB;
use App\Models\Auction;
use App\Models\AuctionHist;
use App\Models\AuctionRefundHist;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\Admin;
use App\Models\SaleOrder;
use App\Models\SettingOther;

use App\Rules\Name;
use Validator;

class AuctionUpdateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:seller');
    }
   
    
        public function auctionsCron()
        { 

        $start_date = date('Y-m-d 00:00:00');
        $end_date = date('Y-m-d 11:59:00');
        $auctions  =   Auction::where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})
        ->where("is_active",1)
        ->where("status","open")
        ->whereBetween('auct_end', [$start_date, $end_date])
        ->orderBy('id', 'DESC')
        ->get();
        $refund_charges             = SettingOther::getOtherSettings();


        if($auctions){ 
            foreach($auctions    as  $row){
              $bids =   AuctionHist::getLog($row->id); 
              
              $winner =   AuctionHist::where('auction_id',$row->id)->orderBy('bid_price', 'DESC')->first();
              foreach($bids as $bid_k=>$bid_v){
                $refund_amt = 0 ;
                if($bid_v['user_id'] !=$winner->user_id){
                  $sale_arr = array();
              $sale_arr['payment_status'] = "processing_refund";
              $sale_arr['updated_at'] = date("Y-m-d H:i:s");
                  SaleOrder::where("id",$bid_v['sale_id'])->update($sale_arr);
                  $sale_info = SaleOrder::where("id",$bid_v['sale_id'])->first();
                
                  $refund_arr = array();
                  $refund_arr['auction_id'] =$row->id;
                  $refund_arr['user_id'] =$bid_v['user_id'];
                  $refund_arr['sale_id'] =$bid_v['sale_id'];
                  $refund_arr['paid_amount'] =$sale_info->g_total;

                //   $refund_amt = (($bid_v['bid_price'] * $refund_charges['refund_deduction'])/100);
                //   $refund_amt = $bid_v['bid_price'] - round($refund_amt,2);
                 $refund_amt = ($sale_info->g_total - $refund_charges['bid_charge']);
                  

                  $refund_arr['refund_amount'] =$refund_amt;
                //   $refund_arr['refund_percentage'] = $refund_charges['refund_deduction'];
                     $refund_arr['refund_charge'] = $refund_charges['bid_charge'];
                  $refund_arr['status'] = "pending";
                  AuctionRefundHist::create($refund_arr);
                }
                
              }
                $auct_arr = array();
                $auct_arr['bid_allocated_to'] = $winner->user_id;
                $auct_arr['sale_id'] = $winner->sale_id;
                $auct_arr['status'] = "processing";
                $auct_arr['updated_at'] = date("Y-m-d H:i:s");
              
                $from    = 1; 
                $utype   = 1;
                $to      = 1; 
                $ntype= 'auction';
                $title = 'Auction';
                $desc = 'The winner of '.$row->auction_code.' auction is'.$winner->user_id;
                $refId = $row->id;
                $reflink = 'auctions/log';
                $notify = 'admin';
                addNotification($from,$utype,$to,$ntype,$title,$desc,$refId,$reflink,$notify);
                
                Auction::where("id",$row->id)->update($auct_arr);
            }
          }
        
      
        }

   
}
