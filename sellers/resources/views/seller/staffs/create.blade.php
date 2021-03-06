@extends('layouts.admin')
@section('css')
		<!-- INTERNAl Data table css -->
		<link href="{{URL::asset('admin/assets/plugins/datatable/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/datatable/css/buttons.bootstrap4.min.css')}}"  rel="stylesheet">
		<link href="{{URL::asset('admin/assets/plugins/datatable/responsive.bootstrap4.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet" />
@endsection
@section('page-header')
						<!--Page header-->


						<div class="page-header">
							<div class="page-leftheader">
								<h4 class="page-title mb-0">{{ $title }}</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>User Management</a></li>
									<li class="breadcrumb-item " aria-current="page"><a href="{{url('/staff-list')}}">Admins</a></li>
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
						<!-- Row -->
						<div class="row flex-lg-nowrap">
							<div class="col-12">

							
								<div class="row flex-lg-nowrap">
									<div class="col-12 mb-3">
										<div class="e-panel card">
											<div class="card-body">
												<div class="e-table">
													<div class="table-responsiv table-lg mt-3">
														

														
 {{ Form::open(array('url' => "/staff-list/save", 'id' => 'adminForm', 'name' => 'adminForm', 'class' => '','files'=>'true')) }}
        
        <div >
            {{Form::hidden('id',0,['id'=>'id'])}}
            {{Form::hidden('user[parent_id]',$seller,['id'=>'parent_id'])}}
             
            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="fname">First name <span class="text-red">*</span></label>
                    <input type="text" class="form-control" name="user[fname]" id="fname" placeholder="First name" value="@if(old()){{old('user')['fname']}}@endif" required>
                    <span class="error"></span>
                     @error('fname')
                    <p style="color: red">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lname">Last name</label>
                    <input type="text" class="form-control" name="user[lname]" id="lname" placeholder="Last name" value="@if(old()){{old('user')['lname']}}@endif" required>
                    <span class="error"></span>
                    @error('lname')
                    <p style="color: red">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="email">Email <span class="text-red">*</span></label>
                    <input type="email" class="form-control" name="user[email]" id="email" placeholder="Email" value="@if(old()){{old('user')['email']}}@endif" required>
                    <span class="error"></span>
                    @error('email')
                    <p style="color: red">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone">Phone <span class="text-red">*</span></label>
                    
                    <input type="text" class="form-control" name="user[phone]" id="phone" placeholder="Phone" value="@if(old()){{old('user')['phone']}}@endif" required>
                    <span class="error"></span>
                    @error('phone')
                    <p style="color: red">{{ $message }}</p>
                    @enderror
                </div>
            </div>
          

            <div class="form-row">
                <div class="col-md-6 mb-3">
                    <label for="password">Password <span class="text-red">*</span></label>
                    <input type="password" class="form-control" name="user[password]" id="password" data-strength placeholder="Password" value="@if(old()){{old('user')['password']}}@endif" >
                    <span class="error"></span>
                    @error('password')
                    <p style="color: red">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="avatar">Avatar</label>
                    {{Form::file('avatar',['id'=>'avatar','class'=>'form-control'])}}
                </div>
                <div class="col-md-6 mb-3">
                    <label for="avatar">Status <span class="text-red">*</span></label>
                    {{Form::select('user[is_active]',['1'=>'Active','0'=>'Inactive'],1,['id'=>'is_active','class'=>'form-control'])}}
                </div>
                <div class="col-md-6 mb-3">
                    <img id="avatar_img" src="{{url('storage/app/public/no-avatar.png')}}" alt="avatar" style="height: 120px;" />
                </div>
            </div>


<div class="form-row">
<div class="col-md-6 mb-3">
<label for="email">Role <span class="text-red">*</span></label>
<select class="form-control custom-select select2" name="user[role_id]" id="role_id" required >
<option value="">Select Role</option>
@if($roles && count($roles) > 0)
@foreach ($roles as $role)
<option value="{{ $role->id }}" <?php if(old()){ if(old('user')['role_id']== $role->id){ echo 'selected'; } } ?>>{{ $role->usr_role_name }}</option>
@endforeach
@endif
</select>
</div>
<div class="col-md-6 mb-3">
<!--     <label for="phone">Phone <span class="text-red">*</span></label>

