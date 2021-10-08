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
                                            <th class="wd-25p notexport">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>

                                </table>
                                {{Form::hidden('baseurl',url('/'),['id'=>'baseurl'])}} {{ csrf_field() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

 