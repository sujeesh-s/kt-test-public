@extends('layouts.admin')
@section('css')
		<!-- INTERNAl Data table css -->
		<link href="{{URL::asset('admin/assets/plugins/datatable/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/datatable/css/buttons.bootstrap4.min.css')}}"  rel="stylesheet">
		<link href="{{URL::asset('admin/assets/plugins/datatable/responsive.bootstrap4.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/css/combo-tree.css')}}" rel="stylesheet" />
		<link rel="stylesheet" href="https://cdn.materialdesignicons.com/5.0.45/css/materialdesignicons.min.css">
		<link href="{{URL::asset('admin/assets/css/datepicker.css')}}" rel="stylesheet" />
@endsection
@section('page-header')
						<!--Page header-->


						<div class="page-header">
							<div class="page-leftheader">
								<h4 class="page-title mb-0">{{ $title }}</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Auctions Management</a></li>
									<li class="breadcrumb-item " aria-current="page"><a href="{{ url('/auctions') }}">Auctions</a></li>
									<li class="breadcrumb-item active" aria-current="page"><a href="#">{{ $title }}</a></li>
								</ol>
							</div>
							<div class="page-rightheader">
						
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
													{{ Form::open(array('url' => "/auctions/save", 'id' => 'auctionForm', 'name' => 'userForm', 'class' => '','files'=>'true')) }}
												{{Form::hidden('id',0,['id'=>'couponid'])}}
												<div class="py-1">
													
														<div class="row">
															<div class="col">
																<div class="row">

																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">Select Language <span class="text-red">*</span></label>
                                                                <select class="form-control custom-select select2" name="glo_lang_cid" required>
                                                                    
                                                                    @foreach ($language as $lang)
                                                                    <option value="{{ $lang->id }}">{{ $lang->glo_lang_name }}</option>
                                                                    @endforeach
                                                                </select>
																		</div>
																		
																	</div>

																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">Category <span class="text-red">*</span></label>
																		
																			<select class="form-control" name="cat_id" id="category_id" required onchange="loadsubcat()">
																			<option value="">Select Category</option>


																			@if($categories && count($categories) > 0)
																			@foreach($categories as $row)

																			
																			<option value="{{ $row['category_id'] }}">{{ $row['cat_name'] }}</option>
																			@endforeach
																			@endif
																			</select>
																		</div>
																		@error('cat_id')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																	</div>

																	
																	
																</div>
																
																<div class="row">
																	
																	<div class="col">
																		<div class="form-group">
																	
																			<label class="form-label">Subcategory <span class="text-red">*</span></label>
																			<input type="text" id="sub-category-id" placeholder="Type to filter" name="subcat_id" autocomplete="off" hidden />
																			 <input type="text" id="sub-category-drop" class="form-control " placeholder="Select Subcategory" readonly style="background-color: #fff !important;">
																		</div>
																		<p style="color: red" id="errNm1"></p>
																		@error('subcat_id')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																	</div>

																	<div class="col">
																		<div class="form-group">
																	
																			<label class="form-label">Product <span class="text-red">*</span></label>
																			<input type="text" id="product_id" placeholder="Type to filter" name="product_id" autocomplete="off" hidden />
																			 <input type="text" id="product-drop" class="form-control " placeholder="Select Product" readonly style="background-color: #fff !important;">
																		</div>
																		<p style="color: red" id="errNm2"></p>
																		@error('product_id')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																	</div>
																	
																</div>
																<div class="row">
																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">Description <span class="text-red">*</span></label>
																			
																			{!! Form::textarea('auction_desc', null, ['class' => 'form-control','rows' => 3,'id'=>'auction_desc']) !!}
																		</div>
																		@error('auction_desc')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																		
																	</div>
																	
																	
																</div>

																<div class="row">
														
																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">Start Date <span class="text-red">*</span></label>
																			
																			<div id="valid_from"  class="datepicker input-group date"
																			data-date-format="yyyy-mm-dd">
																			<input class="form-control" name="auct_start" type="text" readonly  onchange="date_check()" />
																			<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
																			</div>
																		</div>
																		@error('auct_start')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																		
																	</div>

																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">End Date <span class="text-red">*</span></label>
																			
																			<div id="valid_to" class="datepicker input-group date"
																			data-date-format="yyyy-mm-dd">
																			<input class="form-control"  name="auct_end" type="text" readonly  onchange="date_check()" />
																			<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
																			</div>
																		</div>
																		@error('auct_end')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																		
																	</div>
																</div>
																
																
																<div class="row">

																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">Min Bid Price <span class="text-red">*</span></label>
																	
																			{!! Form::number('min_bid_price', null, ['class' => 'form-control','required','id'=>'min_bid_price']) !!}
																		</div>
																		@error('min_bid_price')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																	</div>
																	
																	<div class="col">
																		<div class="form-group">
																			<label class="form-label">Status <span class="text-red">*</span></label>
																	
																			{!! Form::select('is_active', array('1' => 'Active', '0' => 'Inactive'), '1',['class' => 'form-control','required','id'=>'coupon_status']); !!}
																		</div>
																		@error('is_active')
																	<p style="color: red" class="error">{{ $message }}</p>
																	@enderror
																	</div>
																	
																</div>

																

																
															</div>
														</div>
														
														<div class="row">
															<div class="col d-flex justify-content-end">
															    <a href="{{url('/auctions')}}"  class="mr-2 btn btn-secondary" >Cancel</a>
															<input class="btn btn-primary" type="submit" id="frontval" value="Save Changes">
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


								

					</div>
				</div><!-- end app-content-->
            </div>
