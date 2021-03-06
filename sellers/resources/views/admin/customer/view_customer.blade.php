@extends('layouts.admin')
@section('css')
		<!-- INTERNAl alert css -->
		<link href="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet" />

        <!--INTERNAL Select2 css -->
		<link href="{{URL::asset('admin/assets/plugins/select2/select2.min.css')}}" rel="stylesheet" />

        <!-- INTERNAL File Uploads css -->
		<link href="{{URL::asset('admin/assets/plugins/fancyuploder/fancy_fileupload.css')}}" rel="stylesheet" />
        <!-- INTERNAL File Uploads css-->
        <link href="{{URL::asset('admin/assets/plugins/fileupload/css/fileupload.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('page-header')

						<!--Page header-->
                        <div class="page-header">
							<div class="page-leftheader">
								<h4 class="page-title mb-0">{{ $title }}</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Customer</a></li>

									<li class="breadcrumb-item active" aria-current="page"><a href="#">{{ $title }}</a></li>
								</ol>
							</div>
							<div class="page-rightheader">
								<!-- <div class="btn btn-list">
									<a href="#" class="btn btn-info"><i class="fe fe-settings mr-1"></i> General Settings </a>
									<a href="#" class="btn btn-danger"><i class="fe fe-printer mr-1"></i> Print </a>
									<a href="#"  data-target="#user-form-modal" data-toggle="modal" class="btn btn-danger addmodule"><i class="fe fe-shopping-cart mr-1"></i> Add New</a>
								</div> -->
							</div>
						</div>
                        <!--End Page header-->
                        @endsection
