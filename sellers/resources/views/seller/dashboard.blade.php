@extends('layouts.admin')
@section('css')
    <link href="{{URL::asset('admin/assets/traffic/web-traffic.css')}}" rel="stylesheet" type="text/css">
    		<link href="{{URL::asset('admin/assets/css/daterangepicker.css')}}" rel="stylesheet" />
@endsection

   @section('page-header')
						<!--Page header-->
						<div class="page-header">
							<div class="page-leftheader">
								<h4 class="page-title mb-0">Hi! Welcome Back</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="{{url('/' . $page='#')}}"><i class="fe fe-home mr-2 fs-14"></i>Home</a></li>
									<li class="breadcrumb-item active" aria-current="page"><a href="{{url('/' . $page='#')}}">Sales Dashboard</a></li>
								</ol>
							</div>
							<div class="page-rightheader">
								<!-- <div class="btn btn-list">
									<a href="{{url('/' . $page='#')}}" class="btn btn-info"><i class="fe fe-settings mr-1"></i> General Settings </a>
									<a href="{{url('/' . $page='#')}}" class="btn btn-danger"><i class="fe fe-printer mr-1"></i> Print </a>
									<a href="{{url('/' . $page='#')}}" class="btn btn-warning"><i class="fe fe-shopping-cart mr-1"></i> Buy Now </a>
								</div> -->
							</div>
						</div>
						<!--End Page header-->
						@endsection
						@section('content')						
						<!-- Row-1 -->
						<div class="row">
							<div class="col-xl-4 col-lg-6 col-md-6 col-xm-12">
								<div class="card overflow-hidden dash1-card border-0">
									<div class="card-body">
										<p class=" mb-1 ">Total Orders</p>
										<h2 class="mb-1 number-font">{{ $orders_c }}</h2>
										<small class="fs-12 text-muted">Total orders till now</small>
										<span class="ratio bg-warning"><a href="{{ url('/sales/orders') }}"><i class="fa fa-arrow-circle-right"></i> View Details</a></span>
									</div>
									
								</div>
							</div>
							<div class="col-xl-4 col-lg-6 col-md-6 col-xm-12">
								<div class="card overflow-hidden dash1-card border-0">
									<div class="card-body">
										<p class=" mb-1 ">Pending Orders</p>
										<h2 class="mb-1 number-font">{{ $orders_pending_c }}</h2>
										<small class="fs-12 text-muted">Total pending orders</small>
										<span class="ratio bg-info"><a href="{{ url('/sales/orders/request') }}"><i class="fa fa-arrow-circle-right"></i> View Details</a></span>
									</div>
									
								</div>
							</div>
							<div class="col-xl-4 col-lg-6 col-md-6 col-xm-12">
								<div class="card overflow-hidden dash1-card border-0">
									<div class="card-body">
										<p class=" mb-1 ">Total Sales</p>
										<h2 class="mb-1 number-font">RM {{ $total_ord_amt }}</h2>
										<small class="fs-12 text-muted">Total sales till now</small>
										<span class="ratio bg-danger"><a href="{{ url('/sales/orders') }}"><i class="fa fa-arrow-circle-right"></i> View Details</a></span>
									</div>
									
								</div>
							</div>
							
						</div>


						
						<!-- End Row-1 -->

						<!-- Row-2 -->
						<div class="row">
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card">
									<div class="card-header">
										<h3 class="card-title">Overall Sales statistics</h3>
										<div class="card-options">
											<div class="btn-group p-0">
												
											</div>
										</div>
									</div>
									<div class="card-body">
										
									<div class="chart" id="placeholder"></div>
    <div id="legends"></div>  

    <!--<div class="demo-container" style="height:150px;">-->
    <!--  <div id="overview_sale" class="demo-placeholder"></div>-->
    <!--</div>-->
									</div>
								</div>
							</div>
							
						</div>
						<!-- End Row-2 -->

						<!-- Row-3 -->
						<div class="row">
							
					
							<div class="col-xl-3  col-md-12">
								<div class="card">
									<div class="card-body">
										<div class="d-flex align-items-end justify-content-between">
											<div>
												<p class=" mb-1 fs-14">Revenue</p>
												<h2 class="mb-0"><span class="number-font1">RM {{ $revenue }}</span><span class="text-muted fs-11">
													<br><span class="text-success"> this month</span></h2>

											</div>
											<span class="text-secondary fs-35 dash1-iocns bg-secondary-transparent border-secondary"><i class="las la-hand-holding-usd"></i></span>
										</div>
										
									</div>
								</div>
							</div>
							<div class="col-xl-3  col-md-12">
								<div class="card">
									<div class="card-body">
										<div class="d-flex align-items-end justify-content-between">
											<div>
												<p class=" mb-1 fs-14">Orders Processing</p>
												<h2 class="mb-0"><span class="number-font1">{{ $ord_processing_c }}</span><br><span class="text-muted fs-11"><span class="text-success"> this month</span></h2>

											</div>
											<span class="text-primary fs-35 dash1-iocns bg-primary-transparent border-primary"><i class="fe fe-refresh-cw"></i></span>
										</div>
										
									</div>
								</div>
							</div>
							<div class="col-xl-3  col-md-12">
								<div class="card">
									<div class="card-body">
										<div class="d-flex align-items-end justify-content-between">
											<div>
												<p class=" mb-1 fs-14">Orders Completed</p>
												<h2 class="mb-0"><span class="number-font1"> {{ $ord_completed_c }} </span><br><span class="text-muted fs-11"><span class="text-success"> this month</span></h2>
											</div>
											<span class="text-secondary fs-35 dash1-iocns bg-secondary-transparent border-secondary"><i class="fe fe-check-circle"></i></span>
										</div>
										
									</div>
								</div>
							</div>
							<div class="col-xl-3  col-md-12">
								<div class="card">
									<div class="card-body">
										<div class="d-flex align-items-end justify-content-between">
											<div>
												<p class=" mb-1 fs-14">Out of stock</p>
												<h2 class="mb-0"><span class="number-font1">{{ $prd_out_stock_soon_c }}</span><br><span class="text-muted fs-11"><span class="text-danger"> soon</span></h2>
											</div>
											<span class="text-info fs-35 bg-info-transparent border-info dash1-iocns"><i class="fa fa-exclamation-triangle text-warning"></i></span>
										</div>
										
									</div>
								</div>
							</div>
						</div>
						<!-- End Row-3 -->

						<!--Row-->
						<div class="row">
							<div class="col-xl-12 col-lg-12 col-md-12">
								<div class="card">
									<div class="card-header">
										<h3 class="card-title">Products - Out of stock soon</h3>
										<div class="card-options">
											
										</div>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-vcenter text-nowrap mb-0 table-striped table-bordered border-top dashboard-table" id="dashboard-table">
												<thead class="">
													<tr>
														<th class="align-top border-bottom-0 wd-5 notexport">Select</th>
														<th>Product</th>
														<th>Seller</th>
														<th>Category</th>
														<th>Stock</th>
													
													</tr>
												</thead>
												<tbody>
													@if($prd_out_stock_soon && count($prd_out_stock_soon) > 0)
                    											@foreach($prd_out_stock_soon as $row)
                    											@if(($row->product_type ==2 && $row->visible ==0)|| ($row->product_type ==1) )
                    											 
													<tr>
														
														<td class="align-middle select-checkbox" id="moduleid" data-value="{{$row->id}}">
																		<label class="custom-control custom-checkbox">
																			
																			<!--{{ $loop->iteration }}-->
																		</label>
																	</td>
														<td class="align-middle" >

														<div class="d-flex">
														@if($row->prdImage->first())

														 @php $prod_img=config('app.storage_url').''.$row->prdImage->first()->image;
																	    @endphp
														<span class="avatar brround avatar-md d-block" style="background-image: url(<?php echo $prod_img; ?>)"></span>
														@else
														<span class="avatar brround avatar-md d-block" ></span>
														@endif
														<div class="ml-3 mt-1">
														<h6 class="mb-0 font-weight-bold">{{ $row->name }}</h6>
														</div>
														</div>
														</td>
														<td>{{ $row->seller->fname }}</td>
														<td>{{ $row->category->cat_name }}</td>
														<td>{{ $row->prdStock($row->id) }}</td>
														
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
						<!--End row-->

								

					</div>
				</div>
				<!-- End app-content-->
			</div>
			<style type="text/css">
				.chart {
  width:900px;
  height:400px;
  margin: auto;
  display: block;

}

			</style>
