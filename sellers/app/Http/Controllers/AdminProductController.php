<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Tag;
use App\Models\Tax;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\AdminProduct;
use App\Models\Language;
use App\Models\ProductType;
use App\Models\PrdPrice;
use App\Models\PrdImage;
use App\Models\PrdStock;
use App\Models\PrdAttribute;
use App\Models\PrdAttributeValue;
use App\Models\AssignedAttribute;
use App\Models\SellerInfo;

use Validator;

class AdminProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:seller');
    }
    public function products(Request $request){ // echo Auth::user()->id; die;
        $post                       =   (object)$request->post(); $res = [];
        if(isset($post->viewType))  {   $viewType = $post->viewType; }else{ $viewType = ''; }
        $data['title']              =   'Admin Products';
        $data['menuGroup']          =   'productGroup';
        $data['menu']               =   'admin_product';
        $data['active']             =   '';
        $products                   =   AdminProduct::where('is_active',1)->where('is_deleted',0);
        if(isset($request->active)  &&  $request->active != ''){ 
            $products               =   $products->where('is_active',$request->active); 
            $data['active']         =   $request->active;
        }
        if(isset($post->vType)       ==  'ajax'){
           $search                  =   (isset($post->search['value']))? $post->search['value'] : ''; 
           $start                   =   (isset($post->start))? $post->start : 0; 
           $length                  =   (isset($post->length))? $post->length : 10; 
           $draw                    =   (isset($post->draw))? $post->draw : ''; 
           $totCount                =   $products->count(); $filtCount  =   $products->count();
           if($search != ''){
                $products           =   $products->where(function($qry) use ($search){
                                            $qry->where('name', 'LIKE', '%'.$search.'%');
                                            $qry->orWhereIn('category_id', $this->getCatPrdIds($search));
                                            $qry->orWhereIn('sub_category_id', $this->getSubCatPrdIds($search));
                                        });
                $filtCount          =   $products->count();
           }
           if($length>0){$products  =   $products->offset($start)->limit($length); }
           $products                =   $products->orderBy('id','desc')->get();
           if($products){ foreach   (   $products as $row){ $action = '';
               if($row->is_active   ==  1){ $checked    = 'checked="checked"'; $act = 'Active'; }else{ $checked = '';  $act = 'Inactive'; }
               $val['id']           =   '';                                
               $val['name']         =   '<a id="dtlBtn-'.$row->id.'" class="font-weight-bold viewDtl">'.$row->name.'</a>';
               $val['cat']          =   $row->category->cat_name;      
               $val['sub_cat']      =   $row->subCategory->subcategory_name;
               $val['status']       =   '<span class="badge badge-success mt-2">Active</span>';
               $res[] = $val;  
           } }
           $returnData = array(
			"draw"            => $draw,   
			"recordsTotal"    => $totCount,  
			"recordsFiltered" => $filtCount,
			"data"            => $res   // total data array
			);
            return $returnData;
        }
      //  $data['products']           =   $products->orderBy('id','desc')->get();
        if($viewType == 'ajax') {   return view('admin_product.list',$data); }else{ return view('admin_product.page',$data); }
    }
    public function product(Request $request,$id=0,$sellerId=0,$type=''){
        $data['title']              =   'Admin Product';
        $data['menu']               =   'Admin Product';
        $data['product']            =   AdminProduct::where('id',$id)->first();
        $data['filters']            =   $request->post();
        if($type == 'view')         {   $title = 'View Product'; }else if($id > 0){ $title = 'Edit Product'; }else{ $title = 'Add Product'; }
        $data['title']              =   $title; 
        if($type                    ==  'new'){ $data['title']   =   'View Product Detail'; }
        $data['categories']         =   getDropdownData(Category::where('is_deleted',0)->get(),'category_id','cat_name');
        $data['sub_cats']           =   getDropdownData(Subcategory::where('is_deleted',0)->get(),'subcategory_id','subcategory_name');
        $data['brands']             =   getDropdownCmsData(Brand::where('is_active',1)->where('is_deleted',0)->get(),'id','brand_name_cid');
        $data['taxes']              =   getDropdownCmsData(Tax::where('is_active',1)->where('is_deleted',0)->get(),'id','tax_name_cid');
        $data['languages']          =   getDropdownData(Language::where('is_active',1)->where('is_deleted',0)->get(),'id','glo_lang_name');
        if($sellerId                >   0){ $data['seller'] = SellerInfo::where('seller_id',$sellerId)->first(); }
        $data['attributes']         =   $this->getAttributes();
        $data['images']             =   PrdImage::where('prd_id',$id)->where('is_deleted',0)->get();
        $data['isAdded']            =   Product::where('seller_id',auth()->user()->id)->where('admin_prd_id',$id)->where('is_deleted',0)->exists();
        if($type == 'add'){ return view('admin_product.details',$data); }
        return view('admin_product.view',$data);
    }
    
    function getAttributes(){
        $qry                        =   PrdAttribute::where('is_active',1)->where('is_deleted',0)->get();
        if($qry){ foreach($qry      as  $k=>$row){
            $attrs[$k]              =   $row; 
            $attrs[$k]['values']    =   $this->getAttrValues($row->id);
        } return $attrs; }else{         return false; }
    }
    
    function getAttrValues($attrId){ return   PrdAttributeValue::where('attr_id',$attrId)->where('is_active',1)->where('is_deleted',0)->get(); }
    
    function getCatPrdIds($keyword){
        $query              =   Category::where('cat_name', 'LIKE', '%'.$keyword.'%')->where('is_deleted',0); $ids = [0];
        if($query->count()  >   0)  {   foreach($query->get() as $row){ $ids[]    =   $row->category_id; }}return $ids; 
    }
    function getSubCatPrdIds($keyword){
        $query              =   Subcategory::where('subcategory_name', 'LIKE', '%'.$keyword.'%')->where('is_deleted',0); $ids = [0];
        if($query->count()  >   0)  {   foreach($query->get() as $row){ $ids[]    =   $row->subcategory_id; }}return $ids; 
    }
}