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
use App\Models\UserRole;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\CartHistory;
use App\Models\Subcategory;
use App\Models\Store;
use App\Models\SellerReview;
use App\Models\SaleOrder;
use App\Models\SaleorderItems;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductDaily;
use App\Models\PrdAssignedTag;
use App\Models\PrdReview;
use App\Models\PrdShock_Sale;
use App\Models\PrdPrice;
use App\Models\RelatedProduct;
use App\Models\AssignedAttribute;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
class InsertResponse extends Controller
{
    public function insert_seller_review(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $validator=  Validator::make($request->all(),[
            'seller_id' => ['required','numeric'],
            'title' => ['required','string','max:255'],
            'comment'=> ['required','string','min:5','max:255'],
            'rating'=>['required','numeric','max:5'],
            'image'=> ['nullable','image','mimes:jpeg,png,jpg'],
        ]);
        $input = $request->all();
        // echo $input['title'];
        // die;

    if ($validator->fails()) 
    {    
      return response()->json(['httpcode'=>400,'status'=>'error','errors'=>$validator->messages()]);
    }

    else
    {
        $count  =  SaleOrder::where('seller_id',$input['seller_id'])->where('cust_id',$user_id)->count();
        if($count>0)
        {
        $insert =  SellerReview::create(['seller_id' => $input['seller_id'],
                'user_id' => $user_id,
                'comment' => $input['comment'],
                'title'=> $input['title'],
                'rating'  => $input['rating'],
                'is_active'=>1,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        return response()->json(['httpcode'=>200,'status'=>'success','response'=>"Successfully inserted"]);
        }
        else
        {
         return response()->json(['httpcode'=>400,'status'=>'Not available','message'=>'The customer not purchased any product from this store']);
        }
    }

    }

    public function add_bid(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        if($request->access_token=='' && $request->auction_id=='' && $request->bid_amount=='')
        {
           return response()->json(['httpcode'=>400,'status'=>'error','response'=>"Enter valid data"]); 
        }
        else
        {   
            $current_date=Carbon::now();
            $auction=Auction::where('id',$request->auction_id)->where('is_active',1)->where('is_deleted',0)->where('bid_allocated_to',0)->whereDate('auct_end','>=',$current_date)->whereDate('auct_start','<=',$current_date)->first();
            if($auction)
            {
               $max_value=AuctionHist::where('auction_id',$request->auction_id)->max('bid_price');

               if($request->bid_amount > $max_value && $request->bid_amount > $auction->min_bid_price)
                   {
                   $create= AuctionHist::create([
                'auction_id' => $request->auction_id,
                'user_id'=>$user_id,
                'bid_price' => $request->bid_amount,
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'modified_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);

                 return response()->json(['httpcode'=>200,'status'=>'success','response'=>"Success"]);  
               }
               else
               {
                return response()->json(['httpcode'=>400,'status'=>'error','response'=>"Amount not sufficient for bidding"]);
                
               }
            }
            else
            {
                return response()->json(['httpcode'=>400,'status'=>'error','response'=>"Invalid"]);
            }
        }
    }
    
    
    public function insert_product_review(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];

        $validator=  Validator::make($request->all(),[
            'product_id' => ['required','numeric'],
            'title'  =>['max:200','string','required'],
            'comment'=> ['required','string','min:5','max:255'],
            'rating'=>['required','numeric','max:5'],
            'image'=> ['nullable','image','mimes:jpeg,png,jpg'],
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return response()->json(['httpcode'=>400,'status'=>'error',data=>['errors'=>$validator->messages()]]);
    }

    else
    {
        $sale   =  SaleOrder::where('cust_id',$user_id)->get(['id']);
        if(count($sale)>0)
        {
        $count  =  SaleorderItems::where('prd_id',$input['product_id'])->whereIn('sales_id',$sale)->count();
        if($count>0)
        {
            if($request->hasFile('image'))
                    {
                    $file=$request->file('image');
                    $extention=$file->getClientOriginalExtension();
                    $filename=time().'.'.$extention;
                    $file->move(('uploads/storage/app/public/product_review/'),$filename);
                    }
                    else
                    {
                        $filename='';
                    }
        $insert =  PrdReview::create(['prd_id' => $input['product_id'],
                'user_id' => $user_id,
                'headline'=>$input['title'],
                'comment' => $input['comment'],
                'rating'  => $input['rating'],
                'image'  =>$filename,
                'is_active'=>1,
                'is_deleted'=>0,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        return response()->json(['httpcode'=>200,'status'=>'success','response'=>"Successfully inserted"]);
        }
        else
        {
            return response()->json(['httpcode'=>400,'status'=>'Not available','message'=>'The customer not purchased this product']);
        }
    }
        else
        {
         return response()->json(['httpcode'=>400,'status'=>'Not available','message'=>'The customer not purchased any product']);
        }
    }

    }
    
    public function insert_cart(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        
        $validator=  Validator::make($request->all(),[
            'product_id' => ['required','numeric'],
            'quantity'=> ['required','numeric'],
            'cart_type'=>['required','string','max:5']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return response()->json(['httpcode'=>400,'status'=>'error',data=>['errors'=>$validator->messages()]]);
    }

    else
    {
        $product_in =Product::where('id',$input['product_id'])->where('is_active',1)->where('is_deleted',0)->first();
        if($product_in){
        $store_status = Store::where('service_status',1)->where('is_active',1)->where('seller_id',$product_in->seller_id)->first();
        if($store_status)
        {
        $in_cart = Cart::join('usr_cart_item','usr_cart.id','=','usr_cart_item.cart_id')
        ->where('usr_cart_item.is_active',1)->where('usr_cart_item.is_deleted',0)
        ->where('usr_cart.is_active',1)->where('usr_cart.is_deleted',0)
        ->where('usr_cart_item.product_id',$input['product_id'])
        ->where('usr_cart.user_id',$user_id)
        ->first();
        
        if($in_cart)
        {
            $quantity_update = $in_cart->quantity + $input['quantity'];
            Cart::where('id',$in_cart->cart_id)->update([
            'updated_at'=>date("Y-m-d H:i:s")]);

            CartItem::where('cart_id',$in_cart->cart_id)->update([
            'quantity'=>$quantity_update,    
            'updated_at'=>date("Y-m-d H:i:s")]);

            $insert_cart_hist =  CartHistory::create(['org_id' => 1,
                'user_id' => $user_id,
                'product_id' => $input['product_id'],
                'quantity'  => $input['quantity'],
                'action'=>'insert',
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        }
        else
        {
        $insert_cart =  Cart::create(['org_id' => 1,
                'user_id' => $user_id,
                'cart_name' => $input['cart_type'],
                'cart_desc'  => $input['cart_type'],
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        $cart_id = $insert_cart->id;

        $insert_cart_item =  CartItem::create(['org_id' => 1,
                'cart_id' => $cart_id,
                'product_id' => $input['product_id'],
                'quantity'  => $input['quantity'],
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);

        $insert_cart_hist =  CartHistory::create(['org_id' => 1,
                'user_id' => $user_id,
                'product_id' => $input['product_id'],
                'quantity'  => $input['quantity'],
                'action'=>'insert',
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);

            }
        return response()->json(['httpcode'=>200,'status'=>'success','response'=>"Successfully inserted"]);
        
        }
        else
        {
            return response()->json(['httpcode'=>404,'status'=>'error','response'=>"Store unavailable"]);
        }
        }
        else
        {
            return response()->json(['httpcode'=>404,'status'=>'error','response'=>"Product unavailable"]);
        }
       
      }
    }
    
    public function change_cart_qty(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        
        $validator=  Validator::make($request->all(),[
            'product_id' => ['required','numeric'],
            'quantity'=> ['required','numeric'],
            'cart_type'=>['required','string','max:5']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return response()->json(['httpcode'=>400,'status'=>'error',data=>['errors'=>$validator->messages()]]);
    }

    else
    {
        $in_cart = Cart::join('usr_cart_item','usr_cart.id','=','usr_cart_item.cart_id')
        ->where('usr_cart_item.is_active',1)->where('usr_cart_item.is_deleted',0)
        ->where('usr_cart.is_active',1)->where('usr_cart.is_deleted',0)
        ->where('usr_cart_item.product_id',$input['product_id'])
        ->where('usr_cart.user_id',$user_id)
        ->first();
        
        if($in_cart)
        {
            $quantity_update = $input['quantity'];
            Cart::where('id',$in_cart->cart_id)->update([
            'updated_at'=>date("Y-m-d H:i:s")]);

            CartItem::where('cart_id',$in_cart->cart_id)->update([
            'quantity'=>$quantity_update,    
            'updated_at'=>date("Y-m-d H:i:s")]);

            $insert_cart_hist =  CartHistory::create(['org_id' => 1,
                'user_id' => $user_id,
                'product_id' => $input['product_id'],
                'quantity'  => $input['quantity'],
                'action'=>'insert',
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        }
        else
        {
        return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['errors'=>'No product found']];

            }
        return response()->json(['httpcode'=>200,'status'=>'success','response'=>"Successfully inserted"]);
        
    
       
      }
    }
    
    public function insert_wishlist(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        
        $validator=  Validator::make($request->all(),[
            'product_id' => ['required','numeric'],
            'type'=>['required','string','max:5']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return response()->json(['httpcode'=>400,'status'=>'error',data=>['errors'=>$validator->messages()]]);
    }

    else
    {
        $in_wish = Wishlist::join('usr_wishlist_prod','usr_wishlist_lk.id','=','usr_wishlist_prod.usr_wishlist_id')
        ->where('usr_wishlist_lk.is_active',1)->where('usr_wishlist_lk.is_deleted',0)
        ->where('usr_wishlist_prod.is_active',1)->where('usr_wishlist_prod.is_deleted',0)
        ->where('usr_wishlist_prod.product_id',$input['product_id'])
        ->where('usr_wishlist_prod.user_id',$user_id)
        ->first();
        
        if($in_wish)
        {
             return response()->json(['httpcode'=>200,'status'=>'Already exist','response'=>"Product is already in the wishlist"]);
        }
        else
        {
        $insert_wish =  Wishlist::create(['org_id' => 1,
                'usr_wishlist_name' => $input['type'],
                'usr_wishlist_desc'  => $input['type'],
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);
        $cart_id = $insert_wish->id;

        $insert_cart_item =  WishlistItem::create(['org_id' => 1,
                'user_id' => $user_id,
                'usr_wishlist_id' => $cart_id,
                'product_id' => $input['product_id'],
                'is_active'=>1,
                'is_deleted'=>0,
                'created_by'=>$user_id,
                'updated_by'=>$user_id,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s")]);

        return response()->json(['httpcode'=>200,'status'=>'success','response'=>"Successfully inserted"]);

            }
       
      }
    }
}
