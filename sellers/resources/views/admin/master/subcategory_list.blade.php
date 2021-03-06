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
								<h4 class="page-title mb-0">Subcategory List</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Masters</a></li>

									<li class="breadcrumb-item active" aria-current="page"><a href="#">Subcategory List</a></li>
								</ol>
							</div>
							<div class="page-rightheader" style="display:flex; flex-direction: row; justify-content: center; align-items: center">
							    <label class="form-label mr-2" for="filterSel">Filter </label>
							    						<select class="form-control mr-2" id="filterSel">
                                                        <option value="">All Status</option>
                                                        <option value="Active">Active</option>
                                                        <option value="Inactive">Inactive</option>
                                                        </select>
								<div class="btn btn-list">
									<!-- <a href="#" class="btn btn-info"><i class="fe fe-settings mr-1"></i> General Settings </a>
									<a href="#" class="btn btn-danger"><i class="fe fe-printer mr-1"></i> Print </a> -->
									<a href="{{ route('subcategory.new') }}"   class="btn btn-primary addmodule"><i class="fe fe-plus mr-1"></i> Add New</a>
								</div>
							</div>
						</div>
                        <!--End Page header-->
@endsection
@section('content')
						<!-- Row -->
						<div class="row flex-lg-nowrap">
							<div class="col-12">

								<!--@if(Session::has('message'))-->

								<!--<div class="alert alert-{{session('message')['type']}}" role="alert"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">??</button>{{session('message')['text']}}</div>-->
								<!--@endif-->
								<!--@if ($errors->any())-->
								<!--@foreach ($errors->all() as $error)-->

								<!--<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">??</button>{{$error}}</div>-->
								<!--@endforeach-->
								<!--@endif-->
								<div class="row flex-lg-nowrap">
									<div class="col-12 mb-3">
										<div class="e-panel card">
											<div class="card-body">
												<div class="e-table">
													<div class="table-responsive table-lg mt-3">
														<table id="subcategory" class="subcategory-table table table-striped table-bordered w-100 text-nowrap">
															<thead>
																<tr>
																	<th class="align-top border-bottom-0 wd-5"></th>
																	<th class="border-bottom-0 w-20">Sub-Category</th>
                                                                    <th class="border-bottom-0 w-20">Category</th>
                                                                    <th class="border-bottom-0 w-20">Parent</th>
																	<th class="border-bottom-0 w-30">Description</th>
																	<th class="border-bottom-0 w-30">Status</th>
																	<th class="border-bottom-0 w-15">Created On</th>
																	<th class="border-bottom-0 w-10">Actions</th>
																</tr>
															</thead>

															<tbody>

																@if($category && count($category) > 0) @php $n =0 ; @endphp
                    											@foreach($category as $row) @php $n++ ; @endphp
                                                                <?php  $default_lang =DB::table('glo_lang_lk')->where('is_active', 1)->first();
                                                                       $subcategory_name=DB::table('cms_content')->where('cnt_id', $row->sub_name_cid)->where('lang_id', $default_lang->id)->first();
                                                                       $category_data =DB::table('category')->where('category_id', $row->category_id)->first();
                                                                       $category_name=DB::table('cms_content')->where('cnt_id', $category_data->cat_name_cid)->where('lang_id', $default_lang->id)->first();
                                                                       $category_desc=DB::table('cms_content')->where('cnt_id', $row->desc_cid)->where('lang_id', $default_lang->id)->first();
                                                                       $parent_name='';
                                                                       if($row->parent!=0 && $row->parent!='')
                                                                       {
                                                                       $parent_data =DB::table('subcategory')->where('subcategory_id', $row->parent)->first();
                                                                       $parent_name=DB::table('cms_content')->where('cnt_id', $parent_data->sub_name_cid)->where('lang_id', $default_lang->id)->first();
                                                                       } ?>
                                                                       
																<tr>
																	<td class="align-middle" id="moduleid" data-value="{{$row->id}}">
																		<span class="d-none">{{$n}}</span>
																	</td>
																	<td class="align-middle" >
																	    @php $av_image=url('storage/app/public/subcategory/'.$row->image);
																	    @endphp
																	    <div class="d-flex">
																			@if($row->image)
																	<span class="avatar brround avatar-md d-block" style="background-image: url(<?php echo $av_image; ?>)"></span>
																			@else
																			<span class="avatar brround avatar-md d-block" style="background-image: url({{URL::asset('admin/assets/images/users/2.jpg')}})"></span>
																			@endif
																			<div class="ml-3 mt-1">
																				<h6 class="mb-0 font-weight-bold">{{$subcategory_name->content}}</h6>
																			</div>
																		</div>
																		<!--<div class="d-flex">-->
																		<!--		<h6 class=" font-weight-bold">{{$subcategory_name->content}}</h6>-->
                  <!--                                                      </div>-->
																	</td>
                                                                    <td class="align-middle" >
																		<div class="d-flex">
																				<h6 class=" font-weight-bold"><a href="{{ url('admin/subcategory/view/') }}/{{$row->subcategory_id}}">{{$category_name->content}}</a></h6>
                                                                        </div>
																	</td>
                                                                    <td class="align-middle" >
																		<div class="d-flex">
																				<h6 class=" font-weight-bold">@if($parent_name!='')
                                                                                    {{$parent_name->content}}</h6>
                                                                                    @endif
                                                                        </div>
																	</td>
																	<td class="text-nowrap align-middle">

																		<p style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis; max-width: 100px;">{{$category_desc->content}}</p>
																	</td>

																	<td class="text-nowrap align-middle" data-search="@if($row->is_active==1){{ "Active" }}@else{{ "Inactive" }}@endif">
																	    <div class="switch">
                                                                            <input class="switch-input status-btn ser_status" data-selid="{{$row->subcategory_id}}" id="status-{{$row->subcategory_id}}"  data-id="{{ $row->subcategory_id }}" type="checkbox"  @if($row->is_active ==1) {{ "checked" }} @endif>
                                                                            <label class="switch-paddle" for="status-{{$row->subcategory_id}}">
                                                                                <span class="switch-active" aria-hidden="true">Active</span>
                                                                                <span class="switch-inactive" aria-hidden="true">Inactive</span>
                                                                            </label>
                                                                        </div>
																		<!--<label class="onswitch  ">-->
                  <!--                                                          <input class='ser_status' data-selid="{{$row->subcategory_id}}"  data-id="{{ $row->subcategory_id }}"  type="checkbox"  @if($row->is_active ==1) {{ "checked" }} @endif />-->
                  <!--                                                          <span class="slider round"></span>-->
                  <!--                                                          </label>-->
																	</td>
																	<td class="text-nowrap align-middle"><span>{{date('d M Y',strtotime($row->created_at))}}</span></td>

																	<td class="align-middle">
																		<div class="btn-group align-top">

																			<a href="{{ url('admin/subcategory/edit/') }}/{{$row->subcategory_id}}"   class="btn btn-sm btn-info mr-2"><i class="fe fe-edit mr-1"></i> Edit</a>
																			<button  class="btn btn-sm btn-secondary deletecategory" type="button" onclick="delete_cat({{ $row->subcategory_id}})"><i class="fe fe-trash-2"></i>Delete</button>
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

            <!-- Modal -->
			<div class="modal  fade" id="del_modal" tabindex="-1" role="dialog" aria-labelledby="smallmodal" aria-hidden="true">
				<div class="modal-dialog modal-sm" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="smallmodal1">Delete Subcategory</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">??</span>
							</button>
						</div>
						<div class="modal-body">
							<p>Do you want really want to delete?</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
							<button type="button" class="btn btn-primary" id="ok_button">Yes</button>
						</div>
					</div>
				</div>
			</div>
