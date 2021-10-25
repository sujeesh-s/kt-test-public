@extends('layouts.master2')
@section('css')
<link rel="stylesheet" href="{{URL::asset('admin/assets/css/toastr.min.css')}}" />
@endsection
@section('content')
<div class="page">
			<div class="page-content">
				<div class="container">
					<div class="row align-items-center justify-content-center">
						<div class="col-md-6">
							<div class="">
								<div class="text-white">
									<div class="card-body">
										<h2 class="display-4 mb-2 font-weight-bold error-text text-center"><strong>Seller Login</strong></h2>
										<h4 class="text-white-80 mb-7 text-center">Sign in to your account</h4>
                                                                                <form method="POST" action="{{ url('seller/login') }}" id="sellerlogin">
                        @csrf
										<div class="row">
											<div class="col-9 d-block mx-auto">
												<div class="input-group mb-4">
													<div class="input-group-prepend">
														<div class="input-group-text">
															<i class="fe fe-user"></i>
														</div>
													</div>
                                                                                                    <input type="email" name="email" class="form-control" placeholder="Email" required="">
                                                                                                    @error('email')
                                                                                                        <span class="invalid-feedback" role="alert">
                                                                                                            <strong>{{ $message }}</strong>
                                                                                                        </span>
                                                                                                    @enderror
                                                                                                </div>
												<div class="input-group mb-4">
													<div class="input-group-prepend">
														<div class="input-group-text">
															<i class="fe fe-lock"></i>
														</div>
													</div>
                                                                                                    <input type="password" name="password" class="form-control" placeholder="Password" required="">
                                                                                                    @error('password')
                                                                                                        <span class="invalid-feedback" role="alert">
                                                                                                            <strong>{{ $message }}</strong>
                                                                                                        </span>
                                                                                                    @enderror
                                                                                                    <div class="error tac fw" role="alert">
                                                                                                        <strong>{{ Session::get('message')}}</strong>
                                                                                                    </div>
                                                                                                </div>
                                                <div class="row">
												<div class="col-12">
												<label class="custom-control custom-checkbox">
													<input type="checkbox" class="custom-control-input" name="remember" >
													<span class="custom-control-label">Remember me</span>
												</label>
											  </div>
										     </div>
												<div class="row">
													<div class="col-12">
														<button type="submit" class="btn btn-secondary btn-block px-4" style="background:#fff; color:#ff0000 !important; padding:10px;">Login</button>
													</div>
													<div class="col-12 text-center">
														<a href="{{ url('/password/reset')}}" class="btn btn-link box-shadow-0 px-0 text-white-80">Forgot password?</a>
													</div>
                                                    <div class="col-12 text-center">
                                                        <span> Don't have an account? </span> <a href="{{ url('/register')}}" class="btn btn-link box-shadow-0 px-0 text-white-80">Register</a>
													</div>
												</div>
											</div>
										</div>
                                        </form>
									</div>
									<!--<div class="custom-btns text-center">-->
									<!--	<button class="btn btn-icon" type="button"><span class="btn-inner-icon"><i class="fa fa-facebook-f"></i></span></button>-->
									<!--	<button class="btn btn-icon" type="button"><span class="btn-inner-icon"><i class="fa fa-google"></i></span></button>-->
									<!--</div>-->
								</div>
							</div>
						</div>
						<!--<div class="col-md-6 d-none d-md-flex align-items-center">-->
						<!--	<img src="{{URL::asset('admin/assets/images/png/login.png')}}" alt="img">-->
						<!--</div>-->
					</div>
				</div>
			</div>
        </div>
    
    
    <style>
   #sellerlogin .error  {
        color:#fff;
    }        
        </style>
        
@endsection
@section('js')
<script src="{{URL::asset('admin/assets/js/toastr.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){ 
        @if(Session::has('success')) toastr.success("{{ Session::get('success')}}"); 
        @elseif(Session::has('error')) toastr.error("{{ Session::get('error')}}");  @endif 
    });
</script>
@endsection