<?php
use App\Models\CmsContent;
use App\Models\UserRole;
use App\Models\SellerInfo;
use App\Models\Setting;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\Modules;
use App\Models\Staff;
use App\Models\StaffInfo;
use App\Models\AppVersion;
if (!function_exists('geSiteName')) {

    function geSiteName() {
        $data = DB::table('settings')->where('type', 'site_name')->where('is_deleted',0)->first();
        if ($data) {
            return $data->value;
        } else {
            return 'BS';
        }
    }

}if (!function_exists('geAdminName')) {

    function geAdminName() {
        $data = DB::table('settings')->where('type', 'admin_name')->where('is_deleted',0)->first();
        if ($data) {
            return $data->value;
        } else {
            return 'Admin';
        }
    }

}if (!function_exists('geAdminEmail')) {

    function geAdminEmail() {
        $data = DB::table('settings')->where('type', 'admin_email')->where('is_deleted',0)->first();
        if ($data) {
            return $data->value;
        } else {
            return 'admin@gameday.com';
        }
    }

}if (!function_exists('getCurrency')){
    function getCurrency(){
        $data['name'] = 'RM'; $data['symbol'] = 'RM'; 
        $query = Setting::where('type','currency_code')->where('is_active',1)->where('is_deleted',0)->first();
        if($query) $data['name'] = $query->value;
        $query = Setting::where('type','currency_symbol')->where('is_active',1)->where('is_deleted',0)->first();
        if($query) $data['symbol'] = $query->value;
         return (object)$data;
    }
}if (!function_exists('avatar')){
    function avatar($id){
        $avatar                 =   DB::table('users')->where('user_id',$id)->first()->avatar;
        if($avatar == NULL      ||  $avatar == ''){ $avatar = '/app/public/no-avatar.png'; }
        return $avatar;
    }
}if (!function_exists('notifyCount')){
    function notifyCount($id){ return DB::table('users')->where('id',$id)->first()->notify; }
}if (!function_exists('addNotification')){
     function addNotification($from,$utype,$to,$ntype,$title,$desc,$refId,$reflink,$notify){
        if($notify                ==  'admin'){
             DB::table('admin_notifications')->insert(['notify_from'=>$from,'user_type'=>$utype,'notify_to'=>$to,'notify_type'=>$ntype,'title'=>$title,'description'=>$desc,'ref_id'=>$refId,'ref_link'=>$reflink,'created_at'=>date('Y-m-d H:i:s')]);
        }
        else if($notify                ==  'customer'){
             DB::table('usr_notifications')->insert(['notify_from'=>$from,'user_type'=>$utype,'notify_to'=>$to,'notify_type'=>$ntype,'title'=>$title,'description'=>$desc,'ref_id'=>$refId,'ref_link'=>$reflink,'created_at'=>date('Y-m-d H:i:s')]);
        }
    }
}if (!function_exists('getNotifications')){
    function getNotifications(){
        return DB::table('notifications as N')->select('N.*','U.user_role')->join('users as U','N.user_id','=','U.id')->where('N.status',1)->orderBy('N.id','desc')->limit(25)->get();
    }
}if (!function_exists('getDropdownValues')){
    function getDropdownValues($table,$field,$value,$label){
        return DB::table($table)->where($field,$value)->where('is_deleted',0)->orderBy($label,'asc')->get();
    }
}if (!function_exists('getDropdownData')){
    function getDropdownData($data,$value,$label,$label2='') { $res =   [];
        if($data){ foreach($data as $row){ if($label2 != ''){ $res[$row->$value] = $row->$label.' '.$row->$label2; }else{ $res[$row->$value] = $row->$label; } } } return $res; 
    }
}if (!function_exists('getDropdownCmsData')){
    function getDropdownCmsData($data,$value,$label,$label2='') { $res =   [];
        $query          =   Setting::where('type','default_lang')->where('is_active',1)->first();
        if($query){        $langId = $query->value;}else{ $langId = 1; }
        if($data){ foreach($data as $row){ if($label2 != ''){ $res[$row->$value] = getContent($row->$label,$langId).' '.getContent($row->$label2,$langId); }else{ $res[$row->$value] = getContent($row->$label,$langId); } } } return $res; 
    }
}if (!function_exists('defaultLangId')){
    function defaultLangId() { 
        $query          =   Setting::where('type','default_lang')->where('is_active',1)->first();
        if($query){ return $query->value;}else{ return 1; }
    }
}if (!function_exists('roleData')) {
    function roleData() { return UserRole::where('id',3)->first(); }
}
if (!function_exists('sellerData')) {
     function sellerData() { if(SellerInfo::where('seller_id', auth()->user()->id)->first()){
         return SellerInfo::where('seller_id', auth()->user()->id)->first();
    }else {
        return StaffInfo::where('staff_id', auth()->user()->id)->first();
    } }
}if (!function_exists('getContent')) {
    function getContent($cntId=0, $langId=1){ 
        $content            =   CmsContent::where('cnt_id', $cntId)->where('lang_id', $langId)->where('is_deleted', 0)->first(); 
        if($content){            return $content->content; }
        $content            =   CmsContent::where('cnt_id', $cntId)->where('lang_id', defaultLangId())->where('is_deleted', 0)->first(); 
        if($content){            return $content->content; } return '';
    }
}if (!function_exists('countryList')) {
    function countryList(){ 
        return getDropdownData(DB::table('countries')->where('is_deleted',0)->get(),'id','country_name'); }
}if (!function_exists('categoryList')) {
    function categoryList(){ return getDropdownData(DB::table('category')->where('is_active',1)->where('is_deleted',0)->get(),'category_id','cat_name'); }
}if (!function_exists('uploadFile')) {
    function uploadFile($path,$fileName){ 
        $strageFile  =   url('storage').$path.'/'.$fileName; 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, config('app.upload_url').'/file/upload');
        $postData = array(
            'file'  =>  base64_encode(file_get_contents($strageFile)),
            'path'  =>  $path,   'fileName'  =>  $fileName,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        return $response;
    }
}

