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

<style type="text/css">
	.filter-radio {
 display: table-cell;
    vertical-align: middle
	}
</style>
						<div class="page-header">
							<div class="page-leftheader">
								<h4 class="page-title mb-0">{{ $title }}</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Product Management</a></li>
									
									
									<li class="breadcrumb-item active" aria-current="page"><a href="#">{{ $title }}</a></li>
								</ol>
							</div>
											<div class="page-rightheader" style="display:flex; flex-direction: row; justify-content: center; align-items: center">


								<div class="btn btn-list">
						
									<!-- <a href="{{ url('/shocking-sales/create/') }}"  class="btn btn-primary addmodule"><i class="fe fe-plus mr-1"></i> Add New</a> -->
								</div>
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

<div class="panel panel-primary tabs-style-3">
		<div class="tab-menu-heading">
			<div class="tabs-menu ">
				<!-- Tabs -->
				<ul class="nav panel-tabs">
					<li class="fixed_tabs"><a href="#tab11" class="active" data-toggle="tab"><i class="fe fe-airplay mr-1"></i> Stock Log</a></li>
					<li class="fixed_tabs"><a href="#tab12" data-toggle="tab"><i class="fe fe-package mr-1"></i> Price Log</a></li>

				</ul>
			</div>
		</div>
		<div class="panel-body tabs-menu-body">
			<div class="tab-content">
				<div class="tab-pane active" id="tab11">
					<a href="{{ url('/shocking-sales/create/') }}"  data-target="#stock-form-modal" data-toggle="modal" class="btn btn-primary fr mb-4 addmodule"><i class="fe fe-plus mr-1"></i> Add Stock</a>
					<div class="e-table">
					<div class="table-responsive table-lg mt-3">
					<table class="table table-bordered border-top text-nowrap stockprice-stock" id="stockprice-stock">
					<thead>
					<tr>

					<th class="align-top border-bottom-0 wd-5 notexport">Select</th>
					<!-- <th class="border-bottom-0 w-30">Product</th> -->

					<th class="border-bottom-0 w-20">Type</th>
					<th class="border-bottom-0 w-20">Quantity</th>
					<th class="border-bottom-0 w-20">Updated Date</th>

					<!-- <th class="border-bottom-0 w-30">Status</th> -->

					<!-- <th class="border-bottom-0 w-10 notexport">Actions</th> -->
					</tr>
					</thead>

					<tbody>

					@if($stock_log && count($stock_log) > 0)
					@foreach($stock_log as $row)
					<tr>
					<td class="align-middle select-checkbox" id="moduleid" data-value="{{$row['id']}}">
					<label class="custom-control custom-checkbox">

					<!--{{ $loop->iteration }}-->
					</label>
					</td>
					<td class="align-middle" >
					<div class="d-flex">
					<p>@if($row->type =="add") {{ "Stock In" }} @else {{ "Stock Out" }} @endif</p>
					</div>
					</td>

					<td>{{$row->qty}}</td> 
					<td ><span>{{date('d M Y',strtotime($row->created_at))}}</span></td>


					</tr>
					@endforeach
					@endif




					</tbody>
					</table>
					</div>
					</div>
				</div>
				<div class="tab-pane" id="tab12">
					<a href="{{ url('/shocking-sales/create/') }}"  data-target="#price-form-modal" data-toggle="modal" class="btn btn-primary fr mb-4 addmodule"><i class="fe fe-plus mr-1"></i> Add Price</a>
					<div class="e-table">
					<div class="table-responsive table-lg mt-3">
					<table class="table table-bordered border-top text-nowrap stockprice-price" id="stockprice-price" style="width: 100%;">
					<thead>
					<tr>

					<th class="align-top border-bottom-0 wd-5 notexport">Select</th>
					<!-- <th class="border-bottom-0 w-30">Product</th> -->

					<th class="border-bottom-0 w-20">Regular Price</th>
					<th class="border-bottom-0 w-20">Sale Price</th>
					<th class="border-bottom-0 w-20">Sale Start</th>
					<th class="border-bottom-0 w-20">Sale End</th>
					<th class="border-bottom-0 w-20">Updated Date</th>

					<!-- <th class="border-bottom-0 w-30">Status</th> -->

					<!-- <th class="border-bottom-0 w-10 notexport">Actions</th> -->
					</tr>
					</thead>

					<tbody>

					@if($price_log && count($price_log) > 0)
					@foreach($price_log as $row)
					<tr>
					<td class="align-middle select-checkbox" id="moduleid" data-value="{{$row['id']}}">
					<label class="custom-control custom-checkbox">

					<!--{{ $loop->iteration }}-->
					</label>
					</td>
					<td>{{$row->price}}</td> 

					<td>{{$row->sale_price}}</td> 
					<td ><span>@if(isset($row->sale_start_date)) {{date('d M Y',strtotime($row->sale_start_date))}} @endif</span></td>
					<td ><span>@if(isset($row->sale_end_date))  {{date('d M Y',strtotime($row->sale_end_date))}}  @endif</span></td>
					<td ><span>{{date('d M Y',strtotime($row->created_at))}}</span></td>


					</tr>
					@endforeach
					@endif




					</tbody>
					</table>
					</div>
					</div>
				</div>
			
			</div>
		</div>
		</div>




												
											</div>
										</div>
									</div>
								</div>
									<div class="row">
															<div class="col d-flex justify-content-end">
																<a href="{{url('/stock-price')}}"  class="mr-2 btn btn-secondary" >Back</a>
															
															</div>
														</div>
							</div>
						</div>
						<!-- End Row -->


						<!-- Add Form Modal -->

						<div class="modal fade" role="dialog" tabindex="-1" id="stock-form-modal">
						<div class="modal-dialog modal-md" role="document">
						<div class="modal-content">
						<div class="modal-header">
						<h5 class="modal-title">Add Stock</h5>
						<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">×</span>
						</button>
						</div>
						<div class="modal-body">
						{{ Form::open(array('url' => "stock-price/save", 'id' => 'stockForm', 'name' => 'stockForm', 'class' => '','files'=>'true')) }}
						{{Form::hidden('id',$products->id,['id'=>'stockid'])}}
						<div class="py-1">


						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Product Name <span class="text-red">*</span></label>

						{!! Form::text('product_name', $products->name, ['class' => 'form-control','disabled','id'=>'product_name']) !!}
						</div>
						</div>


						</div>
						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Product Price <span class="text-red">*</span></label>

						{!! Form::text('rate',$products->prdPrice->price, ['class' => 'form-control','disabled','id'=>'rate']) !!}
						</div>
						</div>


						</div>
						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Quantity <span class="text-red">*</span></label>

						{!! Form::number('quantity', null, ['class' => 'form-control','required','id'=>'module_class','max'=>999999]) !!}
						</div>
						</div>
						</div>

						<div class="row">
						<div class="col d-flex justify-content-end">
						<input class="btn btn-primary" type="submit" id="stockval" value="Save Changes">
						</div>
						</div>

						</div>
						{{Form::close()}}
						</div>
						</div>
						</div>
						</div>



						<div class="modal fade" role="dialog" tabindex="-1" id="price-form-modal">
						<div class="modal-dialog modal-md" role="document">
						<div class="modal-content">
						<div class="modal-header">
						<h5 class="modal-title">Add Price</h5>
						<button type="button" class="close" data-dismiss="modal">
						<span aria-hidden="true">×</span>
						</button>
						</div>
						<div class="modal-body">
						{{ Form::open(array('url' => "stock-price/price/save", 'id' => 'priceForm', 'name' => 'priceForm', 'class' => '','files'=>'true')) }}
						{{Form::hidden('id',$products->id,['id'=>'priceid'])}}
						<div class="py-1">


						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Product Name <span class="text-red">*</span></label>

						{!! Form::text('product_name', $products->name, ['class' => 'form-control','disabled','id'=>'product_name']) !!}
						</div>
						</div>


						</div>
						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Product Stock <span class="text-red">*</span></label>

						{!! Form::text('stock',$products->prdStock($products->id), ['class' => 'form-control','disabled','id'=>'stock']) !!}
						</div>
						</div>


						</div>
						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Price <span class="text-red">*</span></label>

						{!! Form::number('price', $products->prdPrice->price, ['class' => 'form-control','required','id'=>'price','max'=>999999]) !!}
						</div>
						</div>
						</div>

						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Sale Price <span class="text-red">*</span></label>

						{!! Form::number('sale_price', null, ['class' => 'form-control','required','id'=>'sale_price','max'=>999999]) !!}
						</div>
						</div>
						</div>

						<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Sale Start <span class="text-red">*</span></label>

						<div id="valid_from"  class="datepicker input-group date"
