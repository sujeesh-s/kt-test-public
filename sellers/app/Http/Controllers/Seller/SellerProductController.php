<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
// use Intervention\Image\Facades\Image;
use DB;
use App\Models\Store;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Seller;
use App\Models\SellerInfo;
use App\Models\Product;
use App\Models\Tax;
use App\Models\AdminProduct;
use App\Models\ProductType;
use App\Models\PrdPrice;
use App\Models\PrdImage;
use App\Models\PrdStock;
use App\Models\Language;
use App\Models\CmsContent;
use App\Models\PrdAttribute;
use App\Models\PrdAttributeValue;
use App\Models\AssignedAttribute;
use App\Models\AssociatProduct;
use App\Models\PrdAssignedTag;
use App\Models\RelatedProduct;
use App\Models\AssConfigAttribute;
use App\Models\PrdOffer;
use App\Models\PrdReview;
use App\Models\VariableProdHist;
use App\Models\ProductVideo;
use App\Models\ProdDimension;
use App\Models\Tag;
use App\Models\PrdImports;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

use Validator;
use Session;

class SellerProductController extends Controller{
     public function __construct()
    {
        $this->middleware('auth:seller');
    }
    public function products()
    { 

        $data['title']              =   'Seller Products';
        $data['menuGroup']          =   'sellerGroup';
        $data['menu']               =   'product';
        $data['products']           =   Product::where("seller_id",auth()->user()->id)->where("visible",1)->where('is_deleted',0)->orderBy('id','desc')->get();
        $data['sellerinfo']= SellerInfo::where('is_approved',1)->where("seller_id",auth()->user()->id)->where('is_deleted',0)->first();
        $data['sellers']            =   getDropdownData(SellerInfo::where('is_approved',1)->where('is_deleted',0)->get(),'seller_id','fname');
        $data['categories']         =   getDropdownData(Category::where('is_deleted',0)->get(),'category_id','cat_name');
        // dd($data);
        return view('seller.my_products.list',$data);
    }
    
    function product($id=0,$sellerId=0,$type='',$lang=''){

        $data['title']              =   'Add Product'; $catId = 0; $data['seller'] = false;
        $data['menuGroup']          =   'sellerGroup';
        $data['menu']               =   'product';
        $sellerId                   =   auth()->user()->id;
        $product                    =   Product::where('id',$id)->first();
        if($id>0){ if($product)     {   
            $catId                  =   $product->category_id; 
            $configAttrs            =   $this->getConficAttrPrds($id);
            if($configAttrs){
                $data['attrs']      =   $configAttrs['attrs'];
                $data['assPrds']    =   $this->getAssosiProducts($configAttrs['attrIds'],$sellerId,1);
            }else{ $data['attrs']   =   $data['assPrds'] = []; }
            $data['assAssoPrdIds']  =   $this->getAssignedAssosiProducts($id);
        } }
        
        if($lang    >   0)          {   $data['langId'] =   $lang; }else{ $data['langId'] = Language::where('is_active',1)->where('is_deleted',0)->first()->id; }
        if($type == 'view')         {   $title = 'View Product'; }else if($id > 0){ $title = 'Edit Product'; }else{ $title = 'Add Product'; }
        if($type == 'add')          {   $title = 'Add Admin Product'; }
        $data['title']              =   $title; 
        $data['product']            =   $product;
        if($type                    ==  'new'){ $data['title']   =   'View Product Detail'; }
        $data['dimensions']         =  ProdDimension::where('prd_id',$id)->where("is_deleted",0)->first();
        $data['relatedprods']         =  getDropdownData(RelatedProduct::where('prd_id',$id)->where("is_deleted",0)->get(),'id','rel_prd_id');
        $data['relatedprods']         = array_values($data['relatedprods'] );

        $products                   =   Product::where('is_approved',1)->where('visible',1)->where('is_deleted',0)->where('seller_id',auth()->user()->id)->where('id','!=',$id);
        $data['products']           =   $products->orderBy('id','desc')->get();
        
        $data['categories']         =   getDropdownData(Category::where('is_deleted',0)->get(),'category_id','cat_name');
        $data['sub_cats']           =   getDropdownData(Subcategory::where('is_deleted',0)->get(),'subcategory_id','subcategory_name');
        $data['tags']             =   getDropdownCmsData(Tag::where('is_active',1)->where('is_deleted',0)->get(),'id','tag_name_cid');
        $data['brands']             =   getDropdownCmsData(Brand::where('is_active',1)->where('is_deleted',0)->get(),'id','brand_name_cid');
        $data['taxes']              =   getDropdownCmsData(Tax::where('is_active',1)->where('is_deleted',0)->get(),'id','tax_name_cid');
        $data['languages']          =   getDropdownData(Language::where('is_active',1)->where('is_deleted',0)->get(),'id','glo_lang_name');
        if($sellerId                >   0){ $data['seller'] = SellerInfo::where('seller_id',$sellerId)->first(); }
        $adminPrdIds                =   $this->getAddedAdminPrdIds($sellerId);
        $data['adminProducts']      =   getDropdownData(AdminProduct::where('is_active',1)->whereNotIn('id',$adminPrdIds)->where('is_deleted',0)->get(),'id','name');
        $data['attributes']         =   $this->getAttributes();
        $data['videos']         =  ProductVideo::where('prd_id',$id)->where("is_deleted",0)->first();
        $data['prdTypes']           =   getDropdownData(ProductType::where('is_deleted',0)->get(),'id','type_name');
        $data['configAttrs']        =   PrdAttribute::where('configur',1)->where('is_active',1)->where('is_deleted',0)->get(['id','name']);
        if($type == 'add'){ return view('admin_product.details',$data); }
        if($type == 'new'){ return view('seller.my_products.details',$data); }
        if($type == 'edit')     { $data['variationHist']                   =   VariableProdHist::where('prd_id',$id)->where('seller_id',auth()->user()->id)->where('is_deleted',0)->first(); }
        if($type == 'view')     { 
            $data['variationHist']                   =   VariableProdHist::where('prd_id',$id)->where('seller_id',auth()->user()->id)->where('is_deleted',0)->first();
        $data['prod_attributes']         =  $this->getAssignedAttributes($id); 
        $data['price']         =   PrdPrice::where('prd_id',$id)->orderBy('id','desc')->first();
        $data['images']         =   PrdImage::where('prd_id',$id)->where('is_deleted',0)->get();
        $data['reviews']         =   PrdReview::getProductReviews($id);
            // dd($data);
            return view('seller.my_products.view',$data); 
        }else{ //dd($data);
         return view('seller.my_products.details',$data); }
        return view('seller.my_products.details',$data);
    }
    
    function adminProduct($id){
        $product                    =   AdminProduct::where('id',$id)->first();
        $product->short_desc        =   getContent($product->short_desc,1); 
        $product->desc        =   getContent($product->desc,1); 
        $product->content        =   getContent($product->content,1); 
        $product->spec_cnt_id        =   getContent($product->spec_cnt_id,1); 
        return   $product;
    }