@endsection
@section('js')
		<!-- INTERNAl Data tables -->
		<script src="{{URL::asset('admin/assets/js/datatable/tables/subcategory-datatable.js')}}"></script>
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

		<!-- INTERNAL Sweet alert js -->
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/jquery.sweet-modal.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/sweet-alert.js')}}"></script>
<script type="text/javascript">

	function delete_cat(cat_id){
       $('body').removeClass('timer-alert');
    swal({
        title: "Delete Confirmation",
        text: "Are you sure you want to delete this product?",
        // type: "input",
        showCancelButton: true,
        closeOnConfirm: true,
        confirmButtonText: 'Yes'
    },function(inputValue){
    if (inputValue == true) {
        $.ajax({
            type: "POST",
            url: '{{url("/admin/delete-subcategory/")}}',
            data: { "_token": "{{csrf_token()}}", cat_id: cat_id},
            success: function (data) {
                location.reload();

            }
        });
    }
});
}

    function status_update($cat_id){
       alert($cat_id);
    }

    $(function() {
    $('.ser_status').change(function() {
        var status = $(this).prop('checked') == true ? 1 : 0;
        var cat_id = $(this).data('id');

        $.ajax({
            type: "POST",
            url: '{{url("/admin/category/change-status-subcategory")}}',
            data: { "_token": "{{csrf_token()}}", cat_id: cat_id,status: status},
            success: function (data) {
                console.log(data.success)

            }
        });
        if(status!=true)
        { toastr.success("Inactivated Successfully");
        $(this).prop("");
        }else{ toastr.success("Activated Successfully"); }
    })
  })
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

@endsection
