<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title mb-0">{{$title}}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Product Management</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="#">{{$title}}</a></li>
        </ol>
    </div>
    <div class="page-rightheader" style="display:flex; flex-direction: row; justify-content: center; align-items: center">
        <label class="form-label mr-2" for="filterSel">Filter </label>
        {{Form::select('active',[1=>'Active',0=>'Inactive'],$active,['id'=>'active_filter','class'=>'form-control mr-4','placeholder'=>'All Status'])}}
<!--        <div class="btn btn-list">
            <a id="addNew"   class="btn btn-primary addmodule"><i class="fe fe-plus mr-1"></i> Add New</a>
        </div>-->
    </div>
</div>
<div class="row flex-lg-nowrap">
    <div class="col-12">
        <div class="row flex-lg-nowrap">
            <div class="col-12 mb-3">
                <div class="e-panel card">
                    <div id="data-content" class="card-body">
                       
                        <div id="table_body" class="card-body table-card-body">
                            <div>
                                    <table id="product" class="product table table-striped table-bordered w-100 text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="wd-15p notexport"></th>
                                            <th class="wd-15p">Product Name</th>
                                            <th class="wd-15p">Category</th>
                                            <th class="wd-15p">Sub Category</th>
                                            <th class="wd-25p notexport">Brand</th>
                                            <th class="wd-25p text-center notexport action-btn">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($products && count($products) > 0) @php $n = 0; @endphp
                                            @foreach($products as $row) @php $n++; @endphp <?php // echo '<pre>'; print_r($row->brand); echo '</pre>'; die; ?>
                                                @php if($row->is_active == 1){ $active = "Active"; $checked = 'checked'; }else if ($row->is_active == 0){ $active = "Inactive"; $checked = ""; } @endphp
                                                <tr class="dtrow" id="dtrow-{{$row->id}}">
                                                    <td><span class="d-none">{{$n}}</span></td>
                                                    <td><a id="dtlBtn-{{$row->id}}" class="font-weight-bold viewDtl">{{$row->name}}</a></td> 
                                                    <td>{{$row->category->cat_name}}</td>
                                                    <td>{{$row->subCategory->subcategory_name}}</td>
                                                    <td>@if($row->brand_id != NULL) {{$row->brand->name}} @else -- @endif</td>
                                                    <td class="text-center">
                                                        <button id="editBtn-{{$row->id}}" class="mr-2 btn btn-success btn-sm editBtn"><i class="fa fa-eye mr-1"></i>View</button>
                                                    </td>
                                            @endforeach
                                        @endif
                                    </tbody>

                                </table>
                                {{ csrf_field() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

 <script src="{{asset('admin/assets/js/datatable/tables/admin_product-datatable.js')}}"></script>
 