data-date-format="yyyy-mm-dd">
<input class="form-control" name="valid_from" type="text" readonly  onchange="date_check()" />
<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
</div>
<p style="color: red" id="errNm1"></p>
						</div>

						</div>
						</div>

												<div class="row">
						<div class="col">
						<div class="form-group">
						<label>Sale End <span class="text-red">*</span></label>

<div id="valid_to" class="datepicker input-group date"
data-date-format="yyyy-mm-dd">
<input class="form-control"  name="valid_to" type="text" readonly  onchange="date_check()" />
<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
</div>
<p style="color: red" id="errNm2"></p>
						</div>
						</div>
						</div>


					




						<div class="row">
						<div class="col d-flex justify-content-end">
						<input class="btn btn-primary" type="submit" id="priceval" value="Save Changes">
						</div>
						</div>

						</div>
						{{Form::close()}}
						</div>
						</div>
						</div>
						</div>
								

					</div>
				</div><!-- end app-content-->
            </div>

            <div id="loader" class="d-none"><div class="spinner1 content-spin"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>

<style type="text/css">
	table.dataTable tr.parent {
animation: none !important;
	}
	table.dataTable tr.selected p {
color: #fff;
	}

.fixed-btn {
	width: 120px;
}
.fixed_tabs {
	width: 50%;
}
</style>

