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
use App\Models\Product;
use App\Models\PrdPrice;
use App\Models\PrdOffer;
use App\Models\AssociatProduct;

use App\Rules\Name;
use Validator;
use Carbon\Carbon;

class MyProducts extends Controller
{
    public function list(Request $request)
    {
      if(!$user = validateTokenSeller($request->post('access_token'))){ return invalidToken(); }
      $seller_id = $user->seller_id;  

      $mypro = [];  
      $products = Product::where('is_deleted',0)->where('visible',1)->where('seller_id',$seller_id)->get();
      if(!empty($products))
      {
      foreach($products as $row)
      {
        $list['product_id']=$row->id;
        $list['product_name']=$this->get_content($row->name_cnt_id);
        $list['stock'] =number_format($row->prdStock($row->id));
        $list['out_of_stock_selling'] =$row->out_of_stock_selling;
        $list['category_id']=$row->category_id;
        $list['category_name']=$this->get_content($row->category->cat_name_cid);
        $list['subcategory_id']=$row->sub_category_id;
        $list['subcategory_name']=$this->get_content($row->subCategory->sub_name_cid);
        if($row->brand_id)
        {
        $list['brand_id']=$row->brand_id;
        $list['brand_name']=$this->get_content($row->brand->brand_name_cid);
        }
        else
        {
        $list['brand_id']=0;
        $list['brand_name']='';
        }
        if($row->product_type==1)
        {
         $list['product_type']='simple';    
         $list['actual_price']=number_format($row->prdPrice->price,2);           
        }
        else
        {
        $list['product_type']='config';    
        $list['actual_price']=number_format($this->config_product_price($row->id),2);  
        }
        $list['sale_price']=number_format($this->get_sale_price($row->id));
        $list['currency']=getCurrency()->name;
        $list['is_active']=$row->is_active;
        $mypro[] = $list;
      }
      return ['httpcode'=>200,'status'=>'success','message'=>'My products','data'=>['products'=>$mypro]];
     }
       else
       {
        return ['httpcode'=>404,'status'=>'error','message'=>'No products','data'=>['errors'=>'No products']];
       } 
    }