    function getAssignedAttributes($id){
        $qry                        =   AssignedAttribute::where('prd_id',$id)->where('is_deleted',0)->get();
        $attrs = array();
        if($qry){ foreach($qry      as  $attr){
            $attrs[$attr->attr_id]['name']    =   $this->AttrName($attr->attr_id);
            if($attr->attr_value !="") {
                $attrs[$attr->attr_id]['value']    =   $attr->attr_value;
            }else {
               $attrs[$attr->attr_id]['value']    =   $this->getAssignedAttrValues($attr->attr_val_id); 
            }
            
        } 
         return $attrs; }else{         return false; }
    }
    function getAssignedAttrValues($attr_val_id){ return   PrdAttributeValue::where('id',$attr_val_id)->where('is_active',1)->where('is_deleted',0)->first()->name; }
    function AttrName($attr_id){ return   PrdAttribute::where('id',$attr_id)->where('is_active',1)->where('is_deleted',0)->first()->name; }

    function getAttributes(){
        $qry                        =   PrdAttribute::where('is_active',1)->where('is_deleted',0)->get();
        if($qry){ foreach($qry      as  $k=>$row){
            $attrs[$k]              =   $row; 
            $attrs[$k]['values']    =   $this->getAttrValues($row->id);
        } return $attrs; }else{         return false; }
    }
    
    function getAttrValues($attrId){ return   PrdAttributeValue::where('attr_id',$attrId)->where('is_active',1)->where('is_deleted',0)->get(); }
    
    function getAddedAdminPrdIds($sellerId){ $prdIds = [];
        $query                      =   Product::where('seller_id',$sellerId)->where('admin_prd_id','>',0)->where('is_deleted',0)->get(['admin_prd_id']);
        if($query){ foreach($query  as  $row){ $prdIds[] = $row->admin_prd_id; } } return $prdIds;
    }
    
    function validateProduct(Request $request){
        $post                   =   (object)$request->post(); $error = $validName = false; 
        // dd($request->post('attr_1_value'));
        //   echo '<pre>'; print_r($post); echo '</pre>';
        $prd                   =   $request->post('prd'); 

        $price = $request->post('price');

         $attr = $request->post('attr');
        if($post->prd_option    ==  'option2'){ $rules          =   ['short_desc' => 'required|string|max:250']; }
        else{ $rules            =   [
                                        'name'                  =>  'required|string|max:100','category_id'   =>  'required',
                                        'sub_category_id'       =>  'required', 'short_desc'    =>  'required|string|max:250'
                                    ];
        }
        if($post->id > 0)       {   $sellerId = Product::where('id',$post->id)->first()->seller_id; }else{ $sellerId = 0; }
        $validator              =   Validator::make($post->prd,$rules);
        if($post->prd_option    ==  'option1'){ $validName      =   Product::ValidateUnique('name',$prd['name'],$post->id,$sellerId); }
        if ($validator->fails()){
            $error['error']     =   'prd';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }
        if($validName){ $error['name']    =   $validName; $error['error']     =   'prd';}
        if($error) { return $error; }
        if($post->prd_type ==1) {
         $rules                  =   ['price'  => 'required|numeric|min:1',];   
        $validator              =   Validator::make($post->price,['price'  => 'required|numeric|min:1','tax'=>'required']);
        if ($validator->fails()){
            $error['error']     =   'price';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }    
         }else {

          
        $validator              =   Validator::make($post->attr_1,['attr_name'  => 'required']);
        if ($validator->fails()){
            $error['error']     =   'attribute_1';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }  
        $validator              =   Validator::make($request->post('attr_1_value'),['attr1_0.*' => 'required']);

        if ($validator->fails()){
            $error['error']     =   'attribute_1_val';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }   

          if($post->attr_2['attr_name'] !="" || $post->attr_2['attr_name'] !=null) {
           
             $validator              =   Validator::make($post->attr_2,['attr_name'  => 'required']);
        if ($validator->fails()){
            $error['error']     =   'attribute_2';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        }  
        $validator              =   Validator::make($request->post('attr_2_value'),['attr2_0.*' => 'required']);

        if ($validator->fails()){
            $error['error']     =   'attribute_2_val';
           foreach($validator->messages()->getMessages() as $k=>$row){ $error[$k] = $row[0]; }
        } 
          }     

        }
         if($error) { return $error; }
        $imgArr                   =   $request->post('imgArr'); 
        if( $imgArr ) {
        
        }else {
        $validator              =   Validator::make(request()->all(),['image' => 'required',
        'image.*' => 'image|mimes:jpg,jpeg,png']);
        if ($validator->fails()){
        $error['error']     =   'image_0';
        foreach($validator->messages()->getMessages() as $k=>$row){ $error['image'][] = $row[0]; }
        }  
        if($error) { return $error; }  
        }
        if($error) { return $error; }else{ return 'success'; }
    }   