if (!function_exists('vuploadFile')) {
    function vuploadFile($path,$fileName){ 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, config('app.upload_url').'/file/upload');
        $postData = array(
            'file'  =>  base64_encode(file_get_contents(url('storage'.$path.'/'.$fileName))),
            'path'  =>  $path,   'fileName'  =>  $fileName,
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        return $response;
    }
}

if (!function_exists('getCities')) {
    function getCities($city_id){ 
        $city            =   City::where('id', $city_id)->first(); 
        $city_data = [];
        if($city){           
            $city_data['city'] = $city->city_name;
            $state            =   State::where('id', $city->state_id)->first(); 
            if($state){           
            $city_data['state'] = $state->state_name;

            $country            =   Country::where('id', $state->country_id)->first(); 
            if($country){
            
            $city_data['country'] = $country->country_name;
            }else {
            $city_data['country'] = '';
            }
            }else{
            $city_data['state'] = '';
            }
         }else {
            $city_data['city'] = '';
         }
        
        if($city_data){            return $city_data; } return '';
    }
}
if (!function_exists('checkPermission')) {
    function checkPermission($slug,$act){ 
    if(auth()->user()->role_id ==3) {
        return true;
    }else {

       $check_perm = Modules::checkPermission(StaffInfo::where("staff_id",auth()->user()->id)->first()->staff_role_id,$act); 
    }
    
        // dd($check_perm);
    return $check_perm;        
    }
}

if (!function_exists('sellerIdArray')) {
    function sellerIdArray(){ 
    $ret_arr = array();
    $ret_arr[] = auth()->user()->id;
    $childs = Staff::where("seller_id",auth()->user()->id)->whereIn('id',function($query) {
   $query->select('staff_id')->from('usr_staff_info')->where("is_active",1);})->get();
    if($childs){
        foreach($childs as $row){
           $ret_arr[] = $row->id; 
        }
    }
    if(isset(auth()->user()->seller_id)) { $ret_arr[] =  auth()->user()->seller_id; }

    return $ret_arr;        
    }
}
if (!function_exists('sidebarMenu')) {
    function sidebarMenu(){ 
        // dd(Modules::visibleModules(auth()->user()->role_id));
        if(auth()->user()->role_id ==3) {
            $menu_list = Modules::visibleModules(3);
        }else {
            $menu_list = Modules::visibleModules(StaffInfo::where("staff_id",auth()->user()->id)->first()->staff_role_id);
        }
        if($menu_list){
          if(count($menu_list) >0){
            return $menu_list;
        }else {
           return array();  
        }  
        }else {
           return array(); 
        }
        
        
        
    }
}
if (!function_exists('appVersion')) {
    function appVersion($type){ 
    if($type =="admin") {
        return AppVersion::where('id', 1)->first()->admin_web; 
    }
    else{
        return AppVersion::where('id', 1)->first()->seller_web; 
    }
        
    }
}
