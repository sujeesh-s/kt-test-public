<?php

namespace App\Http\Controllers\Api\seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;
use DB;
use App\Models\Admin;
use App\Models\AppVersion;
use App\Models\Store;
use App\Models\Seller;
use App\Models\SellerInfo;
use App\Models\SellerSecurity;
use App\Models\SellerTelecom;
use App\Models\SellerLogin;
use App\Models\Staff;
use App\Models\StaffInfo;
use App\Models\StaffSecurity;
use App\Models\PasswordReset;
use App\Models\Email;
use App\Models\SellerOTP;
use App\Models\SellerNotification;
use App\Models\Telecom;

use App\Rules\Name;
use Validator;

class AppSettings extends Controller
{
    public function version_check(Request $request)
    {
        $validator = Validator::make($request->all(), ['app_name'=>['required','string']]);
        $input = $request->all();

        if ($validator->passes()) {
            $version = AppVersion::where('seller_app_name',$input['app_name'])->first();
            if($version)
            {
              return ['httpcode'=>200,'status'=>'success','message'=>'App version','data'=>['App_name'=>$version->seller_app_name,'version'=>$version->seller_app_version]];  
            }
            else
            {
                return ['httpcode'=>400,'status'=>'error','message'=>'Invalid version name','data'=>['errors'=>'Invalid version name']];
            }

        }
        else
        {
            return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameter','data'=>['errors'=>$validator->errors()->all()]];
        }
    }
}
