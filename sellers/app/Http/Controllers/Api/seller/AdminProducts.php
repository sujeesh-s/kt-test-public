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

use App\Models\AdminProduct;
use App\Models\AssociatProduct;
use App\Models\SaleOrder;
use App\Models\Auction;
use App\Models\Product;
use App\Models\PrdPrice;
use App\Models\PrdOffer;


use App\Rules\Name;
use Validator;
use Carbon\Carbon;

class AdminProducts extends Controller
{
    public function list(Request $request)
    {
        if(!$user = validateTokenSeller($request->post('access_token'))){ return invalidToken(); }
      $seller_id = $user->seller_id;  

      $mypro = [];  
      $products = AdminProduct::where('is_deleted',0)->where('is_active',1)->get();
      if(!empty($products))
      {
      foreach($products as $row)
      {
        $list['product_id']=$row->id;
        $list['product_name']=$this->get_content($row->name_cid);
        if($row->product_type==1){
        $list['product_type']='simple';
        }
        else
        {
        $list['product_type']='config';    
        }
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
        //$list['image']=$this->get_admin_product_image($row->id);
        $mypro[] = $list;
      }
      return ['httpcode'=>200,'status'=>'success','message'=>'Admin products','data'=>['products'=>$mypro]];
     }
       else
       {
        return ['httpcode'=>404,'status'=>'error','message'=>'No products','data'=>['errors'=>'No products']];
       }
    }















    /*************fetch values***********/
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
}
