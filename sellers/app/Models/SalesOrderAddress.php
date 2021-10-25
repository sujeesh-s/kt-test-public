<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderAddress extends Model
{
    use HasFactory;
    protected $table = 'sales_order_adderss';
    protected $fillable = ['sales_id','cust_id', 'addr_id', 'name','phone','email', 'address1', 'address2','zip_code','city', 'state','country', 'latitude', 'longitude',];
    
        public function bcountry(){ return $this->belongsTo(Country::class, 'country'); }
    public function bstate(){ return $this->belongsTo(State::class, 'state'); }
    public function bcity(){ return $this->belongsTo(City::class, 'city'); }
    public function type(){ return $this->belongsTo(CustomerAddressType::class, 'addr_id'); }
    public function scountry(){ return $this->belongsTo(Country::class, 's_country'); }
    public function sstate(){ return $this->belongsTo(State::class, 's_state'); }
    public function scity(){ return $this->belongsTo(City::class, 's_city'); }
    public function stype(){ return $this->belongsTo(CustomerAddressType::class, 's_addr_id'); }
}
