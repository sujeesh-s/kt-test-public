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
use App\Models\Subcategory;
use App\Models\Store;
use App\Models\SellerReview;
use App\Models\SaleOrder;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\PrdAdminImage;
use App\Models\ProductDaily;
use App\Models\PrdAssignedTag;
use App\Models\PrdReview;
use App\Models\PrdShock_Sale;
use App\Models\PrdShockingSaleProduct;
use App\Models\PrdPrice;
use App\Models\Prd_Recent_View;
use App\Models\Productvisitor;
use App\Models\RelatedProduct;
use App\Models\AssignedAttribute;
use App\Models\UsrWishlist;
use App\Models\UserVisit;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;

class ProductController extends Controller
{
    
    //Product By category

    public function product_by_category(Request $request)
    {
        $lang=$request->lang_id;
        $login=0;
        $user_id=null;
        $user = [];
        $validator=  Validator::make($request->all(),[
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required','url']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
        if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }
        
        $products=[];

        if($request->id!='')
        {
            $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('category_id',$request->id)->paginate(12);
            
            if(!empty($prod_data))
            {
                $usr_visit =UserVisit::create([
                'org_id' =>1,
                'device_id'=>$request->device_id,
                'is_login'=>$login,
                'os'=>$request->os_type,
                'url'=>$request->page_url,
                'visited_on'=>date("Y-m-d H:i:s"),
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else{
                        $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['seller_id']=$row->seller_id;
                    $prd_list['actual_price']=number_format($row->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                    $products[]=$prd_list;
                    
                   }
                }
                
                if(!empty($products))
                {
                    $total_products=$prod_data->total();
                }
                else
                {
                    $total_products=0;
                }

                return ['httpcode'=>200,'status'=>'success','data'=>$products,'total_products'=>$total_products];

            }
            else
            {
               return ['httpcode'=>200,'status'=>'success','message'=>'Product not found']; 
            }

        }
        else
        {
            return ['httpcode'=>200,'status'=>'error','message'=>'Enter valid category'];
        }
    }
    
    //Product by sub category
    public function product_by_subcategory(Request $request)
    {
        $lang=$request->lang_id;
        
         $login=0;
         $user_id=null;
        $user = [];
        
        $validator=  Validator::make($request->all(),[
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required','url']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
            if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }

        if($request->subcat_id!='')
        {
            $products=[];
            $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('sub_category_id',$request->subcat_id)->get();
            if(!empty($prod_data))
            {
                $usr_visit =UserVisit::create([
                'org_id' =>1,
                'device_id'=>$request->device_id,
                'is_login'=>$login,
                'os'=>$request->os_type,
                'url'=>$request->page_url,
                'visited_on'=>date("Y-m-d H:i:s"),
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
                
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['seller_id']=$row->seller_id;
                    $prd_list['actual_price']=number_format($row->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                    $products[]=$prd_list;
                    
                    }
                }

                return ['httpcode'=>200,'status'=>'success','data'=>['products'=>$products]];

            }
            else
            {
               return ['httpcode'=>404,'status'=>'error','message'=>'Product not found']; 
            }

        }
        else
        {
            return ['httpcode'=>404,'status'=>'error','message'=>'Enter valid subcategory'];
        }
    }

    //Product by brand
    public function product_by_brand(Request $request)
    {
        $lang=$request->lang_id;
        
        $login=0;
        $user_id=null;
        $user = [];
        
        $validator=  Validator::make($request->all(),[
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required','url']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
            if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }

        if($request->brand_id!='')
        {
            $products=[];
            $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('brand_id',$request->brand_id)->get();
            if(!empty($prod_data))
            {
                $usr_visit =UserVisit::create([
                'org_id' =>1,
                'device_id'=>$request->device_id,
                'is_login'=>$login,
                'os'=>$request->os_type,
                'url'=>$request->page_url,
                'visited_on'=>date("Y-m-d H:i:s"),
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
                
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['seller_id']=$row->seller_id;
                    $prd_list['actual_price']=number_format($row->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                    $products[]=$prd_list;
                    }
                }

                return ['httpcode'=>200,'status'=>'success','data'=>['products'=>$products]];

            }
            else
            {
               return ['httpcode'=>404,'status'=>'error','message'=>'Product not found']; 
            }

        }
        else
        {
            return ['httpcode'=>404,'status'=>'error','message'=>'Enter valid subcategory'];
        }
    }

    //product Detail page
     public function product_detail(Request $request)
    {
        $login=0;
        $user_id=null;
        if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $user_id = $user['user_id'];
            $checkprd =  UsrWishlist::where('user_id',$user_id)->where('prd_id',$request->id)->where('is_deleted',0)->first();
            if($checkprd)
            {
                $wishlist = 1;
            }
            else
            {
                $wishlist = 0;
            }
            $login=1;
        }
        else
        {
            $wishlist = 0;
        }
        
        $lang=$request->lang_id;
        if($request->id != '')
        {
            $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('id',$request->id)->first();
            $prd_rel=RelatedProduct::where('prd_id',$request->id)->where('is_deleted',0)->get();
            
            if(!empty($prod_data))
            {
                $store_active = Store::where('is_active',1)->where('seller_id',$prod_data->seller_id)->first();
                    if($store_active)
                    {
                //Insert visitors log
                $usr_product_visit=Productvisitor::create([
                'prd_id' => $request->id,
                'user_id'=>$user_id,
                'device_id'=>$request->device_id,
                'os'=>$request->os_type,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);

                $usr_visit =UserVisit::create([
                'org_id' =>1,
                'device_id'=>$request->device_id,
                'is_login'=>$login,
                'os'=>$request->os_type,
                'url'=>$request->page_url,
                'visited_on'=>date("Y-m-d H:i:s"),
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
                //insert on recent view
                if($request->post('access_token')){
                            $recent_views = Prd_Recent_View::where('user_id',$user_id)->first();
                                if($recent_views)
                                {
                                $recent_prd = $recent_views->prd_id;
                                foreach(explode(",",$recent_prd) as $news)
                                {
                                    $new_array[] = $news;
                                }
                                if(count($new_array)>20)
                                {
                                   array_shift($new_array);
                                }
                                $prd_new_id[] = $request->id; 
                                $merge = array_merge($new_array,$prd_new_id);
                                $unique_arr = array_unique($merge);
                                $new_prd =implode(",", $unique_arr);
                    
                                $create=Prd_Recent_View::where('id',$recent_views->id)->update([
                                'prd_id' => $new_prd,
                                'updated_at'=>date("Y-m-d H:i:s")]);
                                }
                                else
                                {
                                $create=Prd_Recent_View::create([
                                'prd_id' => $request->id,
                                'user_id'=>$user_id,
                                'created_at'=>date("Y-m-d H:i:s"),
                                'updated_at'=>date("Y-m-d H:i:s")]);
                                }
                     }
                
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    if($prod_data->product_type == 2)
                    { 
                        $prd_list['product_type']="config";
                    }
                    else
                    {
                        $prd_list['product_type']="simple";
                    }
                    $prd_list['sku']=$prod_data->sku;
                    $prd_list['seller']=$prod_data->Store($prod_data->seller_id)->store_name;
                    $prd_list['seller_id']=$prod_data->seller_id;
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    if($prod_data->brand_id)
                    {
                    $prd_list['brand_id']=$prod_data->brand_id;
                    $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['is_featured']=$prod_data->is_featured;
                    $prd_list['short_description']=$this->get_content($prod_data->short_desc_cnt_id,$lang);
                    $prd_list['long_description']=$this->get_content($prod_data->desc_cnt_id,$lang);
                    $prd_list['content']=$this->get_content($prod_data->content_cnt_id,$lang);
                    $prd_list['specification']=$this->get_content($prod_data->spec_cnt_id,$lang);
                    if($prod_data->product_type == 1)
                    {    
                    $actual_price = number_format($prod_data->prdPrice->price,2);
                    $prd_list['actual_price_quote']= $actual_price;
                    $prd_list['actual_price']= $prod_data->prdPrice->price;
                    $sale_price =$this->get_sale_price($prod_data->id);
                    $prd_list['sale_price']=(float)$sale_price;
                    $prd_list['in_stock']=$prod_data->prdStock($prod_data->id);
                    if($prod_data->is_out_of_stock==0)
                        {
                            $prd_list['is_out_of_stock']=false;
                        }
                        else
                        {
                            $prd_list['is_out_of_stock']=true;
                        }
                        if($prod_data->out_of_stock_selling==0)
                        {
                            $prd_list['out_of_stock_selling']=false;
                        }
                        else
                        {
                            $prd_list['out_of_stock_selling']=true;
                        }
                    
                    }
                    $prd_list['in_wishlist']=$wishlist;
                    $prd_list['tag']=$this->get_product_tag($prod_data->id,$lang); 
                    $prd_list['image']=$this->get_product_image($prod_data->id);
                     
                    $prd_list['rating']=$this->get_rates($prod_data->id);
                    $products=$prd_list;
                    $associative_prd=[];
                   
                     //ASSOCIATIVE products
                    if($prod_data->product_type == 2)
                    {    
                        $prd_ass = AssociatProduct::where('prd_id',$prod_data->id)->where('is_deleted',0)->get();
                        if(count($prd_ass)>0)
                        {
                            foreach($prd_ass as $rows)
                            {
                            $product_visibility= Product::where('id',$rows->ass_prd_id)->where('is_active',1)->where('is_deleted',0)->where('visible',0)->where('is_approved',1)->first();
                            if($product_visibility){
                            //$associative_prd[]=$this->ass_related_product($rows->ass_prd_id,$lang);
                            
                                $associative_prd[]=$this->ass_related_product1($product_visibility->id,$lang);
                            
                            
                             }
                            }
                        }
                        else
                        {
                            $associative_prd=[];
                        }
                  }
                  else
                  {
                    $associative_prd=[];
                  }

                    //related products
                    $prd_rel=RelatedProduct::where('prd_id',$request->id)->where('is_deleted',0)->get();
                    if(count($prd_rel)>0)
                    {
                        foreach($prd_rel as $key)
                        {
                        //$related['products']=$this->related_product($key->rel_prd_id);
                        $related_products=$this->related_product($key->rel_prd_id,$lang);

                        }
                        // $order = array_column($related_products, 'rating');
                        // array_multisort($order, SORT_DESC, $related_products);

                    }
                    else
                    {
                        $related_products=[];
                    }

                    //Same Brand products
                    if($prod_data->brand_id!='')
                    {
                        $brand_products=$this->related_brand_product($prod_data->brand_id,$prod_data->id,$lang);
                    }
                    else
                    {
                        $brand_products=[];
                    }

                    //Product review

                    $product_review=$this->get_product_review($prod_data->id,$count='');
                    $product_review_count=$this->get_product_review($prod_data->id,$count=1);

                    //seller information

                    $store_detail=Store::where('seller_id',$prod_data->seller_id)->first();
                        if ($store_detail) {
                            $store_list['store_id']=$store_detail->id;
                            $store_list['seller_id']=$store_detail->seller_id;
                            $store_list['store_name']=$store_detail->store_name;
                            $store_list['store_rating']=$this->get_seller_rating($store_detail->seller_id);
                            $store_list['store_prd_rating']=$this->get_seller_product_rating($store_detail->seller_id);
                            $store_list['join_date']=date('d M Y',strtotime($store_detail->created_at));
                            $store_list['logo']=url($store_detail->logo);
                            $store_list['banner']=url($store_detail->banner);
                            $store_list['no_of_products']=$this->number_of_products($store_detail->seller_id);
                            $store_data[]=$store_list;
                           }
                    
                    //Available offers for this product
            $current_date=Carbon::now();
            $rows = Auction::where('is_active',1)->where('is_deleted',0)->where('bid_allocated_to',0)->where('product_id',$prod_data->id)->whereDate('auct_end','>=',$current_date)->first();
            $deals= Product::where('id',$prod_data->id)->where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('daily_deals',1)->get();
            $shock = PrdShock_Sale::join('prd_shock_sale_products','prd_shock_sale.id','=','prd_shock_sale_products.shock_sale_id')
            ->where('prd_shock_sale.is_active',1)->where('prd_shock_sale.is_deleted',0)->whereDate('prd_shock_sale.start_time','<=',$current_date)->whereDate('prd_shock_sale.end_time','>=',$current_date)
            ->where('prd_shock_sale_products.is_active',1)->where('prd_shock_sale_products.is_deleted',0)->whereRaw("find_in_set($prod_data->id,prd_shock_sale_products.prd_id)")
            ->select('prd_shock_sale.*','prd_shock_sale_products.seller_id','prd_shock_sale_products.prd_id as shock_prd_id')->
            first();
            if($rows)
            {
                $offer['offer_name']= 'Auction';   
                $offer['auction_id']=$rows->id;
                $offer['url']=url('api/customer/auction');
                $offer_list[]=$offer;
            }
            // else if($deals)
            // {
            //     $offer['offer_name']= 'Daily Deals';   
            //     $offer['offer_id']='';
            //     $offer['url']='';
            //     $offer_list[]=$offer;
            // }
            else if($shock)
            {
                $offer['offer_name']= 'Shocking Sale';   
                $offer['offer_id']=$shock->id;
                $offer['url']=url('api/customer/shock-sale');
                $actual_price=$prod_data->prdPrice->price;
                if($shock->discount_type=="amount")
                    {
                        $offer['offer']=getCurrency()->name." ".$shock->discount_value." Off";
                        $discount_value = $shock->discount_value;
                        $unit_price = (int)$actual_price-(int)$discount_value;
                        $offer['offer_price']= $unit_price;
                       

                    }
                    else
                    {
                        $offer['offer']=$shock->discount_value."% Off";
                        $per=$shock->discount_value/100;
                        $per_value = (float)$actual_price*(float)$per;
                        $discount=(float)$actual_price-(float)$per_value;
                        $round= number_format($discount, 2);
                        $offer['offer_price']=$discount;
                    }
                $offer_list[]=$offer;
            }
            else
            {
                $offer_list=[];
            }


                return response()->json(['httpcode'=>200,'status'=>'success','data'=>['product'=>$products,'associative_products'=>$associative_prd,'relative_products'=>$related_products,'brand_products'=>$brand_products,'review'=>$product_review,'total_review'=>$product_review_count,'seller_info'=>$store_data,'offer'=>$offer_list]]);

            }//Active store
            else
            {
                return ['httpcode'=>404,'status'=>'error','message'=>'Inactive store'];
            }
            
            }
            else
            {
               return response()->json(['httpcode'=>404,'status'=>'error','message'=>'Product not found']); 
            }

        }
        else
        {
            return response()->json(['httpcode'=>400,'status'=>'error','message'=>'Enter valid Product id']);
        }
    }

     // DAILY DEAL PRODUCT DETAIL PAGE
    public function daily_deal(Request $request)
    {
        $lang=$request->lang_id;
         //Daily deals

            $daily= ProductDaily::where('is_active',1)->where('is_deleted',0)->where('id',$request->deal_id)->first();
            if($daily)
            {
                   
                    $product_id=$daily->Productdetail($daily->prd_id);
                    $actual_price=$daily->ProductPrice($daily->prd_id)->price;
                    $sale_price=$this->get_sale_price($daily->prd_id);
                    $d_list['deal_id']=$daily->id;
                    $d_list['product_id']=$product_id->id;
                    $d_list['product_name']=$this->get_content($product_id->name_cnt_id,$lang);
                    $d_list['catagory']=$this->get_content($product_id->category->cat_name_cid,$lang);
                    $d_list['sub_catagory']=$this->get_content($product_id->subCategory->sub_name_cid,$lang);
                    if($product_id->brand_id!='')
                    {
                    $d_list['brand']=$this->get_content($product_id->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                        $d_list['brand']="";
                    }
                    $d_list['short_description']=$this->get_content($product_id->short_desc_cnt_id,$lang);
                    $d_list['long_description']=$this->get_content($product_id->desc_cnt_id,$lang);
                    $d_list['content']=$this->get_content($product_id->content_cnt_id,$lang);
                    $d_list['is_out_of_stock']=$product_id->is_out_of_stock;
                    $d_list['seller']=$daily->Store($product_id->seller_id)->store_name;
                    $d_list['actual_price']=number_format($daily->ProductPrice($daily->prd_id)->price,2);
                    $d_list['sale_price']=$this->get_sale_price($daily->prd_id);
                    if($daily->discount_type=="amount")
                    {
                        $d_list['offer']=$daily->discount_value." Off";
                        $d_list['offer_price']=round($actual_price-$daily->discount_value,2);

                    }
                    else
                    {
                        $d_list['offer']=$daily->discount_value."% Off";
                        $per=($daily->discount_value/100)*$actual_price;
                        $discount=(float)$actual_price-(float)$per;
                        $round= number_format($discount, 2);
                        $d_list['offer_price']=$round;
                    }
                    $d_list['rating']=$this->get_rates($daily->prd_id);
                    $d_list['tag']=$this->get_product_tag($product_id->id,$lang);
                    $d_list['image']=$this->get_product_image($product_id->id);
                    if($product_id->product_type==2){
                    $d_list['attributes']=$this->get_product_attributes($product_id->id,$lang);
                    }


                    $daily_product[]=$d_list;
                

            }
            else
            {
                $daily_product=[];
            }

            return response()->json(['httpcode'=>200,'status'=>'success','data'=>$daily_product]);

    }

    public function shocking_sale(Request $request)
    {
        $lang=$request->lang_id;
        
        $login=0;
        $user_id=null;
        $user = [];
        $validator=  Validator::make($request->all(),[
            'shock_sale_id'=>['required','numeric'],
            'product_id'=>['required','numeric'],
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
        if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }
        
        
        //Shocking sales
        $shock_data=[];
           // $current_date=date('Y-m-d H:i:s');
            $current_date=Carbon::now();
            $shock = PrdShockingSaleProduct::join('prd_shock_sale','prd_shock_sale_products.shock_sale_id','=','prd_shock_sale.id')
            ->where('prd_shock_sale.is_active',1)->where('prd_shock_sale.is_deleted',0)->where('prd_shock_sale_products.shock_sale_id',$request->shock_sale_id)->whereRaw("find_in_set($request->product_id,prd_shock_sale_products.prd_id)")
             ->where('prd_shock_sale_products.is_active',1)->where('prd_shock_sale_products.is_deleted',0)
            ->select('prd_shock_sale.*','prd_shock_sale_products.*','prd_shock_sale_products.seller_id','prd_shock_sale_products.prd_id as shock_prd_id')->first();
            // echo $shock->seller_id;die;
            if($shock)
            {
                $store_active = Store::where('is_active',1)->where('seller_id',$shock->seller_id)->first();
                    if($store_active)
                    {
                        //$prd_data=Product::where('id',$shock->prd_id)->first();
                $usr_visit =UserVisit::create([
                            'org_id' =>1,
                            'device_id'=>$request->device_id,
                            'is_login'=>$login,
                            'os'=>$request->os_type,
                            'url'=>$request->page_url,
                            'visited_on'=>date("Y-m-d H:i:s"),
                            'created_at'=>date("Y-m-d H:i:s"),
                            'updated_at'=>date("Y-m-d H:i:s")]);
                            
                $shock_list['shock_sale_id']=$shock->shock_sale_id;
                
                $real_product = Product::where('id',$request->product_id)->first();
                if($real_product){
                
                $shock_list['product_id']=$real_product->id;
                $shock_list['product_name']=$this->get_content($real_product->name_cnt_id,$lang);
                $shock_list['category']=$this->get_content($real_product->category->cat_name_cid,$lang);
                $shock_list['subcategory']=$this->get_content($real_product->subCategory->sub_name_cid,$lang);
                if($real_product->brand_id)
                {
                $shock_list['brand']=$this->get_content($real_product->brand->brand_name_cid,$lang);
                }
                $shock_list['short_desc']=$this->get_content($real_product->short_desc_cnt_id,$lang);
                $shock_list['long_desc']=$this->get_content($real_product->desc_cnt_id,$lang);
                $shock_list['content']=$this->get_content($real_product->content_cnt_id,$lang);
                $shock_list['start_time']=$shock->start_time;
                $shock_list['end_time']=$shock->end_time;
                if($real_product->product_type==1)
                {
                $sale_price=$this->get_sale_price($real_product->id);
                $actual_price=$real_product->prdPrice->price;
                $shock_list['actual_price']=number_format($real_product->prdPrice->price,2);
                $shock_list['sale_price']=$this->get_sale_price($real_product->id);
                if($shock->discount_type=="percentage")
                {
                    $shock_list['offer']=$shock->discount_value."% OFF";
                    $per=($shock->discount_value/100)*$actual_price;
                    $discount=(float)$actual_price-(float)$per;
                    $round= number_format($discount, 2);
                    $shock_list['offer_price']=$round;
                }
                else
                {
                    $shock_list['offer']=$shock->discount_value." OFF";
                    $ofr_price=(float)$actual_price-(float)$shock->discount_value;
                    $shock_list['offer_price']=number_format($ofr_price,2);
                }
                }
                else
                {
                $shock_list['actual_price']="";
                $shock_list['sale_price']="";
                $shock_list['offer']='';
                $shock_list['offer_price']='';
                }
                
                

                $shock_list['rating']=$this->get_rates($real_product->id);
                $shock_list['seller']=$shock->Store($real_product->seller_id)->store_name;
                $shock_list['image']=$this->get_product_image($real_product->id);
                if($real_product->product_type==2)
                {
                //ASSOCIATIVE products
                    if($real_product->product_type == 2)
                    {    
                        $prd_ass = AssociatProduct::where('prd_id',$real_product->id)->where('is_deleted',0)->get();
                        if(count($prd_ass)>0)
                        {
                            foreach($prd_ass as $rows)
                            {
                            $product_visibility= Product::where('id',$rows->ass_prd_id)->where('is_active',1)->where('is_deleted',0)->where('visible',0)->where('is_approved',1)->first();
                            if($product_visibility){
                            //$associative_prd[]=$this->ass_related_product($rows->ass_prd_id,$lang);
                            
                                $associative_prd[]=$this->ass_related_product1($product_visibility->id,$lang);
                            
                            
                             }
                            }
                        }
                        else
                        {
                            $associative_prd=[];
                        }
                  }
                  else
                  {
                    $associative_prd=[];
                  }
        
                $shock_list['attributes']=$associative_prd; 
                }
                
                 $shock_data[]=$shock_list;
                    }//real
                    }
                    return response()->json(['httpcode'=>200,'status'=>'success','data'=>$shock_data]);
            }
            else
            {
                $shock_data=[];
                return response()->json(['httpcode'=>400,'status'=>'error','data'=>'Not found']);
            }
            
    }

    public function auction_product(Request $request)
    {
        $lang=$request->lang_id;
        
        $login=0;
        $user_id=null;
        $user = [];
        $validator=  Validator::make($request->all(),[
            'auction_id' => ['required'],
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required','url']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
        if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }
        
        //Auction
            $current_date=Carbon::now();
            $rows = Auction::where('is_active',1)->where('is_deleted',0)->where('bid_allocated_to',0)->where('id',$request->auction_id)->first();
            if($rows)
            {
                $usr_visit =UserVisit::create([
                            'org_id' =>1,
                            'device_id'=>$request->device_id,
                            'is_login'=>$login,
                            'os'=>$request->os_type,
                            'url'=>$request->page_url,
                            'visited_on'=>date("Y-m-d H:i:s"),
                            'created_at'=>date("Y-m-d H:i:s"),
                            'updated_at'=>date("Y-m-d H:i:s")]);
            
               $max_value=AuctionHist::where('auction_id',$rows->id)->max('bid_price');
                if($max_value)
                {
                    $highest_bid = $max_value;
                }
                else
                {
                    $highest_bid ='';
                }  
                
                if($rows->bid_allocated_to==0)
                {
                    $auction_status = "Open";
                }
                else
                {
                    $auction_status = "Closed";
                }
                            
                $auction_list['auction_id']=$rows->id;
                $auction_list['auction_code']=$rows->auction_code;
                $auction_list['auction_status']=$auction_status;
                $auction_list['product_id']=$rows->product_id;
                $auction_list['product_name']=$this->get_content($rows->Product->name_cnt_id,$lang);
                $auction_list['category']=$this->get_content($rows->Product->category->cat_name_cid,$lang);
                $auction_list['subcategory']=$this->get_content($rows->Product->subCategory->sub_name_cid,$lang);
                if($rows->Product->brand_id)
                {
                $auction_list['brand']=$this->get_content($rows->Product->brand->brand_name_cid,$lang);
                }
                $auction_list['short_desc']=$this->get_content($rows->Product->short_desc_cnt_id,$lang);
                $auction_list['long_desc']=$this->get_content($rows->Product->desc_cnt_id,$lang);
                $auction_list['content']=$this->get_content($rows->Product->content_cnt_id,$lang);
                $auction_list['start_date']=$rows->auct_start;
                $auction_list['end_date']=$rows->auct_end;
                $auction_list['actual_price']=number_format($rows->Product->prdPrice->price,2);
                $auction_list['sale_price']=$this->get_sale_price($rows->product_id);
                $auction_list['min_bid_price']=$rows->min_bid_price;
                $auction_list['latest_bid_amt']=$highest_bid;
                $auction_list['rating']=$this->get_rates($rows->product_id);
                $auction_list['seller']=$rows->Store($rows->Product->seller_id)->store_name;
                $auction_list['seller_id']=$rows->Product->seller_id;
                $auction_list['no_of_bids']=$rows->AuctionHist($rows->id);
                $auction_list['image']=$this->get_product_image($rows->product_id);
                if($rows->Product->product_type==2){
                $auction_list['attributes']=$this->get_product_attributes($rows->product_id,$lang); 
                }
                $start=Carbon::now();
                $end=Carbon::parse($rows->auct_end);
                $difference=$start->diffInDays($end);
                $auction_list['time_gap']=$difference;
                
                 $auction_data=$auction_list;
                 return response()->json(['httpcode'=>200,'status'=>'success','data'=>$auction_data]);
                

            }
            else
            {
                $auction_data=[];
                return response()->json(['httpcode'=>404,'status'=>'Not found']);

            }
    }


    //Shop detail page
    public function shop_detail(Request $request)
    {
        $lang=$request->lang_id;
        
        $login=0;
        $user_id=null;
        $user = [];
        $validator=  Validator::make($request->all(),[
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required','url']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
        if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }
        
        
        $store_detail=Store::where('is_active',1)->where('is_deleted',0)->where('seller_id',$request->seller_id)->first();
                        if ($store_detail) { 
                            
                            $usr_visit =UserVisit::create([
                            'org_id' =>1,
                            'device_id'=>$request->device_id,
                            'is_login'=>$login,
                            'os'=>$request->os_type,
                            'url'=>$request->page_url,
                            'visited_on'=>date("Y-m-d H:i:s"),
                            'created_at'=>date("Y-m-d H:i:s"),
                            'updated_at'=>date("Y-m-d H:i:s")]);
                            
                            $store_list['store_id']=$store_detail->id;
                            $store_list['seller_id']=$store_detail->seller_id;
                            $store_list['store_name']=$store_detail->store_name;
                            $store_list['store_rating']=$this->get_seller_rating($store_detail->seller_id);
                            $store_list['store_prd_rating']=$this->get_seller_product_rating($store_detail->seller_id);
                            $store_list['join_date']=date('d M Y',strtotime($store_detail->created_at));
                            $store_list['about']=$store_detail->about;
                            $store_list['logo']=url($store_detail->logo);
                            $store_list['banner']=url($store_detail->banner);
                            $store_list['no_of_products']=$this->number_of_products($store_detail->seller_id);
                            $tot_order=$this->order_per_seller($store_detail->seller_id);
                            $store_list['total_orders']=$tot_order;
                            $store_list['address_line1']=$store_detail->address;
                            $store_list['address_line2']=$store_detail->address2;
                            $store_list['country']=$store_detail->country->country_name;
                            $store_list['state']=$store_detail->state->state_name;
                            $store_list['city']=$store_detail->city->city_name;
                            $store_data[]=$store_list;

                    $rang_products=[];
                     $dropdown= $this->seller_products($store_detail->seller_id,$lang,$total_pro='');
                    $total_products=$this->seller_products($store_detail->seller_id,$lang,$total_pro='true');
                    $seller_review=$this->get_seller_review($store_detail->id);
                    if(count($dropdown)>0)
                    {
                        foreach($dropdown as $prod_data)
                        {
                            if($prod_data['rating']>3)
                            {
                            $prd_list['product_id']=$prod_data['product_id'];
                    $prd_list['product_name']=$prod_data['product_name'];
                    $prd_list['seller']=$prod_data['seller'];
                    $prd_list['seller_id']=$prod_data['seller_id'];
                    $prd_list['category_id']=$prod_data['category_id'];
                    $prd_list['category_name']=$prod_data['category_name'];
                    $prd_list['subcategory_id']=$prod_data['subcategory_id'];
                   // $prd_list['subcategory_name']=$prod_data->get_content($prod_data->subCategory->sub_name_cid);
                    if($prod_data['brand_id'])
                    {
                    $prd_list['brand_id']=$prod_data['brand_id'];
                    $prd_list['brand_name']=$prod_data['brand_name'];
                    }
                    else
                    {

                        $prd_list['brand_id']="";
                        $prd_list['brand_name']="";
                    }
                    $prd_list['short_description']=$prod_data['short_description'];
                    $prd_list['long_description']=$prod_data['long_description'];
                    $prd_list['content']=$prod_data['content'];
                    $prd_list['actual_price_quote']=$prod_data['actual_price_quote'];
                    $prd_list['actual_price']=$prod_data['actual_price'];
                    $prd_list['sale_price']=$prod_data['sale_price'];
                    $prd_list['is_out_of_stock']=$prod_data['is_out_of_stock'];
                    $prd_list['tag']=$prod_data['tag']; 
                    $prd_list['rating']=$prod_data['rating'];
                    $prd_list['image']=$prod_data['image']; 
                    $rang_products[]             =   $prd_list;
                        }
                        }
                    }
                    else
                    {
                        $rang_products=[];
                    }

        return response()->json(['httpcode'=>200,'status'=>'success','data'=>['shop_detail'=>$store_data,'product'=>$dropdown,'total_products'=>$total_products,'seller_review'=>$seller_review,'best_products'=>$rang_products]]);
                            
                           }

                      else
                      {
                        return response()->json(['httpcode'=>404,'status'=>'Not found']);
                      }     
    }
    
    //Product LISTING page
    public function product_list(Request $request)
    {    
         $lang=$request->lang_id;
         
         $login=0;
         $user_id=null;
        $user = [];
        
        $validator=  Validator::make($request->all(),[
            'device_id' => ['required'],
            'os_type'=> ['required','string','min:3','max:3'],
            'page_url'=>['required','url']
        ]);
        if ($validator->fails()) 
            {    
              return ['httpcode'=>400,'status'=>'error','message'=>'invalid','data'=>['errors'=>$validator->messages()]];
            }
            
            if($request->post('access_token')){
            if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
            $login=1;
        }

         //filter

        $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->paginate(12);
        $products=[];
        
        
            if(!empty($prod_data))
            {
                $usr_visit =UserVisit::create([
                'org_id' =>1,
                'device_id'=>$request->device_id,
                'is_login'=>$login,
                'os'=>$request->os_type,
                'url'=>$request->page_url,
                'visited_on'=>date("Y-m-d H:i:s"),
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
                
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';  
                    }
                    $prd_list['actual_price']=number_format($row->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                    

                    

            $prd_list['offers']=$this->available_offers($row->id,$lang); 
            $products[]=$prd_list;
                    }
                }
                
                if(!empty($products))
                {
                    $total_products=$prod_data->total();
                }
                else
                {
                    $total_products=0;
                }
                return response()->json(['httpcode'=>200,'status'=>'success','data'=>['products'=>$products,'total_products'=>$total_products]]);

            }
            else
            {
               return response()->json(['httpcode'=>200,'status'=>'success','message'=>'Product not found']); 
            }
    }
    
    public function product_list_filter(Request $request)
    {
        $lang=$request->lang_id;

        $category    = $request->category_id;
        $subcategory = $request->subcategory_id;
        $brand       = $request->brand_id;
        $max_price   = $request->max_price;
        $min_price   = $request->min_price;
        $latest      = $request->latest;
        $low_to_high = $request->low_to_high;
        $high_to_low = $request->high_to_low;

        $products=[];
        $query = Product::query();

        
        
        if ($max_price!='' && $min_price!='') {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->whereBetween('prd_prices.price', [$min_price, $max_price])
        ->select('prd_products.*');
        }
        else
        {
            if($low_to_high==1)
            {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->select('prd_products.*')
        ->orderBy('prd_prices.price', 'ASC');
            }

           if($high_to_low==1)
            {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->select('prd_products.*')
        ->orderBy('prd_prices.price', 'DESC');
            } 

          if($category!='' || $subcategory!='' || $brand!='' || $latest!='') 
          { 

            if($low_to_high!=1 && $high_to_low!=1 && $min_price=='' && $max_price==''){
            $query->where('is_active',1)->where('is_deleted',0)->where('is_approved',1);
        $query->when($category, function ($q,$category) {
            return $q->where('category_id', $category);
        });
        $query->when($subcategory, function ($q,$subcategory) {
            return $q->where('sub_category_id', $subcategory);
        });
        $query->when($brand, function ($q,$brand) {
            return $q->where('brand_id', $brand);
        });
        $query->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('created_at','DESC');
        });
            }
          }
        }

        $prod_data = $query->paginate(12);   

            

       
            if(!empty($prod_data))
            {
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';  
                    }
                    if($row->product_type==1)
                    {
                    $prd_list['actual_price']=$row->prdPrice->price;
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    }
                    else
                    {
                    $prd_list['actual_price']='';
                    $prd_list['sale_price']=''; 
                    }
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                  

            $prd_list['offers']=$this->available_offers($row->id,$lang); 
            $products[]=$prd_list;
            
                }//Active store
                }
           
           if(!empty($products))
           {
               $total_products=$prod_data->total();
           }
           else
           {
               $total_products=0;
           }
                return response()->json(['httpcode'=>200,'status'=>'success','data'=>['products'=>$products,'total_products'=>$total_products]]);

            }
            else
            {
               return response()->json(['httpcode'=>200,'status'=>'success','message'=>'Product not found']); 
            }        
    }
    
    public function featured_products(Request $request)
    {
        $lang=$request->lang_id;

        $category    = $request->category_id;
        $subcategory = $request->subcategory_id;
        $brand       = $request->brand_id;
        $max_price   = $request->max_price;
        $min_price   = $request->min_price;
        $latest      = $request->latest;
        $low_to_high = $request->low_to_high;
        $high_to_low = $request->high_to_low;

        $products=[];
        $query = Product::query();
       
        
        
        if ($max_price!='' && $min_price!='') {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)->where('prd_products.is_featured',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->whereBetween('prd_prices.price', [$min_price, $max_price])
        ->select('prd_products.*');
        }
        else
        {
            if($low_to_high==1)
            {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)->where('prd_products.is_featured',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->select('prd_products.*')
        ->orderBy('prd_prices.price', 'ASC');
            }

           if($high_to_low==1)
            {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)->where('prd_products.is_featured',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->select('prd_products.*')
        ->orderBy('prd_prices.price', 'DESC');
            } 

          if($category!='' || $subcategory!='' || $brand!='') 
          { 

            if($low_to_high!=1 && $high_to_low!=1 && $min_price=='' && $max_price==''){
            $query->where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('is_featured',1);
        $query->when($category, function ($q,$category) {
            return $q->where('category_id', $category);
        });
        $query->when($subcategory, function ($q,$subcategory) {
            return $q->where('sub_category_id', $subcategory);
        });
        $query->when($brand, function ($q,$brand) {
            return $q->where('brand_id', $brand);
        });
        $query->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('created_at','DESC');
        });
            }
          }
          if($low_to_high!=1 && $high_to_low!=1 && $min_price=='' && $max_price=='')
            {
        $query->where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('is_featured',1);
        $query->when($category, function ($q,$category) {
            return $q->where('category_id', $category);
        });
        $query->when($subcategory, function ($q,$subcategory) {
            return $q->where('sub_category_id', $subcategory);
        });
        $query->when($brand, function ($q,$brand) {
            return $q->where('brand_id', $brand);
        });
        $query->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('created_at','DESC');
        });
            }
        }
        
        $prod_data = $query->paginate(12);   

            

       
            if(!empty($prod_data))
            {
                foreach($prod_data as $row)
                {
                    if($row->is_featured==1){
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';  
                    }
                    if($row->product_type==1)
                    {
                    $prd_list['actual_price']=$row->prdPrice->price;
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    }
                    else
                    {
                    $prd_list['actual_price']='';
                    $prd_list['sale_price']=''; 
                    }
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                  

            $prd_list['offers']=$this->available_offers($row->id,$lang); 
            $products[]=$prd_list;
            
                }//Active store
                    }
                }
           
           if(!empty($products))
           {
               $total_products=$prod_data->total();
           }
           else
           {
               $total_products=0;
           }
                return response()->json(['httpcode'=>200,'status'=>'success','data'=>['products'=>$products,'total_products'=>$total_products]]);

            }
            else
            {
               return response()->json(['httpcode'=>200,'status'=>'success','message'=>'Product not found']); 
            }       
    }
    
    public function daily_deals(Request $request)
    {
        $lang=$request->lang_id;

        $category    = $request->category_id;
        $subcategory = $request->subcategory_id;
        $brand       = $request->brand_id;
        $max_price   = $request->max_price;
        $min_price   = $request->min_price;
        $latest      = $request->latest;
        $low_to_high = $request->low_to_high;
        $high_to_low = $request->high_to_low;

        $products=[];
        $query = Product::query();

        
        
        if ($max_price!='' && $min_price!='') {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)->where('prd_products.daily_deals',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->whereBetween('prd_prices.price', [$min_price, $max_price])
        ->select('prd_products.*');
        }
        else
        {
            if($low_to_high==1)
            {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)->where('prd_products.daily_deals',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->select('prd_products.*')
        ->orderBy('prd_prices.price', 'ASC');
            }

           if($high_to_low==1)
            {
        $query->join('prd_prices', 'prd_prices.prd_id', '=', 'prd_products.id')
        ->where('prd_products.is_active',1)->where('prd_products.is_deleted',0)->where('prd_products.is_approved',1)->where('prd_products.daily_deals',1)
        ->where('prd_prices.is_deleted',0)
        ->when($category, function ($q,$category) {
            return $q->where('prd_products.category_id', $category);
        })
        ->when($subcategory, function ($q,$subcategory) {
            return $q->where('prd_products.sub_category_id', $subcategory);
        })
        ->when($brand, function ($q,$brand) {
            return $q->where('prd_products.brand_id', $brand);
        })
        ->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('prd_products.created_at','DESC');
        })
        ->select('prd_products.*')
        ->orderBy('prd_prices.price', 'DESC');
            } 

          if($category!='' || $subcategory!='' || $brand!='' || $latest!='') 
          { 

            if($low_to_high!=1 && $high_to_low!=1 && $min_price=='' && $max_price==''){
            $query->where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('daily_deals',1);
        $query->when($category, function ($q,$category) {
            return $q->where('category_id', $category);
        });
        $query->when($subcategory, function ($q,$subcategory) {
            return $q->where('sub_category_id', $subcategory);
        });
        $query->when($brand, function ($q,$brand) {
            return $q->where('brand_id', $brand);
        });
        $query->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('created_at','DESC');
        });
            }
          }
          if($low_to_high!=1 && $high_to_low!=1 && $min_price=='' && $max_price=='')
            {
        $query->where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('daily_deals',1);
        $query->when($category, function ($q,$category) {
            return $q->where('category_id', $category);
        });
        $query->when($subcategory, function ($q,$subcategory) {
            return $q->where('sub_category_id', $subcategory);
        });
        $query->when($brand, function ($q,$brand) {
            return $q->where('brand_id', $brand);
        });
        $query->when($latest == 1, function ($q,$latest) {
            return $q->orderBy('created_at','DESC');
        });
            }
        }