@endsection
@section('js')
		<!-- INTERNAl Data tables -->

		<script src="{{URL::asset('admin/assets/js/datatable/tables/stockprice-stock-datatable.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/datatable/tables/stockprice-price-datatable.js')}}"></script>

		<script src="{{URL::asset('admin/assets/js/bootstrap-datepicker.js')}}"></script>
	<!-- INTERNAL Popover js -->
		<script src="{{URL::asset('admin/assets/js/popover.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/comboTreePlugin.js')}}"></script>
<script src="{{URL::asset('admin/assets/js/jquery.validate.min.js')}}"></script>
		<!-- INTERNAL Sweet alert js -->
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
<script type="text/javascript">



$(document).ready(function(){

    @if(Session::has('success')) toastr.success("{{ Session::get('success')}}"); 
    @elseif(Session::has('error')) toastr.error("{{ Session::get('error')}}");  @endif
  
$("#stockval").click(function(){

$("#stockForm").validate({
	ignore: [],
rules: {
"price": {
required: true
}
},

messages : {

"price": {
required: "Quantity field is required."
},

},

 errorPlacement: function(error, element) {
 	 console.log($(error).text());
            if (element.attr("name") == "prd_id[]" ) {
            
                $("#errNm1").text($(error).text());
                
            }else if (element.attr("name") == "sale_end" ) {
                $("#errNm2").text($(error).text());
                
            }else if (element.attr("name") == "sale_start" ) {
                $("#errNm3").text($(error).text());
                
            }else {
               error.insertAfter(element)
            }
        },

});
});



$("#priceval").click(function(){






$("#priceForm").validate({
	ignore: [],
rules: {
"price": {
required: true
},
"sale_price": {
required: true
},
"valid_from": {
required: true
},
"valid_to": {
required: true
}
},

messages : {

"price": {
required: "Price field is required."
},
"sale_price": {
required: "Sale Price field is required."
},
"valid_from": {
required: "Sale start field is required."
},
"valid_to": {
required: "Sale end field is required."
},

},

 errorPlacement: function(error, element) {
 	 // $("#errNm1").empty();$("#errNm2").empty();
 	 console.log($(error).text());
            if (element.attr("name") == "valid_from" ) {
            
                $("#errNm1").text($(error).text());
                
            }else if (element.attr("name") == "valid_to" ) {
                $("#errNm2").text($(error).text());
                
            }else if (element.attr("name") == "sale_start" ) {
                $("#errNm3").text($(error).text());
                
            }else {
               error.insertAfter(element)
            }
        },

});
});

$(".valid_from").change(function(){
$("#errNm1").empty();
});
$(".valid_to").change(function(){
$("#errNm2").empty();
});
	 $(".datepicker").datepicker({ 
        autoclose: true, 
        todayHighlight: true,
       startDate: new Date()
  }).datepicker('update', new Date());

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

	jQuery(document).ready(function(){
 
// Custom filtering function which will search data in column four between two values

 
$('body').on('change','.ser_status',function(){  

        
        
        var selid = jQuery(this).data("selid");
        
        var sestatus='0';
        if($(this).prop('checked') == true)
        {
        sestatus='1';
        }
        
        $.ajax({
        type: "POST",
        url: '{{url("/shocking-sales/status")}}',
        data: { "_token": "{{csrf_token()}}", id: selid,status:sestatus},
        success: function (data) {
        // alert(data);
        if(data ==1) {
        if(sestatus ==1) {
        	jQuery('#status-'+selid).closest("td").attr("data-search","Active");
           
                toastr.success("Shocking Sale activated successfully.");
             
            }else {
            	jQuery('#status-'+selid).closest("td").attr("data-search","Inactive");
                toastr.success("Shocking Sale deactivated successfully."); 

            }
            var table = $.fn.dataTable.tables( { api: true } );
            table.rows().invalidate().draw();
        }else {
        toastr.error("Failed to update status."); 	
        }
        
        
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
$(function() {
jQuery(".addmodule").click(function(){

jQuery("#stock-form-modal .modal-title").text("Add Stock");

$("#stockForm").trigger("reset");

jQuery("#price-form-modal .modal-title").text("Add Price");

$("#priceForm").trigger("reset");

});
});
</script>
@endsection