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
        <link href="{{URL::asset('admin/assets/css/combo-tree.css')}}" rel="stylesheet" />
@endsection
@section('page-header')
						<!--Page header-->


						<div class="page-header">
							<div class="page-leftheader">
								<h4 class="page-title mb-0">Edit Subcategory</h4>
								<ol class="breadcrumb">
									<li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Master Settings</a></li>

									<li class="breadcrumb-item active" aria-current="page"><a href="{{ route('admin.subcategory')}}">Subcategory List</a></li>
									<li class="breadcrumb-item active" aria-current="page"><a href="#">Edit Subcategory</a></li>
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

								<!--@if(Session::has('message'))-->

								<!--<div class="alert alert-{{session('message')['type']}}" role="alert"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>{{session('message')['text']}}</div>-->
								<!--@endif-->
								<!--@if ($errors->any())-->
								<!--@foreach ($errors->all() as $error)-->

								<!--<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>{{$error}}</div>-->
								<!--@endforeach-->
								<!--@endif-->
								<div class="row flex-lg-nowrap">
									<div class="col-12 mb-3">
											<div class="card">
                                                <div class="card-body">
                                                    <form action="{{url('admin/update-subcategory/'.$subcategory->subcategory_id)}}" method="POST" enctype="multipart/form-data">
													@csrf
                                                    <?php  $default_lang =DB::table('glo_lang_lk')->where('is_active', 1)->first();
                                                            $category_data =DB::table('category')->where('category_id', $subcategory->category_id)->first();
                                                           $subcategory_name=DB::table('cms_content')->where('cnt_id', $subcategory->sub_name_cid)->where('lang_id', $default_lang->id)->first();
                                                           $category_name=DB::table('cms_content')->where('cnt_id', $category_data->cat_name_cid)->where('lang_id', $default_lang->id)->first();
                                                           if($subcategory->desc_cid){
                                                           $subcategory_desc=DB::table('cms_content')->where('cnt_id', $subcategory->desc_cid)->where('lang_id', $default_lang->id)->first();
                                                           $desc=$subcategory_desc->content;
                                                           }else{$desc='';} 
                                                           if($subcategory->parent!=0)
                                                           {
                                                               $parent_data=DB::table('subcategory')->where('subcategory_id', $subcategory->parent)->first();
                                                               $parent_name=DB::table('cms_content')->where('cnt_id', $parent_data->sub_name_cid)->where('lang_id', $default_lang->id)->first();
                                                               $parent_content= $parent_name->content;
                                                           }
                                                           else
                                                           {
                                                            $parent_content='';
                                                           }
                                                           ?>
                                                           
                                                           <input type="hidden" value="{{ $subcategory->sub_name_cid }}" name="sub_name_cid">
                                                           <input type="hidden" value="{{ $subcategory->desc_cid }}" name="desc_cid">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Select Language <span class="text-red">*</span></label>
                                                                <select class="form-control custom-select select2" name="language" required>
                                                                    
                                                                    @foreach ($language as $lang)
                                                                    <option value="{{ $lang->id }}">{{ $lang->glo_lang_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Subcategory Name <span class="text-red">*</span></label>
                                                                <input type="text" class="form-control @error('sub_category_name') is-invalid @enderror" placeholder="Sub Category" name="sub_category_name" value="{{ $subcategory_name->content }}">
                                                                <input type="hidden" name="id" id="curent_subid" value="{{ $subcategory->parent }}" />
                                                                <input type="hidden" name="id" id="curent_subid1" value="0" />
                                                                @error('sub_category_name')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Name in Local Language <span class="text-red">*</span></label>
                                                                <input type="text" class="form-control @error('local_name') is-invalid @enderror" placeholder="Name in Local Language" value="{{ $subcategory->local_name }}" name="local_name">
                                                                <input type="hidden" name="id" id="" value="0" />
                                                                @error('local_name')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Category<span class="text-red">*</span></label>
                                                                <select class="form-control custom-select select2 @error('category') is-invalid @enderror" name="category" id="category_id" onchange="loadsubcat()" required>
                                                                    <option value="">--Select--</option>
                                                                    @foreach ($category as $cat)
                                                                    <?php
                                                                    $default_lang =DB::table('glo_lang_lk')->where('is_active', 1)->first();
                                                                    $category_name=DB::table('cms_content')->where('cnt_id', $cat->cat_name_cid)->where('lang_id', $default_lang->id)->first();
                                                                    ?>
                                                                    <option value="{{ $cat->category_id }}" @if($cat->category_id==$subcategory->category_id){{ "selected" }} @endif>{{ $category_name->content }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('category')
                                                                    <span class="invalid-feedback" role="alert">
                                                                        <strong>{{ $message }}</strong>
                                                                    </span>
                                                                @enderror
                                                                <input type="hidden" value="{{ $subcategory->category_id }}" id="oldcat">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Parent Category<span class="text-red"></span></label>
                                                                <input type="text" id="sub-category-id" name="" value="{{ $subcategory->parent }}" hidden />
                                                                <input type="text" id="sub-category-drop" placeholder="Select" name="parent" class="form-control" value="{{ $parent_content }}" readonly  style="background-color: #fff !important;">
                                                                
                                                                
                                                               
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Subcategory Description <span class="text-red"></span></label>
                                                                <input type="text" class="form-control" placeholder="Description" name="subcategory_description" value="{{ $desc }}">
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-sm-12 col-md-12">
                                                            <div class="form-group">
                                                                <label class="form-label">Status <span class="text-red">*</span></label>
                                                                <select class="form-control select2" name="status">
                                                                    <option value="1" <?php if($subcategory->is_active==1){ echo "selected";}?>>Active</option>
                                                                    <option value="0" <?php if($subcategory->is_active==0){ echo "selected";}?>>Inactive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        {{-- <div class="col-sm-6 col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Daily Product <span class="text-red">*</span></label>
                                                                    <div class="row">
                                                                    <div class="col-md-2">
                                                                    <label class="custom-control custom-radio">
                                                                        <input type="radio" class="custom-control-input" name="daily_pro" value="1" checked>
                                                                        <span class="custom-control-label">Yes</span>
                                                                    </label>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                    <label class="custom-control custom-radio">
                                                                        <input type="radio" class="custom-control-input" name="daily_pro" value="0" checked>
                                                                        <span class="custom-control-label">No</span>
                                                                    </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> --}}
                                                        @if($subcategory->image)
                                                        <div class="col-lg-4 col-md-4 col-sm-12">
                                                            <label class="form-label">Subcategory Image <span class="text-red">*</span></label>
                                                            <div class="d-flex">
                                                                <img src="{{ url('storage/app/public/subcategory/'.$subcategory->image) }}" alt="{{ $subcategory->image }}"  style="height: 150px; max-height:150px; width:auto;">
                                                                <input type="hidden" value="{{ $subcategory->image }}" name="image_file">
                                                            </div>
                                                        </div>
                                                        @endif
                                                        <div class="col-lg-4 col-md-4 col-sm-12">
                                                            <label class="form-label">Choose another Subcategory Image <span class="text-red">*</span></label>
                                                            <input type="file" class="dropify" data-height="180" name="subcategory_image" />
                                                        </div>
                                                    </div>
                                                    <div class="col d-flex justify-content-end">
                                                    <a href="{{ route('admin.subcategory')}}" class="mr-2 mt-4 mb-0 btn btn-secondary" >Cancel</a>
                                                    <button type="submit" class="btn btn-primary mt-4 mb-0" >Submit</button>
                                                    </div>
                                                </form>
                                                </div>
                                            </div>
                                                    <!---hhj-->


									</div>
								</div>
							</div>
						</div>
						<!-- End Row -->

					</div>
				</div><!-- end app-content-->
            </div>
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

        <!-- INTERNAL File-Uploads Js-->
		<script src="{{URL::asset('admin/assets/plugins/fancyuploder/jquery.ui.widget.js')}}"></script>
        <script src="{{URL::asset('admin/assets/plugins/fancyuploder/jquery.fileupload.js')}}"></script>
        <script src="{{URL::asset('admin/assets/plugins/fancyuploder/jquery.iframe-transport.js')}}"></script>
        <script src="{{URL::asset('admin/assets/plugins/fancyuploder/jquery.fancy-fileupload.js')}}"></script>
        <script src="{{URL::asset('admin/assets/plugins/fancyuploder/fancy-uploader.js')}}"></script>

		<!-- INTERNAL File uploads js -->
        <script src="{{URL::asset('admin/assets/plugins/fileupload/js/dropify.js')}}"></script>
		<script src="{{URL::asset('admin/assets/js/filupload.js')}}"></script>

        <!----combotree----->
        <script src="{{URL::asset('admin/assets/plugins/combotree/comboTreePlugin.js')}}"></script>
        <!--<script src="https://estrradoweb.com/vrise/template/seller/assets/scripts/dropdown-tree/comboTreePlugin.js" ></script>-->
<script type="text/javascript">
    
    $(document).ready(function () {
  $('#subcategory_list').addClass("active");
  $('#a_sub').addClass("active");
  $('#master').addClass("is-expanded");
  
  
    });
	
    var instance = $('#sub-category-drop').comboTree({
    collapse:true,
    cascadeSelect:true,
    isMultiple: false
    });
    loadsubcat('1');
    var selectionIdList = new Array($("#sub-category-id").val());
    instance.setSelection(selectionIdList);
    

 function loadsubcat(clear='')
    {
        var category_id=$("#category_id").val();
        
        if(clear!='1')
        {
            $("#sub-category-id").val('');
        }
        
         $.ajax({
            type: "POST",
            url: '{{url("/admin/tags/subcategory")}}',
            data: { "_token": "{{csrf_token()}}", category_id: category_id},
            success: function (data) {
            	var obj = JSON.parse(data);
            
            	console.log(obj);
            	 var obj = JSON.parse(data);
            if(obj.subdata.length >=1)
            {
              $('#sub-category-drop').attr("placeholder", "Select subcategory"); 
            }
            else
            {
                $('#sub-category-drop').attr("placeholder", "No subcategory to display"); 
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
    $('#sub-category-drop').on('change',function()
        {
            if(instance.getSelectedIds())
            {
                $("#sub-category-id").val(instance.getSelectedIds()[0]);
            }
        });
    

    
  

</script>

@endsection