        $prod_data = $query->paginate(12);   

            

       
            if(!empty($prod_data))
            {
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$row->id;
                    $prd_list['product_name']=$this->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$this->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$this->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';  
                    }
                    if($row->product_type==1)
                    {
                    $prd_list['actual_price']=$row->prdPrice->price;
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    }
                    else
                    {
                    $prd_list['actual_price']='';
                    $prd_list['sale_price']=''; 
                    }
                    $prd_list['short_description']=$this->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                  

            $prd_list['offers']=$this->available_offers($row->id,$lang); 
            $products[]=$prd_list;
            
                }//Active store
                }
           
           if(!empty($products))
           {
               $total_products=$prod_data->total();
           }
           else
           {
               $total_products=0;
           }
                return response()->json(['httpcode'=>200,'status'=>'success','data'=>['products'=>$products,'total_products'=>$total_products]]);

            }
            else
            {
               return response()->json(['httpcode'=>200,'status'=>'success','message'=>'Product not found']); 
            }       
    }

    function available_offers($prd_id,$lang)
    {
        //Available offers for this product
            $current_date=Carbon::now();
            $rows = Auction::where('is_active',1)->where('is_deleted',0)->where('bid_allocated_to',0)->where('product_id',$prd_id)->whereDate('auct_end','>=',$current_date)->first();
            $deals= Product::where('is_active',1)->where('is_deleted',0)->where('id',$prd_id)->where('daily_deals',1)->first();
            $shock = PrdShock_Sale::where('is_active',1)->where('is_deleted',0)->where('prd_id',$prd_id)->whereDate('start_time','<=',$current_date)->whereDate('end_time','>=',$current_date)->first();
            if($rows)
            {
                $offer['offer_name']= 'Auction';   
                $offer['auction_id']=$rows->id;
                $offer['url']=url('api/customer/auction');
                $offer_list[]=$offer;
            }
            else if($deals)
            {
                $offer['offer_name']= 'Daily Deals';   
                $offer['product_id']=$prd_id;
                $offer['url']='';
                $offer_list[]=$offer;
            }
            else if($shock)
            {
                $offer['offer_name']= 'Shocking Sale';   
                $offer['shock_sale_id']=$shock->id;
                $offer['url']=url('api/customer/shock-sale');
                $offer_list[]=$offer;
            }
            else
            {
                $offer_list=[];
            }

            return $offer_list;
    }

    //SEARCH INSIDE SHOP
    public function shop_product_search(Request $request)
    {
        $lang=$request->lang_id;
        $products=[];
        //  $data =   DB::table('cms_content')
        // ->join('prd_products', 'cms_content.cnt_id', '=', 'prd_products.name_cnt_id')
        // ->join('category', 'prd_products.category_id', '=', 'category.category_id')
        // ->where('cms_content.content', 'Like', '%' . $request->keyword . '%')
        // ->where('prd_products.seller_id',$request->seller_id)
        // ->get();

         $data_product = DB::table('cms_content')
        ->leftjoin('prd_products',function($q){
            $q->on('prd_products.name_cnt_id' ,'cms_content.cnt_id');
        })->select(['prd_products.id'])
        ->where('cms_content.content', 'Like', '%' . $request->keyword . '%')
        ->where('prd_products.seller_id',$request->seller_id)
        ->get();

        $data_category = DB::table('cms_content')
        ->leftjoin('category',function($q){
            $q->on('category.cat_name_cid' ,'cms_content.cnt_id');
        })->select(['category.category_id'])
        ->where('cms_content.content', 'Like', '%' . $request->keyword . '%')
        ->get();

        $data_subcategory = DB::table('cms_content')
        ->leftjoin('subcategory',function($q){
            $q->on('subcategory.sub_name_cid' ,'cms_content.cnt_id');
        })->select(['subcategory.subcategory_id'])
        ->where('cms_content.content', 'Like', '%' . $request->keyword . '%')
        ->get();

        $data_brand = DB::table('cms_content')
        ->leftjoin('prd_brand',function($q){
            $q->on('prd_brand.brand_name_cid' ,'cms_content.cnt_id');
        })->select(['prd_brand.id'])
        ->where('cms_content.content', 'Like', '%' . $request->keyword . '%')
        ->get();

        if(count($data_product)>0)
        {
            foreach($data_product as $key)
            {
                $products=$this->get_search_products($key->id,$category='',$subcategory='',$brand='',$tag='',$request->seller_id,$request->lang_id);
            }
        }
       if(count($data_category)>0)
       {
            foreach($data_category as $row)
            {
                if($row->category_id!='')
                {
                $products=$this->get_search_products($prd_id='',$row->category_id,$subcategory='',$brand='',$tag='',$request->seller_id,$request->lang_id);
               }
            }
        }

        if(count($data_subcategory)>0)
       {
            foreach($data_subcategory as $row)
            {
                if($row->subcategory_id!='')
                {
                $products=$this->get_search_products($prd_id='',$category='',$row->subcategory_id,$brand='',$tag='',$request->seller_id,$request->lang_id);
               }
            }
        }

        if(count($data_brand)>0)
       {
            foreach($data_brand as $row)
            {
                if($row->id!='')
                {
                $products=$this->get_search_products($prd_id='',$category='',$subcategory='',$brand=$row->id,$tag='',$request->seller_id,$request->lang_id);
               }
            }
        }

        
        
        return ['httpcode'=>200,'status'=>'success','data'=>['products'=>$products]];

   }