<input type="text" class="form-control" name="user[phone]" id="phone" placeholder="Phone" value="" required>
<span class="error"></span> -->
</div>
</div>
        </div>
        <div >
            <div class="row" style="margin-top: 30px;">
															<div class="col d-flex justify-content-end">
															<a href="{{url('/staff-list')}}"  class="mr-2 btn btn-secondary" >Cancel</a>           
            <input id="save_btn" type="submit" class="btn btn-primary" style="float:right;" value="Save">
															</div>
														</div>
            
        </div>
    {{Form::close()}}
													

													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- End Row -->


						<!-- User Form Modal -->
								

					</div>
				</div><!-- end app-content-->
            </div>
@endsection
@section('js')
		<!-- INTERNAl Data tables -->
		<script src="{{URL::asset('admin/assets/plugins/datatable/js/jquery.dataTables.js')}}"></script>
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
		<script src="{{URL::asset('admin/assets/js/datatables.js')}}"></script>
	<!-- INTERNAL Popover js -->
		<script src="{{URL::asset('admin/assets/js/popover.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/jquery.validate.min.js')}}"></script>

		<!-- INTERNAL Sweet alert js -->
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
		<script type="text/javascript">
		
		
if (window.File && window.FileList && window.FileReader) {
    $("#avatar").on("change", function(e) {
        $(".pip1").remove();
      var files = e.target.files,
        filesLength = files.length;
      for (var i = 0; i < filesLength; i++) {
        var f = files[i]
        var fileReader = new FileReader();
        fileReader.onload = (function(e) {
          var file = e.target;
          // $("<span class=\"pip1\">" +
          //   "<input type=\"file\" id=\"havefil\" hidden name=\"havefil[]\" value=\"" + e.target.result + "\"/>"+
          //   "<img class=\"imageThumb1\" src=\"" + e.target.result + "\" title=\"" + file.name + "\"/>" +
          //   "<br/>" +
          //   "</span>").insertAfter("#avatar");
          // $(".remove").click(function(){
          //   $(this).parent(".pip").remove();
          // });

          $("#avatar_img").attr("src",e.target.result);

          // <span class=\"remove\">Remove image</span>Old code here
          /*$("<img></img>", {
            class: "imageThumb",
            src: e.target.result,
            title: file.name + " | Click to remove"
          }).insertAfter("#avatar").click(function(){$(this).remove();});*/

        });
        fileReader.readAsDataURL(f);
      }
    });
  } else {
    alert("Your browser doesn't support to File API")
  }
  
	jQuery(document).ready(function(){

jQuery.validator.addMethod("phone", function (phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, "");
        return this.optional(element) || phone_number.length > 9 &&
              phone_number.match(/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/);
    }, "Invalid phone number");

jQuery.validator.addMethod("lettersonly", function(value, element) 
{
return this.optional(element) || /^[a-z ]+$/i.test(value);
}, "Please enter valid name.");

$("#save_btn").click(function(){

$("#adminForm").validate({
	ignore: [],
rules: {

"user[fname]" : {
required: true,
lettersonly: true
},
"user[lname]" : {
required: true,
lettersonly: true
},

"user[email]": {
required: true,
email: true
},
"user[phone]": {

required: true,
phone:true,
number: true,
},
"user[password]" : {
required: true,
maxlength: 15,
minlength: 6

},
"user[role_id]" : {
required: true
},


},

messages : {
"user[fname]": {
required: "First name is required."
},
"user[lname]": {
required: "Last name is required."
},


"user[email]": {
required: "Email is required."
},
"user[phone]": {
required: "Phone number is required."
},
"user[password]": {
required: "Password is required."
},
"user[role_id]": {
required: "Role is required."
}

},


 errorPlacement: function(error, element) {
 	 // $("#errNm1").empty();$("#errNm2").empty();
 	 console.log($(error).text());
            if (element.attr("name") == "subcat_id" ) {
            	console.log("innnnnn");
                $("#errNm1").text($(error).text());
                
            }else if (element.attr("name") == "product_id" ) {
                $("#errNm2").text($(error).text());
                
            }else {
               error.insertAfter(element)
            }
        },

});
});


	});
</script>
<script type="text/javascript">
	jQuery(document).ready(function(){




	});
</script>

@endsection