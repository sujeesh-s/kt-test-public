<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Intervention\Image\Facades\Image;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;

use App\Models\AdminProduct;
use App\Models\ProductType;
use App\Models\PrdPrice;
use App\Models\PrdImage;
use App\Models\Language;
use App\Models\SellerInfo;
use App\Models\PrdOffer;
use App\Models\PrdShockingSale;
use App\Models\PrdShockingSaleProduct;
use DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use Validator;
use Session;

class ShockingSaleController extends Controller{
     public function __construct()
    {
        $this->middleware('auth:seller');
    }
    public function shocking_sales()
    { 

        $data['title']              =   'Shocking Sale';
        $data['menu']               =   'shocking-sales';
        $data['products']           =  PrdShockingSale::getShockingSales();
        
        // dd($data);
        return view('seller.shocking_sale.list',$data);
    }
    
   

     function createShockingSale($sale_id){
        $data['title']              =   'Create Shocking Sale';
        $data['menu']               =   'create-shocking-sales';
        $data['language']      =    DB::table('glo_lang_lk')->where('is_active', 1)->get();
        $products           =   Product::where('is_deleted',0)->where('seller_id',auth()->user()->id)->where('visible',1)->where('is_approved',1)->where('is_active',1)->orderBy('id', 'DESC')->get();
        $data['products']           = $products;
        $data['shockingsale']      = PrdShockingSale::getShockingSalesData($sale_id);
        // dd($data);
        return view('seller.shocking_sale.create',$data);
    }


     function saveShockingSale(Request $request){
       $input = $request->all();
        // dd($input); 
       $validator= $request->validate([
   
        'prd_id' => ['required']
     
        ], [], 
        [
    
   
        'prd_id' => 'Product'
       
        ]);

        

        $prd_id                    =  implode(",",$input['prd_id']) ;
       
        $sale_arr = [];
        $sale_arr['seller_id'] = auth()->user()->id;
        $sale_arr['shock_sale_id'] = $input['id'];
        $sale_arr['prd_id'] = $prd_id;
        $sale_arr['is_active'] = 1;
        $sale_arr['is_deleted'] = 0;

        
        if($input['id'] >0){

        $saleId                  =   PrdShockingSaleProduct::create($sale_arr)->id;
         $msg    =   'Shocking Sale Products added successfully!';

        }
        
       
        if($saleId){   
Session::flash('message', ['text'=>$msg,'type'=>'success']);  
             }else{
Session::flash('message', ['text'=>'Shocking Sale Products creation failed','type'=>'danger']);
           }


            // dd($data);
        return redirect(route('seller.shocking_sales'));
    }
    public function ShockingSaleStatus(Request $request)
        {
        $input = $request->all();
        
        if($input['id']>0) {
        $deleted =  PrdShockingSaleProduct::where('id',$input['id'])->update(array('is_active'=>$input['status']));
        
        return '1';
        }else {
        
        return '0';
        }
        
        }

    
}