@endsection
@section('js')

<!--INTERNAL Peitychart js-->
<script src="{{URL::asset('assets/plugins/peitychart/jquery.peity.min.js')}}"></script>
<script src="{{URL::asset('assets/plugins/peitychart/peitychart.init.js')}}"></script>
<script src="{{URL::asset('admin/assets/js/datatable/tables/dashboard-table.js')}}"></script>
<!--INTERNAL Apexchart js-->


    <script src="{{URL::asset('admin/assets/traffic/jquery.flot1.js')}}"></script>
    <script src="{{URL::asset('admin/assets/traffic/jquery.flot.pie.js')}}"></script>
    <script src="{{URL::asset('admin/assets/traffic/jquery.flot.resize.js')}}"></script>
        <script src="{{URL::asset('admin/assets/traffic/jquery.flot.stack.js')}}"></script>

    <script src="{{URL::asset('admin/assets/traffic/jquery.flot.categories.js')}}"></script>
   <script type='text/javascript' src="{{URL::asset('admin/assets/traffic/jquery.flot.valuelabels.js')}}"></script>

	<script src="{{URL::asset('admin/assets/js/moment.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/daterangepicker.js')}}"></script>


  <script type="text/javascript">

$(document).ready(function(){


  
			var d = [
        {"label":"Annual Revenue", "data": [ <?php foreach ($sales_graph_revenue as $srk => $srv) { ?>[<?php echo $srk; ?>,<?php echo $srv; ?>],<?php } ?> ],"color":"#ef4b4b"},
        {"label":"Annual Profit", "data":[ <?php foreach ($sales_graph_profit as $spk => $spv) { ?>[<?php echo $spk; ?>,<?php echo $spv; ?>],<?php } ?> ], "color":"#5b7fff"},
    ];

    var options = {
        series: {
           
            bars: {
                show: true,
                barWidth: 0.2,
                fillColor: {
                    colors:[{opacity: 1},{opacity: 1}]
                },
                align: "center"
            }
        },
        legend : {
            container : "#legends",
            noColumns : 2
        }, valueLabels: {
                    show: true
                },
                  grid: {
            hoverable: true,
            clickable: true,
             borderColor: "#504848"

            //mouseActiveRadius: 30  //specifies how far the mouse can activate an item
        },
         xaxis: {tickSize : 1,tickLength:0,ticks: [[1, 'Jan'], [2, 'Feb'], [3, 'Mar'], [4, 'Apr'], [5, 'May'], [6, 'June'], [7, 'July'], [8, 'Aug'], [9, 'Sept'], [10, 'Oct'], [11, 'Now'], [12, 'Dec']]},
        yaxis: {max:5000,tickLength:0} 
    };

   plot =  $.plot($("#placeholder"), d, options);

    

function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 40,
        left: x - 120,
        border: '2px solid ' + color,
        padding: '3px',
            'font-size': '9px',
            'border-radius': '5px',
            'background-color': '#fff',
            'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.9
    }).appendTo("body").fadeIn(200);
}


