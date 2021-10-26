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
use App\Models\Banner;
use App\Models\Brand;
use App\Models\UserRole;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\CartHistory;
use App\Models\Coupon;
use App\Models\CouponHist;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerAddressType;
use App\Models\CustomerWallet_Model;
use App\Models\Subcategory;
use App\Models\Store;
use App\Models\SellerReview;
use App\Models\SaleOrder;
use App\Models\SaleorderItems;
use App\Models\SalesOrderAddress;
use App\Models\SalesOrderPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductDaily;
use App\Models\PrdAssignedTag;
use App\Models\PrdReview;
use App\Models\PrdShock_Sale;
use App\Models\PrdPrice;
use App\Models\PrdStock;
use App\Models\ParentSale;
use App\Models\RelatedProduct;
use App\Models\Reward;
use App\Models\RewardType;
use App\Models\AssignedAttribute;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;

class OrderController extends Controller
{
    public function placeorder(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $user_email = $user['email'];
        $lang=$request->lang_id;
        // dd($request->all());
        // //print_r($request->all());
        // die;
        
        $validator=  Validator::make($request->all(),[
            'access_token'          => ['required'],
            'seller_array'          => ['required','array'],
            'e_money_amt'           => ['required'],
            'is_platform_coupon'    => ['nullable','boolean'],
            'platform_coupon_id'    => ['nullable','numeric'],
            'platform_discount_type'=> ['nullable','string',Rule::in(['discount', 'cashback'])],
            'total_amt'             => ['required','numeric'],
            'discount_amt'          => ['required','numeric'],
            'is_reward'             => ['nullable','numeric'],
            'reward_amt'            => ['nullable'],
            'reward_id'             => ['nullable','numeric'],
            'payment_type'          => ['required'],
            'address_id'            => ['required','numeric']

        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
    //   $sale_items = $this->insert_seller_products(12,48,$user_id,$lang);
    //   return $sale_items;
    //   die;
        $carts = Product::join('usr_cart_item','prd_products.id','=','usr_cart_item.product_id')
                        ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->get();  
       $cart = Product::join('usr_cart_item','prd_products.id','=','usr_cart_item.product_id')
                        ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->distinct()
                        ->get('seller_id');                           


        if(count($cart)>0)
        {    
            //ADDRESS
          $addr_list =  CustomerAddress::where('id',$input['address_id'])->first();
           foreach($carts as $rows)
            {
                $products[] = $this->get_cart_products($rows->product_id,$rows->cart_id,$rows->quantity,$lang);
            }  
            $filter = array_filter($products);
            $tot_tax =0;
            $total_cost=0;
            $total_discount=0;
            if(count($filter)>0)
            {
                foreach($filter as $value)
                {
                    $tot_tax += $value['total_tax_value'];
                    if($value['total_discount_price']==0)
                    {
                     $total_cost +=(int)$value['total_actual_price']; 
                     $total_discount +=(int)$value['discount_values'];   
                    }
                    else
                    {
                      $total_cost +=(int)$value['total_discount_price'];
                      $total_discount +=(int)$value['discount_values'];    
                    }
                }

                $grand_tot = $tot_tax+$total_cost;
            }

        //   return [$carts];
        //   die;
        
        if($input['is_platform_coupon']==true)
        {
            $pltform_coupon_id = $input['platform_coupon_id'];
            if($input['platform_discount_type']=='discount')
            {
                $plform_discount_type = $input['platform_discount_type'];
                $pltform_discount_amt = $input['discount_amt'];
                $parent_g_total = $input['total_amt'] - $pltform_discount_amt;
                
               
            }
            else
            {
                $pltform_coupon_id = '';
                $pltform_discount_amt = 0;
                $parent_g_total = '';
                $plform_discount_type = '';
                
            }
        }
        else
        {
                $pltform_coupon_id = '';
                $pltform_discount_amt = 0;
                $parent_g_total = '';
                $plform_discount_type = '';
        }
        
        //WALlet balance
        if($input['e_money_amt']==false)
        {
            $wallet_amt = 0;
        }
        else
        {
            $wallet_amt = $input['e_money_amt'];
            
        }
        
        $insert_sale_parent = ParentSale::create(['org_id'            => 1,
                                                  'user_id'           => $user_id,
                                                  'tot_amount'        => $input['total_amt'],
                                                  'platform_coupon_id'=> $pltform_coupon_id,
                                                  'discount_type'     => $plform_discount_type,
                                                  'discount_amt'      => $pltform_discount_amt,
                                                  'wallet_amt'        => $wallet_amt,
                                                  'reward_id'         => $input['reward_id'],
                                                  'reward_amt'        => $input['reward_amt'],    
                                                  'grand_total'       => $input['total_amt']-$pltform_discount_amt - $wallet_amt,   
                                                  'created_at'        => date("Y-m-d H:i:s"),
                                                  'updated_at'        => date("Y-m-d H:i:s")
                                                  ]);
        $parent_sale_id  = $insert_sale_parent->id;                                          
          
          if($input['is_platform_coupon']==true)
        {
            $pltform_coupon_id = $input['platform_coupon_id'];
             $coupon_data= Coupon::where('id',$pltform_coupon_id)->first();
                    if($coupon_data)
                    {
                    $coupon_usage= CouponHist::create(['org_id'       =>  1,
                                                       'coupon_id'    =>  $coupon_data->id,
                                                       'order_id'     =>  $sale_id,
                                                       'ofr_value'    =>  $coupon_data->ofr_value,
                                                       'ofr_value_type'=> $coupon_data->ofr_value_type,
                                                       'ofr_type'     =>  $coupon_data->ofr_type,
                                                       'created_at'    =>date("Y-m-d H:i:s"),
                                                       'updated_at'    =>date("Y-m-d H:i:s")
                                                        ]);
                    }
            if($input['platform_discount_type']=='cashback')
            {                                        
        //CASHBACK
                $cashback = CustomerWallet_Model::create(['user_id'    =>  $user_id,
                                                          'source_id'  =>  $parent_sale_id,
                                                          'source'     =>  'Platform coupon',
                                                          'credit'     =>  $input['discount_amt'],
                                                          'is_active'  =>  1,
                                                          'is_deleted' =>  0,
                                                          'created_at'    =>date("Y-m-d H:i:s"),
                                                          'updated_at'    =>date("Y-m-d H:i:s")]);  
            }
            
        }
        
        // reward application
        $sale_before  =  SaleOrder::where('cust_id',$user_id)->count();
        if($sale_before<1)
        {
            $reward = Reward::where('is_active',1)->where('is_deleted',0)->where('ord_min_amount', '<=', $input['total_amt'])->first();
            if($reward)
            {
                if($reward->ord_type=='cashback')
                {
                $cashback_reward = CustomerWallet_Model::create(['user_id'    =>  $user_id,
                                                          'source_id'  =>  $reward->id,
                                                          'source'     =>  'First Buy',
                                                          'credit'     =>  $reward->ord_amount,
                                                          'is_active'  =>  1,
                                                          'is_deleted' =>  0,
                                                          'created_at'    =>date("Y-m-d H:i:s"),
                                                          'updated_at'    =>date("Y-m-d H:i:s")]); 
                }
                                                          
                                                          //If INVITED BY someone
            $cust_master = Customer::where('id',$user_id)->first();
            if($cust_master->invited_by!='' && $cust_master->invited_by!=0)
                {
                   if($reward->rwd_type==2 || $reward->rwd_type==3)
                   {
                       $typ_pts = $reward->rewardType_purchase()->points;
                       if($typ_pts!='')
                       {
                           $credit_value = $typ_pts * $reward->point_val;
                       }
                       else
                       {
                           $credit_value =1 * $reward->point_val;
                       }
                       $cashback_reward_invite = CustomerWallet_Model::create(['user_id'    =>  $cust_master->invited_by,
                                                          'source_id'  =>  $reward->id,
                                                          'source'     =>  'Reward',
                                                          'credit'     =>  $credit_value,
                                                          'is_active'  =>  1,
                                                          'is_deleted' =>  0,
                                                          'created_at'    =>date("Y-m-d H:i:s"),
                                                          'updated_at'    =>date("Y-m-d H:i:s")]); 
                   }
                }
            }
        }
        
        //Wallet used
        if($wallet_amt>0)
        {
            $wallet_usage = CustomerWallet_Model::create(['user_id'    =>  $user_id,
                                                          'source_id'  =>  $parent_sale_id,
                                                          'source'     =>  'Order',
                                                          'debit'      =>  $wallet_amt,
                                                          'is_active'  =>  1,
                                                          'is_deleted' =>  0,
                                                          'created_at'    =>date("Y-m-d H:i:s"),
                                                          'updated_at'    =>date("Y-m-d H:i:s")]); 
        }
        
            $latestorder_ids=1;
            $latestOrder = SaleOrder::orderBy('created_at','DESC')->first();
            
            if($latestOrder)
            {
                $latestorder_ids = $latestOrder->id;
            }
           
            $saleorder_id = date('y').date('m').str_pad($latestorder_ids + 1, 6, "0", STR_PAD_LEFT);
            // echo $saleorder_id;
            // die;
            
            $seller_arrays =$input['seller_array'];
            
            foreach($seller_arrays as $rows)
            {   
                
                if($rows['discount_amt']!="")
                {
                    $discount_amt_sale = $rows['discount_amt'];
                }
                else
                {
                    $discount_amt_sale = 0;
                }
                if($rows['packing_charge']!="")
                {
                    $packing_chrg = $rows['packing_charge'];
                }
                else
                {
                    $packing_chrg = 0;
                }
                if($rows['shipping_charge']!="")
                {
                    $shipping_chrg = (float)$rows['shipping_charge'];
                }
                else
                {
                    $shipping_chrg = 0;
                }
                
                
                
                $grnd_tot_sale = ($rows['total_cost']+$rows['total_tax']) - $discount_amt_sale - $wallet_amt;
                //$total = $this->get_total_seller_product($rows->seller_id); 
                $create_saleorder = SaleOrder::create(['org_id' => 1,
                'parent_sale_id'  =>$parent_sale_id,
                'order_id'        => $saleorder_id,
                'cust_id'         => $user_id,
                'seller_id'       => $rows['seller_id'],
                'total'           => $rows['total_cost'],
                'discount'        => $discount_amt_sale,
                'tax'             => $rows['total_tax'],
                'shiping_charge'  => $shipping_chrg,
                'packing_charge'  => $packing_chrg,
                'wallet_amount'   => $wallet_amt,
                'g_total'         => $grnd_tot_sale,
                'ecom_commission' => 0,
                'discount_type'   => $rows['discount_type'],  
                'coupon_id'       => $rows['coupon_id'],
                'order_status'    => 'accepted',
                'payment_status'  => 'pending',
                'shipping_status' => 'pending',
                'cancel_process'  => 0,
                'cust_message'    => $rows['message'],    
                'created_at'    =>date("Y-m-d H:i:s"),
                'updated_at'    =>date("Y-m-d H:i:s")]);
                $sale_id  = $create_saleorder->id;
                
                //coupon usage history insertion
                if($rows['is_coupon']==true)
                {
                    $coupon_data= Coupon::where('id',$rows['coupon_id'])->first();
                    if($coupon_data)
                    {
                    $coupon_usage= CouponHist::create(['org_id'       =>  1,
                                                       'coupon_id'    =>  $coupon_data->id,
                                                       'order_id'     =>  $sale_id,
                                                       'ofr_value'    =>  $coupon_data->ofr_value,
                                                       'ofr_value_type'=> $coupon_data->ofr_value_type,
                                                       'ofr_type'     =>  $coupon_data->ofr_type,
                                                       'created_at'    =>date("Y-m-d H:i:s"),
                                                       'updated_at'    =>date("Y-m-d H:i:s")
                                                        ]);
                    }
                }

                //Payment
                $saleorder_payment = SalesOrderPayment::create(['org_id' => 1,
                'sales_id'         => $sale_id,
                'payment_method_id'=> $input['payment_type'],
                'payment_type'     => 'Cash on Delivery',
                'transaction_id'   => '',
                'payment_data'     => '',
                'amount'           => $grnd_tot_sale,
                'payment_status'  => 'pending']);
                 $sale_items = $this->insert_seller_products($sale_id,$rows['seller_id'],$user_id,$lang);
                
                $insert_address = SalesOrderAddress::create(['sales_id' => $sale_id,
                'order_id'        => $saleorder_id,
                'cust_id'         => $user_id,
                'ref_addr_id'     => $input['address_id'],
                'addr_id'         => $addr_list->usr_addr_typ_id,
                'name'            => $addr_list->name,
                'phone'           => $addr_list->phone,
                'email'           => $user_email,
                'address1'        => $addr_list->address_1,
                'address2'        => $addr_list->address_2,
                'zip_code'        => $addr_list->pincode,
                'city'            => $addr_list->city_id,
                'state'           => $addr_list->state_id,
                'country'         => $addr_list->country_id,  
                'latitude'        => $addr_list->latitude,
                'longitude'       => $addr_list->longitude,
                's_addr_id'       => $addr_list->usr_addr_typ_id,
                's_name'          => $addr_list->name,
                's_phone'         => $addr_list->phone,
                's_email'         => $user_email,
                's_address1'      => $addr_list->address_1,
                's_address2'      => $addr_list->address_2,
                's_zip_code'      => $addr_list->pincode,
                's_city'          => $addr_list->city_id,
                's_state'         => $addr_list->state_id,
                's_country'       => $addr_list->country_id,  
                's_latitude'      => $addr_list->latitude,
                's_longitude'     => $addr_list->longitude]);
            }   

            
            
            
            
            return ['httpcode'=>200,'status'=>'success','message'=>'Order placed','data'=>['order_id'=>$saleorder_id]];

        }

        else
        {
            return ['httpcode'=>404,'status'=>'error','message'=>'Cart is empty','data'=>['errors'=>'Cart is empty']];
        }
    }//validation true




    }
    public function placeorder123(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $lang=$request->lang_id;
        $validator=  Validator::make($request->all(),[
            'access_token' => ['required'],
            'e_money'      => ['nullable','numeric','max:1'],
            'e_money_amt'  => ['required','numeric'],
            'is_coupon'    => ['nullable','numeric','max:1'],
            'coupon_id'    => ['nullable','numeric'],
            'payment_type' => ['required'],
            'shipping_chrg'=> ['required'], 
            'address_id'   => ['nullable'],
            'name'         => ['required'],
            'phone'        => ['required'],
            'email'        => ['required','email'],
            'address_line1'=> ['required'],
            'address_line2'=> ['required'],   
            'zip_code'     => ['required'],
            'city'         => ['required'],
            'state'        => ['required'],
            'country'      => ['required'],
            'latitude'     => ['nullable'],
            'longitude'    => ['nullable'] 

        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {

        $carts = Product::join('usr_cart_item','prd_products.id','=','usr_cart_item.product_id')
                        ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->get();  
       $cart = Product::join('usr_cart_item','prd_products.id','=','usr_cart_item.product_id')
                        ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                        ->where('usr_cart.user_id',$user_id)    
                        ->where('usr_cart.is_active',1)
                        ->where('usr_cart.is_deleted',0)
                        ->where('usr_cart_item.is_active',1)
                        ->where('usr_cart_item.is_deleted',0)
                        ->distinct()
                        ->get('seller_id');                           


        if(count($cart)>0)
        {    
           foreach($carts as $rows)
            {
                $products[] = $this->get_cart_products($rows->product_id,$rows->cart_id,$rows->quantity,$lang);
            }  
            $filter = array_filter($products);
            $tot_tax =0;
            $total_cost=0;
            $total_discount=0;
            if(count($filter)>0)
            {
                foreach($filter as $value)
                {
                    $tot_tax += $value['total_tax_value'];
                    if($value['total_discount_price']==0)
                    {
                     $total_cost +=(int)$value['total_actual_price']; 
                     $total_discount +=(int)$value['discount_values'];   
                    }
                    else
                    {
                      $total_cost +=(int)$value['total_discount_price'];
                      $total_discount +=(int)$value['discount_values'];    
                    }
                }

                $grand_tot = $tot_tax+$total_cost;
            }

        //   return [$carts];
        //   die;

            $latestOrder = SaleOrder::orderBy('created_at','DESC')->first();
            $saleorder_id = date('y').date('m').str_pad($latestOrder->id + 1, 6, "0", STR_PAD_LEFT);
            // echo $saleorder_id;
            // die;
            foreach($cart as $rows)
            {   
                //$total = $this->get_total_seller_product($rows->seller_id); 
                $create_saleorder = SaleOrder::create(['org_id' => 1,
                'order_id'        => $saleorder_id,
                'cust_id'         => $user_id,
                'seller_id'       => $rows->seller_id,
                'total'           => $total_cost,
                'discount'        => $total_discount,
                'tax'             => $tot_tax,
                'shiping_charge'  => 0,
                'packing_charge'  => 0,
                'wallet_amount'   => $input['e_money_amt'],
                'g_total'         => $grand_tot,
                'ecom_commission' => 0,
                'discount_type'   => '',  
                'coupon_id'       => 0,
                'order_status'    => 'pending',
                'payment_status'  => 'pending',
                'shipping_status' => 'pending',
                'cancel_process'  => 0,
                'created_at'    =>date("Y-m-d H:i:s"),
                'updated_at'    =>date("Y-m-d H:i:s")]);
                $sale_id  = $create_saleorder->id;

                //Payment
                $saleorder_payment = SalesOrderPayment::create(['org_id' => 1,
                'sales_id'         => $sale_id,
                'payment_method_id'=> $input['payment_type'],
                'payment_type'     => 'Cash on Delivery',
                'transaction_id'   => '',
                'payment_data'     => '',
                'amount'           => $grand_tot,
                'payment_status'  => 'pending']);
                $sale_items = $this->insert_seller_products($sale_id,$rows->seller_id,$user_id,$lang);
            }   

            $insert_address = SalesOrderAddress::create(['sales_id' => $sale_id,
                'order_id'        => $saleorder_id,
                'cust_id'         => $user_id,
                'addr_id'         => 1,
                'name'            => $input['name'],
                'phone'           => $input['phone'],
                'email'           => $input['email'],
                'address1'        => $input['address_line1'],
                'address2'        => $input['address_line2'],
                'zip_code'        => $input['zip_code'],
                'city'            => $input['city'],
                'state'           => $input['state'],
                'country'         => $input['country'],  
                'latitude'        => $input['latitude'],
                'longitude'       => $input['longitude'],
                's_addr_id'       => 1,
                's_name'          => $input['name'],
                's_phone'         => $input['phone'],
                's_email'         => $input['email'],
                's_address1'      => $input['address_line1'],
                's_address2'      => $input['address_line2'],
                's_zip_code'      => $input['zip_code'],
                's_city'          => $input['city'],
                's_state'         => $input['state'],
                's_country'       => $input['country'],  
                's_latitude'      => $input['latitude'],
                's_longitude'     => $input['longitude']]);
            
            
            
            return ['httpcode'=>200,'status'=>'success','message'=>'Order placed','data'=>['order_id'=>$saleorder_id]];

        }

        else
        {
            return ['httpcode'=>404,'status'=>'error','message'=>'Cart is empty','data'=>['errors'=>'Cart is empty']];
        }
    }//validation true




    }
    
    //Track order
  public function track_order(Request $request)
    {    
        
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
         $lang =  $request->lang_id;
         $orderId= $request->order_id;
                    
                    if($orderId)
                    {
                        $sales =  SaleOrder::where('cust_id',$user_id)->where('order_id',$orderId)->get();
                    }
                    else
                    {
                        $sales =  SaleOrder::where('cust_id',$user_id)->whereNotIn('shipping_status', ['delivered','cancelled'])->get();
                    }
                     
                    if($sales->count() > 0)
                    {
                        foreach($sales  as $row)
                        {
                            $all_items      =   SaleorderItems::where('sales_id',$row->id)->get(); 
                            // return [$all_items];
                            // die;
                            foreach($all_items  as $items)
                            {
                                $prdId      =   $items->prd_id;
                                
                                $data['sale_id']           =   $row->id;
                                $data['order_id']          =   $row->order_id;
                                $data['sale_items_id']     =   $items->id;
                                $data['product_id']        =   $prdId;
                                $data['product_name']      =   $this->get_content($items->product->name_cnt_id,$lang);
                                $data['price']             =   $items->row_total;
                                $data['currency']          =   getCurrency()->name;
                                //$data['image']     =   $this->get_product_image($items->prd_id);
                                $data['quantity']          =   $items->qty;
                                $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                                $data['delivery_status']   =   $row->shipping_status;
                                $data['delivery_date']       =   '';
                                $val[] = $data;
                             }
                        }
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return ['httpcode'=>'200','status'=>'success','message'=>'track order','data'=>['order'=>$val]];
    }
    
    public function checkout_info_page(Request $request)
  {
    
    if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        //$lang=$request->lang_id;


        //******TYpes of addresses
        $adr_typ = CustomerAddressType::where('is_active',1)->where('is_deleted',0)->get();
        foreach($adr_typ as $rows)
        {
            $typ['addr_type_id']      = $rows->id;
            $typ['addr_type_name']    = $rows->usr_addr_typ_name;
            $typ['addr_type_desc']    = $rows->usr_addr_typ_desc; 
            $typ_data[]               = $typ;   
        }



        //previous address
        $customer_address = CustomerAddress::where('is_active',1)->where('is_deleted',0)->where('user_id',$user_id)->get();
        if(count($customer_address)>0)
        {
        foreach($customer_address as $row)
        {
            $cust_addr['addr_id']        = $row->id;
            $cust_addr['address_type']   = $row->type->usr_addr_typ_name;
            $cust_addr['address_1']      = $row->address_1;
            $cust_addr['address_2']      = $row->address_2;
            $cust_addr['pincode']        = $row->pincode;
            $cust_addr['city_id']        = $row->city_id;
            $cust_addr['city_name']      = $row->city->city_name;
            $cust_addr['state_id']       = $row->state_id;
            $cust_addr['state_name']     = $row->state->state_name;
            $cust_addr['country_id']     = $row->country_id;
            $cust_addr['country_name']   = $row->country->country_name;
            $cust_addr['latitude']       = $row->latitude;
            $cust_addr['longitude']      = $row->longitude;
            $cust_addr['is_default_addr'] = $row->is_default;
            $addr_data[]                  = $cust_addr;
        }
      }
      else
      {
        $addr_data = [];
      }


        //******Payment methods
        $pay_method = PaymentMethod::select('id','title','desc')->where('is_active',1)->where('is_deleted',0)->get();

        return ['httpcode'=>200,'status'=>'success','message'=>'Success','data'=>['address'=>$addr_data,'address_types'=>$typ_data,'payment_methods'=>$pay_method]];
  }
  
  //list of coupons
  
  public function coupon_list(Request $request)
  {
      $lang=$request->lang_id;
      $current_date=date('Y-m-d');
      $coupon = Coupon::whereDate('valid_from','<=',$current_date)->whereDate('valid_to','>=',$current_date)->where('is_active',1)->where('is_deleted',0)->get();
      $c_list =[];
      foreach($coupon as $row)
      {
           if($row->ofr_value_type=="percentage")
               {
                    $discount = $row->ofr_value." ".$row->ofr_value_type;

               }
               else
               {
                    $discount = $row->ofr_value." ".$row->ofr_value_type;
               }
                    $coupon_details['coupon_id']=$row->id;
                    $coupon_details['title']=$this->get_content($row->cpn_title_cid,$lang);
                    $coupon_details['desc']=$this->get_content($row->cpn_desc_cid,$lang);
                    $coupon_details['offer']=$discount." ".$row->ofr_type;
                    $coupon_details['coupon_code']=$row->ofr_code;
                    $coupon_details['minimum_purchase']=$row->ofr_min_amount;
                    $coupon_details['offer_type']=$row->ofr_type;
                    $c_list[]  = $coupon_details;
      }
      
      return ['httpcode'=>200,'status'=>'success','message'=>'Coupon list','data'=>['coupon'=>$c_list]];
  }
  

    function get_total_seller_product($seller_id)
    {
        $total = 0;
        return $total;
    }

    function get_cart_products($prd_id,$cart_id,$qty,$lang){
        $data     =   [];
        
        $prod_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('id',$prd_id)->first();
            if($prod_data)   { 
                    $prd_list['cart_id']=$cart_id;   
                    $prd_list['product_id']=$prod_data->product_id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['quantity']=$qty;
                    $prd_list['seller']=$prod_data->Store($prod_data->seller_id)->store_name;
                    $prd_list['seller_id']=$prod_data->seller_id;
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
                    
                    $actual_price =$prod_data->prdPrice->price;
                    $prd_list['unit_actual_price']=(int)$actual_price;
                    $tot_actual=(int)$actual_price*(int)$qty;
                    $prd_list['total_actual_price']=(int)$tot_actual;
                    $tax_amt=$prod_data->getTaxValue($prod_data->tax_id);
                    $tax_amt_per =$tax_amt/100;
                    $total_tax_amount = (int)$tot_actual * (int)$tax_amt_per;
                    $prd_list['total_tax_value']=$total_tax_amount;
                     
                    
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
                        $prd_list['discount_values']= (int)$discount_value;
                        $prd_list['unit_discount_price']= $unit_price;
                        $prd_list['total_discount_price']=$unit_price * $qty;
                       

                    }
                    else
                    {
                        $prd_list['offer']=$shock->discount_value."% Off";
                        $shock_discount =$shock->discount_value/100;
                        $per=(int)$shock_discount*(int)$actual_price;
                        $discount=(float)$actual_price-(float)$per;
                        $round= $discount;
                        $prd_list['discount_values']= (int)$per;
                        $prd_list['unit_discount_price']=(int)$round;
                        $prd_list['total_discount_price']=$round * $qty;
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
                $prd_list['total_discount_price']=(int)$tot;
                $prd_list['discount_values']= (int)$actual_price-(int)$sale_price;
                }
                else
                {   $prd_list['discount_values']= 0;
                    $prd_list['unit_discount_price']=0;
                    $prd_list['total_discount_price']=0;
                }
            }

            $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
           // $prd_list['image']=$this->get_product_image($prod_data->id);
                    $data             =   $prd_list;
             }
            // else{ $data     =   []; } 
             return $data;
        
    }

    function insert_seller_products($sale_id,$seller_id,$user_id,$lang){
        
        $product=[];
        $prod_datas  = Product::join('usr_cart_item','prd_products.id','=','usr_cart_item.product_id')
                            ->join('usr_cart','usr_cart_item.cart_id','=','usr_cart.id')
                            ->where('usr_cart.user_id',$user_id)    
                            ->where('usr_cart.is_active',1)
                            ->where('usr_cart.is_deleted',0)
                            ->where('usr_cart_item.is_active',1)
                            ->where('usr_cart_item.is_deleted',0)
                            ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)
                            ->where('prd_products.seller_id',$seller_id)
                            ->select('prd_products.*','usr_cart.*','usr_cart_item.*','usr_cart_item.product_id as cart_prd_id')
                            ->get();
        
            if(count($prod_datas)>0)   {    
                foreach($prod_datas as $prod_data)
                {   $qty = $prod_data->quantity;
                    $prd_list['product_id']=$prod_data->product_id;
                    if($prod_data->product_type==1){
                    $prd_list['product_name']=$product_name=$this->get_content($prod_data->name_cnt_id,$lang);
                    }
                    else
                    {
                     $associate= AssociatProduct::where('ass_prd_id',$prod_data->product_id)->first();   
                    $prd_list['product_name']=$product_name=$this->get_content($associate->product->name_cnt_id,$lang);    
                    }
                    
                    
                    $prd_list['currency']=getCurrency()->name;
                    
                    $actual_prices =$this->get_price($prod_data->product_id);
                    $sale_price =$this->get_sale_price($prod_data->product_id);
                    if($sale_price!='')
                    { 
                        $actual_prices=$sale_price;
                    }
                    else
                    {
                      $actual_prices =$this->get_price($prod_data->product_id);  
                    }
                    $actual_price=$actual_prices;
                    $prd_list['unit_actual_price']=$actual_price;
                    $tot_actual=$actual_price*$qty;
                    $prd_list['total_actual_price']=$tot_actual;
                    if($prod_data->tax_id){
                    $tax_amt=$prod_data->getTaxValue($prod_data->tax_id);
                    $total_tax_amount = $tot_actual * ($tax_amt/100);
                    }
                    else
                    {
                        $total_tax_amount=0;
                    }
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
                 $actual_price_sh = $this->get_price($prod_data->product_id);  
                if($shock->discount_type=="amount")
                    {  
                        $prd_list['offer']=getCurrency()->name." ".$shock->discount_value." Off";
                        $discount_value = $shock->discount_value;
                        $unit_price = (int)$actual_price_sh-(int)$discount_value;
                        $actual_price =$unit_price;
                       

                    }
                    else
                    {
                        $prd_list['offer']=$shock->discount_value."% Off";
                        $per=($shock->discount_value/100)*$actual_price_sh;
                        $discount=(float)$actual_price_sh-(float)$per;
                        $round= (int)$discount;
                        $prd_list['unit_discount_price']=$round;
                        $actual_price =$round;
                        $prd_list['total_discount_price']=(int)$round * (int)$qty;
                    }
            }
            
            else
            {
                $prd_list['offer_available']= 0;
                $prd_list['offer_name']= ''; 
                $sale_price =$this->get_sale_price($prod_data->product_id);
                
                if($sale_price!='')
                {
                $tot= $sale_price * $qty;
                $prd_list['total_discount_price']=number_format($tot,2);
                }
                
            }

            $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
           // $discount_price =$prd_list['total_discount_price'];
            
            $tot_actual=$actual_price*$qty;
          
            $create_saleorder = SaleorderItems::create([
                'sales_id'        => $sale_id,
                'parent_id'       => $sale_id,
                'prd_id'          => $prod_data->product_id,
                'prd_type'        => $prod_data->product_type,
                'prd_name'        => $product_name,
                'price'           => $actual_price,
                'qty'             => $prod_data->quantity,
                'total'           => $tot_actual,
                'discount'        => 0,
                'tax'             => $total_tax_amount,
                'row_total'       => $tot_actual + $total_tax_amount,
                'coupon_id'       => '', 
                'created_at'    =>date("Y-m-d H:i:s"),
                'updated_at'    =>date("Y-m-d H:i:s"),
                'is_deleted'    =>0]);  
                
           $prd_stock_update = PrdStock::create(['seller_id'  => $seller_id,
                                                 'type'       =>'destroy',
                                                 'prd_id'     => $prod_data->product_id,
                                                 'qty'        => $prod_data->quantity,
                                                 'rate'       => $actual_price,
                                                 'created_by' => $user_id,
                                                 'sale_id'    => $sale_id,
                                                 'created_at' => date("Y-m-d H:i:s"),
                                                 'updated_at' => date("Y-m-d H:i:s")
                                                 ]);     
                $cart_update= Cart::where('id',$prod_data->cart_id)->update([
             'is_active'=>0,'updated_at'=>date("Y-m-d H:i:s")]);

            $cart_item_update=CartItem::where('cart_id',$prod_data->cart_id)->update([
            'is_active'=>0,
            'updated_at'=>date("Y-m-d H:i:s")]);  

            $insert_cart_hist =  CartHistory::create(['org_id' => 1,
                'user_id' => $user_id,
                'product_id' => $prod_data->product_id,
                'quantity'  => $prod_data->quantity,
                'action'=>'ordered',
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);                                 

       //$product[]=$prd_list;
               }      //foreachend    

       
            
            
             }return $prod_datas;
           
        
    }


    /********GET values********/
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
        $return_val = (int)$rows->sale_price;
        return $return_val;
        }
        else
            { $return_val=0;
                return $return_val; }
        }


        public function get_price($field_id){ 

       $current_date=Carbon::now();
       $rows = PrdPrice::where('is_deleted',0)->where('prd_id',$field_id)->first();        
        if($rows){ 
        $return_val = $rows->price;
        return $return_val;
        }
        else
            { $return_val=0;
                return $return_val; }
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
}
