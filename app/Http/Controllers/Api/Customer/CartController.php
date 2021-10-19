<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Modules;
use App\Models\UserRoles;
use App\Models\Admin;
use App\Models\Auction;
use App\Models\AuctionHist;
use App\Models\AssociatProduct;
use App\Models\UserRole;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\CartHistory;
use App\Models\Coupon;
use App\Models\CouponHist;
use App\Models\Subcategory;
use App\Models\Store;
use App\Models\SellerReview;
use App\Models\SaleOrder;
use App\Models\SaleorderItems;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\PrdAdminImage;
use App\Models\ProductDaily;
use App\Models\PrdAssignedTag;
use App\Models\PrdReview;
use App\Models\PrdShock_Sale;
use App\Models\PrdPrice;
use App\Models\Reward;
use App\Models\RewardType;
use App\Models\RelatedProduct;
use App\Models\AssignedAttribute;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $lang=$request->lang_id;
        $validator=  Validator::make($request->all(),[
            'access_token' => ['required']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
       $cart = Cart::join('usr_cart_item','usr_cart.id','=','usr_cart_item.cart_id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->get();
        if(count($cart)>0)
        {
            $cart_count = count($cart);
            $wallet = $this->wallet_balance($user_id);
            
            //Seller cart products
            $cart_bySeller = Cart::join('usr_cart_item','usr_cart.id','=','usr_cart_item.cart_id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->pluck('product_id');
            $seller_products = Product::whereIn('id',$cart_bySeller)->where('is_active',1)->where('is_deleted',0)->groupBy('seller_id')->get();
            $seller_product_list=[];
            $products=[];
            $rewards=[];
            foreach($seller_products as $s_row)
            {
                $store_active = Store::where('service_status',1)->where('is_active',1)->where('seller_id',$s_row->seller_id)->first();
                    if($store_active)
                    {
                $products_seller['seller']                = array('seller_id'=>$s_row->seller_id,'seller'=>$s_row->Store($s_row->seller_id)->store_name);
                $products_seller['seller']['products']    = $this->get_cart_seller_products($s_row->seller_id,$user_id,$lang);
                $products_seller['seller']['coupon']      = $this->get_cart_seller_coupon($s_row->seller_id,$user_id,$lang);
                $seller_product_list[]                    = $products_seller;
                    }
            }
            //end seller cart products
            
            foreach($cart as $rows)
            {
                $products[] = $this->get_cart_products($rows->product_id,$rows->cart_id,$rows->quantity,$lang);
            }   

            $filter = array_filter($products);
            $tot_tax =0;
            $total_cost=0;
            $grand_tot=0;
            if(count($filter)>0)
            {
                foreach($filter as $value)
                {
                    $tot_tax += $value['total_tax_value'];
                    if($value['total_discount_price']==0)
                    {
                     $total_cost +=(int)$value['total_actual_price'];    
                    }
                    else
                    {
                      $total_cost +=(int)$value['total_discount_price'];    
                    }
                }

                $grand_tot = $tot_tax+$total_cost;
            }
            $sale_before  =  SaleOrder::where('cust_id',$user_id)->count();
            if($sale_before<1)
            {
                $reward = Reward::where('is_active',1)->where('is_deleted',0)->where('ord_min_amount', '<=', $total_cost)->where('ord_type','discount')->first();
                if($reward){
                $rewards= ['reward_id'=>$reward->id,'reward_amt'=>$reward->ord_amount];
                }
            }
            return ['httpcode'=>200,'status'=>'success','message'=>'Success','data'=>['cart_count'=>$cart_count,'product'=>$seller_product_list,'currency'=>getCurrency()->name,'wallet_balance'=>$wallet,'reward'=>$rewards,'total_cost'=>$total_cost,'total_tax'=>number_format($tot_tax,2),'grand_total'=>$grand_tot]];       
         }
         else
         {
            return ['httpcode'=>404,'status'=>'error','message'=>'Cart is empty','data'=>['errors'=>'Cart is empty']];
         }  
    }

    }
    
    //Total cart price
    public function cart_total(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $lang=$request->lang_id;
        $validator=  Validator::make($request->all(),[
            'access_token' => ['required']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
       $cart = Cart::join('usr_cart_item','usr_cart.id','=','usr_cart_item.cart_id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->get();
        if(count($cart)>0)
        {                
            foreach($cart as $rows)
            {
                $products[] = $this->get_cart_products($rows->product_id,$rows->cart_id,$rows->quantity,$lang);
            }   

            $filter = array_filter($products);
            $tot_tax =0;
            $total_cost=0;
            $grand_tot=0;
            if(count($filter)>0)
            {
                foreach($filter as $value)
                {
                    $tot_tax += $value['total_tax_value'];
                    if($value['total_discount_price']==0)
                    {
                     $total_cost +=(int)$value['total_actual_price'];    
                    }
                    else
                    {
                      $total_cost +=(int)$value['total_discount_price'];    
                    }
                }

                $grand_tot = number_format($tot_tax+$total_cost,2);
            }
            return ['httpcode'=>200,'status'=>'success','message'=>'Success','data'=>['currency'=>getCurrency()->name,'total_tax'=>number_format($tot_tax,2),'total_cost'=>number_format($total_cost,2),'grand_total'=>$grand_tot]];       
         }
         else
         {
            return ['httpcode'=>404,'status'=>'error','message'=>'Cart is empty','data'=>['errors'=>'Cart is empty']];
         }  
    }

    }

    public function delete_cart(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $validator=  Validator::make($request->all(),[
            'cart_id' => ['required']
        ]);
        $input = $request->all();
        $user_id = $user['user_id'];

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
        foreach($input['cart_id'] as $cart)
        {
            $cart_product = CartItem::where('cart_id',$cart)->first();

            Cart::where('id',$cart)->update([
            'is_active'=>0,
            'is_deleted'=>1,
            'updated_by'=>$user_id,
            'updated_at'=>date("Y-m-d H:i:s")]);

            CartItem::where('cart_id',$cart)->update([
            'is_active'=>0,
            'is_deleted'=>1,
            'updated_by'=>$user_id,
            'updated_at'=>date("Y-m-d H:i:s")]);

            CartHistory::create(['org_id' => 1,
                'user_id' => $user_id,
                'product_id' => $cart_product->product_id,
                'quantity'  => 0,
                'action'=>'delete',
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        }

    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully Deleted','data'=>['response'=>'Successfully Deleted']];
    }

    }

    //Apply Coupon
    public function apply_coupon(Request $request)
    {   
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];

        $lang=$request->lang_id;

        $validator=  Validator::make($request->all(),[
            'coupon_code' =>['required','string','min:6','max:6']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }

     else
     { 
                   

        $query = Coupon::query();
        $avail = Coupon::where('ofr_code',$input['coupon_code'])->first();

        $category= $avail->category_id;
       
        if($category>0)
        {
            $cat_id=$category;
        }
        else
        {
            $cat_id='';
        }
        $subcategory = $avail->subcategory_id;
        if($subcategory!=0)
        {
            $sub_id =$subcategory;
        }
        else
        {
            $sub_id = '';
        }
        $seller_id =$avail->seller_id;
        if($seller_id!=0)
        {
            $seller=$seller_id;
        }
        else
        {
            $seller='';
        }

        $cart = Product::join('usr_cart_item','usr_cart_item.product_id','=','prd_products.id')
                        ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)
                        ->when($cat_id,function ($q,$cat_id) {
                            $q->where('prd_products.category_id', $cat_id);
                        })
                        ->when($sub_id,function ($q,$sub_id) {
                            $q->where('prd_products.sub_category_id', $sub_id);
                        })
                        ->when($seller,function ($q,$seller) {
                            $q->where('prd_products.seller_id', $seller);
                        })
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->get();
           $no_prds = count($cart); 


                         
        if(!$avail)

        {
        return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'Not found']];
        }
        else if($avail->validity_type == "range")
        {   $current_date=date('Y-m-d');
            $range = $query->whereDate('valid_from','<=',$current_date)->whereDate('valid_to','>=',$current_date)->where('ofr_code',$input['coupon_code'])->first();
           if($no_prds>0){
                foreach($cart as $rows)
           
            {
                $products[] = $this->get_cart_ofr_products($rows->product_id,$rows->cart_id,$rows->quantity,$lang,$cat_id,$sub_id,$seller);
            }   

            $filter = array_filter($products);
            $tot_tax =0;
            $total_cost =0;
            if(count($filter)>0)
            {
                foreach($filter as $value)
                {
                    $tot_tax += $value['total_tax_value'];
                    if($value['total_discount_price']==0)
                    {
                     $total_cost +=(int)$value['total_discount_price'];    
                    }
                    else
                    {
                      $total_cost +=(int)$value['total_actual_price'];    
                    }
                }
            }
            $sale =SaleOrder::where('order_status','delivered')->where('cust_id',$user_id)
             ->when($seller,function ($q,$seller) {
                            $q->where('seller_id', $seller);})
             ->count();
            $sale_amt =SaleOrder::where('order_status','delivered')->where('cust_id',$user_id)
             ->when($seller,function ($q,$seller) {
                            $q->where('seller_id', $seller);
                            })
             ->sum('total'); 

             // echo $sale_amt;
             // die;
            if($range->purchase_type == "number" && $range->ofr_min_amount >=$total_cost && $sale >= $range->purchase_number)
            {
               if($range->ofr_value_type=="percentage")
               {
                    $discount = $total_cost * ($range->ofr_value/100);
                    $grand = $total_cost - $discount;
                    $grand_tot = number_format($grand + $tot_tax,2);

               }
               else
               {
                    $discount = $range->ofr_value;
                    $grand = $total_cost - $discount;
                    $grand_tot = number_format($grand + $tot_tax,2);
               }
                //Coupon details
                    $coupon_details['coupon_id']=$range->id;
                    $coupon_details['title']=$this->get_content($range->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($range->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$range->ofr_type;
                  if($range->ofr_type=="cashback")
                  {
                    
                    $tot_grand = number_format($total_cost+$tot_tax,2);
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'cashback'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$tot_grand,'coupon_details'=>$coupon_details]];
                  }
                  else if($range->ofr_type=="discount")
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
                  else
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>0,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
                 
            }
            else if($range->purchase_type == "amount" && $range->ofr_min_amount >=$total_cost && $sale_amt >= $range->purchase_amount)
            {
                    if($range->ofr_type=="cashback")
                  {
                    $tot_grand = number_format($total_cost+$tot_tax,2);
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'cashback'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$tot_grand,'coupon_details'=>$coupon_details]];
                  }
                  else if($range->ofr_type=="discount")
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
                  else
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>0,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
            }
            else{ return ['httpcode'=>404,'status'=>'error','message'=>'Not applicable','data'=>['errors'=>'Coupon is not applicable']];}

          }
          //if no prd in cart
          else
          {
             return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'Not found']];
          }
        }
        elseif($avail->validity_type == "days")
        {
            $created_date=$avail->created_at;
            $valid_date=$avail->created_at->addDays($avail->valid_days);
            $current_date=Carbon::now();
            //$diff_in_days = $current_date->diffInDays($valid_date);
            $validity =$valid_date->gte($current_date);
           

            $range = $query->where('ofr_code',$input['coupon_code'])->first();
            
           if($no_prds>0 && $validity==1){
                foreach($cart as $rows)
           
            {
                $products[] = $this->get_cart_ofr_products($rows->product_id,$rows->cart_id,$rows->quantity,$lang,$cat_id,$sub_id,$seller);
            }   

            $filter = array_filter($products);
            $tot_tax =0;
            $total_cost =0;
            if(count($filter)>0)
            {
                foreach($filter as $value)
                {
                    $tot_tax += $value['total_tax_value'];
                    if($value['total_discount_price']==0)
                    {
                     $total_cost +=(int)$value['total_discount_price'];    
                    }
                    else
                    {
                      $total_cost +=(int)$value['total_actual_price'];    
                    }
                }
            }
            $sale =SaleOrder::where('order_status','delivered')->where('cust_id',$user_id)
             ->when($seller,function ($q,$seller) {
                            $q->where('seller_id', $seller);})
             ->count();
            $sale_amt =SaleOrder::where('order_status','delivered')->where('cust_id',$user_id)
             ->when($seller,function ($q,$seller) {
                            $q->where('seller_id', $seller);
                            })
             ->sum('total'); 

             // echo $sale_amt;
             // die;
            if($range->purchase_type == "number" && $range->ofr_min_amount >=$total_cost && $sale >= $range->purchase_number)
            {
               if($range->ofr_value_type=="percentage")
               {
                    $discount = $total_cost * ($range->ofr_value/100);
                    $grand = $total_cost - $discount;
                    $grand_tot = number_format($grand + $tot_tax,2);

               }
               else
               {
                    $discount = $range->ofr_value;
                    $grand = $total_cost - $discount;
                    $grand_tot = number_format($grand + $tot_tax,2);
               }
                //Coupon details
                    $coupon_details['coupon_id']=$range->id;
                    $coupon_details['title']=$this->get_content($range->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($range->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$range->ofr_type;
                  if($range->ofr_type=="cashback")
                  {
                    
                    $tot_grand = number_format($total_cost+$tot_tax,2);
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'cashback'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$tot_grand,'coupon_details'=>$coupon_details]];
                  }
                  else if($range->ofr_type=="discount")
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
                  else
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
                 
            }
            else if($range->purchase_type == "amount" && $range->ofr_min_amount >=$total_cost && $sale_amt >= $range->purchase_amount)
            {
                    if($range->ofr_type=="cashback")
                  {
                    $tot_grand = number_format($total_cost+$tot_tax,2);
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'cashback'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$tot_grand,'coupon_details'=>$coupon_details]];
                  }
                  else if($range->ofr_type=="discount")
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>$discount,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
                  else
                  {
                    return ['httpcode'=>200,'status'=>'success','message'=>'Successfully applied','data'=>['currency'=>getCurrency()->name,'total_cost'=>$total_cost,'discount'=>0,'tax_total'=>$tot_tax,'grand_total'=>$grand_tot,'coupon_details'=>$coupon_details]];
                  }
            }
            else{ return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'Not found']];}

          }
          //if no prd in cart
          else
          {
             return ['httpcode'=>404,'status'=>'error','message'=>'Not applicable','data'=>['errors'=>'Coupon is not applicable']];
          }
        }
                        

     }
    }
    
    function get_cart_seller_coupon($seller_id,$user_id,$lang)
    {
        $cart =Cart::join('usr_cart_item','usr_cart.id','=','usr_cart_item.cart_id')->join('prd_products','usr_cart_item.product_id','=','prd_products.id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->where('prd_products.is_active',1)
                        ->where('prd_products.is_deleted',0)
                        ->pluck('usr_cart_item.product_id');
                        
                        
                        
        $sale =SaleOrder::where('order_status','delivered')->where('cust_id',$user_id)
             ->when($seller_id,function ($q,$seller_id) {
                            $q->where('seller_id', $seller_id);})
             ->count();
        $sale_amt =SaleOrder::where('order_status','pending')->where('cust_id',$user_id)
             ->when($seller_id,function ($q,$seller_id) {
                            $q->where('seller_id', $seller_id);
                            })
             ->sum('total');                 
        
        $current_date=date('Y-m-d');
      $coupon = Coupon::where('is_active',1)->where('is_deleted',0)->where('seller_id',$seller_id)->get();
      
      $c_list =[];
      $coupon_details=[];
      foreach($coupon as $row)
      { 
          $querys = Product::whereIn('id',$cart);
        if($row->category_id!=0)
        {
          $querys = $querys->whereIn('category_id', [$row->category_id]);
        }
        if($row->subcategory_id!=0)
        {
            $querys = $querys->whereIn('sub_category_id', [$row->subcategory_id]);
        }
        
        $product = $querys->where('seller_id',$seller_id)->get();
    //     return $sale_amt;
    //   die;
        if(count($product)>0){
        if($row->validity_type=="range")
        {

          $range_coupon = Coupon::where('id',$row->id)->whereDate('valid_from','<=',$current_date)->whereDate('valid_to','>=',$current_date)->first();
          if($range_coupon){
              if($row->purchase_type=='number')
               {
                   if($sale>=$row->purchase_number)
                   {
                   
              if($row->ofr_value_type=="percentage")
               {
                    $discount = $row->ofr_value." "."%";

               }
               else
               {
                    $discount = $row->ofr_value." "."RM";
               }
                    $coupon_details['coupon_id']=$row->id;
                    $coupon_details['title']=$this->get_content($row->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($row->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$row->ofr_type;
                    $coupon_details['coupon_code']=$row->ofr_code;
                    $coupon_details['minimum_purchase']=$row->ofr_min_amount;
                    $coupon_details['offer_type']=$row->ofr_type;
                    $coupon_details['offer_value']=$row->ofr_value;
                    $coupon_details['offer_value_in']=$row->ofr_value_type;

               
                $coupon_details['purchase_type']=$row->purchase_type;
                $coupon_details['previous_order_count']=$row->purchase_number;
                $coupon_details['previous_order_amount']="";
               } 
               }
               else
               {
                   if($sale_amt>=$row->purchase_amount)
                   {
                   
              if($row->ofr_value_type=="percentage")
               {
                    $discount = $row->ofr_value." "."%";

               }
               else
               {
                    $discount = $row->ofr_value." "."RM";
               }
                    $coupon_details['coupon_id']=$row->id;
                    $coupon_details['title']=$this->get_content($row->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($row->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$row->ofr_type;
                    $coupon_details['coupon_code']=$row->ofr_code;
                    $coupon_details['minimum_purchase']=$row->ofr_min_amount;
                    $coupon_details['offer_type']=$row->ofr_type;
                    $coupon_details['offer_value']=$row->ofr_value;
                    $coupon_details['offer_value_in']=$row->ofr_value_type;

               
                $coupon_details['purchase_type']=$row->purchase_type;
                $coupon_details['previous_order_count']=$row->purchase_number;
                $coupon_details['previous_order_amount']="";
               } 
               }
                   
            }

          }
           if($row->validity_type=="days")
          {
            
            $created_date=$row->created_at;
            $valid_date=$row->created_at->addDays($row->valid_days);
            $current_dates=Carbon::now();
            //$diff_in_days = $current_date->diffInDays($valid_date);
            $validity =$valid_date->gte($current_dates);
          if($validity==1){  
            if($row->purchase_type=='amount')
               {
                   if($sale_amt>=$row->purchase_amount)
                   {  
           if($row->ofr_value_type=="percentage")
               {
                    $discount = $row->ofr_value." "."%";

               }
               else
               {
                    $discount = $row->ofr_value." "."RM";
               }
                    $coupon_details['coupon_id']=$row->id;
                    $coupon_details['title']=$this->get_content($row->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($row->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$row->ofr_type;
                    $coupon_details['coupon_code']=$row->ofr_code;
                    $coupon_details['minimum_purchase']=$row->ofr_min_amount;
                    $coupon_details['offer_type']=$row->ofr_type;
                    $coupon_details['offer_value_in']=$row->ofr_value_type;
                    $coupon_details['offer_value']=$row->ofr_value;

               
                $coupon_details['purchase_type']=$row->purchase_type;
                $coupon_details['previous_order_count']=$row->purchase_number;
                $coupon_details['previous_order_amount']="";
               } 
                 
               } 
               
               else
               {
                   if($sale>=$row->purchase_number)
                   {  
           if($row->ofr_value_type=="percentage")
               {
                    $discount = $row->ofr_value." "."%";

               }
               else
               {
                    $discount = $row->ofr_value." "."RM";
               }
                    $coupon_details['coupon_id']=$row->id;
                    $coupon_details['title']=$this->get_content($row->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($row->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$row->ofr_type;
                    $coupon_details['coupon_code']=$row->ofr_code;
                    $coupon_details['minimum_purchase']=$row->ofr_min_amount;
                    $coupon_details['offer_type']=$row->ofr_type;
                    $coupon_details['offer_value']=$row->ofr_value;
                    $coupon_details['offer_value_in']=$row->ofr_value_type;

               
                $coupon_details['purchase_type']=$row->purchase_type;
                $coupon_details['previous_order_count']=$row->purchase_number;
                $coupon_details['previous_order_amount']="";
               } 
               }
                    
                    }

          }
      }
                   if($coupon_details)
                   {
                    $c_list[]  = $coupon_details;
                   }

          
        }
        
        return $c_list;
    }

   function get_cart_products($prd_id,$cart_id,$qty,$lang){
        $data     =   [];
        
        $prod_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('id',$prd_id)->first();
            if($prod_data)   { 
                $store_active = Store::where('service_status',1)->where('is_active',1)->where('seller_id',$prod_data->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['cart_id']=$cart_id;   
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['quantity']=$qty;
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    
                    $prd_list['currency']=getCurrency()->name;
                    if($prod_data->brand_id)
                    {
                        $prd_list['brand_id']=$prod_data->brand_id;
                        $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    
                    $prd_list['seller_id']=$prod_data->seller_id;
                    $prd_list['seller']=$prod_data->Store($prod_data->seller_id)->store_name;
                   if($prod_data->prdPrice){ $actual_price =$prod_data->prdPrice->price; }else { $actual_price =0; } 
                    $prd_list['unit_actual_price']=number_format($actual_price,2);
                    $tot_actual=(int)$actual_price*(int)$qty;
                    $prd_list['total_actual_price']=$tot_actual;
                    $tax_amt=$prod_data->getTaxValue($prod_data->tax_id);
                    $total_tax_amount = $tot_actual * ($tax_amt/100);
                    $prd_list['total_tax_value']=number_format($total_tax_amount,2);
                     
                    
                    //Available offers for this product
            $current_date=Carbon::now();
           
           

            $shock = PrdShock_Sale::join('prd_shock_sale_products','prd_shock_sale.id','=','prd_shock_sale_products.shock_sale_id')
            ->whereRaw("find_in_set(".$prd_id.",prd_shock_sale_products.prd_id)")
            ->where('prd_shock_sale.is_active',1)->where('prd_shock_sale.is_deleted',0)->whereDate('prd_shock_sale.start_time','<=',$current_date)->whereDate('prd_shock_sale.end_time','>=',$current_date)
            ->where('prd_shock_sale_products.is_active',1)->where('prd_shock_sale_products.is_deleted',0)->first();

           
             if($shock)
            {
                 $prd_list['offer_available']= 1;
                 $prd_list['offer_name']= 'Shocking Sale'; 
                if($shock->discount_type=="amount")
                    {
                        $prd_list['offer']=getCurrency()->name." ".$shock->discount_value." Off";
                        $discount_value = $shock->discount_value;
                        $unit_price = (int)$actual_price-(int)$discount_value;
                        $prd_list['unit_discount_price']= $unit_price;
                        $prd_list['total_discount_price']=$unit_price * $qty;
                       

                    }
                    else
                    {
                        $prd_list['offer']=$shock->discount_value."% Off";
                        $per=$shock->discount_value/100;
                        $per_value = (float)$actual_price*(float)$per;
                        $discount=(float)$actual_price-(float)$per_value;
                        $round= number_format($discount, 2);
                        $prd_list['unit_discount_price']=$discount;
                        $prd_list['total_discount_price']=(int)$discount * (int)$qty;
                    }
            }
            
            else
            {
                $prd_list['offer_available']= 0;
                $prd_list['offer_name']= ''; 
                $sale_price =$this->get_sale_price($prd_id);
                if($sale_price!='')
                {
                $prd_list['unit_discount_price']=$sale_price;
                $tot= $sale_price * $qty;
                $prd_list['total_discount_price']=number_format($tot,2);
                }
                else
                {
                    $prd_list['unit_discount_price']=0;
                    $prd_list['total_discount_price']=0;
                }
            }

            $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
            $prd_list['image']=$this->get_product_image($prod_data->id);
                    $data             =   $prd_list;
            }//Active store
             }
            // else{ $data     =   []; } 
             return $data;
        
    }
    
    //cart product according to seller
    function get_cart_seller_products($seller_id,$user_id,$lang){
        $data     =   [];
        
        
        $prod_data1       =   Product::join('usr_cart_item','prd_products.id','=','usr_cart_item.product_id')
                            ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                            ->where('usr_cart.user_id',$user_id)    
                            ->where('usr_cart.is_active',1)
                            ->where('usr_cart.is_deleted',0)
                            ->where('usr_cart_item.is_active',1)
                            ->where('usr_cart_item.is_deleted',0)
                            ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)
                            ->where('prd_products.seller_id',$seller_id)
                            ->get();
            if($prod_data1)   { 
                
                foreach($prod_data1 as $prod_data){
                    $store_active = Store::where('service_status',1)->where('is_active',1)->where('seller_id',$prod_data->seller_id)->first();
                    if($store_active)
                    {
                    $qty=$prod_data->quantity;
                    $prd_list['cart_id']=$prod_data->cart_id;   
                    $prd_list['product_id']=$prod_data->product_id;
                    if($prod_data->product_type==1){
                     $prd_list['product_type'] ='simple';   
                    }
                    else
                    {
                        $prd_list['product_type'] ='config'; 
                    }
                    if($prod_data->product_type==1){
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['image']=$this->get_product_image($prod_data->product_id);
                    }
                    else
                    {
                        $associate= AssociatProduct::where('ass_prd_id',$prod_data->product_id)->first();
                        $attr_data =   AssignedAttribute::where('is_deleted',0)->where('prd_id',$prod_data->product_id)->get();
                         //print_r($attr_data);die;
                         $i=1;
                        foreach($attr_data as $attr)
                        {
                        $prd_listsatr=$attr->attr_value;
                        $prd_list['attr_name'.$i]=$prd_listsatr;
                        $i++;
                        }
                        
                        $prd_list['product_name']=$this->get_content($associate->product->name_cnt_id,$lang); 
                        $prd_list['image']=$this->get_product_image($associate->product->id);
                    }
                    $prd_list['quantity']=$prod_data->quantity;
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    $prd_list['seller_id']=$prod_data->seller_id;
                   // $prd_list['brand_id']=$prod_data->brand_id;
                    $prd_list['currency']=getCurrency()->name;
                    if($prod_data->brand_id)
                    {
                        $prd_list['brand_id']=$prod_data->brand_id;
                        $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    //$prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    $actual_price =$this->get_actual_price($prod_data->product_id);
                    $prd_list['unit_actual_price']=$actual_price;
                    $tot_actual=(int) $actual_price * (int) $qty;
                    $prd_list['total_actual_price']=(int)$tot_actual;
                    $tax_amt=$prod_data->getTaxValue($prod_data->tax_id);
                    $total_tax_amount = $tot_actual * ($tax_amt/100);
                    $prd_list['total_tax_value']=number_format($total_tax_amount,2);
                     
                    
                    //Available offers for this product
            $current_date=Carbon::now();
           
           

            $shock = PrdShock_Sale::join('prd_shock_sale_products','prd_shock_sale.id','=','prd_shock_sale_products.shock_sale_id')
            ->whereRaw("find_in_set(".$prod_data->product_id.",prd_shock_sale_products.prd_id)")
            ->where('prd_shock_sale.is_active',1)->where('prd_shock_sale.is_deleted',0)->whereDate('prd_shock_sale.start_time','<=',$current_date)->whereDate('prd_shock_sale.end_time','>=',$current_date)
            ->where('prd_shock_sale_products.is_active',1)->where('prd_shock_sale_products.is_deleted',0)->first();

           
             if($shock)
            {
                 $prd_list['offer_available']= 1;
                 $prd_list['offer_name']= 'Shocking Sale'; 
                if($shock->discount_type=="amount")
                    {
                        $prd_list['offer']=getCurrency()->name." ".$shock->discount_value." Off";
                        $discount_value = $shock->discount_value;
                        $unit_price = (int)$actual_price-(int)$discount_value;
                        $prd_list['unit_discount_price']= $unit_price;
                        $prd_list['total_discount_price']=$unit_price * $qty;
                       

                    }
                    else
                    {
                        $prd_list['offer']=$shock->discount_value."% Off";
                        $per=$shock->discount_value/100;
                        $per_value = (float)$actual_price*(float)$per;
                        $discount=(float)$actual_price-(float)$per_value;
                        $round= number_format($discount, 2);
                        $prd_list['unit_discount_price']=$discount;
                        $prd_list['total_discount_price']=(int)$discount * (int)$qty;
                    }
            }
            
            else
            {
                $prd_list['offer_available']= 0;
                $prd_list['offer_name']= ''; 
                $sale_price =$this->get_sale_price($prod_data->product_id);
                if($sale_price!='')
                {
                $prd_list['unit_discount_price']=(int)$sale_price;
                $tot= $sale_price * $qty;
                $prd_list['total_discount_price']=(int)$tot;
                }
                else
                {
                    $prd_list['unit_discount_price']=0;
                    $prd_list['total_discount_price']=0;
                }
            }

            $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
           
                    $data[]             =  $prd_list;
                    }//Active store
             }
             
        }//end if
            // else{ $data     =   []; } 
             return $data;
        
    }


/***************get coupon applied products****/
function get_cart_ofr_products($prd_id,$cart_id,$qty,$lang,$cat_id,$sub_id,$seller){
        $data     =   [];
        
        $prod_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('id',$prd_id)
        ->when($cat_id,function ($q,$cat_id) {
                            $q->where('category_id', $cat_id);
                        })
        ->when($sub_id,function ($q,$sub_id) {
                            $q->where('sub_category_id', $sub_id);
                        })
        ->when($seller,function ($q,$seller) {
                            $q->where('seller_id', $seller);
                        })
        ->first();
            if($prod_data)   { 
                    $prd_list['cart_id']=$cart_id;   
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['quantity']=$qty;
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    if($prod_data->brand_id)
                    {
                        $prd_list['brand_id']=$prod_data->brand_id;
                        $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    
                    $actual_price =number_format($prod_data->prdPrice->price,2);
                    $prd_list['unit_actual_price']=number_format($prod_data->prdPrice->price,2);
                    $tot_actual=$actual_price*$qty;
                    $prd_list['total_actual_price']=number_format($tot_actual,2);
                    $tax_amt=$prod_data->getTaxValue($prod_data->tax_id);
                    $total_tax_amount = $tot_actual * ($tax_amt/100);
                    $prd_list['total_tax_value']=number_format($total_tax_amount,2);
                     
                    
             
                $prd_list['offer_available']= 0;
                $prd_list['offer_name']= ''; 
                $sale_price =$this->get_sale_price($prd_id);
                if($sale_price!='')
                {
                $prd_list['unit_discount_price']=$sale_price;
                $tot= $sale_price * $qty;
                $prd_list['total_discount_price']=number_format($tot,2);
                }
                else
                {
                    $prd_list['unit_discount_price']=0;
                    $prd_list['total_discount_price']=0;
                }
            

            $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
            $prd_list['image']=$this->get_product_image($prod_data->id);
                    $data             =   $prd_list;
             }
            // else{ $data     =   []; } 
             return $data;
        
    }


  /*************GET VALUES **************/
    
    //wallet balance
  function wallet_balance($user_id)
  {
    $wallet=DB::table("usr_cust_wallet")->select(DB::raw("SUM(credit)-SUM(debit) as wallet"))->where('user_id',$user_id)->where('is_deleted',0)->first();
    if($wallet->wallet > 0)
    {
        return $wallet->wallet;
    }
    else
    {
        return false;
    }
  }

    //Product sale price
    public function get_sale_price($field_id){ 

     
       $current_date=Carbon::now();
       $rows = PrdPrice::where('is_deleted',0)->where('prd_id',$field_id)->whereDate('sale_end_date','>=',$current_date)->first();        
        if($rows){ 
        $return_val = $rows->sale_price;
        return $return_val;
        }
        else
            { $return_val='';
                return $return_val; }
        }
        
        //Product ACTUAL price
    public function get_actual_price($field_id){ 

     
       //$current_date=Carbon::now();
       $rows = PrdPrice::where('is_deleted',0)->where('prd_id',$field_id)->first();        
        if($rows){ 
        $return_val = $rows->price;
        return $return_val;
        }
        else
            { $return_val='';
                return $return_val; }
        }

         function get_product_image($prd_id){
        $data     =   [];
        
        //$admin_pro=Product::where('id',$prd_id)->first();
        
        
        $product_seller       =   ProductImage::where('prd_id',$prd_id)->where('is_deleted',0)->get();
        $product_admin       =   PrdAdminImage::where('prd_id',$prd_id)->where('is_deleted',0)->get();
        if(!empty($product_seller))
        {
            foreach($product_seller as $k=>$row){ 
                if($row->image)
                {
                $val['image']       =   config('app.storage_url').$row->image;
                }
                if($row->thumbnail)
                {
                $val['thumbnail']   =   config('app.storage_url').$row->thumbnail;
                }
                $data[]             =   $val;
            }
        }
        else if(!empty($product_admin))
        {
            foreach($product_admin as $k=>$row){ 
                if($row->image)
                {
                $val['image']       =   config('app.storage_url').$row->image;
                }
                if($row->thumbnail)
                {
                $val['thumbnail']   =   config('app.storage_url').$row->thumbnail;
                }
                $data[]             =   $val;
            }
        } 
        
        else{ $data     =   []; } return $data;
        
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