    public function delete_product(Request $request)
    {
        if(!$user = validateTokenSeller($request->post('access_token'))){ return invalidToken(); }
        $seller_id = $user->seller_id;

        $validator=  Validator::make($request->all(),[
            'access_token'=>['required'],
            'product_id' => ['required']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
        $update = ['is_active'=>0,
            'is_deleted'=>1,
            'updated_by'=>$seller_id,
            'updated_at'=>date("Y-m-d H:i:s")];
        $delete=Product::where('id',$input['product_id'])->update($update);
        return ['httpcode'=>200,'status'=>'success','message'=>'Successfully deleted','data'=>['response'=>'Successfully deleted']];
    }
  

  }


public function add_special_offer(Request $request)
    {
        if(!$user = validateTokenSeller($request->post('access_token'))){ return invalidToken(); }
        $seller_id = $user->seller_id;

        $validator=  Validator::make($request->all(),[
            'access_token'=>['required'],
            'product_id' => ['required'],
            'discount_value'=>['required','numeric'],
            'discount_type'=>['required','string',Rule::in(['percentage','amount'])],
            'qty_limit' =>['required','numeric'],
            'valid_from'=>['required'],
            'valid_to'=>['required'],
            'active'=>['required','numeric',Rule::in(['0','1'])]
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
        $prd_offr = PrdOffer::where('prd_id',$input['product_id'])->where('is_deleted',0)->first();
        if($prd_offr){
        $update = ['discount_value'=>$input['discount_value'],
            'discount_type'=>$input['discount_type'],
            'quantity_limit'=>$input['qty_limit'],
            'valid_from'=>$input['valid_from'],
            'valid_to'=>$input['valid_to'],
            'is_active'=>$input['active'],
            'is_deleted'=>0,
            'updated_by'=>$seller_id];
        $updates=PrdOffer::where('prd_id',$input['product_id'])->update($update);
        return ['httpcode'=>200,'status'=>'success','message'=>'Successfully inserted','data'=>['response'=>'Successfully inserted']];
       }
       else
       {
         $update = ['prd_id'=>$input['product_id'],
            'discount_value'=>$input['discount_value'],
            'discount_type'=>$input['discount_type'],
            'quantity_limit'=>$input['qty_limit'],
            'valid_from'=>$input['valid_from'],
            'valid_to'=>$input['valid_to'],
            'is_active'=>1,
            'is_deleted'=>0,
            'created_by'=>$seller_id,
            'updated_by'=>$seller_id];
            $updates=PrdOffer::create($update);
        return ['httpcode'=>200,'status'=>'success','message'=>'Successfully inserted','data'=>['response'=>'Successfully inserted']];
       }
    }
  

  }


  public function fetch_special_offer(Request $request)
    {
        if(!$user = validateTokenSeller($request->post('access_token'))){ return invalidToken(); }
        $seller_id = $user->seller_id;

        $validator=  Validator::make($request->all(),[
            'access_token'=>['required'],
            'product_id' => ['required','numeric']
        ]);
        $input = $request->all();

    if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }

    else
    {
        $prd_offr = PrdOffer::where('prd_id',$input['product_id'])->where('is_deleted',0)->first();
        if($prd_offr){
        $update = ['product_name'=>$this->get_content($prd_offr->product->name_cnt_id),
            'discount_value'=>$prd_offr->discount_value,
            'discount_type'=>$prd_offr->discount_type,
            'qty_limit'=>$prd_offr->quantity_limit,
            'valid_from'=>$prd_offr->valid_from,
            'valid_to'=>$prd_offr->valid_to,
            'is_active'=>$prd_offr->is_active];
        
        return ['httpcode'=>200,'status'=>'success','message'=>'Special offer','data'=>['offer'=>$update]];
       }
       else{
        return ['httpcode'=>404,'status'=>'error','message'=>'Not found','data'=>['response'=>'Empty/not found']];
       }
    }

  }



/********************************fetch values********/
    //Product sale price
    public function get_sale_price($field_id){ 

       $current_date=Carbon::now();
       $rows = PrdPrice::where('is_deleted',0)->where('prd_id',$field_id)->whereDate('sale_end_date','>=',$current_date)->first();        
        if($rows){ 
        $return_val = $rows->sale_price;
        return $return_val;
        }
        else
            { $return_val=0;
                return $return_val; }
        }

       // No.of products
        function number_of_products($seller_id){
        
        $count       =   Product::where('is_active',1)->where('is_deleted',0)->where('is_approved',1)->where('visible',1)->where('seller_id',$seller_id)->count();
        return $count;
       }

     function get_content($field_id){ 
     
        
        $language =DB::table('glo_lang_lk')->where('is_active', 1)->first();
        $language_id=$language->id;
        $content_table=DB::table('cms_content')->where('cnt_id', $field_id)->where('lang_id', $language_id)->first();
        if(!empty($content_table)){ 
        $return_cont = $content_table->content;
        return $return_cont;
        }
        else
            { return false; }
        }
   
   //CONFIG PRODUCT PRICE
        
        function config_product_price($prd_id)
        {
            $val = 0;
            $prd_ass = AssociatProduct::where('prd_id',$prd_id)->where('is_deleted',0)->get(['ass_prd_id']);
            if($prd_ass){
            $join = Product::join('prd_prices', 'prd_products.id', '=', 'prd_prices.prd_id')
                    ->selectRaw("MAX(prd_prices.price) AS max_val, MIN(prd_prices.price) AS min_val")
                    ->whereIn('prd_products.id',$prd_ass)->first();
                    if($join)
                    {
                        $min = $join->min_val;
                        $max = $join->max_val;
                        $val = $min;
                        // if($min > 0 && $max > 0){
                        // $val = $min."-".$max;
                        // }
                        // else if($min > 0 && $max ==0)
                        // {
                        //     $val = $min;
                        // }
                        // else if($min==$max)
                        // {
                        //   $val = $min; 
                        // }
                        // else
                        // {
                        //     $val = $max;
                        // }
                    }
            }
            
            return $val;
                    
        }
}
