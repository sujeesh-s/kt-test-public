<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerLogin extends Model
{
    use HasFactory;
    protected $table = 'usr_seller_logins';
    protected $fillable = ['seller_id', 'device_id', 'device_name','access_token','is_login','device_token','os','last_login','login_at'];
}
