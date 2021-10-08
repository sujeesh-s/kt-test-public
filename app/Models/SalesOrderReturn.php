<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderReturn extends Model
{
    use HasFactory;
    protected $fillable     =   ['sales_id','sales_item_id','seller_id','user_id','prd_id','qty','amount','reason','desc','status','payment_status','issue_item'];
    
}