//Get products
   function get_search_products($prd_id,$category,$subcategory,$brand,$tag,$seller_id,$lang)
   {
       $products=[];
    if($prd_id!='' && $seller_id!='')
    {
    $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('seller_id',$seller_id)->where('is_approved',1)->where('id',$prd_id)->get();
    }
    if($category!='' && $seller_id!='')
    {
     $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('seller_id',$seller_id)->where('visible',1)->where('category_id',$category)->get(); 
    }
    if($subcategory!='' && $seller_id!='')
    {
     $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('seller_id',$seller_id)->where('visible',1)->where('sub_category_id',$subcategory)->get(); 
    }
    if($brand!='' && $seller_id!='')
    {
     $prod_data= Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('seller_id',$seller_id)->where('visible',1)->where('brand_id',$brand)->get(); 
    }
            if(count($prod_data)>0)
            {
                foreach($prod_data as $row)
                {
                    $store_active = Store::where('is_active',1)->where('seller_id',$row->seller_id)->first();
                    if($store_active)
                    {
                    
                    $prd_list['product_id']=$row->id;
                    $prd_list['product_name']=$row->get_content($row->name_cnt_id,$lang);
                    $prd_list['category_id']=$row->category_id;
                    $prd_list['category_name']=$row->get_content($row->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$row->sub_category_id;
                    $prd_list['subcategory_name']=$row->get_content($row->subCategory->sub_name_cid,$lang);
                    if($row->brand_id)
                    {
                    $prd_list['brand_id']=$row->brand_id;
                    $prd_list['brand_name']=$row->get_content($row->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['actual_price']=number_format($row->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($row->id);
                    $prd_list['short_description']=$row->get_content($row->short_desc_cnt_id,$lang);
                    $prd_list['tag']=$this->get_product_tag($row->id,$lang);
                    $prd_list['rating']=$this->get_rates($row->id);
                    $prd_list['seller']=$row->Store($row->seller_id)->store_name;
                    $prd_list['image']=$this->get_product_image($row->id); 
            

                    $products[]=$prd_list;
                    
                    }
                   }return $products;
           }
           else
                {
                   $products='';
                   return $products;
                 }
}

// GET sub category
    function get_subcategory($cat_id){
        $data     =   [];
        
        $subcat       =   Subcategory::where('category_id',$cat_id)->where('is_active',1)->get(['subcategory_id','sub_name_cid']); 
            if($subcat)   {   foreach($subcat as $k=>$row){ 
                $val['id']    =   $row->subcategory_id;
                $val['subcategory_name']   =  $this->get_content($row->sub_name_cid,$lang='');
                $data[]       =   $val;
            } }
            else{ $data     =   []; } return $data;
        
    }

    // function get_product_image($prd_id){
        
        
    //     $data     =   [];
        
    //     $admin_pro=Product::where('id',$prd_id)->first();
        
    //     if($admin_pro->admin_prd_id > 0)
    //     {
    //     $product       =   ProductImage::where('prd_id',$prd_id)->where('is_deleted',0)->get(); 
    //     }
    //     else
    //     {
    //     $product       =   PrdAdminImage::where('prd_id',$prd_id)->where('is_deleted',0)->get();
    //     }
    //         if($product)   {   foreach($product as $k=>$row){ 
    //             if($row->image)
    //             {
    //             $val['image']       =   config('app.storage_url').$row->image;
    //             }
    //             if($row->thumbnail)
    //             {
    //             $val['thumbnail']   =   config('app.storage_url').$row->thumbnail;
    //             }
    //             $data[]             =   $val;
    //         } }
    //         else{ $data     =   []; } return $data;
        
    // }
    
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

    function get_product_tag($prd_id,$lang){
        $data     =   [];
        
        $product       =   PrdAssignedTag::where('prd_id',$prd_id)->get(); 
            if($product)   {   foreach($product as $k=>$row){ 
                $val['tag_name']    =   $this->get_content($row->tag->tag_name_cid,$lang);
                $data[]             =   $val;
            } }
            else{ $data     =   []; } return $data;
        
    }
   
   function ass_related_product($prd_id,$lang){
        $data     =   [];
        
        $prod_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',0)->where('id',$prd_id)->first();
            if($prod_data)   {    
                $store_active = Store::where('is_active',1)->where('seller_id',$prod_data->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    if($prod_data->brand_id)
                    {
                    $prd_list['brand_id']=$prod_data->brand_id;
                    $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['short_description']=$this->get_content($prod_data->short_desc_cnt_id,$lang);
                    $prd_list['long_description']=$this->get_content($prod_data->desc_cnt_id,$lang);
                    $prd_list['content']=$this->get_content($prod_data->content_cnt_id,$lang);
                    $prd_list['attributes']=$this->get_product_attributes($prod_data->id,$lang);
                    $prd_list['available_attributes']=$this->get_product_attributes($prod_data->id,$lang);
                    
                    $prd_list['actual_price']=$prod_data->prdPrice->price;
                    $prd_list['sale_price']=$this->get_sale_price($prod_data->id);
                    
                    // $actual_price = number_format($prod_data->prdPrice->price,2);
                    // $prd_list['actual_price_quote']= $actual_price;
                    // $prd_list['actual_price']= $prod_data->prdPrice->price;
                    // $sale_price =$this->get_sale_price($prod_data->prd_id);
                    // $prd_list['sale_price']=$sale_price;
                    
                    $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
                    $prd_list['tag']=$this->get_product_tag($prod_data->id,$lang); 
                    $prd_list['rating']=$this->get_rates($prod_data->id);
                    $prd_list['image']=$this->get_product_image($prod_data->id); 
                    $data             =   $prd_list;
                    }
             }
            else{ $data     =   []; } return $data;
        
    }
    function ass_related_product1($prd_id,$lang){
        $data     =   [];
        
        $prod_data       =   AssignedAttribute::where('is_deleted',0)->where('prd_id',$prd_id)->groupBy('prd_id')->get();
            if(count($prod_data)>0)   { 
                foreach($prod_data as $row)  {
                    $attr_list['attr_id']=$row->attr_id;
                   $attr_list['product_id']=$row->prd_id;
                    //$attr_list['attr_id']=$row->attr_id;
                    $attr_list['attr_name']=$this->get_content($row->PrdAttr->name_cnt_id,$lang);
                   // $attr_list['attr_type']=$row->PrdAttr->type;
                   // $attr_list['attr_data_type']=$row->PrdAttr->data_type;
                   // $attr_list['attr_value_name']=$this->get_content($row->PrdAttr_value->name_cnt_id,$lang);
                    $attr_list['attr_value']=$row->attr_value;
                    $attr_list['image']=config('app.storage_url').$row->attrValue->image;
                    
                    
                    
                    $attr_list['image']=config('app.storage_url').$row->attrValue->image;
                    $attr_list['attr_value1'][] =$this->inner_attribute($prd_id,$row->attr_id,$lang);
                    if(empty($attr_list['attr_value1'][1]))
                    {
                        $actual_price = number_format($row->prdPrice->price,2);
                    $attr_list['actual_price_quote']= $actual_price;
                    $attr_list['actual_price']= $row->prdPrice->price;
                    $sale_price =$this->get_sale_price($row->prd_id);
                    $attr_list['sale_price']=$sale_price;
                    $attr_list['stock']=$row->prdStock($row->prd_id);
                    $attr_list['sku']=$row->Product->sku;
                    if($row->Product->is_out_of_stock==0)
                        {
                            $attr_list['is_out_of_stock']=false;
                        }
                        else
                        {
                            $attr_list['is_out_of_stock']=true;
                        }
                        if($row->Product->out_of_stock_selling==0)
                        {
                            $attr_list['out_of_stock_selling']=false;
                        }
                        else
                        {
                            $attr_list['out_of_stock_selling']=true;
                        }
                    }
                    $data             =   $attr_list;
                }
             }
            else{ $data     =   []; } return $data;
        
    }
    
    function inner_attribute($prd_id,$attr_id,$lang)
    {
        $data=[];
        $rowss = AssignedAttribute::where('is_deleted',0)->where('prd_id',$prd_id)->where('attr_id','!=',$attr_id)->first();
                     if($rowss)
                    {
                        
                        $atr_inn['attr_id']=$rowss->attr_id;    
                        $atr_inn['attr_name']= $this->get_content($rowss->PrdAttr->name_cnt_id,$lang);
                        $atr_inn['attr_value']= $rowss->attr_value;
                        $atr_inn['image']=config('app.storage_url').$rowss->attrValue->image;
                        $actual_price = number_format($rowss->prdPrice->price,2);
                        $atr_inn['actual_price_quote']= $actual_price;
                        $atr_inn['actual_price']= $rowss->prdPrice->price;
                        $sale_price =$this->get_sale_price($rowss->prd_id);
                        $atr_inn['sale_price']=$sale_price;
                        $atr_inn['stock']=$rowss->prdStock($rowss->prd_id);
                        $atr_inn['sku']=$rowss->Product->sku;
                        if($rowss->Product->is_out_of_stock==0)
                        {
                            $atr_inn['is_out_of_stock']=false;
                        }
                        else
                        {
                            $atr_inn['is_out_of_stock']=true;
                        }
                        if($rowss->Product->out_of_stock_selling==0)
                        {
                            $atr_inn['out_of_stock_selling']=false;
                        }
                        else
                        {
                            $atr_inn['out_of_stock_selling']=true;
                        }
                        $data             =   $atr_inn;
                        
                        
                    }return $data;
    }
    function related_product($prd_id,$lang){
        $data     =   [];
        
        $prod_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('id',$prd_id)->first();
            if($prod_data)   {    
                $store_active = Store::where('is_active',1)->where('seller_id',$prod_data->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    if($prod_data->brand_id)
                    {
                    $prd_list['brand_id']=$prod_data->brand_id;
                    $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['short_description']=$this->get_content($prod_data->short_desc_cnt_id,$lang);
                    $prd_list['long_description']=$this->get_content($prod_data->desc_cnt_id,$lang);
                    $prd_list['content']=$this->get_content($prod_data->content_cnt_id,$lang);
                    if($prod_data->product_type==1)
                    {
                    $prd_list['actual_price']=number_format($prod_data->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($prod_data->id);
                    }
                    else
                    {
                    $prd_list['actual_price']='';
                    $prd_list['sale_price']='';
                    }
                    $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
                    $prd_list['tag']=$this->get_product_tag($prod_data->id,$lang); 
                    $prd_list['rating']=$this->get_rates($prod_data->id);
                    $prd_list['image']=$this->get_product_image($prod_data->id); 
                    $data             =   $prd_list;
                    }
             }
            else{ $data     =   []; } return $data;
        
    }
    

    //Related brand products
    function related_brand_product($brand_id,$prd_id,$lang){
        $data     =   [];
        
        $brand_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('product_type',1)->where('brand_id',$brand_id)->where('visible',1)->whereNotIn('id', [$prd_id])->get();
            if(count($brand_data)>0)   {   
              foreach($brand_data as $prod_data) {
                  $store_active = Store::where('is_active',1)->where('seller_id',$prod_data->seller_id)->first();
                    if($store_active)
                    {
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang);
                    $prd_list['seller']=$prod_data->Store($prod_data->seller_id)->store_name;
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                    $prd_list['subcategory_name']=$this->get_content($prod_data->subCategory->sub_name_cid,$lang);
                    if($prod_data->brand_id)
                    {
                    $prd_list['brand_id']=$prod_data->brand_id;
                    $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    else
                    {
                    $prd_list['brand_id']='';
                    $prd_list['brand_name']='';
                    }
                    $prd_list['short_description']=$this->get_content($prod_data->short_desc_cnt_id,$lang);
                    $prd_list['long_description']=$this->get_content($prod_data->desc_cnt_id,$lang);
                    $prd_list['content']=$this->get_content($prod_data->content_cnt_id,$lang);
                    $prd_list['actual_price']=number_format($prod_data->prdPrice->price,2);
                    $prd_list['sale_price']=$this->get_sale_price($prod_data->id);
                    $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
                    $prd_list['tag']=$this->get_product_tag($prod_data->id,$lang); 
                    $prd_list['rating']=$this->get_rates($prod_data->id);
                    $prd_list['image']=$this->get_product_image($prod_data->id); 
                    $data[]             =   $prd_list;
                    }
                }

                $order = array_column($data, 'rating');

                array_multisort($order, SORT_DESC, $data);
             }
            else{ $data     =   []; } return $data;
        
    }


    //Related seller products
    function seller_products($seller_id,$lang,$total_pro){
        $data     =   [];

        $seller_data       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('seller_id',$seller_id)->paginate(12);

           if($total_pro==''){
            if(count($seller_data)>0)   {   
              foreach($seller_data as $prod_data) {
                  
                    $prd_list['product_id']=$prod_data->id;
                    $prd_list['product_name']=$this->get_content($prod_data->name_cnt_id,$lang='');
                    $prd_list['seller']=$prod_data->Store($prod_data->seller_id)->store_name;
                    $prd_list['seller_id']=$prod_data->seller_id;
                    $prd_list['category_id']=$prod_data->category_id;
                    $prd_list['category_name']=$this->get_content($prod_data->category->cat_name_cid,$lang);
                    $prd_list['subcategory_id']=$prod_data->sub_category_id;
                   // $prd_list['subcategory_name']=$prod_data->get_content($prod_data->subCategory->sub_name_cid);
                    if($prod_data->brand_id)
                    {
                    $prd_list['brand_id']=$prod_data->brand_id;
                    $prd_list['brand_name']=$this->get_content($prod_data->brand->brand_name_cid,$lang);
                    }
                    else
                    {

                        $prd_list['brand_id']="";
                        $prd_list['brand_name']="";
                    }
                    $prd_list['short_description']=$this->get_content($prod_data->short_desc_cnt_id,$lang);
                    $prd_list['long_description']=$this->get_content($prod_data->desc_cnt_id,$lang);
                    $prd_list['content']=$this->get_content($prod_data->content_cnt_id,$lang);
                    if($prod_data->product_type==1){
                    $actual_price = number_format($prod_data->prdPrice->price,2);
                    $prd_list['actual_price_quote']= $actual_price;
                    $prd_list['actual_price']= $prod_data->prdPrice->price;
                    $sale_price =$this->get_sale_price($prod_data->id);
                    $prd_list['sale_price']=(float)$sale_price;
                    }
                    $prd_list['is_out_of_stock']=$prod_data->is_out_of_stock;
                    $prd_list['tag']=$this->get_product_tag($prod_data->id,$lang); 
                    $prd_list['rating']=$this->get_rates($prod_data->id);
                    $prd_list['image']=$this->get_product_image($prod_data->id); 
                    $data[]             =   $prd_list;
                }

                $order = array_column($data, 'rating');

                array_multisort($order, SORT_DESC, $data);
             }
            else{ $data     =   []; } return $data;

            }//if total==''

            else
            {
                $total=$seller_data->total();
                return $total;
            }
        }



  // Product Attributes
    function get_product_attributes($prd_id,$lang){
        $data     =   [];
        
        $prod_data       =   AssignedAttribute::where('is_deleted',0)->where('prd_id',$prd_id)->get();
            if(count($prod_data)>0)   { 
                foreach($prod_data as $row)  {
                   // $attr_list['id']=$row->id;
                    //$attr_list['attr_id']=$row->attr_id;
                    $attr_list['attr_name']=$this->get_content($row->PrdAttr->name_cnt_id,$lang);
                   // $attr_list['attr_type']=$row->PrdAttr->type;
                   // $attr_list['attr_data_type']=$row->PrdAttr->data_type;
                   // $attr_list['attr_value_name']=$this->get_content($row->PrdAttr_value->name_cnt_id,$lang);
                    $attr_list['attr_value']=$row->attr_value;
                    
                    $actual_price = number_format($row->prdPrice->price,2);
                    $attr_list['actual_price_quote']= $actual_price;
                    $attr_list['actual_price']= $row->prdPrice->price;
                    $sale_price =$this->get_sale_price($row->prd_id);
                    $attr_list['sale_price']=$sale_price;
                    
                    $attr_list['image']=config('app.storage_url').$row->attrValue->image;
                    $data[]             =   $attr_list;
                }
             }
            else{ $data     =   []; } return $data;
        
    }

    // Seller/Store Review
        function get_seller_review($field_id){ 
            $data=[];
        $review =SellerReview::where('seller_id',$field_id)->where('is_active',1)->get();
        if(count($review)>0){ 
            foreach($review as $row)
            {
            $user=$row->customerinfo($row->user_id);    
            $list['customer_name']=$list['customer_name']=$user->first_name." ".$user->middle_name." ".$user->last_name;
            $list['rating']=$row->rating;
            $list['comment']=$row->comment;
            $data[]=$list;
           }
            return $data;
        }
        else
            { 
                return $data; }
        }

    //Avg rating
    function get_rates($field_id){ 

        $rate =DB::table('prd_review')->select(DB::raw('AVG(rating) as rating'))->where('prd_id',$field_id)->where('is_active',1)->where('is_deleted',0)->first();
        if($rate){ 
        $return_val = round($rate->rating);
        return $return_val;
        }
        else
            { $return_val=0;
                return $return_val; }
        }

        //Avg rating of Seller/Store
        function get_seller_rating($field_id){ 

        $rate =SellerReview::select(DB::raw('AVG(rating) as rating'))->where('seller_id',$field_id)->where('is_active',1)->first();
        if($rate){ 
        $return_val = round($rate->rating);
        return $return_val;
        }
        else
            { $return_val=0;
                return $return_val; }
        }

        //Seller product rating
    function get_seller_product_rating($field_id){ 

        $rate = DB::table('prd_products')
        ->select(DB::raw('AVG(prd_review.rating) as rating'))
            ->leftJoin('prd_review', 'prd_review.prd_id', 'prd_products.id')
            ->where('prd_products.seller_id',$field_id)
            ->groupBy('prd_products.seller_id')->first();        
        if($rate){ 
        $return_val = round($rate->rating);
        return $return_val;
        }
        else
            { $return_val=0;
                return $return_val; }
        }

        //Each Product review
    function get_product_review($prd_id,$count){
        $data     =   [];
        $prod_data       =   PrdReview::where('is_deleted',0)->where('is_active',1)->where('prd_id',$prd_id)->get();

        if($count==1)
        {
            $count_prd=count($prod_data);
            return $count_prd;
        }

        else{
            if(count($prod_data)>0)   { 
                foreach($prod_data as $row)  {
                    $user=$row->customerinfo($row->user_id);
                    $list['review_id']=$row->id;
                    $list['customer_name']=$user->first_name." ".$user->middle_name." ".$user->last_name;
                    $list['rating']=$this->get_rates($row->prd_id);
                    $list['comment']=$row->comment;
                    if($row->image)
                    {
                    $list['image']=config('app.storage_url')."/app/public/product_review/".$row->image;  
                    }
                    else
                    {
                     $list['image']='';  
                    }
                    $list['date']=date('d M Y',strtotime($row->created_at));
                    $data[]             =   $list;
                }
                $order = array_column($data, 'rating');

                array_multisort($order, SORT_DESC, $data);
             }
            else{ $data     =   []; } return $data;
        }
        
    }

    //count down Timer
    public function timer($start_date,$end_date)
    {
        $d1 = new DateTime($start_date);
        $d2 = new DateTime($end_date);
        $interval = $d1->diff($d2);
        $diffInSeconds = $interval->s; //45
        $diffInMinutes = $interval->i; //23
        $diffInHours   = $interval->h; //8
        $diffInDays    = $interval->d; //21
        $diffInMonths  = $interval->m; //4
        $diffInYears   = $interval->y; //1

        return $diffInYears;
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
        $return_val = number_format($rows->price,2);
        return $return_val;
        }
        else
            { $return_val='';
                return $return_val; }
        }

       // No.of products
        function number_of_products($seller_id){
        
        $count       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('seller_id',$seller_id)->count();
        return $count;
       }

       // No.of orders per seller
        function order_per_seller($seller_id){
        
        $count       =   SaleOrder::where('seller_id',$seller_id)->whereNotIn('order_status', ["cancelled"])->count();
        return $count;
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
