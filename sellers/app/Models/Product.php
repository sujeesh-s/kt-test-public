<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Product extends Model{
    use HasFactory;
    protected $table = 'prd_products';
    protected $fillable = [
        'seller_id','product_type','sku','category_id','sub_category_id','brand_id','tax_id','tag_id','name',' name_cnt_id ','short_desc_cnt_id','desc_cnt_id','content_cnt_id','spec_cnt_id','is_featured','daily_deals',
        'is_out_of_stock','out_of_stock_selling','min_stock_alert','commission','commi_type','is_approved','visible','admin_prd_id','is_active','created_by'
    ];
    public function prdType(){ return $this->belongsTo(ProductType ::class, 'product_type'); }
    public function category(){ return $this->belongsTo(Category ::class, 'category_id'); }
    public function seller(){ return $this->belongsTo(SellerInfo ::class, 'seller_id'); }
    public function tax(){ return $this->belongsTo(Tax ::class, 'tax_id'); }
    public function subCategory(){ return $this->belongsTo(Subcategory ::class, 'sub_category_id'); }
    public function brand(){ return $this->belongsTo(Brand ::class, 'brand_id'); }
    public function prdPrice(){ return $this->hasOne(PrdPrice ::class, 'prd_id')->latest(); }    
    public function prdTag(){ return $this->hasMany(PrdAssignedTag::class, 'prd_id'); } 
    public function assAttrs(){ return $this->hasMany(AssignedAttribute ::class, 'prd_id')->latest(); } 
    public function prdImage(){ return $this->hasMany(ProductImage ::class, 'prd_id')->where('is_deleted',0); }
    
    public function Store($seller_id){ return DB::table('usr_stores')->where('seller_id', $seller_id)->first(); }
    
    public function prdStock($prdId){ 
        $in             =   (int)PrdStock ::where('prd_id',$prdId)->where('type','add')->where('is_deleted',0)->sum('qty'); 
        $out            =   (int)PrdStock ::where('prd_id',$prdId)->where('type','destroy')->where('is_deleted',0)->sum('qty'); 
        return ($in-$out);
    }    
    
    public function assignedAttrs($prdId) {
        $query          =   AssignedAttribute::where('prd_id',$prdId)->where('is_deleted',0)->get();$data = array();
        if($query)      {   foreach($query as $row){ $data[$row->attr_id] = $row; } }else{ $data = []; } return $data;
    }


    static function ValidateUnique($field,$value,$id,$sellerId) {
        $query                      =   Product::where($field,$value)->where('seller_id',$sellerId)->where('is_deleted',0)->first();
        if($query){ if($query->id   !=  $id){ return 'This '.$field.' already has been taken'; }else{ return false; } }else{ return false; }
    }
    
    static function get_content($field_id){ 

        $language =DB::table('glo_lang_lk')->where('is_active', 1)->first();
        $content_table=DB::table('cms_content')->where('cnt_id', $field_id)->where('lang_id', $language->id)->first();
        if($content_table){ 
        $return_cont = $content_table->content;
        return $return_cont;
        }
        else
            { return false; }
        }
}