<style type="text/css">
	.radio-opts {
		margin-top: 10px;
	}
</style>

@endsection
@section('js')
		<!-- INTERNAl Data tables -->
<script src="{{URL::asset('admin/assets/js/jquery.validate.min.js')}}"></script>

		<script src="{{URL::asset('admin/assets/js/datatables.js')}}"></script>
	<!-- INTERNAL Popover js -->
		<script src="{{URL::asset('admin/assets/js/popover.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/bootstrap-datepicker.js')}}"></script>
		<!-- INTERNAL Sweet alert js -->
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/comboTreePlugin.js')}}"></script>
<script type="text/javascript">
	jQuery(document).ready(function(){


$("#frontval").click(function(){

$("#auctionForm").validate({
	ignore: [],
rules: {

cat_id : {
required: true
},

subcat_id: {
required: true
},
product_id: {

required: true
},
auction_desc : {
required: true

},
auct_start : {
required: true
},

auct_end: {
required: true
},
min_bid_price: {
required: true,
number: true,
min:1
},
is_active: {
required: true

},

},

messages : {
cat_id: {
required: "Category is required."
},
subcat_id: {
required: "Subcategory is required."
},
product_id: {
required: "Product is required."
},
auction_desc: {
required: "Description is required."
},
auct_start: {
required: "Start Date is required."
},
auct_end: {
required: "End Date is required."

},
min_bid_price: {
required: "Min Bid Price is required.",
min: "Min Bid Price must be greater than 0"
},
is_active: {
required: "Status is required."
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



	 $(".datepicker").datepicker({ 
        autoclose: true, 
        todayHighlight: true,
       startDate: new Date()
  }).datepicker('update', new Date());




	// Prompt
	$(".deletemodule").on("click", function(e){

		var moduleid = jQuery(this).parents("tr").find("#moduleid").data("value");
		$('body').removeClass('timer-alert');
		swal({
			title: "Delete Confirmation",
			text: "Are you sure you want to delete this module?",
			// type: "input",
			showCancelButton: true,
			closeOnConfirm: true,
			confirmButtonText: 'Yes'
		},function(inputValue){



			if (inputValue == true) {
			 $.ajax({
            type: "POST",
            url: '{{url("/admin/modules/delete")}}',
            data: { "_token": "{{csrf_token()}}", id: moduleid},
            success: function (data) {
            	// alert(data);
            	if(data ==1){
            		location.reload();
            	}
            
            }
        });

			}
		});
	});

	});
</script>

<script type="text/javascript">
    $(document).ready(function(){
            @if(Session::has('message'))
            @if(session('message')['type'] =="success")
            
            toastr.success("{{session('message')['text']}}"); 
            @else
            toastr.error("{{session('message')['text']}}"); 
            @endif
            @endif
            
            @if ($errors->any())
            @foreach ($errors->all() as $error)
            toastr.error("{{$error}}"); 
            
            @endforeach
            @endif
    });
    </script>


<script type="text/javascript">


var instance = $('#sub-category-drop').comboTree({
collapse:true,
cascadeSelect:true,
isMultiple: false
});

var prod_instance = $('#product-drop').comboTree({
collapse:true,
cascadeSelect:true,
isMultiple: false
});

 function loadsubcat(clear='')
    {
        var category_id=$("#category_id").val();
        
        if(clear!='1')
        {
            $("#sub-category-id").val('');
        }
        
         $.ajax({
            type: "POST",
            url: '{{url("/auctions/subcategory")}}',
            data: { "_token": "{{csrf_token()}}", category_id: category_id},
            success: function (data) {
            	var obj = JSON.parse(data);
            
            	console.log(obj);
            	 var obj = JSON.parse(data);
            if(obj.subdata.length >=1)
            {
               $('#sub-category-drop').attr("placeholder", "Select Subcategory"); 
            }
            else
            {
                $('#sub-category-drop').attr("placeholder", "No Subcategory to display"); 
            }
            instance.setSource(obj.subdata);
            if($("#sub-category-id").val())
            {
                var selectionIdList = new Array($("#sub-category-id").val());
                instance.setSelection(selectionIdList);

            }
            
            }
        });
        
        
        
    }

    function loadProd(clear='')
    {
        var category_id=$("#category_id").val();
        var subcategory_id=$("#sub-category-id").val();
        
        if(clear!='1')
        {
            $("#product_id").val('');
        }
        
         $.ajax({
            type: "POST",
            url: '{{url("/auctions/prodsdata")}}',
            data: { "_token": "{{csrf_token()}}", category_id: category_id,subcategory_id:subcategory_id},
            success: function (data) {
            	var obj = JSON.parse(data);
            
            	console.log(obj);
            	 var obj = JSON.parse(data);
            if(obj.subdata.length >=1)
            {
               $('#product-drop').attr("placeholder", "Select Product"); 
            }
            else
            {
                $('#product-drop').attr("placeholder", "No Products to display"); 
            }
            prod_instance.setSource(obj.subdata);
            if($("#product_id").val())
            {
                var selectionIdList = new Array($("#product_id").val());
                prod_instance.setSelection(selectionIdList);

            }
            
            }
        });
        
        
        
    }
    $('#sub-category-drop').on('change',function()
        {
            if(instance.getSelectedIds())
            {
                $("#sub-category-id").val(instance.getSelectedIds()[0]);
                loadProd();
                $("#errNm1").empty();
            }
            
        });

        $('#product-drop').on('change',function()
        {
            if(prod_instance.getSelectedIds())
            {
                $("#product_id").val(prod_instance.getSelectedIds()[0]);
                $("#errNm2").empty();
            }
            
        });


$(document).ready(function(){


$(".purchase_type").click(function(){
$(".purchase_type_options").hide('1000');    
$(".purchase-"+$(this).val()).show('1000');
});

$(".validity_type").click(function(){
$(".validity_type_options").hide('1000');    
$(".valid-"+$(this).val()).show('1000');
});


});

 function date_check() 
    {
      var sdate=$("[name='valid_from']").val();
      var tdate=$("[name='valid_to']").val();
      
      $('#valid_from').datepicker('setStartDate',new Date(sdate));
      if(sdate && tdate)
      {
        var d1 = Date.parse(sdate);
        var d2 = Date.parse(tdate);
        if (d1 > d2) 
        {
          $("[name='valid_to']").val(sdate);
          $('#valid_to').datepicker('setStartDate',new Date(sdate));
        }
      }
      
    }

</script>
@endsection