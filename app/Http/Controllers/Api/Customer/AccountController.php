<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Wishlist;
use App\Models\UsrWishlist;
use App\Models\Product;
use App\Models\CmsContent;
use App\Models\PrdReview;
use App\Models\PrdPrice;
use App\Models\ProductImage;
use App\Models\SalesOrder;
use App\Models\SaleorderItems;
use App\Models\SalesOrderCancel;
use App\Models\SalesOrderCancelNote;
use App\Models\CustomerMaster;
use App\Models\CustomerInfo;
use App\Models\CustomerAddress;
use App\Models\CustomerTelecom;
use App\Models\CustomerSecurity;
use App\Models\CustomerAddressType;
use App\Models\CustomerLogin;
use App\Models\SalesOrderReturn;
use App\Models\SalesOrderReturnStatus;
use App\Models\CouponHist;
use App\Models\Prd_Recent_View;
use App\Models\CustomerWallet_Model;
use App\Models\UserVisit;
use App\Models\SalesOrderAddress;
use App\Models\SellerInfo;
use App\Models\SalesOrderPayment;
use App\Models\SalesOrderShippingStatus;
use App\Models\UsrNotification;
use App\Models\SalesOrderRefundPayment;
use App\Models\SalesOrderReturnShipment;
use App\Models\Auction;
use App\Models\AuctionHist;
use App\Models\SettingOther;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class AccountController extends Controller
{
    public function addWishlist(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['product_id']    = 'required|numeric';
            $rules['type']          = 'required|string';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $checklist = Wishlist::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->first();
                    $checkprd =  UsrWishlist::where('user_id',$user_id)->where('prd_id',$formData['product_id'])->where('is_deleted',0)->first();
                    if($checkprd)
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Already exist','data'=>['message' =>'Product is already in the wishlist!']);
                    }
                    
                    if($checklist == '')
                    {
                        $createwishlist =  Wishlist::create(['user_id' => $user_id,
                        'title' => $formData['type'],
                        'created_at'=>date("Y-m-d H:i:s"),
                        'updated_at'=>date("Y-m-d H:i:s")]);
                    }
                    $wishlist = UsrWishlist::create(['user_id' => $user_id,
                    'prd_id' => $formData['product_id'],
                    'created_at'=>date("Y-m-d H:i:s"),
                    'updated_at'=>date("Y-m-d H:i:s")]);
                    return array('httpcode'=>'200','status'=>'success','message'=>'product added','data'=>['message' =>'Your product successfully added in wishlist!']);
                }
        }else{ return invalidToken(); }
    }
    public function wishlist(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val       =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $list =  UsrWishlist::where('user_id',$user_id)->where('is_deleted',0)->get();
                    foreach($list  as $row)
                    {
                         $prdId                     =   $row->prd_id;
                         $avaliable = Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('id',$prdId)->first();
                          $products = Product::where('id',$prdId)->first();

                         $data['id']                =   $row->id;
                         $data['product_id']        =   $prdId;
                         $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);;
                         $data['product_rating']    =   $this->get_rates($products->id);
                         $data['actual_price']      =   $products->prdPrice->price;
                         $data['currency']          =   getCurrency()->name;
                         $data['sale_price']        =   $this->get_sale_price($products->id);
                         $data['product_image']     =   $this->get_product_image($products->id);
                         $data['shop_name']         =   $products->Store($products->seller_id)->store_name;
                         if($avaliable == NULL)
                         {
                            $data['status']         =   'Unavaliable';
                         }
                         else
                         {
                         $data['status']            =   '';
                         }
                         $val[] = $data;
                    }
                   
                    return array('httpcode'=>'200','status'=>'success','message'=>'wishlist','data'=>['wishlist'=>$val]);
                }
        }else{ return invalidToken(); }
    }
    public function removeWishlist(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['product_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $checkprd =  UsrWishlist::where('user_id',$user_id)->where('prd_id',$formData['product_id'])->where('is_deleted',0)->first();
                    if($checkprd)
                    {
                         UsrWishlist::where('user_id',$user_id)->where('prd_id',$formData['product_id'])->update(['is_deleted'=>1]);
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'product removed','data'=>['message' =>'Your product successfully removed from wishlist!']);
                }
        }else{ return invalidToken(); }
    }
    function get_content($field_id,$lang)
    { 
        if($lang=='')
        { 
        $language =DB::table('glo_lang_lk')->where('is_active', 1)->first();
        $language_id=$language->id;
        }
        else
        {
            $language_id=$lang;
        }
        $content_table  =   CmsContent::where('cnt_id', $field_id)->where('lang_id', $language_id)->first();
        if(!empty($content_table))
        { 
            $return_cont = $content_table->content;   
        }
        else
        {
            $return_cont = ''; 
        }
        return $return_cont;
    }

    function get_rates($field_id)
    { 
        $rate = PrdReview::select(DB::raw('AVG(rating) as rating'))->where('prd_id',$field_id)->where('is_active',1)->where('is_deleted',0)->first();
        if($rate)
        { 
            $return_val = round($rate->rating);
        }
        else
        { 
            $return_val =   0;
        }
        return $return_val;
    }

    public function get_sale_price($field_id)
    { 
       $current_date    =   Carbon::now();
       $rows            =   PrdPrice::where('is_deleted',0)->where('prd_id',$field_id)->whereDate('sale_end_date','>=',$current_date)->first();        
        if($rows)
        { 
            $return_val = $rows->sale_price;
        }
        else
        { 
             $return_val='';
        } return $return_val;
    }

    function get_product_image($prd_id)
    {
        $data     =   [];
        $product       =   ProductImage::where('prd_id',$prd_id)->where('is_deleted',0)->get(); 
        if($product->count() > 0)   
            {   
                foreach($product as $k=>$row)
                { 
                    $val['image']       =   config('app.storage_url').$row->image;
                    $val['thumbnail']   =   config('app.storage_url').$row->thumb;
                    $data[]             =   $val;
                } 
            }
        else
            { 
                $val['image']       =   config('app.storage_url').'/app/public/no-image.png';
                $val['thumbnail']   =   config('app.storage_url').'/app/public/no-image-thumbnail.jpg';
                $data[]     =   $val; 
            } 
        return $data;
    }

    public function purchase(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];  $cdata   =   []; 
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->orderBy('id','desc')->get(); 
                    if($sales->count() > 0)
                    {
                        foreach($sales  as $row)
                        {

                            $sal_id = $row->id;
                            $all_items  =   SaleorderItems::where('sales_id',$row->id)->get();
                                $ship       =   SalesOrderShippingStatus::where('sales_id',$row->id)->orderBy('created_at', 'desc')->first(); 
                                $ord        =   SalesOrderCancel::where('sales_id',$row->id)->orderBy('created_at', 'desc')->first();
                                $adddr      =   SalesOrderAddress::where('sales_id',$row->id)->first();
                                $seller     =   SellerInfo::where('seller_id',$row->seller_id)->first();
                            $histories =  AuctionHist::where('user_id',$user_id)->where('sale_id',$row->id)->where('is_deleted',0)->where('is_active',1)->orderBy('created_at', 'desc');
                            $auctionwin = Auction::where('bid_allocated_to',$user_id)->where('sale_id',$row->id)->where('status','closed')->where('is_deleted',0)->where('is_active',1);
                            if($histories->count() > 0)
                            {
                                if($auctionwin->count() > 0)
                                {
                                foreach($all_items  as $items)
                                {
                                    $prdId      =   $items->prd_id;
                                    $products   =   Product::where('id',$prdId)->first();
                                    // $data['ids']           =   $hist;
                                    $data['sale_id']           =   $row->id;
                                    $data['order_id']          =   $row->order_id;
                                    $data['sale_items_id']     =   $items->id;
                                    $data['product_id']        =   $prdId;
                                    $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                                    $data['actual_price']      =   $products->prdPrice->price;
                                    $data['sale_price']        =   $items->price;
                                    $data['currency']          =   getCurrency()->name;
                                    $data['product_image']     =   $this->get_product_image($products->id);
                                    $data['quantity']          =   $items->qty;
                                    $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                    $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                
                                    $data['delivered_date']    =   '';
                                    $data['return_date']       =   '';
                                    if($ship)
                                    {
                                         $data['delivery_status']   =   $ship->status;
                                         if($ship->status == 'delivered')
                                         {
                                            $data['delivered_date']    =   date('Y-m-d',strtotime($ship->updated_at));;
                                            $data['return_date']       =    date('Y-m-d',strtotime($ship->updated_at. ' + 2 days'));
                                         }
                                    }
                                    else
                                    {
                                        $data['delivery_status']   =   'Pending';
                                    }
                                  
                                    $data['sold_by']        =   $seller->fname;
                                    $data['track_order']    =   '';
                                    $data['order_status']   =   $row->order_status;
                                    $data['cancel_order_detail'] = array();
                                    if($row->order_status == 'cancel_initiated')
                                    {
                                         if($ord)
                                            {
                                                $ordnote = SalesOrderCancelNote::where('cancel_id',$ord->id)->first();
                                                $cdata['cancel_id']      =   $ord->id;
                                                $cdata['cancel_title']   =   $ordnote->title;
                                                $cdata['cancel_notes']   =   $ordnote->note;
                                                 $data['cancel_order_detail'] =  $cdata;
                                            }
                                            else
                                            {
                                                 $data['cancel_order_detail'] =  [];
                                            }
                                           
                                    }
                                    
                                    $return_order = SalesOrderReturn::where('sales_item_id',$items->id)->where('is_deleted',0)->first();
                
                                    if($return_order)
                                    {
                                        $rtrn['status']      =   $return_order->status;
                                        $rtrn['id']          =   $return_order->id;
                                        $rtrn['payment_status']     =   $return_order->payment_status;
                                        $data['return_detail']       =       $rtrn;
                                    }
                                    else
                                    {
                                        $data['return_detail']       =       [];
                                    }
                                     $data['auction_status']=   'True';
                                     $data['bid_charge']    =   $row->bid_charge;
                                     $saddr['address_type'] = $adddr->stype->usr_addr_typ_name;
                                     $saddr['name']         = $adddr->s_name; 
                                     $saddr['phone']        = $adddr->s_phone; 
                                     $saddr['email']        = $adddr->s_email; 
                                     $saddr['address1']     = $adddr->s_address1; 
                                     $saddr['address2']     = $adddr->s_address2; 
                                     $saddr['zip_code']     = $adddr->s_zip_code;      
                                     $saddr['country']      = $adddr->scountry->country_name; 
                                     $saddr['state']        = $adddr->sstate->state_name; 
                                     $saddr['city']         = $adddr->scity->city_name; 
                                     $saddr['latitude']     = $adddr->s_latitude; 
                                     $saddr['longitude']    = $adddr->s_longitude;
                                     $data['shipping_address'] =  $saddr;
                                    $val[] = $data;
                                }
                                }
                                else
                                {

                                }
                            }
                            else
                            {
                                foreach($all_items  as $items)
                                {
                                    $prdId      =   $items->prd_id;
                                    $products   =   Product::where('id',$prdId)->first();
                                    // $data['ids']           =   $hist;
                                    $data['sale_id']           =   $row->id;
                                    $data['order_id']          =   $row->order_id;
                                    $data['sale_items_id']     =   $items->id;
                                    $data['product_id']        =   $prdId;
                                    $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                                    $data['actual_price']      =   $products->prdPrice->price;
                                    $data['sale_price']        =   $items->price;
                                    $data['currency']          =   getCurrency()->name;
                                    $data['product_image']     =   $this->get_product_image($products->id);
                                    $data['quantity']          =   $items->qty;
                                    $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                    $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                
                                    $data['delivered_date']    =   '';
                                    $data['return_date']       =   '';
                                    if($ship)
                                    {
                                         $data['delivery_status']   =   $ship->status;
                                         if($ship->status == 'delivered')
                                         {
                                            $data['delivered_date']    =   date('Y-m-d',strtotime($ship->updated_at));;
                                            $data['return_date']       =    date('Y-m-d',strtotime($ship->updated_at. ' + 2 days'));
                                         }
                                    }
                                    else
                                    {
                                        $data['delivery_status']   =   'Pending';
                                    }
                                  
                                    $data['sold_by']        =   $seller->fname;
                                    $data['track_order']    =   '';
                                    $data['order_status']   =   $row->order_status;
                                    $data['cancel_order_detail'] = array();
                                    if($row->order_status == 'cancel_initiated')
                                    {
                                         if($ord)
                                            {
                                                $ordnote = SalesOrderCancelNote::where('cancel_id',$ord->id)->first();
                                                $cdata['cancel_id']      =   $ord->id;
                                                $cdata['cancel_title']   =   $ordnote->title;
                                                $cdata['cancel_notes']   =   $ordnote->note;
                                                 $data['cancel_order_detail'] =  $cdata;
                                            }
                                            else
                                            {
                                                 $data['cancel_order_detail'] =  [];
                                            }
                                           
                                    }
                                    
                                    $return_order = SalesOrderReturn::where('sales_item_id',$items->id)->where('is_deleted',0)->first();
                
                                    if($return_order)
                                    {
                                        $rtrn['status']      =   $return_order->status;
                                        $rtrn['id']          =   $return_order->id;
                                        $rtrn['payment_status']     =   $return_order->payment_status;
                                        $data['return_detail']       =       $rtrn;
                                    }
                                    else
                                    {
                                        $data['return_detail']       =       [];
                                    }
                                     $data['auction_status']=   'False';
                                     $data['bid_charge']    =   0;
                                     $saddr['address_type'] = $adddr->stype->usr_addr_typ_name;
                                     $saddr['name']         = $adddr->s_name; 
                                     $saddr['phone']        = $adddr->s_phone; 
                                     $saddr['email']        = $adddr->s_email; 
                                     $saddr['address1']     = $adddr->s_address1; 
                                     $saddr['address2']     = $adddr->s_address2; 
                                     $saddr['zip_code']     = $adddr->s_zip_code;      
                                     $saddr['country']      = $adddr->scountry->country_name; 
                                     $saddr['state']        = $adddr->sstate->state_name; 
                                     $saddr['city']         = $adddr->scity->city_name; 
                                     $saddr['latitude']     = $adddr->s_latitude; 
                                     $saddr['longitude']    = $adddr->s_longitude;
                                     $data['shipping_address'] =  $saddr;
                                    $val[] = $data;
                                }
                            }
                                
                        }
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'my purchase','data'=>['purchase'=>$val]);
                }
        }else{ return invalidToken(); }
    }
    public function order_detail(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['sale_id']   = 'required|numeric';
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->where('id',$formData['sale_id'])->first(); 
                    if($sales)
                    {
                        $pay      =   SalesOrderPayment::where('sales_id',$request->sale_id)->first();
                        $seller   =   SellerInfo::where('seller_id',$sales->seller_id)->first();
                        $ship     =   SalesOrderShippingStatus::where('sales_id',$request->sale_id)->first();
                        $histories =  AuctionHist::where('user_id',$user_id)->where('sale_id',$request->sale_id)->where('is_deleted',0)->where('is_active',1)->orderBy('created_at', 'desc');
                        $auctionwin = Auction::where('bid_allocated_to',$user_id)->where('sale_id',$request->sale_id)->where('status','closed')->where('is_deleted',0)->where('is_active',1);
                        if($histories->count() > 0)
                        {
                            if($auctionwin->count() > 0)
                            {
                                $au_status=   'True';
                                $charge    =   $sales->bid_charge;
                            }
                            else
                            {
                                $au_status=   'False';
                                $charge    =   0;
                            }
                        }
                        else
                        {
                            $au_status =   'False';
                            $charge    =   0;
                        }
                        $ord      =   SalesOrderCancel::where('sales_id',$sales->id)->orderBy('created_at', 'desc')->first();
                        $data['order_id']          =   $sales->order_id;
                        $data['order_date']        =   date('Y-m-d',strtotime($sales->created_at));
                        $data['order_time']        =   date('g:i a',strtotime($sales->created_at));
                        $data['pay_method']        =   $pay->payment_type;
                        $data['sub_total']         =   $sales->total;
                        $data['shipping']          =   $sales->shiping_charge;
                        $tot                       =   $data['sub_total'] + $data['shipping'];
                        $data['total']             =   $tot;
                        $data['promotion']         =   $sales->discount;
                        $data['tax']               =   $sales->tax;
                        $data['grand_total']       =   $sales->g_total;
                        $data['delivered_date']    =   '';
                        $data['return_date']       =   '';
                        if($ship)
                        {
                             $data['delivery_status']   =   $ship->status;
                             if($ship->status == 'delivered')
                             {
                                $data['delivered_date']    =   date('Y-m-d',strtotime($ship->updated_at));;
                                $data['return_date']   =    date('Y-m-d',strtotime($ship->updated_at. ' + 2 days'));
                             }
                        }
                        else
                        {
                            $data['delivery_status']   =   'Pending';
                        }
                        
                        $data['sold_by']            =   $seller->fname;
                        $data['order_status']       =   $sales->order_status;
                        $data['cancel_order_detail'] = array();
                        if($sales->order_status == 'cancel_initiated')
                        {
                             if($ord)
                                {
                                    $ordnote = SalesOrderCancelNote::where('cancel_id',$ord->id)->first();
                                    $cdata['cancel_id']      =   $ord->id;
                                    $cdata['cancel_title']   =   $ordnote->title;
                                    $cdata['cancel_notes']   =   $ordnote->note;
                                     $data['cancel_order_detail'] =  $cdata;
                                }
                                else
                                {
                                     $data['cancel_order_detail'] =  [];
                                }
                               
                        }
                        $all_items      =   SaleorderItems::where('sales_id',$sales->id)->get(); 
                        foreach($all_items  as $items)
                        {
                            $prdId      =   $items->prd_id;
                            $products   =   Product::where('id',$prdId)->first();
                            
                            $prd['sale_items_id']     =   $items->id;
                            $prd['product_id']        =   $prdId;
                            $prd['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                            $prd['actual_price']      =   $products->prdPrice->price;
                            $prd['sale_price']        =   $items->price;
                            $prd['currency']          =   getCurrency()->name;
                            $prd['quantity']          =   $items->qty;
                            $prd['product_image']     =   $this->get_product_image($products->id);
                            $return_order = SalesOrderReturn::where('sales_item_id',$items->id)->where('is_deleted',0)->first();
                            // $data['return_detail']       =       array();
                            if($return_order)
                            {
                                $rtrn['status']      =   $return_order->status;
                                $rtrn['id']          =   $return_order->id;
                                $rtrn['payment_status']     =   $return_order->payment_status;
                                $prd['return_detail']       =       $rtrn;
                            }
                            else
                            {
                                $prd['return_detail']       =       [];
                            }
                            $data['products'][]       =       $prd;
                         }
                         $data['auction_status']=   $au_status;
                         $data['bid_charge']    =   $charge;
                         $adddr   =   SalesOrderAddress::where('sales_id',$request->sale_id)->first();
                         $baddr['address_type'] = $adddr->type->usr_addr_typ_name;
                         $baddr['name']         = $adddr->name; 
                         $baddr['phone']        = $adddr->phone; 
                         $baddr['email']        = $adddr->email; 
                         $baddr['address1']     = $adddr->address1; 
                         $baddr['address2']     = $adddr->address2; 
                         $baddr['zip_code']     = $adddr->zip_code;      
                         $baddr['country']      = $adddr->bcountry->country_name; 
                         $baddr['state']        = $adddr->bstate->state_name; 
                         $baddr['city']         = $adddr->bcity->city_name; 
                         $baddr['latitude']     = $adddr->latitude; 
                         $baddr['longitude']    = $adddr->longitude;
                         $data['billing_address'] =  $baddr;

                         $saddr['address_type'] = $adddr->stype->usr_addr_typ_name;
                         $saddr['name']         = $adddr->s_name; 
                         $saddr['phone']        = $adddr->s_phone; 
                         $saddr['email']        = $adddr->s_email; 
                         $saddr['address1']     = $adddr->s_address1; 
                         $saddr['address2']     = $adddr->s_address2; 
                         $saddr['zip_code']     = $adddr->s_zip_code;      
                         $saddr['country']      = $adddr->scountry->country_name; 
                         $saddr['state']        = $adddr->sstate->state_name; 
                         $saddr['city']         = $adddr->scity->city_name; 
                         $saddr['latitude']     = $adddr->s_latitude; 
                         $saddr['longitude']    = $adddr->s_longitude;
                         $data['shipping_address'] =  $saddr;

                         return array('httpcode'=>'200','status'=>'success','message'=>'Order Detail','data'=>['order_detail'=>$data]);
                    }
                    else
                    {
                       return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Sale not found!']);
                    }
                    
                }
        }
        else{ return invalidToken(); }
    }
    public function invoice(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['sale_id']    = 'required|numeric';
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->where('id',$formData['sale_id'])->first(); 
                    if($sales)
                    {
                        $pay      =   SalesOrderPayment::where('sales_id',$request->sale_id)->first();
                        $seller   =   SellerInfo::where('seller_id',$sales->seller_id)->first();
                        $ship     =   SalesOrderShippingStatus::where('sales_id',$request->sale_id)->first();
                        $histories =  AuctionHist::where('user_id',$user_id)->where('sale_id',$request->sale_id)->where('is_deleted',0)->where('is_active',1)->orderBy('created_at', 'desc');
                        $auctionwin = Auction::where('bid_allocated_to',$user_id)->where('sale_id',$request->sale_id)->where('status','closed')->where('is_deleted',0)->where('is_active',1);
                        if($histories->count() > 0)
                        {
                            if($auctionwin->count() > 0)
                            {
                                $au_status=   'True';
                                $charge    =   $sales->bid_charge;
                            }
                            else
                            {
                                $au_status=   'False';
                                $charge    =   0;
                            }
                        }
                        else
                        {
                            $au_status =   'False';
                            $charge    =   0;
                        }
                        $data['order_id']          =   $sales->order_id;
                        $data['order_date']        =   date('Y-m-d',strtotime($sales->created_at));
                        $data['order_time']        =   date('g:i a',strtotime($sales->created_at));
                        $data['pay_method']        =   $pay->payment_type;
                        $data['sub_total']         =   $sales->total;
                        $data['shipping']          =   $sales->shiping_charge;
                        $tot                       =   $data['sub_total'] + $data['shipping'];
                        $data['total']             =   $tot;
                        $data['promotion']         =   $sales->discount;
                        $data['tax']               =   $sales->tax;
                        $data['grand_total']       =   $sales->g_total;
                        $data['delivered_date']    =   '';
                        $data['return_date']       =   '';
                        if($ship)
                        {
                             $data['delivery_status']   =   $ship->status;
                             if($ship->status == 'delivered')
                             {
                                $data['delivered_date']    =   date('Y-m-d',strtotime($ship->updated_at));;
                                $data['return_date']   =    date('Y-m-d',strtotime($ship->updated_at. ' + 2 days'));
                             }
                        }
                        else
                        {
                            $data['delivery_status']   =   'Pending';
                        }
                        
                        $data['sold_by']            =   $seller->fname;
                        $all_items      =   SaleorderItems::where('sales_id',$sales->id)->get(); 
                        foreach($all_items  as $items)
                        {
                            $prdId      =   $items->prd_id;
                            $products   =   Product::where('id',$prdId)->first();
                            
                            $prd['sale_items_id']=   $items->id;
                            $prd['product_id']   =   $prdId;
                            $prd['product_name'] =   $this->get_content($products->name_cnt_id,$lang);
                            $prd['short_desc']   =   $this->get_content($products->short_desc_cnt_id,$lang);
                            $prd['desc']         =   $this->get_content($products->desc_cnt_id,$lang);
                            $prd['sale_price']   =   $items->price;
                            $prd['currency']     =   getCurrency()->name;
                            $prd['quantity']     =   $items->qty;
                            $data['products'][]  =       $prd;
                         }
                         $data['auction_status']=   $au_status;
                         $data['bid_charge']    =   $charge;
                         $adddr   =   SalesOrderAddress::where('sales_id',$request->sale_id)->first();
                         $baddr['address_type'] = $adddr->type->usr_addr_typ_name;
                         $baddr['name']         = $adddr->name; 
                         $baddr['phone']        = $adddr->phone; 
                         $baddr['email']        = $adddr->email; 
                         $baddr['address1']     = $adddr->address1; 
                         $baddr['address2']     = $adddr->address2; 
                         $baddr['zip_code']     = $adddr->zip_code;      
                         $baddr['country']      = $adddr->bcountry->country_name; 
                         $baddr['state']        = $adddr->bstate->state_name; 
                         $baddr['city']         = $adddr->bcity->city_name; 
                         $baddr['latitude']     = $adddr->latitude; 
                         $baddr['longitude']    = $adddr->longitude;
                         $data['billing_address'] =  $baddr;

                         $saddr['address_type'] = $adddr->stype->usr_addr_typ_name;
                         $saddr['name']         = $adddr->s_name; 
                         $saddr['phone']        = $adddr->s_phone; 
                         $saddr['email']        = $adddr->s_email; 
                         $saddr['address1']     = $adddr->s_address1; 
                         $saddr['address2']     = $adddr->s_address2; 
                         $saddr['zip_code']     = $adddr->s_zip_code;      
                         $saddr['country']      = $adddr->scountry->country_name; 
                         $saddr['state']        = $adddr->sstate->state_name; 
                         $saddr['city']         = $adddr->scity->city_name; 
                         $saddr['latitude']     = $adddr->s_latitude; 
                         $saddr['longitude']    = $adddr->s_longitude;
                         $data['shipping_address'] =  $saddr;

                         return array('httpcode'=>'200','status'=>'success','message'=>'invoice','data'=>['order_detail'=>$data]);
                    }
                    else
                    {
                       return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Sale not found!']);
                    }
                    
                }
        }
        else{ return invalidToken(); }
    }
    public function cancel_request(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['sale_id']     = 'required|numeric';
            $rules['reason']      = 'required|string';
            $rules['notes']       = 'required|string';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $sales =  SalesOrder::where('cust_id',$user_id)->where('id',$formData['sale_id'])->first(); 
                    if($sales)
                    {
                        SalesOrder::where('id',$formData['sale_id'])->update(['cancel_process'=>1]);
                        $ordercancel = SalesOrderCancel::create(['sales_id' => $sales->id,
                        'seller_id' => $sales->seller_id,
                        'created_by' => $user_id,
                        'customer_id' => $sales->cust_id,
                        'role_id' => 5,
                        'status' => 'pending']);
                        SalesOrderCancelNote::create(['cancel_id' => $ordercancel->id,
                        'created_by' => $user_id,
                        'role_id' => 5,
                        'title' => $formData['reason'],
                        'note' => $formData['notes']]);
                        return array('httpcode'=>'200','status'=>'success','message'=>'Request sent','data'=>['message' =>'Your cancel request sent successfully!']);
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Order not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }

    public function seller_req_list(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->where('cancel_process',1)->get(); 
                    if($sales->count() > 0)
                    {
                        foreach($sales  as $row)
                        {
                            $cancelorders  = SalesOrderCancel::where('sales_id',$row->id)->where('role_id',3)->orderBy('id', 'DESC')->first();
                            if($cancelorders)
                            {
                                $all_items      =   SaleorderItems::where('sales_id',$row->id)->get(); 
                                $calcelnotes    =   SalesOrderCancelNote::where('cancel_id',$cancelorders->id)->first();
                                foreach($all_items  as $items)
                                {
                                    $prdId      =   $items->prd_id;
                                    $products   =   Product::where('id',$prdId)->first();
                                    $data['cancel_id']         =   $cancelorders->id;
                                    $data['order_id']          =   $row->order_id;
                                    $data['seller_id']         =   $row->seller_id;
                                    $data['sale_items_id']     =   $items->id;
                                    $data['product_id']        =   $prdId;
                                    $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                                    $data['price']             =   $items->row_total;
                                    $data['currency']          =   getCurrency()->name;
                                    $data['product_image']     =   $this->get_product_image($products->id);
                                    $data['quantity']          =   $items->qty;
                                    $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                    $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                                    $data['delivery_status']   =   $row->shipping_status;
                                    $data['cancel_notes']       =  $calcelnotes->note;
                                    $val[] = $data;
                                }
                            }
                          
                        }
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'Seller request to customer','data'=>['request_list'=>$val]);
                }
        }
        else{ return invalidToken(); }
    }

    public function seller_past_list(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->whereIn('cancel_process',[2,3])->get(); 
                    if($sales->count() > 0)
                    {
                        foreach($sales  as $row)
                        {
                            $cancelorders  = SalesOrderCancel::where('sales_id',$row->id)->where('role_id',3)->orderBy('id', 'DESC')->first();
                            if($cancelorders)
                            {
                                $all_items      =   SaleorderItems::where('sales_id',$row->id)->get(); 
                                $calcelnotes    =   SalesOrderCancelNote::where('cancel_id',$cancelorders->id)->first();
                                foreach($all_items  as $items)
                                {
                                    $prdId      =   $items->prd_id;
                                    $products   =   Product::where('id',$prdId)->first();
                                    $data['cancel_id']         =   $cancelorders->id;
                                    $data['order_id']          =   $row->order_id;
                                    $data['sale_items_id']     =   $items->id;
                                    $data['product_id']        =   $prdId;
                                    $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                                    $data['price']             =   $items->row_total;
                                    $data['currency']          =   getCurrency()->name;
                                    $data['product_image']     =   $this->get_product_image($products->id);
                                    $data['quantity']          =   $items->qty;
                                    $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                    $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                                    $data['delivery_status']   =   $row->shipping_status;
                                    $data['cancel_notes']      =  $calcelnotes->note;
                                    $data['cancel_response']   =  $calcelnotes->response;
                                    $val[] = $data;
                                }
                            }
                          
                        }
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'Seller past request to customer','data'=>['past_request_list'=>$val]);
                }
        }
        else{ return invalidToken(); }
    }

    public function cust_req_list(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->where('cancel_process',1)->get(); 
                    if($sales->count() > 0)
                    {
                        foreach($sales  as $row)
                        {
                            $cancelorders  = SalesOrderCancel::where('sales_id',$row->id)->where('role_id',5)->orderBy('id', 'DESC')->first();
                            if($cancelorders)
                            {
                                $all_items      =   SaleorderItems::where('sales_id',$row->id)->get(); 
                                $calcelnotes    =   SalesOrderCancelNote::where('cancel_id',$cancelorders->id)->first();
                                foreach($all_items  as $items)
                                {
                                    $prdId      =   $items->prd_id;
                                    $products   =   Product::where('id',$prdId)->first();
                                    $data['cancel_id']         =   $cancelorders->id;
                                    $data['order_id']          =   $row->order_id;
                                    $data['seller_id']         =   $row->seller_id;
                                    $data['sale_items_id']     =   $items->id;
                                    $data['product_id']        =   $prdId;
                                    $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                                    $data['price']             =   $items->row_total;
                                    $data['currency']          =   getCurrency()->name;
                                    $data['product_image']     =   $this->get_product_image($products->id);
                                    $data['quantity']          =   $items->qty;
                                    $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                    $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                                    $data['delivery_status']   =   $row->shipping_status;
                                    $data['cancel_notes']       =  $calcelnotes->note;
                                    $val[] = $data;
                                }
                            }
                          
                        }
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'Customer requests','data'=>['request_list'=>$val]);
                }
        }
        else{ return invalidToken(); }
    }

    public function cust_past_list(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $sales =  SalesOrder::where('cust_id',$user_id)->whereIn('cancel_process',[2,3])->get(); 
                    if($sales->count() > 0)
                    {
                        foreach($sales  as $row)
                        {
                            $cancelorders  = SalesOrderCancel::where('sales_id',$row->id)->where('role_id',5)->orderBy('id', 'DESC')->first();
                            if($cancelorders)
                            {
                                $all_items      =   SaleorderItems::where('sales_id',$row->id)->get(); 
                                $calcelnotes    =   SalesOrderCancelNote::where('cancel_id',$cancelorders->id)->first();
                                foreach($all_items  as $items)
                                {
                                    $prdId      =   $items->prd_id;
                                    $products   =   Product::where('id',$prdId)->first();
                                    $data['cancel_id']         =   $cancelorders->id;
                                    $data['order_id']          =   $row->order_id;
                                    $data['sale_items_id']     =   $items->id;
                                    $data['product_id']        =   $prdId;
                                    $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);
                                    $data['price']             =   $items->row_total;
                                    $data['currency']          =   getCurrency()->name;
                                    $data['product_image']     =   $this->get_product_image($products->id);
                                    $data['quantity']          =   $items->qty;
                                    $data['order_date']        =   date('Y-m-d',strtotime($row->created_at));
                                    $data['order_time']        =   date('g:i a',strtotime($row->created_at));
                                    $data['delivery_status']   =   $row->shipping_status;
                                    $data['cancel_notes']       =  $calcelnotes->note;
                                    $data['cancel_response']   =  $calcelnotes->response;
                                    $val[] = $data;
                                }
                            }
                          
                        }
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'Customer past requests','data'=>['past_request_list'=>$val]);
                }
        }
        else{ return invalidToken(); }
    }

    public function response_request(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['cancel_id']     = 'required|numeric';
            $rules['status']        = 'required|numeric';
            if($request->status == 1)
            {
                $rules['refund_mode']        = 'required|numeric';
                if($request->refund_mode == 2)
                {
                    $rules['bank_name']        = 'required|string';
                    $rules['account_number']   = 'required|string';
                    $rules['branch_name']      = 'required|string';
                    $rules['ifsc_code']        = 'required|string';
                }
            }
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $cancels = SalesOrderCancel::where('id',$formData['cancel_id'])->where('role_id',3)->where('is_deleted',0)->first();
                    if($cancels)
                    {
                        if($formData['status'] == 1)
                        {
                            $status = 'accepted';
                            $ord_status = 'cancelled';
                        }
                        else
                        {
                            $status = 'rejected';
                            $ord_status = 'pending';
                        }
                        $salOrd = SalesOrder::where('id',$cancels->sales_id)->update(['order_status'=>$ord_status,'cancel_process'=>$formData['status']]);
                        $sales = SalesOrder::where('id',$cancels->sales_id)->first();
                        SalesOrderCancel::where('id',$formData['cancel_id'])->update([
                        'status' => $status]);
                        if($formData['status'] == 1)
                        {
                          SalesOrderRefundPayment::create([
                         'ref_id' => $formData['cancel_id'],'source' =>'cancel','refund_mode' => $formData['refund_mode'],'bank_name' => $formData['bank_name'],'account_number' => $formData['account_number'],'branch_name' => $formData['branch_name'],'ifsc_code' => $formData['ifsc_code']]);
                        }
                        return array('httpcode'=>'200','status'=>'success','message'=>'Response sent','data'=>['message' =>'Your cancel response sent successfully!']);
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Cancel request not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }
    
     public function get_profile(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $customer =  CustomerMaster::where('id',$user_id)->first(); 
                    if($customer)
                    {
                        $cust_info = CustomerInfo::where('user_id',$user_id)->first(); 
                        $cust_tele = CustomerTelecom::where('user_id',$user_id)->first();
                        if(!empty($cust_info->country_id)){ $country = $cust_info->country->country_name;} else { $country = '';}
                        if(!empty($cust_info->state_id)){ $state = $cust_info->state->state_name;} else { $state = '';}
                        if(!empty($cust_info->city_id)){ $city = $cust_info->city->city_name;} else { $city = '';}
                        if(!empty($cust_info->profile_image)){ $avatar = config('app.storage_url').'/app/public/customer_profile/'.$cust_info->profile_image;} else { $avatar = config('app.storage_url').'/app/public/no-avatar.png';}
                        if(!empty($cust_info->address)){ $address = $cust_info->address;} else { $address = '';}
                        $data['user_id']        =   $user_id;
                        $data['username']       =   $customer->username;
                        $data['first_name']     =   $user['first_name'];
                        $data['last_name']      =   $user['last_name'];
                        $data['phone']          =   $user['phone'];
                        $data['email']          =   $user['email'];
                        $data['address1']       =   $address;
                     //   $data['pincode']        =   $cust_info->pincode;
                        $data['country']        =   $country;
                        $data['state']          =   $state;
                        $data['city']           =   $city;
                        $data['country_id']     =   $cust_info->country_id;
                        $data['state_id']       =   $cust_info->state_id;
                        $data['city_id']        =   $cust_info->city_id;
                        $data['profile_image']  =   $avatar;
                        $val[] = $data;
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'profile','data'=>['profile'=>$val]);
                }
        }else{ return invalidToken(); }
    }

    public function edit_profile(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['username']   = 'required|string|unique:usr_mst,username,'.$user_id;
            $rules['first_name'] = 'required|string';
            $rules['last_name']  = 'required|string';
            $rules['email']      = 'required_without:phone|nullable|email|max:255|unique:usr_telecom,usr_telecom_value,'.$user_id.',user_id';
            $rules['phone']      = 'required_without:email|nullable|numeric|digits_between:7,12|unique:usr_telecom,usr_telecom_value,'.$user_id.',user_id';
            $rules['address']    = 'required|string';
            $rules['state']      = 'required|numeric';
            $rules['country']    = 'required|numeric';
            $rules['city']       = 'required|numeric';
            $rules['device_id']  = 'required|string';
            $rules['os_type']    = 'required|string';
            $rules['page_url']   = 'required';
            if (array_key_exists("password",$formData))
            {
                if($formData['password']!='')
                {
                    $rules['password']='min:8|required_with:password_confirmation|confirmed';
                }
            }
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $usr_visit = UserVisit::create([
                    'org_id' =>1,
                    'device_id'=>$request->device_id,
                    'is_login'=>1,
                    'os'=>$request->os_type,
                    'url'=>$request->page_url,
                    'visited_on'=>date("Y-m-d H:i:s"),
                    'created_at'=>date("Y-m-d H:i:s"),
                    'updated_at'=>date("Y-m-d H:i:s")]);
                    
                    CustomerMaster::where('id',$user_id)->where('is_deleted',0)->where('is_active',1)->update(['username' => $formData['username']]);

                    if($request->hasFile('profile_img'))
                    {
                    $file=$request->file('profile_img');
                    $extention=$file->getClientOriginalExtension();
                    $filename=time().'.'.$extention;
                    $file->move(('uploads/storage/app/public/customer_profile/'),$filename);
                    }
                    else
                    {
                        $filename='';
                    }

                    $info = CustomerInfo::where('user_id',$user_id)->where('is_deleted',0)->where('is_active',1)->update([
                           'first_name' => $formData['first_name'],
                           'last_name' =>$formData['last_name'],
                           'address' => $formData['address'],
                           'country_id' => $formData['country'],
                           'state_id' =>$formData['state'],
                           'city_id'=>$formData['city'],
                           'profile_image'=>$filename,
                           ]);
                    if (array_key_exists("phone",$formData))
                    {
                        if($formData['phone']!='')
                        {
                            $exist = CustomerTelecom::where('user_id',$user_id)->where('usr_telecom_typ_id',2)->where('is_deleted',0)->where('is_active',1)->first();
                            if($exist)
                            {
                                CustomerTelecom::where('user_id',$user_id)->where('usr_telecom_typ_id',2)->where('is_deleted',0)->where('is_active',1)->update(['usr_telecom_value' => $formData['phone']]);
                            }
                            else
                            {
                                $telecom_ph = CustomerTelecom::create(['org_id' => 1,
                               'user_id' => $user_id,
                               'usr_telecom_typ_id'=>2,
                               'usr_telecom_value'=>$formData['phone'],
                               'is_active'=>1,
                               'is_deleted'=>0,
                               'created_at'=>date("Y-m-d H:i:s"),
                               'updated_at'=>date("Y-m-d H:i:s")]);
                               $ph_tele=$telecom_ph->id;

                               CustomerMaster::where('id',$user_id)->update([
                                   'phone'=>$ph_tele
                               ]);
                            }
                        }
                    }
                    if (array_key_exists("email",$formData))
                    {
                        if($formData['email']!='')
                        {
                            $exist = CustomerTelecom::where('user_id',$user_id)->where('usr_telecom_typ_id',1)->where('is_deleted',0)->where('is_active',1)->first();
                            if($exist)
                            {
                                CustomerTelecom::where('user_id',$user_id)->where('usr_telecom_typ_id',1)->where('is_deleted',0)->where('is_active',1)->update(['usr_telecom_value' => $formData['email']]);
                            }
                            else
                            {
                                $telecom_ph = CustomerTelecom::create(['org_id' => 1,
                               'user_id' => $user_id,
                               'usr_telecom_typ_id'=>1,
                               'usr_telecom_value'=>$formData['email'],
                               'is_active'=>1,
                               'is_deleted'=>0,
                               'created_at'=>date("Y-m-d H:i:s"),
                               'updated_at'=>date("Y-m-d H:i:s")]);
                               $ph_tele=$telecom_ph->id;

                               CustomerMaster::where('id',$user_id)->update([
                                   'email'=>$ph_tele
                               ]);
                            }
                        }
                    }
                    // CustomerAddress::where('user_id',$user_id)->where('is_deleted',0)->where('is_active',1)->where('is_default',1)->update(['address_1' => $formData['address'],
                    //        'country_id' => $formData['country'],
                    //        'state_id' =>$formData['state'],
                    //        'city_id'=>$formData['city'],
                    //        ]);
                    if (array_key_exists("password",$formData))
                    {
                        if($formData['password']!='')
                        {
                            $pass['password_hash']= Hash::make(trim($formData['password']));
                           CustomerSecurity::where('user_id',$user_id)->where('is_deleted',0)->where('is_active',1)->update($pass); 
                        }
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'Profile updated','data'=>['message' =>'Profile updated successfully!']);
                }
        }else{ return invalidToken(); }
    }
    
    public function userAddress(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val       =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $list =  CustomerAddress::where('user_id',$user_id)->where('is_deleted',0)->where('is_active',1)->get();
                    foreach($list  as $row)
                    {
                        if(!empty($row->country_id)){ $country = $row->country->country_name;} else { $country = '';}
                        if(!empty($row->state_id)){ $state = $row->state->state_name;} else { $state = '';}
                        if(!empty($row->city_id)){ $city = $row->city->city_name;} else { $city = '';}

                         $data['id']            =   $row->id;
                         $data['name']          =   $row->name;
                         $data['address_type']  =   $row->type->usr_addr_typ_name;
                         $data['phone']         =   $row->phone;
                         $data['country']       =   $country;
                         $data['state']         =   $state;
                         $data['city']          =   $city;
                         $data['country_id']    =   $row->country_id;
                         $data['state_id']      =   $row->state_id;
                         $data['city_id']       =   $row->city_id;
                         $data['address1']      =   $row->address_1;
                         $data['address2']      =   $row->address_2;
                         $data['pincode']       =   $row->pincode;
                         $data['latitude']      =   $row->latitude;
                         $data['longitude']     =   $row->longitude; 
                         $data['is_default']    =   $row->is_default;  
                         
                         $val[] = $data;
                    }
                   
                    return array('httpcode'=>'200','status'=>'success','message'=>'User address list','data'=>['address_list'=>$val]);
                }
        }else{ return invalidToken(); }
    }

    public function addAddress(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['name']          = 'required|string';
            $rules['address_type']  = 'required|numeric';
            $rules['phone']         = 'required|numeric|digits_between:7,12';
            $rules['country']       = 'required|numeric';
            $rules['state']         = 'required|numeric';
            $rules['city']          = 'required|numeric';
            $rules['address1']      = 'required|string';
            $rules['address2']      = 'required|string';
            $rules['pincode']       = 'required|numeric';
            $rules['latitude']      = 'numeric';
            $rules['longitude']     = 'numeric';
            $rules['is_default']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $data['user_id']            = $user_id;
                    $data['name']               = $formData['name'];
                    $data['usr_addr_typ_id']    = $formData['address_type'];
                    $data['phone']              = $formData['phone'];
                    $data['country_id']         = $formData['country'];
                    $data['state_id']           = $formData['state'];
                    $data['city_id']            = $formData['city'];
                    $data['address_1']          = $formData['address1'];
                    $data['address_2']          = $formData['address2'];
                    $data['pincode']            = $formData['pincode'];
                    $data['latitude']           = $formData['latitude'];
                    $data['longitude']          = $formData['longitude'];
                    $data['created_by']         = $user_id;
                    $data['updated_by']         = $user_id;
                    $exist = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->first();
                    if($exist)
                    {
                        $data['is_default'] = $formData['is_default'];
                        if($formData['is_default'] == 1)
                        {
                            $dafault = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_default',1)->where('is_deleted',0)->update(['is_default' => 0]);
                        }
                    }
                    else
                    {
                        $data['is_default']         = 1;
                    }
                    CustomerAddress::create($data);
                    return array('httpcode'=>'200','status'=>'success','message'=>'Address added','data'=>['message' =>'Your address added successfully!']);
                }
        }else{ return invalidToken(); }
    }

    public function editAddress(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['address_id']    = 'required|numeric';
            $rules['name']          = 'required|string';
            $rules['address_type']  = 'required|numeric';
            $rules['phone']         = 'required|numeric|digits_between:7,12';
            $rules['country']       = 'required|numeric';
            $rules['state']         = 'required|numeric';
            $rules['city']          = 'required|numeric';
            $rules['address1']      = 'required|string';
            $rules['address2']      = 'required|string';
            $rules['pincode']       = 'required|numeric';
            $rules['latitude']      = 'numeric';
            $rules['longitude']     = 'numeric';
            $rules['is_default']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $data['name']               = $formData['name'];
                    $data['usr_addr_typ_id']    = $formData['address_type'];
                    $data['phone']              = $formData['phone'];
                    $data['country_id']         = $formData['country'];
                    $data['state_id']           = $formData['state'];
                    $data['city_id']            = $formData['city'];
                    $data['address_1']          = $formData['address1'];
                    $data['address_2']          = $formData['address2'];
                    $data['pincode']            = $formData['pincode'];
                    $data['latitude']           = $formData['latitude'];
                    $data['longitude']          = $formData['longitude'];
                    $data['updated_by']         = $user_id;
                    $exist = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->where('id',$formData['address_id'])->first();
                    if($exist)
                    {
                        if($formData['is_default'] == 1)
                        {
                            $dafault = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_default',1)->where('is_deleted',0)->update(['is_default' => 0]);
                            $data['is_default']         = $formData['is_default'];
                        }
                        else if($formData['is_default'] == 0)
                        {
                            $currentData = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->where('is_default',1)->where('id',$formData['address_id'])->first();
                            if($currentData)
                            {
                                $existData = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('id','!=' ,$formData['address_id'])->where('is_deleted',0)->first();
                                if($existData)
                                {
                                    CustomerAddress::where('user_id',$user_id)->where('id','!=' ,$formData['address_id'])->where('is_active',1)->where('is_deleted',0)->take(1)->update(['is_default'=>1]);
                                    $data['is_default']         = $formData['is_default'];
                                }
                            }
                        }
                        CustomerAddress::where('id',$formData['address_id'])->update($data);
                        return array('httpcode'=>'200','status'=>'success','message'=>'Address updated','data'=>['message' =>'Your address updated successfully!']);
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not found','data'=>['message' =>'Address not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }

    public function deleteAddress(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['address_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    
                    $exist = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->where('id',$formData['address_id'])->first();
                    if($exist)
                    {
                        $default_exist = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->where('is_default',1)->where('id',$formData['address_id'])->first();
                        if($default_exist)
                        {
                            $data['is_deleted']         = 1;
                            CustomerAddress::where('id',$formData['address_id'])->update($data);

                            $existData = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->first();
                            if($existData)
                            {
                                CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->take(1)->update(['is_default'=>1]);
                            }
                        }
                        else
                        {
                            $data['is_deleted']         = 1;
                            CustomerAddress::where('id',$formData['address_id'])->update($data);
                        }
                        return array('httpcode'=>'200','status'=>'success','message'=>'Address removed','data'=>['message' =>'Your address removed successfully!']);
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not found','data'=>['message' =>'Address not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }
    
    public function defaultAddress(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $data = [];
            $rules      =   array();
            $rules['address_id']    = 'required|numeric';
            $rules['is_default']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    
                    $exist = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->where('id',$formData['address_id'])->first();
                    if($exist)
                    {
                        if($formData['is_default'] == 1)
                        {
                            $dafault = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_default',1)->where('is_deleted',0)->update(['is_default' => 0]);
                            $data['is_default']         = $formData['is_default'];
                        }
                        else if($formData['is_default'] == 0)
                        {
                            $currentData = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('is_deleted',0)->where('is_default',1)->where('id',$formData['address_id'])->first();
                            if($currentData)
                            {
                                $existData = CustomerAddress::where('user_id',$user_id)->where('is_active',1)->where('id','!=' ,$formData['address_id'])->where('is_deleted',0)->first();
                                if($existData)
                                {
                                    CustomerAddress::where('user_id',$user_id)->where('id','!=' ,$formData['address_id'])->where('is_active',1)->where('is_deleted',0)->take(1)->update(['is_default'=>1]);
                                    $data['is_default']         = $formData['is_default'];
                                }
                            }
                        }
                        CustomerAddress::where('id',$formData['address_id'])->update($data);
                        return array('httpcode'=>'200','status'=>'success','message'=>'Default address updated','data'=>['message' =>'Default address updated successfully!']);
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not found','data'=>['message' =>'Address not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }
    
    function logout(Request $request){
        if($user = validateToken($request->post('access_token'))){ 
            $user_id    =   $user['user_id'];
            CustomerLogin::where('user_id',$user_id)->update(['is_login'=>0,'access_token'=>NULL]);
            return array('httpcode'=>'200','status'=>'success','message'=>'Logged out successfully!','data'=>array('message'=>'You are logged out successfully'));     
        }else{ return invalidToken(); }
    }

    public function return_request(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['sale_id']      = 'required|numeric';
            $rules['quantity']     = 'required|numeric|min:1';
            $rules['product_id']   = 'required|numeric';
            $rules['reason']       = 'required|string';
            // $rules['message']      = 'required|string';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $sales =  SalesOrder::where('cust_id',$user_id)->where('id',$formData['sale_id'])->first(); 
                    if($sales)
                    {
                        $prds =  SaleorderItems::where('sales_id',$formData['sale_id'])->where('prd_id',$formData['product_id'])->first();
                        if($prds)
                        {
                            if($formData['quantity'] > $prds->qty)
                            {
                                return array('httpcode'=>'400','status'=>'error','message'=>'Quantity exceeds','data'=>['message' =>'Quantity exceeds!']);
                            }
                            else
                            {
                                $amt = $prds->price * $prds->qty; 
                                $orderreturn = SalesOrderReturn::create(['sales_id' => $formData['sale_id'],'seller_id' => $sales->seller_id,'user_id' => $user_id,'sales_item_id' => $prds->id,'prd_id' => $formData['product_id'],'qty' => $formData['quantity'],'amount' =>  $amt,'reason' =>  $formData['reason'],'desc' =>  $formData['message'],'issue_item'=>$formData['issue_item'],'status'=>"return_initiated"]);
                                SalesOrderReturnStatus::create(['sales_id' => $formData['sale_id'],
                                'return_id' => $orderreturn->id,
                                'status' => 'return_initiated']);
                                return array('httpcode'=>'200','status'=>'success','message'=>'Request sent','data'=>['message' =>'Your return request sent successfully!','return_id' =>$orderreturn->id]);
                            }
                       
                        }
                        else
                        {
                             return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Product not found!']);
                        }
                    }
                       
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Order not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }

    public function usageCoupon(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val       =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            if (array_key_exists("start_date",$formData))
            {
                if($formData['start_date']!='')
                {
                    $rules['start_date']    = 'required|date_format:Y-m-d|before:end_date';
                    $rules['end_date']      = 'required|date_format:Y-m-d';
                }
            }
            
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $coupen =  CouponHist::where('user_id',$user_id);

                    if (array_key_exists("start_date",$formData))
                    {
                        if($formData['start_date']!='')
                        {
                            $coupen = $coupen->whereDate('created_at', '>=', $formData['start_date'])
                            ->whereDate('created_at', '<=', $formData['end_date']);
                        }
                    }
                    $list = $coupen->get();
                    foreach($list  as $row)
                    {
                         $orderId                   =   $row->order_id;
                         $orders = SalesOrder::where('id',$orderId)->first();
                         $data['id']                =   $row->id;
                         $data['order_id']          =   $orderId;
                         $data['purchase_date']     =   date('Y-m-d',strtotime($orders->created_at));
                         $data['order_value']       =   $orders->g_total;
                         $data['coupon_code']       =   $row->coupon->ofr_code;
                         $data['coupon_value']      =   $orders->discount;
                         $data['created_at']        =   date('Y-m-d',strtotime($row->created_at));
                         $val[] = $data;
                    }
                   
                    return array('httpcode'=>'200','status'=>'success','message'=>'coupons','data'=>['coupons'=>$val]);
                }
        }else{ return invalidToken(); }
    }

    public function recent_views(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val        =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['lang_id']    = 'required|numeric';
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $lang =  $request->lang_id;
                    $views =  Prd_Recent_View::where('user_id',$user_id)->first();   
                    if($views)
                    {
                        $prdIds = $views->prd_id;  
                        $prdId  = explode(",",$prdIds);  
                        foreach($prdId  as $pId)
                        {
                         $avaliable = Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('id',$pId)->first();
                          $products = Product::where('id',$pId)->first();

                         $data['product_id']        =   $pId;
                         $data['product_name']      =   $this->get_content($products->name_cnt_id,$lang);;
                         $data['product_rating']    =   $this->get_rates($products->id);
                         $data['actual_price']      =   $products->prdPrice->price;
                         $data['currency']          =   getCurrency()->name;
                         $data['sale_price']        =   $this->get_sale_price($products->id);
                         $data['product_image']     =   $this->get_product_image($products->id);
                         $data['shop_name']         =   $products->Store($products->seller_id)->store_name;
                         if($avaliable == NULL)
                         {
                            $data['status']         =   'Unavaliable';
                         }
                         else
                         {
                         $data['status']            =   'Avaliable';
                         }
                         $val[] = $data;
                         }
                        
                    }
                    else
                    {
                       $val        =   []; 
                    }
                    return array('httpcode'=>'200','status'=>'success','message'=>'Recent views','data'=>['recent_views'=>$val]);
                }
        }else{ return invalidToken(); }
    }

    public function wallet_amount(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val       =   [];
            $formData   =   $request->all(); 
            $rules      =   array();
            if (array_key_exists("search",$formData))
            {
                if($formData['search']!='')
                {
                    $rules['search']    = 'string';
                    
                }
            }

            if (array_key_exists("start_date",$formData))
            {
                if($formData['start_date']!='')
                {
                    $rules['start_date']    = 'required|date_format:Y-m-d|before:end_date';
                    $rules['end_date']      = 'required|date_format:Y-m-d';
                }
            }
            
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $wallet =  CustomerWallet_Model::where('user_id',$user_id);
                    $credit = $wallet->sum('credit');
                    $debit = $wallet->sum('debit');
                    $total = $credit - $debit;
                    $total = number_format($total,2);
                    if (array_key_exists("search",$formData))
                    {
                        if($formData['search']!='')
                        {
                            $wallet = $wallet->where('source', 'like', '%' . $formData['search'] . '%')
                            ->orWhere('credit', 'like', '%' . $formData['search'] . '%')
                            ->orWhere('debit', 'like', '%' . $formData['search'] . '%');
                        }
                    }
                    if (array_key_exists("start_date",$formData))
                    {
                        if($formData['start_date']!='')
                        {
                            $wallet = $wallet->whereDate('created_at', '>=', $formData['start_date'])
                            ->whereDate('created_at', '<=', $formData['end_date']);
                        }
                    }
                    $list = $wallet->get();
                    foreach($list  as $row)
                    {
                         $orderId                   =   $row->order_id;
                         $orders = SalesOrder::where('id',$orderId)->first();
                         $data['id']                =   $row->id;
                         $data['source']            =   $row->source;
                         $data['credit']            =   $row->credit;
                         $data['debit']             =   $row->debit;
                         $data['created_at']        =   date('Y-m-d',strtotime($row->created_at));
                         $val[] = $data;
                    }
                   
                    return array('httpcode'=>'200','status'=>'success','message'=>'wallet','data'=>['wallet'=>$val,'total_balance'=>$total]);
                }
        }else{ return invalidToken(); }
    }
    
    public function notifications(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $val       =   [];
            $formData   =   $request->all(); 
            
            $list =  UsrNotification::where('notify_to',$user_id)->where('status',1)->get();
            foreach($list  as $row)
            {

                 $data['id']           =    $row->id;
                 $data['title']        =    $row->title;
                 $data['description']  =    $row->description;
                 $data['ref_id']       =    $row->ref_id;
                 $data['ref_link']     =    url($row->ref_link);
                 $data['viewed']       =    $row->viewed;
                 $data['created_at']   =    date('Y-m-d h:i:s',strtotime($row->created_at));
                 $val[] = $data;
            }
                   
            return array('httpcode'=>'200','status'=>'success','message'=>'All notifications','data'=>['notifications'=>$val]);
                
        }else{ return invalidToken(); }
    }
    public function return_shipment(Request $request)
    {
        if($user = validateToken($request->post('access_token')))
        {
            $user_id    =   $user['user_id'];
            $formData   =   $request->all(); 
            $rules      =   array();
            $rules['return_id']            = 'required|numeric';
            $rules['shipment_detail']      = 'required';
            $rules['shipment_bill']        = 'required';
            $rules['refund_mode']          = 'required|numeric';
            if($request->refund_mode == 2)
            {
                $rules['bank_name']        = 'required|string';
                $rules['account_number']   = 'required|string';
                $rules['branch_name']      = 'required|string';
                $rules['ifsc_code']        = 'required|string';
            }
            $validator  =   Validator::make($request->all(), $rules);
            if ($validator->fails()) 
                {
                    foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; $errorMag[] = $row[0]; }  
                    return array('httpcode'=>'400','status'=>'error','message'=>$errorMag[0],'data'=>array('errors' =>(object)$error));
                }
            else
                { 
                    $return = SalesOrderReturn::where('id',$formData['return_id'])->where('is_deleted',0)->first();
                    if($return)
                    {
                        if($request->hasFile('shipment_bill'))
                        {
                        $file=$request->file('shipment_bill');
                        $extention=$file->getClientOriginalExtension();
                        $filename='bill_'.$formData['return_id'].'.'.$extention;
                        $file->move(('uploads/storage/app/public/shipment_bills/'),$filename);
                        }
                        else
                        {
                            $filename='';
                        }
                        $refundcharge = SettingOther::first();
                        SalesOrderReturn::where('id',$formData['return_id'])->update(['status'=>'shipment_initiated']);
                        SalesOrderReturnShipment::create(['return_id' => $formData['return_id'],'description' => $formData['shipment_detail'],'document' => 'app/public/shipment_bills/'.$filename]);
                        $tot = $return->amount;
                        $gtot = $tot - $refundcharge->refund_deduction;
                        SalesOrderRefundPayment::create([
                         'ref_id' => $formData['return_id'],'sales_id' => $return->sales_id,'source' =>'return','refund_mode' => $formData['refund_mode'],'total' => $tot,'refund_tax' => $refundcharge->refund_deduction,'grand_total' => $gtot,'bank_name' => $formData['bank_name'],'account_number' => $formData['account_number'],'branch_name' => $formData['branch_name'],'ifsc_code' => $formData['ifsc_code']]);

                         SalesOrderReturnStatus::create(['sales_id' => $return->sales_id,
                                'return_id' => $formData['return_id'],
                                'status' => 'shipment_initiated']);

                        return array('httpcode'=>'200','status'=>'success','message'=>'Shipment submitted','data'=>['message' =>'Your shipment details sent successfully!']);
                    }
                    else
                    {
                        return array('httpcode'=>'400','status'=>'error','message'=>'Not Found','data'=>['message' =>'Return request not found!']);
                    }
                }
        }else{ return invalidToken(); }
    }
}
