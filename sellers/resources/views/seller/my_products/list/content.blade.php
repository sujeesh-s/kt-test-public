<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title mb-0">{{$title}}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Seller Management</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="#">{{$title}}</a></li>
        </ol>
    </div>

<div class="page-rightheader" style="display:flex; flex-direction: row; justify-content: center; align-items: center">
        <div class="btn btn-list">
            <a id="addNew"   class="btn btn-primary addmodule" data-toggle="modal" data-target=".bd-example-modal-sm"><i class="fe fe-plus mr-1"></i> Add New</a>
            <a id="ImpProd"   class="btn btn-primary importprod" data-target="#prod-import-modal" data-toggle="modal" ><i class="fe fe-plus mr-1"></i>Import</a>
        </div>
    </div>
</div>

<div class="row" id="filterrow">
<div class="plus-minus-toggle collapsed"><p>Additional Filters</p></div> 


</div>
<div class="row" id="filtersec" style="display:none; margin-bottom: 20px;">
<!--<div class="col-3">-->
<!--<div class="page-filters">-->
<!--<select class="form-control" id="filterSel" style="margin-right: 30px;">-->
<!--<option value="">All Sellers</option>-->
<!--@if($sellers && count($sellers) > 0)-->
<!--@foreach($sellers as $kr=>$kv)-->
<!--<option value="{{ $kv }}">{{ $kv }}</option>-->
<!--@endforeach-->
<!--@endif-->

<!--</select>-->

<!--</div>-->
<!--</div>-->
<div class="col-3">

<div class="page-filters">

<select class="form-control" id="filterCat" style="margin-right: 30px;">
<option value="">All Categories</option>
@if($categories && count($categories) > 0)
@foreach($categories as $kr=>$kv)
<option value="{{ $kv }}">{{ $kv }}</option>
@endforeach
@endif
</select>
</div>

</div>
<div class="col-3">
<div class="page-filters" >
<select class="form-control" id="filterStat" style="margin-right: 30px;">
<option value="">All Status</option>
<option value="Active">Active</option>
<option value="Inactive">Inactive</option>
</select>

</div>

</div> 
<div class="col-3">
    <!-- <div class="page-filters" style="display: inline-flex;">
<select class="form-control" id="filterSel" style="margin-right: 30px;">
<option value="">All Status</option>
<option value="Active">Active</option>
<option value="Inactive">Inactive</option>
</select>

</div> -->
</div>                             
</div>



<div class="row flex-lg-nowrap">
    <div class="col-12">
        <div class="row flex-lg-nowrap">
            <div class="col-12 mb-3">
                <div class="e-panel card">
                    <div id="data-content" class="card-body">
                        <div class="e-table">
                            <div class="table-responsive table-lg mt-3">
                                    <table id="product" class="product-table table table-striped table-bordered w-100 text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="wd-15p notexport"></th>
                                            <th class="wd-15p">Product Name</th>
                                            <!--<th class="wd-15p">Seller Name</th>-->
                                            <th class="wd-15p">Category</th>
                                            <th class="wd-20p">Sub Category</th>
                                            <th class="wd-10p">Created On</th>
                                            <th class="wd-25p notexport">Status</th>
                                            <th class="wd-20p">Admin Status</th>
                                            <th class="wd-25p text-center notexport action-btn">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody> 
                                        @if($products && count($products) > 0) @php $n = 0; @endphp
                                            @foreach($products as $row) @php $n++; @endphp <?php // echo 'ssdss<pre>'; print_r($row->prdType); echo '</pre>'; die; ?>
                                                @php if($row->is_active == 1){ $active = "Active"; $checked = 'checked'; }else if ($row->is_active == 0){ $active = "Inactive"; $checked = ""; }

                                                if($row->is_approved == 1){ $approval = "Approved"; $checked_app = 'checked'; }else if ($row->is_approved == 0){ $approval = "Pending"; $checked_app = ""; }
                                                 @endphp
                                                <tr class="dtrow" id="dtrow-{{$row->id}}">
                                                    <td><span class="d-none">{{$n}}</span></td>
                                                    <td><a style="cursor: pointer;" class="pointer viewBtn" id="viewBtn-{{$row->id}}">{{$row->name}}</a></td>
                                                    <!--<td data-search="{{$row->seller->fname}}">{{$row->seller->fname}}</td>-->
                                                    <td data-search="{{$row->category->cat_name}}">{{$row->category->cat_name}}</td>
                                                    <td>{{$row->subCategory->subcategory_name}}</td>
                                                    <td>{{date('d M Y',strtotime($row->created_at))}}</td>
                                                    <td data-search="{{$active}}">
                                                        <div class="switch">
                                                            <input class="switch-input status-btn" id="status-{{$row->id}}" type="checkbox" {{$checked}} name="status">
                                                            <label class="switch-paddle" for="status-{{$row->id}}">
                                                                <span class="switch-active" aria-hidden="true">Active</span>
                                                                <span class="switch-inactive" aria-hidden="true">Inactive</span>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td data-search="{{$approval}}">
                                                       <p>{{$approval}}</p>
                                                    </td>
                                                    <td class="text-center">
                                                        <button id="specialOfr-{{$row->id}}" class="mr-2 btn btn-info btn-sm specialOfr"><i class="fa fa-edit mr-1"></i>Special Offer</button>
                                                        <button id="editBtn-{{$row->id}}" class="mr-2 btn btn-info btn-sm editBtn"><i class="fa fa-edit mr-1"></i>Edit</button>
                                                        <button id="delBtn-{{$row->id}}" class="mr-2 btn btn-secondary btn-sm delBtn"><i class="fe fe-trash-2 mr-1"></i>Delete</button>
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

<div class="modal fade" role="dialog" tabindex="-1" id="prod-import-modal">
<div class="modal-dialog modal-md" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Import Excel</h5>
<button type="button" class="close" data-dismiss="modal">
<span aria-hidden="true">×</span>
</button>
</div>
<div class="modal-body">
{{ Form::open(array('url' => "my-products/import", 'id' => 'importForm', 'name' => 'importForm', 'class' => '','files'=>'true')) }}

<div class="py-1">


<div class="row">
<div class="col">
<div class="form-group">


<input type="file" class="dropify" data-height="180" name="imp_file" id="imp_file" data-allowed-file-extensions='["xlsx", "xls"]' />
<p style="color: red" id="errNm1"></p>
</div>
</div>


</div>



<div class="row">
<div class="col d-flex justify-content-end">
<input class="btn btn-primary" type="submit" id="importval" value="Import">
</div>
</div>

</div>
{{Form::close()}}
</div>
</div>
</div>
</div>  
    