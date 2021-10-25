<?php

namespace App\Http\Controllers\Seller\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Seller;
use App\Models\SellerInfo;
use App\Models\SellerSecurity;
use App\Models\Staff;
use App\Models\StaffInfo;
use App\Models\StaffSecurity;
use App\Models\PasswordReset;
use App\Models\Email;
use App\Models\SellerOTP;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
         $this->middleware('guest')->except('logout');
        $this->middleware('guest:seller')->except('logout');
    }
    public function showSellerLoginForm(){ 
        return view('seller.auth.login');
    }

    public function sellerLogin(Request $request){
        
        $remember_me = $request->has('remember') ? true : false;
        
        if (Auth::guard('seller')->attempt(['username' => $request->email, 'password' => '123456'],$remember_me)) {
            $security       =   SellerSecurity::where('seller_id',Auth::guard('seller')->user()->id)->first();
            if(Hash::check($request->password, $security->password_hash)){ }else{ Auth::guard('seller')->logout(); return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. '); }
            if(SellerInfo::where('seller_id', Auth::guard('seller')->user()->id)->first()) {
                if(SellerInfo::where('seller_id', Auth::guard('seller')->user()->id)->first()->is_approved == 1){ return redirect()->intended('/dashboard'); }
                else{
                    Auth::guard('seller')->logout(); $request->session()->flush(); $request->session()->regenerate();
                    //return redirect('/login')->withInput($request->only('email', 'remember'))->with('message',' The seller is not approved yet. ');
                    return redirect(route('approval'));
                }
            }else{ return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. '); }
        }else if(Auth::guard('staff')->attempt(['username' => $request->email, 'password' => '123456'])) {

            $security       =   StaffSecurity::where('staff_id',Auth::guard('staff')->user()->id)->first();
           
            if(Hash::check($request->password, $security->password_hash)){ }else{   Auth::guard('staff')->logout(); return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. '); }
            if(StaffInfo::where('staff_id', Auth::guard('staff')->user()->id)->first()) {

                if(StaffInfo::where('staff_id', Auth::guard('staff')->user()->id)->first()->is_active == 1){ 
                     
                     return redirect()->intended('/dashboard'); }
                else{

                    Auth::guard('staff')->logout(); $request->session()->flush(); $request->session()->regenerate();
                    return redirect('/login')->withInput($request->only('email', 'remember'))->with('message',' The staff is not activated yet. ');
                }
            }else{  return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. '); }
        }else {
          
        }
        return back()->withInput($request->only('email', 'remember'))->with('message',' These credentials do not match our records. ');
    }
    
    function forgotPassword(Request $request){
        $post = (object)$request->post();
        $user           =   Seller::where('username',$post->email)->first();
        if($user){          $seller = SellerInfo::where('seller_id',$user->id)->first(); 
            if ($seller) {
                if ($seller->is_active == 0 || $seller->is_deleted == 1) {
                    return redirect('/password/reset')->with('message', 'This account not activated or disabled')->withInput();
                }else{
                    $resetLink = base64_encode(rand(100000, 999999) . 'resetpassword' . time() . '1');
                    $resetLink = urlencode($resetLink);
                    $currTime = date('Y-m-d H:i:s');
                    $data = array('active_link' => $resetLink, 'email_verified_at' => $currTime);
                    $msg = '<h4>Hi, ' . $seller->fname . ' </h4>';
                    $msg .= 'You can reset password of ' . ucfirst(geSiteName()) . ' seller portal through this <a href="' . url('/reset/password/' . $resetLink) . '">Reset Password</a> link.';
                    $update = PasswordReset::create(['user_id'=>$user->id,'user_type'=>'seller','email'=>$post->email,'token'=>$resetLink]);
                    if ($update) Email::sendEmail(geAdminEmail(), $post->email, 'Reset Password', $msg);
                    
                    return redirect('/password/reset')->with('success', "Reset password link sent to your registered email!");
                }
            }
        }
        return redirect('/password/reset')->with('message', "We can't find a user with that e-mail address.")->withInput();
    }
    
    public function resetPassword($token){
        $reset = PasswordReset::where('token',$token)->where('is_deleted',0)->first();
        if ($reset){ // echo date('YmdHi',strtotime($reset->created_at)).' > '.date('YmdHi', strtotime('-20 minutes', strtotime(date('Y-m-d H:i:s'))))); die;
            if(date('YmdHi',strtotime($reset->created_at)) > date('YmdHi', strtotime('-20 minutes', strtotime(date('Y-m-d H:i:s'))))){
                return view('auth.passwords.reset', compact('reset')); 
            }else{ return redirect('/login')->with('error', 'Expired authentication link.'); }
        }else{ return redirect('/login')->with('error', 'Invalid authentication link.'); }
    }

    public function updatePassword(Request $request){
        $post               =   (object)$request->post();
        $reset              =   PasswordReset::where('token',$post->token)->where('is_deleted',0)->first();
        if($reset) {
            $update         =   $this->updateUserPassword($reset,$post);
            return redirect('/login')->with('success', 'Password reset successfully');
        }else{ return redirect('/login')->with('error', 'Invalid authentication link.'); }
    }
    
    function updateUserPassword($data,$post){
        $password                   =   Hash::make($post->password);
        if($data->user_type         ==  'admin'){       return  Admin::where('id',$data->user_id)->update(['password'=>$password]); }
        else if($data->user_type    ==  'seller'){      return  SellerSecurity::where('seller_id',$data->user_id)->update(['password_hash'=>$password]); }
        else if($data->user_type    ==  'customer'){    return  CustomerSecurity::where('customer_id',$data->user_id)->update(['password_hash'=>$password]); }
    }
    public function showApproval(){ 
        return view('auth.approval');
    }
    public function otpPage(){ 
        return view('auth.otp');
    }
    public function sellerOtp(Request $request){
         $post               =   (object)$request->post();
    //   dd($post);
      $reset = SellerOTP::where('token',$post->token)->where('is_deleted',0)->first();
        if ($reset){ // echo date('YmdHi',strtotime($reset->created_at)).' > '.date('YmdHi', strtotime('-20 minutes', strtotime(date('Y-m-d H:i:s'))))); die;
            if(date('YmdHi',strtotime($reset->created_at)) > date('YmdHi', strtotime('-155 minutes', strtotime(date('Y-m-d H:i:s'))))){
                SellerOTP::where('token',$post->token)->update(['is_deleted'=>1,'verified_at'=>date('Y-m-d H:i:s')]);
                return view('auth.passwords.create', compact('reset')); 
            }else{ 
                Session::flash('message', ['text'=>'OTP is expired.','type'=>'danger']);
                return back(); }
        }else{ Session::flash('message', ['text'=>'Invalid OTP.','type'=>'danger']);
        return back(); }
    }
     public function createPassword(Request $request){
        $post               =   (object)$request->post();
        $reset              =   SellerOTP::where('token',$post->token)->whereNotNull('verified_at')->first();
        if($reset) {
            $update         =   $this->updateUserPassword($reset,$post);
            return redirect('/login')->with('success', 'Password created successfully');
        }else{ return redirect('/login')->with('error', 'Something went wrong.'); }
    }
    
}