@section('content')
						<!--/app header-->
						<div class="main-proifle">
							<div class="row">
								<div class="col-lg-7">
									<div class="box-widget widget-user">
										<div class="widget-user-image1 d-sm-flex">
										    @if($info->profile_image!='')
										    <img alt="User Avatar" class="rounded-circle border p-0" style="width:120px;height:130px;" src="{{ url('storage/app/public/customer_profile/'.$info->profile_image) }}">
										    @else
											<img alt="User Avatar" class="rounded-circle border p-0" src="{{URL::asset('admin/assets/images/users/2.jpg')}}">
											@endif
											<div class="mt-1 ml-lg-5">
												<h4 class="pro-user-username mb-3 font-weight-bold">{{ $info->first_name." ".$info->middle_name." ".$info->last_name }} <i class="fa fa-check-circle text-success"></i></h4>

												<ul class="mb-0 pro-details">
                                                    @foreach($telecom as $tele)

                                                    @if($tele->usr_telecom_typ_id==2)
													<li><span class="profile-icon"><i class="fe fe-phone-call"></i></span><span class="h6 mt-3">{{ $tele->usr_telecom_value }}</span></li>
                                                    @endif
                                                    @if($tele->usr_telecom_typ_id==1)
													<li><span class="profile-icon"><i class="fe fe-mail"></i></span><span class="h6 mt-3">{{ $tele->usr_telecom_value }}</span></li>
                                                    @endif
                                                    @endforeach
                                                    <li><span class="profile-icon"><i class="fe fe-calendar"></i></span><span class="h6 mt-3">{{date('d M Y',strtotime($customer_mst->created_on))}}</span></li>
                                                    <li><span class="profile-icon"><i class="fe fe-globe"></i></span>@if($customer_mst->is_active==1)<span class="h6 mt-3 badge badge-primary">{{"Active"}}</span>@endif @if($customer_mst->is_active==0)<span class="h6 mt-3 badge badge-pill badge-danger" style="color:white">{{"Inactive"}}</span>@endif</li>
												</ul>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-5 col-md-auto">
									<div class="text-lg-right btn-list mt-4 mt-lg-0">
										<!--<a href="#" class="btn btn-light">Change Password</a>-->
										<button data-toggle="modal" data-target="#SignUp" class="btn btn-primary">Edit Profile</button>
									</div>
									<div class="mt-5">
										<div class="main-profile-contact-list row">
											<div class="media col-sm-4">
												<div class="media-icon bg-primary text-white mr-3 mt-1">
													<i class="fa fa-shopping-cart fs-18"></i>
												</div>
												<div class="media-body">
													<small class="text-muted">Orders</small>
													<div class="font-weight-bold number-font">
														{{count($order)}}
													</div>
												</div>
											</div>
											<div class="media col-sm-4">
												<div class="media-icon bg-secondary text-white mr-1 mt-1">
													<i class="las la-hand-holding-usd fs-18"></i>
												</div>
												<div class="media-body">
													<small class="text-muted">Wallet</small>
													<div class="font-weight-bold number-font">
														@if($wallet)
														{{$wallet->wallet}}
														@else
														{{"0"}}
														@endif
													</div>
												</div>
											</div>
											<div class="media col-sm-4">
												<div class="media-icon bg-info text-white mr-1 mt-1">
													<i class="fa fa-briefcase fs-18"></i>
												</div>
												<div class="media-body">
													<small class="text-muted">Purchased Amount</small>
													<div class="font-weight-bold number-font">
													@if(!empty($order))
													@php
													$t_amt=0;
													foreach($order as $key)
													{
													    $t_amt +=$key->g_total;
													}
													@endphp
													{{$t_amt}}
													@else
													{{"0"}}
													@endif
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="profile-cover">
								<div class="wideget-user-tab">
									<div class="tab-menu-heading p-0">
										<div class="tabs-menu1 px-3">
											<ul class="nav">
												<li><a href="#tab-7" class="active fs-14" data-toggle="tab">Address</a></li>
												<li><a href="#tab-8" data-toggle="tab" class="fs-14">Order Summary</a></li>
												<li><a href="#tab-9" data-toggle="tab" class="fs-14">Order History</a></li>
											</ul>
										</div>
									</div>
								</div>
							</div><!-- /.profile-cover -->
						</div>
						<!-- Row -->
						<div class="row">
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="border-0">
									<div class="tab-content">
										<div class="tab-pane active" id="tab-7">
											<div class="card">
												<div class="card-body border-top">
													<h5 class="font-weight-bold">Address</h5>
													<div class="main-profile-contact-list d-lg-flex">
														<div class="media mr-5">
															<div class="media-icon bg-danger text-white mr-4">
																<i class="fa fa-briefcase"></i>
															</div>
															<div class="media-body">
																<h6 class="font-weight-bold mb-1">Home Address </h6>
																<span>2008vv</span>
																<p>Cac Lorem lupsm artitoselm</p>
															</div>
														</div>
                                                        <div class="media mr-5">
															<div class="media-icon bg-danger text-white mr-4">
																<i class="fa fa-briefcase"></i>
															</div>
															<div class="media-body">
																<h6 class="font-weight-bold mb-1">Office address</h6>
																<span>57668</span>
																<p>Cac Lorem lupsm artitoselm</p>
															</div>
														</div>
													</div>
												</div>


											</div>
										</div>
										<div class="tab-pane" id="tab-8">
											<div class="card p-5">
												<!--Row-->
						<div class="row">
							<div class="col-md-12">
								<div class="card">
									<div class="row mr-0 ml-0">
										<div class="col-xl-3 col-lg-6 col-sm-6 pr-0 pl-0 border-right">
											<div class="card-body text-center">
												<p class="mb-1">Number of orders</p>
									            <h2 class="mb-1 font-weight-bold">356</h2>
												<span class="mb-0 text-muted"><span class="text-success"><i class="fa fa-caret-up  mr-1"></i> 2</span> Last month</span>
									        </div>
										</div>
										<div class="col-xl-3 col-lg-6 col-sm-6 pr-0 pl-0 border-right">
											<div class="card-body text-center">
												<p class="mb-1">Purchase Amount</p>
									            <h2 class="mb-1 font-weight-bold">2500</h2>
									            <span class="mb-0 text-muted"><span class="text-success"><i class="fa fa-caret-up  mr-1"></i> 250</span> Last month</span>
									        </div>
										</div>
										<div class="col-xl-3 col-lg-6 col-sm-6 pr-0 pl-0 border-right">
											<div class="card-body text-center">
												<p class="mb-1">Cancelled Orders</p>
									            <h2 class="mb-1 font-weight-bold">5,987</h2>
									            <span class="mb-0 text-muted"><span class="text-danger"><i class="fa fa-caret-down  mr-1"></i> 0</span> Last month</span>
									        </div>
										</div>
										<div class="col-xl-3 col-lg-6 col-sm-6 pr-0 pl-0 border-right">
											<div class="card-body text-center">
												<p class="mb-1">Amount Refunded</p>
									            <h2 class="mb-1 font-weight-bold">350</h2>
									            <span class="mb-0 text-muted"><span class="text-danger"><i class="fa fa-caret-up  mr-1"></i> 0</span> Last month</span>
									        </div>
										</div>
										{{-- <div class="col-xl-2 col-lg-6 col-sm-6 pr-0 pl-0 border-right">
											<div class="card-body text-center">
												<p class="mb-1">Amount Wallet</p>
									            <h2 class="mb-1 font-weight-bold">2.89</h2>
									            <span class="mb-0 text-muted"><span class="text-success"><i class="fa fa-caret-down  mr-1"></i> 1.2%</span> Last month</span>
									        </div>
										</div>
										<div class="col-xl-2 col-lg-6 col-sm-6 pr-0 pl-0">
											<div class="card-body text-center">
												<p class="mb-1">Goal Conversion</p>
									            <h2 class="mb-1 font-weight-bold">12.7%</h2>
									            <span class="mb-0 text-muted"><span class="text-danger"><i class="fa fa-caret-down  mr-1"></i> 0.6%</span> Last month</span>
									        </div>
										</div> --}}
									</div>
								</div>
							</div>
						</div>
						<!--End row-->
											</div>
										</div>
										<div class="tab-pane" id="tab-9">
                                            <div class="card">
                                            <div class="card-body">
												<div class="e-table">
													<div class="table-responsive table-lg mt-3">
														<table id="order-table" class="order-table table table-striped table-bordered w-100 text-nowrap">
															<thead>
																<tr>
																	<th class="align-top border-bottom-0 wd-5"></th>
																	<th class="border-bottom-0 w-30">Order ID</th>
																	<th class="border-bottom-0 w-15">Qty</th>
                                                                    <th class="border-bottom-0 w-30">Amount</th>
																	<th class="border-bottom-0 w-15">Ordered Date</th>
                                                                    <th class="border-bottom-0 w-15">Delivery Date</th>
																	<th class="border-bottom-0 w-20">Delivery Status</th>
																	<th class="border-bottom-0 w-30">Invoice</th>
																</tr>
															</thead>

															<tbody>
															    @if($order && count($order) > 0)
                    											@foreach($order as $row)
                    											<tr>
                                                                <td class="align-middle select-checkbox" id="">
                                                                    <label class="custom-control custom-checkbox">
                                                                    </label>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold">#{{$row->order_id}}</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle">
                                                                    @php
                                                                    $total=0;
                                                                    $items=$row->orderitem($row->id); 
                                                                    foreach($items as $item)
                                                                    {
                                                                    $total += $item->qty;
                                                                    }
                                                                    @endphp
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold">{{$total}}</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold">{{$row->g_total}}</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold">{{date('d-m-Y',strtotime($row->created_at))}}</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold">02-07-2021</h6>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold"><span class="badge badge-warning">{{$row->shipping_status}}</span></h6>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle">
                                                                    <div class="d-flex">
                                                                    <h6 class=" font-weight-bold"><a href="#" class="btn btn-sm btn-info">Invoice</a></h6>
                                                                    </div>
                                                                </td>
                                                                </tr>
                                                                 @endforeach
              													  @endif
															</tbody>
														</table>
													</div>
												</div>
                                            </div>
                                        </div><!--card-->
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div><!-- end app-content-->
			</div>

            <!-- Modal change password -->
			<div id="changepwd" class="modal fade" role="dialog">
                <div class="modal-dialog">

                <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title text-center">Change Password</h3>
                            <button type="button" class="close" data-dismiss="modal">??</button>

                        </div>
                        <div class="modal-body" style="overflow: hidden;">
                            <div class="alert alert-danger" role="alert" style="display: none">
                            </div>
                            <div class="col-md-offset-1 col-md-12">
                                <form method="POST" id="changepwd" enctype="multipart/form-data">
                                   @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">New password<span class="text-red">*</span></label>
                                        <input type="text" name="password" id="password" class="form-control" placeholder="New Password">
                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">Confirm password<span class="text-red">*</span></label>
                                        <input type="text" name="c_password" id="c_password"  class="form-control" placeholder="Confirm Password">
                                    </div>
                                </div>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal regsiter -->
			<div id="SignUp" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">

                <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title text-center">Edit Profile</h3>
                            <button type="button" class="close" data-dismiss="modal">??</button>

                        </div>
                        <div class="modal-body" style="overflow: hidden;">
                            <div class="alert alert-danger" role="alert" style="display: none">
                            </div>
                            {{-- <div id="success-msg" class="" style="">
                                <div class="alert alert-info alert-dismissible fade in" role="alert">
                                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">??</span>
                                  </button>
                                  <strong>Success!</strong> Check your mail for login confirmation!!
                                </div>
                            </div> --}}

                            <div class="col-md-offset-1 col-md-12">
                                <form method="POST" id="editprofile" action="{{url('admin/customer/update-profile/'.$customer_mst->id)}}" name="editprofile" enctype="multipart/form-data">
                                   @csrf
                                <div class="row">
                                    <div class="col-md-6 col-lg-6">
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">First Name<span class="text-red">*</span></label>
                                        <input type="text" name="first_name"  id="first_name" value="{{ $info->first_name }}" class="form-control" placeholder="First name">

                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">Last Name<span class="text-red">*</span></label>
                                        <input type="text" name="last_name" id="last_name" value="{{ $info->last_name }}" class="form-control" placeholder="Last name">

                                    </div>
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">Status<span class="text-red">*</span></label>
                                        <select class="form-control select2" id="status" name="status">
                                            <option value="1" @if($info->is_active==1){{ "selected" }}@endif>Active</option>
                                            <option value="0" @if($info->is_active==0){{ "selected" }}@endif>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    @foreach($telecom as $tele)
                                    @if($tele->usr_telecom_typ_id==1)
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">Email<span class="text-red">*</span></label>
                                        <input type="email" name="email" id="email" value="{{ $tele->usr_telecom_value }}" class="form-control" placeholder="Email">
                                    </div>
                                    @endif
                                    @if($tele->usr_telecom_typ_id==2)
                                    <div class="form-group has-feedback">
                                        <label class= "form-label">Contact Number<span class="text-red">*</span></label>
                                        <input type="number" name="number" id="number" min="0" value="{{ $tele->usr_telecom_value }}" class="form-control" placeholder="Contact number">
                                    </div>
                                    @endif
                                    @endforeach
                                    <div class="form-group has-feedback">
                                        <label class="form-label">Profile Image <span class="text-red"></span></label>
                                        <input type="file" id="profile_img" class="form-control" accept=".jpg, .png, image/jpeg, image/png"  name="profile_img" />
                                        </div>
                                </div><!---col--->
                            </div><!---row-->

                                    <div class="row">
                                        <div class="col-xs-12 col-md-12 text-center">
                                          <button type="submit" id="submitForm" class="btn btn-primary btn-prime white btn-flat">Save</button>
                                        </div>
                                    </div>
                        </form>
                        </div>
                    </div>

                </div>
            </div></div>