$("#placeholder").on("plothover", function (event, pos, item) {
    if (item) {
    	var previousPoint = null,
    previousLabel = null;
        if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
            previousPoint = item.dataIndex;
            previousLabel = item.series.label;
            $("#tooltip").remove();

            var x = item.datapoint[0];
            var y = item.datapoint[1];

            var color = item.series.color;

            //console.log(item.series.xaxis.ticks[x].label);  
            console.log("YYYY-"+y);               

            showTooltip(item.pageX,
            item.pageY,
            color,
                "<strong>" + item.series.label + "</strong><br>Total : <strong>RM " + y + "</strong>");
        }
    } else {
        $("#tooltip").remove();
        previousPoint = null;
    }
});
});
 



  $(function() {

   

  


        $('#valid_from_sale').daterangepicker
        (
          {
            locale: {
                      format: 'DD/MM/YYYY'
                    },
            ranges:
            {
              'Today'       : [moment(), moment()],
              'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'This Month'  : [moment().startOf('month'), moment().endOf('month')],
              'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            opens:'left',
          },
          function(start, end, label)
          {
            startDate = start.format('YYYY/MM/DD  HH:mm:ss');
            endDate = end.format('YYYY/MM/DD  HH:mm:ss');
            console.log('A date range was chosen: ' + startDate + ' to ' + endDate);

          }
        );
    // $.plot("#placeholder", [d], {
    //   xaxis: { mode: "time", timeBase: "milliseconds"}
    // });

 

  });

  </script>
@endsection