    function saveProduct(Request $request){
        $post                   =   (object)$request->post(); 
        // dd($post);

        $prd                    =   $post->prd;
        $price                  =   $post->price; 
       if($post->prd_type ==2) { $stock                  =   $post->stock; 
        $sku                  =   $post->sku;
        if(isset($post->weight)){ $weight                  =   $post->weight; }else{ $weight = [];}
        if(isset($post->length)){ $length                  =   $post->length; }else{ $length = [];}
        if(isset($post->width)){ $width                  =   $post->width; }else{ $width = [];}
        if(isset($post->height)){ $height                  =   $post->height; }else{ $height = [];}
           
       }
       $dimension                  =   $post->dimension; 
      if(isset($post->prd_id)){ $related_prd_id                  =   $post->prd_id; }
        $attrs                  = array();//  (object)$post->attr; 
        if(isset($post->assosi)){   $assosi =   (object)$post->assosi; }else{ $assosi = false; }
        $images                 =   $request->file('image'); 
        $videos = $request->file('video');
        $prd['tax_id']          =   $price['tax']; 
        $prd['is_approved']     =   0;
        if(!isset($prd['is_featured'])) { $prd['is_featured']     =   0;}
         if(!isset($prd['daily_deals'])) { $prd['daily_deals']     =   0;}
        //         echo '<pre>'; print_r($post); echo '</pre>'; 
        // echo '<pre>'; print_r($request->file()); echo '</pre>'; die;

        if($post->prd_option    ==  'option2' && $post->id == 0){
            $adPrd              =   AdminProduct::where('id',$post->admin_prd_id)->first();
            $prd['name']        =   $adPrd->name; $prd['category_id'] = $adPrd->category_id; $prd['sub_category_id'] = $adPrd->sub_category_id;  $prd['is_approved'] =   1;
            $prd['brand_id']    =   $adPrd->brand_id; $prd['admin_prd_id'] = $post->admin_prd_id; $prd['created_by'] = auth()->user()->id; 
        } $name = $prd['name'];
        if($post->admin_prd_id  >   0 && $post->id > 0){
            unset($prd['name']);    unset($prd['category_id']); unset($prd['sub_category_id']); unset($prd['brand_id']);
        }   $sDesc              =   $prd['short_desc']; $desc = $prd['desc'];  $content = $prd['content']; $specification = @$prd['specification']; unset($prd['specification']);
                                    unset($prd['short_desc']);  unset($prd['desc']); unset($prd['content']);
        if($post->id == 0){

            if($post->prd_type ==2) {
                $attr_data_arr = array();
                $attr_data_arr['attr_1'] = $post->attr_1['attr_name'];
                $attr_data_arr['attr_1_value'] = $post->attr_1_value;

             $latest = DB::table('cms_content')->orderBy('id', 'DESC')->first();
            $name_cnt_id=++$latest->cnt_id;

            DB::table('cms_content')->insertGetId([
            'org_id' => 1, 
            'lang_id' => 1,
            'cnt_id'=>$name_cnt_id,
            'content' => $post->attr_1['attr_name'],
            'is_active'=>1,
            'created_by'=>auth()->user()->id,
            'updated_by'=>auth()->user()->id,
            'is_deleted'=>0,
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")
            ]);

            $attr_arr = array();
            $attr_arr['name'] = $post->attr_1['attr_name'];
            $attr_arr['name_cnt_id'] = $name_cnt_id;
            $attr_arr['type'] = "text";
            $attr_arr['seller_id'] = auth()->user()->id;
            $attr_arr['is_active'] = 1;
            $attr_arr['created_by'] = auth()->user()->id;
            $attr_arr['updated_by'] = auth()->user()->id;
            $attr_arr['is_deleted'] = 0;
            $attr_arr['created_at'] = date("Y-m-d H:i:s");
           
            $attr_1_id           =   PrdAttribute::create($attr_arr)->id; 
            $attr_1_vals_arr = array();

            if($attr_1_id){
                if(isset($post->attr_1_value) && count($post->attr_1_value)>0)
                {
                    foreach($post->attr_1_value as $a1k=>$a1v){
                          $attr_1_img = "";
                        if($request->file('attr_1_img'))
                        {
                            
                           $image = $imgName = "";
                            
                            if(isset($request->file('attr_1_img')[$a1k])){ 
                            $image = $request->file('attr_1_img')[$a1k][0]; 
                            $imgName            =   time().'.'.$image->extension();
                            $path               =   '/app/public/products/attributes/'.$attr_1_id;
                            $img                =   Image::make($image->path()); 
                            $destinationPath    =   storage_path($path); 
                            $image->move($destinationPath.'/', $imgName);
                            $imgUpload          =   uploadFile($path,$imgName);
                            $attr_1_img = $path.'/'.$imgName;
                            
                            }else {
                             if(isset($post->attr_1_img[$a1k][0])) { $attr_1_img = $post->attr_1_img[$a1k][0]; }  
                            }
                        }

                        $attr_data_arr['attr_1_img'][$a1k] =  $attr_1_img;
                   $attr_1_vals_arr[$a1k] = PrdAttributeValue::create(['attr_id'=>$attr_1_id,'name'=>$a1v[0],'image'=>$attr_1_img,'created_by'=>auth()->user()->id])->id; 
                    }
                }
                
            }

            if(isset($post->attr_2['attr_name'])){
                $attr_data_arr['attr_2'] = $post->attr_2['attr_name'];
                $attr_data_arr['attr_2_value'] = $post->attr_2_value;

            $latest = DB::table('cms_content')->orderBy('id', 'DESC')->first();
            $name_cnt_id=++$latest->cnt_id;
             DB::table('cms_content')->insertGetId([
            'org_id' => 1, 
            'lang_id' => 1,
            'cnt_id'=>$name_cnt_id,
            'content' => $post->attr_2['attr_name'],
            'is_active'=>1,
            'created_by'=>auth()->user()->id,
            'updated_by'=>auth()->user()->id,
            'is_deleted'=>0,
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")
            ]);
            $attr_arr = array();
            $attr_arr['name'] = $post->attr_2['attr_name'];
            $attr_arr['name_cnt_id'] = $name_cnt_id;
            $attr_arr['type'] = "text";
            $attr_arr['seller_id'] = auth()->user()->id;
            $attr_arr['is_active'] = 1;
            $attr_arr['created_by'] = auth()->user()->id;
            $attr_arr['updated_by'] = auth()->user()->id;
            $attr_arr['is_deleted'] = 0;
            $attr_arr['created_at'] = date("Y-m-d H:i:s");
           
            $attr_2_id           =   PrdAttribute::create($attr_arr)->id; 
            $attr_2_vals_arr = array();

            if($attr_2_id){
                if(isset($post->attr_2_value) && count($post->attr_2_value)>0)
                {
                    foreach($post->attr_2_value as $a2k=>$a2v){
                        
                        $attr_2_img = "";
                         if($request->file('attr_2_img'))
                        {   $image = $imgName = "";
                           
                            if(isset($request->file('attr_2_img')[$a2k])){ 
                            $image = $request->file('attr_2_img')[$a2k][0]; 
                            $imgName            =   time().'.'.$image->extension();
                            $path               =   '/app/public/products/attributes/'.$attr_2_id;
                            $img                =   Image::make($image->path()); 
                            $destinationPath    =   storage_path($path); 
                            $image->move($destinationPath.'/', $imgName);
                            $imgUpload          =   uploadFile($path,$imgName);
                            $attr_2_img = $path.'/'.$imgName;
                            
                            }else {
                             if(isset($post->attr_2_img[$a2k][0])) { $attr_2_img = $post->attr_2_img[$a2k][0]; }  
                            }
                        }
                        $attr_data_arr['attr_2_img'][$a2k] = $attr_2_img;
                        
                   $attr_2_vals_arr[$a2k] = PrdAttributeValue::create(['attr_id'=>$attr_2_id,'name'=>$a2v[0],'name'=>$a2v[0],'created_by'=>auth()->user()->id])->id; 
                    }
                }
                
            }
            }
                $prd_name   =  $prd['name'];
                if(isset($post->dyn_prds_names) && count($post->dyn_prds_names)>0)
                {
                $assoc_arr = array();
                foreach($post->dyn_prds_names as $dyk=>$dyv){
                unset($prd['name']);unset($prd['product_type']);unset($prd['seller_id']);
                $prd['name']   =  $prd_name." - ". $dyv;

                $prd['product_type']=   $post->prd_type; $prd['seller_id']   =   auth()->user()->id; $prd['visible']   = 0; 
                $prdId =   Product::create($prd)->id;

                $assoc_arr[$dyk] =$prdId;

                


                $exp_arr = explode("~", $dyk);
                if(isset($exp_arr[1])) {

            
                PrdPrice::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'price'=>$price[$exp_arr[0]][$exp_arr[1]],'created_by'=>auth()->user()->id]);
                $stockId            =   PrdStock::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'qty'=>$stock[$exp_arr[0]][$exp_arr[1]],'rate'=>$price[$exp_arr[0]][$exp_arr[1]],'created_by'=>auth()->user()->id]);

                Product::where('id',$prdId)->update(['sku'=> $sku[$exp_arr[0]][$exp_arr[1]]] );

                $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$attr_1_id,'created_by'=>auth()->user()->id];
                $prdAttr['attr_val_id'] = $attr_1_vals_arr[$exp_arr[0]]; $prdAttr['attr_value'] = $post->attr_1_value[$exp_arr[0]][0];
                if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->exists()){ 
                $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
                AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->update($prdAttr);
                }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }

                $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$attr_2_id,'created_by'=>auth()->user()->id];
                $prdAttr['attr_val_id'] = $attr_2_vals_arr[$exp_arr[1]]; $prdAttr['attr_value'] = $post->attr_2_value[$exp_arr[1]][0];
                if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_2_id)->exists()){ 
                $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
                AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_2_id)->update($prdAttr);
                }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }


                }else{

                    PrdPrice::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'price'=>$price[$exp_arr[0]],'created_by'=>auth()->user()->id]);
                $stockId            =   PrdStock::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'qty'=>$stock[$exp_arr[0]],'rate'=>$price[$exp_arr[0]],'created_by'=>auth()->user()->id]);
                Product::where('id',$prdId)->update(['sku'=>$sku[$exp_arr[0]]]);

                $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$attr_1_id,'created_by'=>auth()->user()->id];
                $prdAttr['attr_val_id'] = $attr_1_vals_arr[$exp_arr[0]]; $prdAttr['attr_value'] = $post->attr_1_value[$exp_arr[0]][0];
                if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->exists()){ 
                $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
                AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->update($prdAttr);
                }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }

                }

                }

                }   





                 $prd['name'] = $prd_name;
            

            $prd['product_type']=   $post->prd_type; $prd['seller_id']   =   auth()->user()->id; $prd['visible']   = 1; 
                $prdId =   Product::create($prd)->id;

                
                PrdAttribute::where('id',$attr_1_id)->update(['prd_id'=>$prdId,'updated_by'=>auth()->user()->id]); 
               if(isset($attr_2_id)){
                 PrdAttribute::where('id',$attr_2_id)->update(['prd_id'=>$prdId,'updated_by'=>auth()->user()->id]); 
                 PrdAttributeValue::where('attr_id',$attr_2_id)->update(['prd_id'=>$prdId,'seller_id'=>$post->seller_id,'updated_by'=>auth()->user()->id]); 
               } 
               PrdAttributeValue::where('attr_id',$attr_1_id)->update(['prd_id'=>$prdId,'seller_id'=>$post->seller_id,'updated_by'=>auth()->user()->id]); 


                $var_hist_arr = array();
                
                $var_hist_arr['seller_id'] = $post->seller_id;
                 $var_hist_arr['attr_data'] = json_encode($attr_data_arr);
                $var_hist_arr['price_data'] = json_encode($price);
                $var_hist_arr['stock_data'] =json_encode($stock);
                $var_hist_arr['sku_data'] = json_encode($sku);
                $var_hist_arr['weight'] = json_encode($weight);
                $var_hist_arr['length'] = json_encode($length);
                $var_hist_arr['width'] = json_encode($width);
                $var_hist_arr['height'] = json_encode($height);
             if(isset($post->dyn_prds)){   $var_hist_arr['dynamic_ids'] = json_encode($post->dyn_prds);}
               if(isset($post->dyn_prds_names)){ $var_hist_arr['dynamic_prod_names'] = json_encode($post->dyn_prds_names); }
                $var_hist_arr['prd_id'] = $prdId;
              if(isset($assoc_arr)){  $var_hist_arr['assoc_prds'] = json_encode($assoc_arr);}
                $var_hist_arr['created_by'] = auth()->user()->id;
                $var_hist_arr['is_active'] = 1;

                VariableProdHist::create($var_hist_arr);
                if(isset($related_prd_id)){
                RelatedProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
                foreach($related_prd_id as $kv=>$rp_id) {
                $rltd_prd['prd_id']    =   $prdId;
                $rltd_prd['rel_prd_id']    =   $rp_id;
                $rltd_prd['created_by']    =   auth()->user()->id;
                $rltd_prd['is_deleted']    =   0;
                RelatedProduct::create($rltd_prd);
                }
                }

                AssociatProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
        if($assoc_arr){ foreach($assoc_arr as $k=>$ass){
            if(AssociatProduct::where('prd_id',$prdId)->where('ass_prd_id',$ass)->exists()){ AssociatProduct::where('prd_id',$prdId)->where('ass_prd_id',$ass)->update(['is_deleted'=>0]); }
            else{ AssociatProduct::create(['prd_id'=>$prdId,'ass_prd_id'=>$ass]); }
        } }


            }else {

                $prd['product_type']=   $post->prd_type; $prd['seller_id']   =   auth()->user()->id; 
            $prdId              =   Product::create($prd)->id; $price['created_by']    =   auth()->user()->id;
            $price['prd_id']    =   $prdId; unset($price['tax']); PrdPrice::create($price);
            if(isset($related_prd_id)){
                RelatedProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
                foreach($related_prd_id as $kv=>$rp_id) {
                $rltd_prd['prd_id']    =   $prdId;
                $rltd_prd['rel_prd_id']    =   $rp_id;
                $rltd_prd['created_by']    =   auth()->user()->id;
                $rltd_prd['is_deleted']    =   0;
                RelatedProduct::create($rltd_prd);
                }
                }
            $stockId            =   PrdStock::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'qty'=>0,'rate'=>$post->price['price'],'created_by'=>auth()->user()->id]);
            $dimension['prd_id']    =   $prdId; $dimension['created_by']    =   auth()->user()->id;
            ProdDimension::create($dimension);

            }
            




            
        }else{ 





            if($post->prd_type ==2) {
                $attr_data_arr = array();
                $attr_data_arr['attr_1'] = $post->attr_1['attr_name'];
                $attr_data_arr['attr_1_value'] = $post->attr_1_value;

             $latest = DB::table('cms_content')->orderBy('id', 'DESC')->first();
            $name_cnt_id=++$latest->cnt_id;

            DB::table('cms_content')->insertGetId([
            'org_id' => 1, 
            'lang_id' => 1,
            'cnt_id'=>$name_cnt_id,
            'content' => $post->attr_1['attr_name'],
            'is_active'=>1,
            'created_by'=>auth()->user()->id,
            'updated_by'=>auth()->user()->id,
            'is_deleted'=>0,
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")
            ]);
            PrdAttribute::where('prd_id',$post->id)->update(['is_deleted'=>1,'is_active'=>0,'updated_by'=>auth()->user()->id]); 
            $attr_arr = array();
            $attr_arr['name'] = $post->attr_1['attr_name'];
            $attr_arr['name_cnt_id'] = $name_cnt_id;
            $attr_arr['type'] = "text";
            $attr_arr['seller_id'] = auth()->user()->id;
            $attr_arr['is_active'] = 1;
            $attr_arr['created_by'] = auth()->user()->id;
            $attr_arr['updated_by'] = auth()->user()->id;
            $attr_arr['is_deleted'] = 0;
            $attr_arr['created_at'] = date("Y-m-d H:i:s");
           
            $attr_1_id           =   PrdAttribute::create($attr_arr)->id; 
           

            PrdAttributeValue::where('prd_id',$post->id)->update(['is_deleted'=>1,'is_active'=>0,'updated_by'=>auth()->user()->id]);
             $attr_1_vals_arr = array();
            if($attr_1_id){
                if(isset($post->attr_1_value) && count($post->attr_1_value)>0)
                {

                    foreach($post->attr_1_value as $a1k=>$a1v){
                        
                        $attr_1_img = "";
                        if($request->file('attr_1_img'))
                        {
                           $image = $imgName = "";
                            if(isset($request->file('attr_1_img')[$a1k])){ 
                            $image = $request->file('attr_1_img')[$a1k][0]; 

                            $imgName            =   time().'.'.$image->extension();
                            $path               =   '/app/public/products/attributes/'.$attr_1_id;
                            $img                =   Image::make($image->path()); 
                            $destinationPath    =   storage_path($path); 
                            $image->move($destinationPath.'/', $imgName);
                            $imgUpload          =   uploadFile($path,$imgName);
                            $attr_1_img = $path.'/'.$imgName;
                             
                            }else {
                             if(isset($post->attr_1_img[$a1k][0])) { $attr_1_img = $post->attr_1_img[$a1k][0]; }  
                            }
                        }else {
                             if(isset($post->attr_1_img[$a1k][0])) { $attr_1_img = $post->attr_1_img[$a1k][0]; }  
                            }

                        $attr_data_arr['attr_1_img'][$a1k] =  $attr_1_img;
                        
                   $attr_1_vals_arr[$a1k] = PrdAttributeValue::create(['attr_id'=>$attr_1_id,'name'=>$a1v[0],'image'=>$attr_1_img,'created_by'=>auth()->user()->id])->id; 
                    }
                }
                
            }

            if(isset($post->attr_2['attr_name'])){
                $attr_data_arr['attr_2'] = $post->attr_2['attr_name'];
                $attr_data_arr['attr_2_value'] = $post->attr_2_value;

            $latest = DB::table('cms_content')->orderBy('id', 'DESC')->first();
            $name_cnt_id=++$latest->cnt_id;
             DB::table('cms_content')->insertGetId([
            'org_id' => 1, 
            'lang_id' => 1,
            'cnt_id'=>$name_cnt_id,
            'content' => $post->attr_2['attr_name'],
            'is_active'=>1,
            'created_by'=>auth()->user()->id,
            'updated_by'=>auth()->user()->id,
            'is_deleted'=>0,
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s")
            ]);
            $attr_arr = array();
            $attr_arr['name'] = $post->attr_2['attr_name'];
            $attr_arr['name_cnt_id'] = $name_cnt_id;
            $attr_arr['type'] = "text";
            $attr_arr['seller_id'] = auth()->user()->id;
            $attr_arr['is_active'] = 1;
            $attr_arr['created_by'] = auth()->user()->id;
            $attr_arr['updated_by'] = auth()->user()->id;
            $attr_arr['is_deleted'] = 0;
            $attr_arr['created_at'] = date("Y-m-d H:i:s");
           
            $attr_2_id           =   PrdAttribute::create($attr_arr)->id; 
            $attr_2_vals_arr = array();

            if($attr_2_id){
                if(isset($post->attr_2_value) && count($post->attr_2_value)>0)
                {
                    foreach($post->attr_2_value as $a2k=>$a2v){
                        
                        $attr_2_img = "";
                         if($request->file('attr_2_img'))
                        {   $image = $imgName = "";
                            
                            if(isset($request->file('attr_2_img')[$a2k])){ 
                            
                                $image = $request->file('attr_2_img')[$a2k][0]; 
                            $imgName            =   time().'.'.$image->extension();
                            $path               =   '/app/public/products/attributes/'.$attr_2_id;
                            $img                =   Image::make($image->path()); 
                            $destinationPath    =   storage_path($path); 
                            $image->move($destinationPath.'/', $imgName);
                            $imgUpload          =   uploadFile($path,$imgName);
                            $attr_2_img = $path.'/'.$imgName;
                            
                            }else {

                             if(isset($post->attr_2_img[$a2k][0])) { $attr_2_img = $post->attr_2_img[$a2k][0]; }  
                            }
                        }else {

                             if(isset($post->attr_2_img[$a2k][0])) { $attr_2_img = $post->attr_2_img[$a2k][0]; }  
                            }
                        $attr_data_arr['attr_2_img'][$a2k] = $attr_2_img;
                        
                   $attr_2_vals_arr[$a2k] = PrdAttributeValue::create(['attr_id'=>$attr_2_id,'name'=>$a2v[0],'name'=>$a2v[0],'created_by'=>auth()->user()->id])->id; 
                    }
                }
                
            }
            }
                $prd_name   =  $prd['name'];

                $existing_assoc = $this->getAssignedAssosiProducts($post->id);
                if(isset($existing_assoc)) {
                    foreach($existing_assoc as $eak=>$eav){
                        Product::where('id',$eav)->update(['is_deleted'=>1,'is_active'=>0,'updated_by'=>auth()->user()->id]);
                        AssociatProduct::where('prd_id',$post->id)->where('ass_prd_id',$eav)->update(['is_deleted'=>1,'updated_by'=>auth()->user()->id]);
                    }
                }

                if(isset($post->dyn_prds_names) && count($post->dyn_prds_names)>0)
                {
                $assoc_arr = array();
                foreach($post->dyn_prds_names as $dyk=>$dyv){
                unset($prd['name']);unset($prd['product_type']);unset($prd['seller_id']);
                $prd['name']   =  $prd_name." - ". $dyv;

                $prd['product_type']=   $post->prd_type; $prd['seller_id']   =   auth()->user()->id; $prd['visible']   = 0; 
                $prdId =   Product::create($prd)->id;

                $assoc_arr[$dyk] =$prdId;

                


                $exp_arr = explode("~", $dyk);
                if(isset($exp_arr[1])) {

            
                PrdPrice::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'price'=>$price[$exp_arr[0]][$exp_arr[1]],'created_by'=>auth()->user()->id]);
                $stockId            =   PrdStock::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'qty'=>$stock[$exp_arr[0]][$exp_arr[1]],'rate'=>$price[$exp_arr[0]][$exp_arr[1]],'created_by'=>auth()->user()->id]);

                Product::where('id',$prdId)->update(['sku'=>$sku[$exp_arr[0]][$exp_arr[1]]]);

                $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$attr_1_id,'created_by'=>auth()->user()->id];
                $prdAttr['attr_val_id'] = $attr_1_vals_arr[$exp_arr[0]]; $prdAttr['attr_value'] = $post->attr_1_value[$exp_arr[0]][0];
                if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->exists()){ 
                $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
                AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->update($prdAttr);
                }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }

                $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$attr_2_id,'created_by'=>auth()->user()->id];
                $prdAttr['attr_val_id'] = $attr_2_vals_arr[$exp_arr[1]]; $prdAttr['attr_value'] = $post->attr_2_value[$exp_arr[1]][0];
                if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_2_id)->exists()){ 
                $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
                AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_2_id)->update($prdAttr);
                }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }


                }else{

                    PrdPrice::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'price'=>$price[$exp_arr[0]],'created_by'=>auth()->user()->id]);
                $stockId            =   PrdStock::create(['seller_id'=>$post->seller_id,'prd_id'=>$prdId,'qty'=>$stock[$exp_arr[0]],'rate'=>$price[$exp_arr[0]],'created_by'=>auth()->user()->id]);

                Product::where('id',$prdId)->update(['sku'=>$sku[$exp_arr[0]]]);

                $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$attr_1_id,'created_by'=>auth()->user()->id];
                $prdAttr['attr_val_id'] = $attr_1_vals_arr[$exp_arr[0]]; $prdAttr['attr_value'] = $post->attr_1_value[$exp_arr[0]][0];
                if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->exists()){ 
                $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
                AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$attr_1_id)->update($prdAttr);
                }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }

                }

                }

                }   





                 $prd['name'] = $prd_name;
            

            $prd['product_type']=   $post->prd_type; $prd['seller_id']   =   auth()->user()->id; $prd['visible']   = 1; 
               $prdId              =   $post->id; Product::where('id',$prdId)->update($prd);

               VariableProdHist::where('prd_id',$prdId)->update(['is_deleted'=>1,'is_active'=>0]);

                $var_hist_arr = array();
                
                $var_hist_arr['seller_id'] = $post->seller_id;
                 $var_hist_arr['attr_data'] = json_encode($attr_data_arr);
                $var_hist_arr['price_data'] = json_encode($price);
                $var_hist_arr['stock_data'] =json_encode($stock);
                $var_hist_arr['sku_data'] = json_encode($sku);
                $var_hist_arr['weight'] = json_encode($weight);
                $var_hist_arr['length'] = json_encode($length);
                $var_hist_arr['width'] = json_encode($width);
                $var_hist_arr['height'] = json_encode($height);
               if(isset($post->dyn_prds)){  $var_hist_arr['dynamic_ids'] = json_encode($post->dyn_prds); }
               if(isset($post->dyn_prds_names)){ $var_hist_arr['dynamic_prod_names'] = json_encode($post->dyn_prds_names); }
                $var_hist_arr['prd_id'] = $prdId;
                if(isset($assoc_arr)){  $var_hist_arr['assoc_prds'] = json_encode($assoc_arr);}
                $var_hist_arr['created_by'] = auth()->user()->id;
                $var_hist_arr['is_active'] = 1;

                VariableProdHist::create($var_hist_arr);
                if(isset($related_prd_id)){
                RelatedProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
                foreach($related_prd_id as $kv=>$rp_id) {
                $rltd_prd['prd_id']    =   $prdId;
                $rltd_prd['rel_prd_id']    =   $rp_id;
                $rltd_prd['created_by']    =   auth()->user()->id;
                $rltd_prd['is_deleted']    =   0;
                RelatedProduct::create($rltd_prd);
                }
                }

                AssociatProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
        if($assoc_arr){ foreach($assoc_arr as $k=>$ass){
            if(AssociatProduct::where('prd_id',$prdId)->where('ass_prd_id',$ass)->exists()){ AssociatProduct::where('prd_id',$prdId)->where('ass_prd_id',$ass)->update(['is_deleted'=>0]); }
            else{ AssociatProduct::create(['prd_id'=>$prdId,'ass_prd_id'=>$ass]); }
        } }


            }else {
            $prdId              =   $post->id; Product::where('id',$prdId)->update($prd);
            $prdDimensions           =   ProdDimension::where('prd_id',$prdId)->where('is_deleted',0)->orderBy('id','desc')->first();
            if(isset($prdDimensions)) {
             $dimension['updated_at'] = date("Y-m-d H:i:s"); 
             ProdDimension::where('prd_id',$prdId)->update($dimension);
            }else {
               $dimension['prd_id']    =   $prdId; $dimension['created_by']    =   auth()->user()->id;
            ProdDimension::create($dimension); 
            }
            if(isset($related_prd_id)){
                RelatedProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
                foreach($related_prd_id as $kv=>$rp_id) {
                $rltd_prd['prd_id']    =   $prdId;
                $rltd_prd['rel_prd_id']    =   $rp_id;
                $rltd_prd['created_by']    =   auth()->user()->id;
                $rltd_prd['is_deleted']    =   0;
                RelatedProduct::create($rltd_prd);
                }
                }
            $price['created_by']    =   auth()->user()->id; 
            $prdPrice           =   PrdPrice::where('prd_id',$prdId)->where('is_deleted',0)->orderBy('id','desc')->first();
            if($price['price']  !=  $prdPrice->price || $price['sale_price']  !=  $prdPrice->sale_price || $price['sale_start_date']  !=  $prdPrice->sale_start_date || $price['sale_end_date']  !=  $prdPrice->sale_end_date){
                $price['prd_id']=   $prdId; unset($price['tax']); PrdPrice::create($price);
                }
            }
        }
         
        // AssignedAttribute::where('prd_id',$prdId)->update(['is_deleted'=>1]);
        // if($attrs){ foreach     (   $attrs as $k=>$attr){ 
        //     $prdAttr            =   ['prd_id'=>$prdId,'attr_id'=>$k,'created_by'=>auth()->user()->id];
        //     if(!isset($attr['valId'])){ $attr['valId'] = NULL; } if(!isset($attr['value']) || $attr['value'] == ''){ $attr['value'] = NULL; }
        //     $prdAttr['attr_val_id'] = $attr['valId']; $prdAttr['attr_value'] = $attr['value'];
        //     if($attr['valId']  !=  NULL    ||  $attr['value'] != NULL){  
        //         if(AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$k)->exists()){ 
        //            $prdAttr['updated_by']   =   auth()->user()->id; $prdAttr['is_deleted'] = 0;
        //            AssignedAttribute::where('prd_id',$prdId)->where('attr_id',$k)->update($prdAttr);
        //         }else{ $prdAttr['updated_by'] = auth()->user()->id; AssignedAttribute::create($prdAttr); }
        //     }
        // } }
        $cmsContent             =   ['name_cnt_id'=>$name,'short_desc_cnt_id'=>$sDesc,'desc_cnt_id'=>$desc,'content_cnt_id'=>$content,'spec_cnt_id'=>$specification];
        if($post->id > 0)       {   $product = Product::where('id',$post->id)->first(); }else{ $product = false; }
        foreach($cmsContent     as  $k=>$content){ 
            if($product)        {   $cId = $product->$k; }else{ $cId = 0 ; }
            $cntId = $this->addCmsContent($cId,$post->lang_id,$content);
            Product::where('id',$prdId)->update([$k=>$cntId]);
        }
        
        if($images){ foreach($images as $k=>$image){
            $imgName            =   time().'.'.$image->extension();
            $path               =   '/app/public/products/'.$prdId;
            $destinationPath    =   storage_path($path.'/thumb');
            $img                =   Image::make($image->path());
            if(!file_exists($destinationPath)) { mkdir($destinationPath, 755, true);}
            $img->resize(250, 250, function($constraint){ $constraint->aspectRatio(); })->save($destinationPath.'/'.$imgName);
            $destinationPath    =   storage_path($path);
            $image->move($destinationPath, $imgName);
            $imgUpload          =   uploadFile($path,$imgName);
            $thumbUpload        =   uploadFile($path.'/thumb',$imgName);
            if($imgUpload){
                PrdImage::create(['prd_id'=>$prdId,'image'=>$path.'/'.$imgName,'thumb'=>$path.'/thumb/'.$imgName,'created_by'=>auth()->user()->id]);
            }
        } }
         if($videos){
            $vidName            =   time().'.'.$videos->getClientOriginalExtension();
            $path               =   '/app/public/products/'.$prdId; 
            $destinationPath    =   storage_path($path);
            $videos->move($destinationPath, $vidName);

            $vidUpload          =   vuploadFile($path,$vidName);
         
            if($vidUpload){
                ProductVideo::create(['prd_id'=>$prdId,'video'=>$path.'/'.$vidName,'created_by'=>auth()->user()->id]);
            }
         }  
        
        // if($post->id == 0 && $post->prd_type ==  2 && count($post->config) > 0){
        //     foreach($post->config   as $row){ AssConfigAttribute::create(['prd_id'=>$prdId,'attr_id'=>$row]); }
        // }
        // AssociatProduct::where('prd_id',$prdId)->update(['is_deleted'=>1]);
        // if($assosi){ foreach($assosi as $k=>$ass){
        //     if(AssociatProduct::where('prd_id',$prdId)->where('ass_prd_id',$k)->exists()){ AssociatProduct::where('prd_id',$prdId)->where('ass_prd_id',$k)->update(['is_deleted'=>0]); }
        //     else{ AssociatProduct::create(['prd_id'=>$prdId,'ass_prd_id'=>$k]); }
        // } }
        if($post->id == 0){ $msg    =   'Product added successfully!'; }else{ $msg    =   'Product updated successfully!'; }
        if($prdId){   return      back()->with('success',$msg); }else{ return back()->with('error','Somthing went wrong. Plese try again after some time.'); }
    }
    
    function addCmsContent($cntId,$l, $cnt){
        $qry                =   CmsContent::where('cnt_id',$cntId)->where('is_deleted',0)->first(); $insId = false;
        $query              =   CmsContent::where('cnt_id',$cntId)->where('is_deleted',0)->where('lang_id',$l)->first();
        if($query)          {   CmsContent::where('id',$query->id)->update(['content'=>$cnt,'updated_by'=>auth()->user()->id]); }
        else if($qry)       {   $insId   =   CmsContent::create(['cnt_id'=>$cntId,'lang_id'=>$l,'content'=>$cnt,'created_by'=>auth()->user()->id])->id; }
        else{
            $cms            =   CmsContent::orderBy('cnt_id','desc')->first(); if($cms){ $cntId = ($cms->cnt_id+1); }else{ $cntId = 1; }
            $insId          =   CmsContent::create(['cnt_id'=>$cntId,'lang_id'=>$l,'content'=>$cnt,'created_by'=>auth()->user()->id])->id;
        }
        return $cntId;
    }
    
    function assignStoreCategories($catIds,$storeId,$sellerId){
        StoreCategory::where('store_id',$storeId)->update(['is_deleted'=>1]);
        foreach($catIds as $cId){ 
            if(StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->count() > 0){ 
                StoreCategory::where('store_id',$storeId)->where('category_id',$cId)->update(['is_deleted'=>0]);
            }else{ StoreCategory::create(['seller_id'=>$sellerId,'store_id'=>$storeId,'category_id'=>$cId]); }
        } return true;
    }
    
    function updateStatus(Request $request){
        $post               =   (object)$request->post(); 
        $result             =   Product::where('id',$post->id)->update([$post->field => $post->value]);
        if($post->page      !=  'new_seller'){  Store::where('seller_id',$post->id)->update([$post->field => $post->value]); }
        if($post->field     ==  'is_deleted' || $post->field  == 'is_approved'){
//        $data['title']      =   'Seller List';
//        $data['sellers']    =   Seller::get();
//            return view('admin.seller.list.content',$data);
            Session::flash('success', $post->msg);
        }else{
            if($result){ return ['type'=>'success','id'=>$post->id]; }else{  return ['type'=>'warning','id'=>$post->id]; } 
        }
    }
    
    function associativeProducts(Request $request){
        $post                       =   (object)$request->post(); $prdIds = [0]; $products = false; $attrIds = $attrs = [];
        if(isset($post->attrIds))   {   $attrIds    =   $post->attrIds; }
        if(count($attrIds) > 0){
            $attrs                  =   PrdAttribute::whereIn('id',$attrIds)->where('is_deleted',0)->get();
            foreach($attrIds        as  $k=>$rw){ 
               if($k == 0){ $qry    =   AssignedAttribute::where('attr_id',$rw)->where('is_deleted',0)->get(); }
               else{ $qry           =   AssignedAttribute::whereIn('prd_id',$prdIds)->where('attr_id',$rw)->where('is_deleted',0)->get(); }
               $prdIds              =   [];
               if($qry){ foreach    (   $qry  as  $row){ $prdIds[] = $row->prd_id; }  }
           //    print_r($prdIds); die;
               if(count($prdIds)    ==  0){                   break; }
            }
        }
        if(count($prdIds)           >   0){ $products = Product::whereIn('id',$prdIds)->where('seller_id',$post->sellerId)->where('is_approved',1)->where('is_active',1)->where('is_deleted',0)->get(); }
        $data['assPrds']            =   $products; $data['attrs'] = $attrs; $data['unassAssoPrds'] = false; $data['assAssoPrdIds'] = [];
      //   echo '<pre>'; print_r($data['assPrds']); echo '</pre>'; die;
        return view('seller.my_products.details.associative_prds',$data);
    }
    
    function getAssosiProducts($attrIds,$sellerId,$assgned){
        if(count($attrIds) > 0){
            $attrs                  =   PrdAttribute::whereIn('id',$attrIds)->where('is_deleted',0)->get();
            foreach($attrIds        as  $k=>$rw){ 
               if($k == 0){ $qry    =   AssignedAttribute::where('attr_id',$rw)->where('is_deleted',0)->get(); }
               else{ $qry           =   AssignedAttribute::whereIn('prd_id',$prdIds)->where('attr_id',$rw)->where('is_deleted',0)->get(); }
               $prdIds              =   [];
               if($qry){ foreach    (   $qry  as  $row){ $prdIds[] = $row->prd_id; }  }
           //    print_r($prdIds); die;
               if(count($prdIds)    ==  0){                   break; }
            }
        }
        if(count($prdIds)           >   0){ $products = Product::whereIn('id',$prdIds)->where('seller_id',$sellerId)->where('is_approved',1)->where('is_active',1)->where('is_deleted',0)->get(); }
        return $products; // $data['attrs'] = $attrs; $data['unassAssoPrds'] = false;
    }
    
    function getConficAttrPrds($prdId){
         $res = $attrs          =   []; $attrIds = $res = false;
        $query                  =   AssConfigAttribute::where('prd_id',$prdId)->where('is_deleted',0)->get();
        if($query){ foreach     (   $query as $row){ $attrIds[] = $row->attr_id; } }
        if($attrIds){
            $res['attrIds']     =   $attrIds;
            $res['attrs']       =   PrdAttribute::whereIn('id',$attrIds)->where('is_active',1)->where('is_deleted',0)->get();
        } return $res;
    }
    
    function getAssignedAssosiProducts($prdId){  $assPrdIds = [];
        $query                  =   AssociatProduct::where('prd_id',$prdId)->where('is_deleted',0)->get(['ass_prd_id']);
        if($query){ foreach     (   $query as $row){ $assPrdIds[]   =   $row->ass_prd_id; } }else{ $assPrdIds = []; }
        return $assPrdIds;
    }
    
     function specialOffer($prd_id){
        $data['title']              =   'Product Special Offer';
        $data['menuGroup']          =   'sellerGroup';
        $data['menu']               =   'specialoffer';
        $offer                     =   PrdOffer::where('prd_id',$prd_id)->where('is_deleted',0)->first();
        if(isset($offer)){ 
            $data['offer']             =   $offer;
         }
         $data['prd_id']             =   $prd_id;
        // dd($data);
        return view('seller.my_products.offer',$data);
    }


     function saveOffer(Request $request){
        $post                   =   (object)$request->post(); 
       $validator= $request->validate([
        'discount_value'   =>  ['required'],
        'quantity_limit' => ['required'],
        'valid_from' => ['required'],
        'valid_to' => ['required']

        ], [], 
        [
        'discount_value' => 'Discount Value',
        'quantity_limit' => 'Quantity Limit',
        'country' => 'Country',
        'valid_from' => 'Valid From',
        'valid_to' => 'Valid To'
        ]);

        $prd_id                    =   $post->prd_id;
        $offr_arr = [];
        $offr_arr['org_id'] = 1;
        $offr_arr['prd_id'] = $prd_id;
        $offr_arr['discount_value'] = $post->discount_value;
        $offr_arr['discount_type'] = $post->discount_type;
        $offr_arr['quantity_limit'] = $post->quantity_limit;
        $offr_arr['valid_from'] = $post->valid_from;
        $offr_arr['valid_to'] = $post->valid_to;
        $offr_arr['is_active'] = $post->is_active;
        $offr_arr['is_deleted'] = 0;
        
        if($post->id >0){
        $offr_arr['updated_by'] = auth()->user()->id;
        $offr_arr['updated_at'] = date("Y-m-d H:i:s");
        $offrId =  PrdOffer::where('id',$post->id)->update($offr_arr); 
         $msg    =   'Special Offer updated successfully!';
        }else {
        $offr_arr['created_by'] = auth()->user()->id;
        $offrId                  =   PrdOffer::create($offr_arr)->id;
         $msg    =   'Special Offer added successfully!';

        }
        
       
        if($offrId){   return      back()->with('success',$msg); }else{ return back()->with('error','Somthing went wrong. Plese try again after some time.'); }
    }
    
    function stocks(){
        $data['title']              =   'Product Stock List';
        $data['menuGroup']          =   'sellerGroup';
        $data['menu']               =   'stock';
        $stocks                     =   PrdStock::where('is_deleted',0)->groupBy('prd_id')->get();
        if($stocks){ foreach        (   $stocks as $row){
            $row->stock             =   PrdStock::where('prd_id',$row->prd_id)->where('is_deleted',0)->sum('qty');
            $data[]                 =   $row;
        } }
        $data['stocks']             =   $stocks;
        return view('seller.stock.list',$data);
    }
    
    function stock(Request $request){
        $post                       =  (object) $request->post();
        $data['title']              =   'Addd Stock';
        $data['menuGroup']          =   'sellerGroup';
        $data['menu']               =   'stock';
        $data['product']            =   Product::where('id',$post->prdId)->first();
        $data['seller']             =   SellerInfo::where('seller_id',$post->sellerId)->first();
        $data['price']              =   PrdPrice::where('prd_id',$post->prdId)->where('is_deleted',0)->orderBy('id','desc')->first();
        return view('seller.stock.details',$data);
    }
    
    function saveStock(Request $request){
        $post                       =  (object) $request->post();
     //   echo '<pre>'; print_r($post); echo '</pre>';
        $insId                      =   PrdStock::create($post->stock)->id;
        if($insId){ $type           =   'success'; $msg = 'Stock added successfully!'; }
        else{ $type = 'error';          $msg = 'Somthing went wrong. Please try after some time'; }
        return back()->with($type,$msg);
    }
        function importProducts(Request $request){
        $imp_file               =   $request->file('imp_file');
       
        $request->validate([
        'imp_file' => 'required'
        ]);

         if($imp_file){ 
            $fileName            =  'prod_import_'.time().'.'.$imp_file->getClientOriginalExtension();
            $path = '/app/public/products/imports';
            $destinationPath    =   storage_path('/app/public/products/imports/');
            $imp_file->move($destinationPath, $fileName);

            PrdImports::create(['file'=>$path."/".$fileName,'user_id'=>auth()->user()->id,'created_at'=>time()]);
            // dd(url('storage/'.$path."/".$fileName));
    
            Excel::import(new ProductImport, storage_path('/app/public/products/imports/')."/".$fileName);

           
            return redirect()->back();   
        }
    }
    
    function editorImage(Request $request){
        $input = $request->all();
       $image = $input['image']; 

     $imgName            =   time().'.'.$image->extension();
            $path               =   '/app/public/products/editor/';
            
            $img                =   Image::make($image->path());
           
            $destinationPath    =   storage_path($path);
            $image->move($destinationPath, $imgName);
            $image_url = $path.$imgName; 
            $image_url = url('storage/'.$image_url);
           return $image_url;


       
    }
    
    function deletePrdImg(Request $request){
        $res                    =   PrdImage::where('id',$request->post('imgId'))->where('is_deleted',0)->update(['is_deleted'=>1]);
        if($res){ return 'success'; }else{ return 'error'; }
    }

}
