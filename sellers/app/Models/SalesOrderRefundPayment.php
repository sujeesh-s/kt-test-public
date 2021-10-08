<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderRefundPayment extends Model
{
    use HasFactory;
    protected $fillable = ['ref_id','source','refund_mode','total','bank_name','account_number','branch_name','ifsc_code'];
}
