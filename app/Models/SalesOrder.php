<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;
    protected $fillable     =   [
                                    'org_id','parent_sale_id','order_id','cust_id','seller_id','total','discount','tax','packing_charge','wallet_amount','bid_charge','g_total',
                                    'discount_type','coupon_id','order_status'
                                ];
    public function seller(){ return $this->belongsTo(Seller ::class, 'seller_id'); }
    public function customer(){ return $this->belongsTo(Customer::class, 'cust_id'); }
    public function address(){ return $this->hasOne(SalesOrderAddress ::class, 'sales_id'); }
    public function payment(){ return $this->hasOne(SalesOrderPayment ::class, 'sales_id'); }
    public function shipping(){ return $this->hasOne(SalesOrderShipping ::class, 'sales_id'); }
    public function calcel(){ return $this->hasOne(SalesOrderCancel ::class, 'sales_id')->latest(); }
    public function payments(){ return $this->hasMany(SalesOrderPayment ::class, 'sales_id'); }
    public function items(){ return $this->hasMany(SalesOrderItem ::class, 'sales_id'); }
        
    public function totEarnings($sellerId){ return SalesOrder::where('seller_id',$sellerId)->where('payment_status','success'); }
    public function paidSettlement($sellerId){ return Settlement::where('seller_id',$sellerId)->where('is_deleted',0)->sum('amount'); }
}

