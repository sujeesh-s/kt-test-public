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
		<link href="{{URL::asset('admin/assets/css/daterangepicker.css')}}" rel="stylesheet" />
		<link href="{{URL::asset('admin/assets/css/jquery-ui.css')}}" rel="stylesheet" />
			

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
												<div class="e-table">
													<div class="table-responsive table-lg mt-3">
														<table class="table table-bordered border-top text-nowrap stockprice" id="stockprice">
															<thead>
																<tr>

																	<th class="align-top border-bottom-0 wd-5 notexport">Select</th>
																	<th class="border-bottom-0 w-30">Product</th>
																	
																	<th class="border-bottom-0 w-20">Current Stock</th>
																	<th class="border-bottom-0 w-20">Current Price</th>
																	<th class="border-bottom-0 w-20">Action</th>
																	<!-- <th class="border-bottom-0 w-30">Status</th> -->
																						
																   <!-- <th class="border-bottom-0 w-10 notexport">Actions</th> -->
																</tr>
															</thead>

															<tbody>

																@if($products && count($products) > 0)
                    											@foreach($products as $row)
                    											@if(($row->product_type ==2 && $row->visible ==0)|| ($row->product_type ==1) )
																<tr>
																	<td class="align-middle select-checkbox" id="moduleid" data-value="{{$row['id']}}">
																		<label class="custom-control custom-checkbox">
																			
																			<!--{{ $loop->iteration }}-->
																		</label>
																	</td>
																	<td class="align-middle" >
																		<div class="d-flex">
																		<p>{{$row->name}}</p>
																	</div>
																	</td>

																	<td>{{$row->prdStock($row->id)}}</td> 
																<td>@if($row->prdPrice) {{$row->prdPrice->price}} @endif</td>
																	
																	
																	
																	
																 <td class="align-middle">
																		<div class="btn-group align-top">
																			
																			<a href="{{url('stock-price/log/')}}/{{$row->id}}"   class="mr-2 btn btn-info btn-sm editmodule"><i class="fe fe-edit mr-1"></i> View Logs</a>
																			
																		</div>
																	</td>  
																</tr>
																@endif
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
						<!-- End Row -->


						<!-- Add Form Modal -->

							<div style="display:none;">
								<table id="hiddentable" style="display: none;">
									<tbody>
										
									</tbody>
								</table>
								
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
</style>

@endsection
@section('js')
		<!-- INTERNAl Data tables -->

		<script src="{{URL::asset('admin/assets/js/datatable/tables/stockprice-datatable.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/moment.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/daterangepicker.js')}}"></script>
	<!-- INTERNAL Popover js -->
		<script src="{{URL::asset('admin/assets/js/popover.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/comboTreePlugin.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/jquery-ui.js')}}"></script>

		<!-- INTERNAL Sweet alert js -->
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
<script type="text/javascript">


function deletecpn(cpnid){
$('body').removeClass('timer-alert');
		swal({
			title: "Delete Confirmation",
			text: "Are you sure you want to delete this Auction?",
			// type: "input",
			showCancelButton: true,
			closeOnConfirm: true,
			confirmButtonText: 'Yes'
		},function(inputValue){



			if (inputValue == true) {
			 $.ajax({
            type: "POST",
             url: '{{url("/admin/coupons/delete")}}',
            data: { "_token": "{{csrf_token()}}", id: cpnid},
            success: function (data) {
            	// alert(data);
            	if(data ==1){
            		location.reload();
            	}
            
            }
        });

			}
		});
}

$(document).ready(function(){

  $('#valid_from').daterangepicker
        (
          {
            locale: {
                      format: 'DD/MM/YYYY'
                    },
            ranges:
            {
              'Today'       : [moment(), moment()],
              'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'Tomorrow'    : [moment().add(1, 'days'), moment().add(1, 'days')],
              'Next 7 Days' : [moment(),moment().add(6, 'days')],
              'Next 30 Days': [moment(),moment().add(29, 'days')],
              'This Month'  : [moment().startOf('month'), moment().endOf('month')],
              'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
          },
          function(start, end, label)
          {
            startDate = start.format('YYYY-MM-DD');
            endDate = end.format('YYYY-MM-DD');
            console.log('A date range was chosen: ' + startDate + ' to ' + endDate);
            $("#startdate").val(startDate);
            $("#enddate").val(endDate);
      
        
          }
        );
});



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


        $(document).ready(function() {


      $("#viewfilter").click(function(){
 $('#auctionslist tbody').append($('#loader').html()); $('#auctionslist').addClass('blur'); 
			var startdate = $("#startdate").val();
			var enddate = $("#enddate").val();




			$.ajax({
			type: "POST",
			url: '{{url("/auctions/filter")}}',
			 data: { "_token": "{{csrf_token()}}", startdate: startdate,enddate:enddate},
			success: function (data) {
			// console.log(data);
			var table = $.fn.dataTable.tables( { api: true } );
			if(data =="0") {
				// alert("no data");
				table.clear().draw();
			}else {
				$("#hiddentable tbody").html(data);
				// alert(data.length);
				html = data;
				i=0;
				var htmlFiltered = $(html).find("tr")
				// console.log(htmlFiltered);
				table.clear().draw();
				$("#hiddentable tr").each(function(index, tr) { 
				console.log(index);
				console.log(tr);
				table.row.add($(tr)).columns.adjust().draw();
				});

				//   table.rows.add(data); // Add new data
				//   table.columns.adjust().draw();
   

			}
			$('#auctionslist').removeClass('blur');
			 $('#auctionslist tbody').remove($('#loader').html()); 

			}
			});

      });
          
         });  
    </script>
<script type="text/javascript">
$(function() {
$('.plus-minus-toggle').on('click', function() {
$(this).toggleClass('collapsed');
$('#filtersec').toggle('slow');
});
});
</script>
@endsection