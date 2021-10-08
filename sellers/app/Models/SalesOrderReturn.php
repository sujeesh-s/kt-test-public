<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderReturn extends Model
{
    use HasFactory;
    protected $fillable     =   ['sales_id','seller_id','sales_item_id','user_id','prd_id','qty','amount','reason','desc','status','payment_status','issue_item'];
     public function order(){ return $this->belongsTo(SalesOrder::class, 'sales_id'); }
     public function product(){ return $this->belongsTo(Product::class, 'prd_id'); }
     public function shipment(){  return $this->hasOne(SalesOrderReturnShipment::class, 'return_id'); }
    
}