@endsection
@section('js')
<!--INTERNAL Select2 js -->
<script src="{{URL::asset('admin/assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{URL::asset('admin/assets/js/select2.js')}}"></script>
<!-- INTERNAL Popover js -->
<script src="{{URL::asset('admin/admin/assets/js/popover.js')}}"></script>

<!-- INTERNAL Sweet alert js -->
<script src="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.js')}}"></script>
<script src="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js"></script>

<!-- INTERNAl Data tables -->
		<script src="{{URL::asset('admin/assets/js/datatable/tables/order-datatable.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/dataTables.bootstrap4.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/dataTables.buttons.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/buttons.bootstrap4.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/jszip.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/pdfmake.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/vfs_fonts.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/buttons.html5.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/buttons.print.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/buttons.colVis.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/dataTables.responsive.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/datatable/responsive.bootstrap4.min.js')}}"></script>

<script type="text/javascript">

$(document).ready(function() {
$('#cust_item').addClass("active");
$('#editprofile').submit(function(e) {
  e.preventDefault();
  var first_name = $('#first_name').val();
  var last_name = $('#last_name').val();
  var email = $('#email').val();
  var number = $('#number').val();

  $(".error").remove();

  if (first_name.length < 1) {
    $('#first_name').after('<span class="error">This field is required</span>');
  }
  if (last_name.length < 1) {
    $('#last_name').after('<span class="error">This field is required</span>');
  }
  if (email.length < 1) {
    $('#email').after('<span class="error">This field is required</span>');
  }
  if (number.length < 1) {
    $('#number').after('<span class="error">This field is required</span>');
  }

  if(first_name!= '' && last_name!='')
  {  
      if(email!= '' && number!= '')
      {
      $('#editprofile')[0].submit();
      }
  }
//   if (password.length < 8) {
//     $('#password').after('<span class="error">Password must be at least 8 characters long</span>');
//   }
});

});

</script>
@endsection
