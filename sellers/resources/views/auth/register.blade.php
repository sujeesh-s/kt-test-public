@extends('layouts.master2')
@section('css')
<link rel="stylesheet" href="{{URL::asset('admin/assets/css/toastr.min.css')}}" />
<style> 
    .container{ max-width: 960px; } .fw{ width: 100%; } .error{ position: absolute; top: 43px; right: 15px; font-weight: bold; } .form-control{ border-radius: 0; }
    .custom-radio .custom-control-input:checked~.custom-control-label::before { background-color: #00ffff !important; border-color:#00ffff !important; } 
    .error.mlt-select{ bottom: 0; right: 35px; top: auto}
</style>
@endsection
@section('content')
<div class="page">
    <div class="page-content">
        {{ Form::open(array('url' => "/create/seller", 'id' => 'sellerForm', 'name' => 'sellerForm', 'class' => '','files'=>'true')) }}
            <div class="container">
                <div class="row align-items-center justify-content-center">
                    <div class="col-md-12">
                        <div class="">
                            <div class="text-white">
                                <div class="card-body">
                                    <h2 class="display-4 mb-2 font-weight-bold error-text text-center"><strong>Seller Registration</strong></h2>
                                    <h4 class="text-white-80 mb-7 text-center"></h4>
                                    <div class="row">
                                        <h4 class="col-12 mb-4 text-white">Basic Information </h4>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="business_name"><strong>Business Name</strong></label>
                                            <input type="text" id="business_name" name="store[business_name]" class="form-control fw" placeholder="Business Name">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="store_name"><strong>Store Name</strong></label>
                                            <input type="text" id="store_name" name="store[store_name]" class="form-control fw" placeholder="Store Name">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Phone</strong></label>
                                            <input type="text" id="phone" name="phone" class="form-control fw" placeholder="Phone Number">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="Email"><strong>Email</strong></label>
                                            <input type="email" id="email" name="email" class="form-control fw" placeholder="Email">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Store License</strong></label>
                                            <input type="text" id="licence" name="store[licence]" class="form-control fw" placeholder="Store License">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Directer Name</strong></label>
                                            <input type="text" id="director_name" name="info[director_name]" class="form-control fw" placeholder="Director Name">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Director IC Number</strong></label>
                                            <input type="text" id="ic_number" name="info[ic_number]" class="form-control fw" placeholder="Director IC Number">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Incharge Name</strong></label>
                                            <input type="text" id="incharge_name" name="store[incharge_name]" class="form-control fw" placeholder="Incharge Name">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="incharge_phone"><strong>Incharge Contact Number</strong></label>
                                            <input type="text" id="incharge_phone" name="store[incharge_phone]" class="form-control fw" placeholder="Incharge Contact No.">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="store_desc"><strong>Store Description</strong></label>
                                            <textarea id="store_desc" name="store[store_desc]" class="form-control fw" placeholder="Store Description"></textarea>
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2 d-none">
                                            <label class="col-12" for="store_certificate"><strong>Certificate</strong></label>
                                            <input type="file" id="store_certificate" name="certi[]" class="form-control fw">
                                        </div>
                                        
                                        <h4 class="col-12 mb-2 mt-4 text-white">Store Address </h4>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="address"><strong>Address Line 1</strong></label>
                                            <input type="text" id="address" name="store[address]" class="form-control fw" placeholder="Address Line 1">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="address2"><strong>Address Line 2</strong></label>
                                            <input type="text" id="address2" name="store[address2]" class="form-control fw" placeholder="Address Line 2">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Country</strong></label>
                                            {{Form::select('store[country_id]',countryList(),'',['id'=>'country_id','class'=>'form-control fw', 'placeholder'=>'Select Country'])}}
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>State</strong></label>
                                            {{Form::select('store[state_id]',[],'',['id'=>'state_id','class'=>'form-control fw', 'placeholder'=>'Select State'])}}
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>City</strong></label>
                                            {{Form::select('store[city_id]',[],'',['id'=>'city_id','class'=>'form-control fw', 'placeholder'=>'Select City'])}}
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Zip Code</strong></label>
                                            <input type="text" id="zip_code" name="store[zip_code]" class="form-control fw" placeholder="Zip Code">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Latitude</strong></label>
                                            <input type="number" id="latitude" name="store[latitude]" class="form-control fw" placeholder="Latitude">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Longitude</strong></label>
                                            <input type="number" id="longitude" name="store[longitude]" class="form-control fw" placeholder="Longitude">
                                            <span class="error"></span>
                                        </div>
                                        
                                        <h4 class="col-12 mb-2 mt-4 text-white">Store Settings </h4>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Commission</strong></label>
                                            <input type="number" id="commission" name="store[commission]" class="form-control fw" placeholder="Commission">
                                            <span class="error"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <div class="col-12">
                                                <label class="fw" for="apply_tax"><strong>Do you apply service tax?</strong></label>
                                                <label for="apply_tax_yes" class="custom-control custom-radio custom-control-md col-md-6 fl">
                                                    <input type="radio" class="custom-control-input cus_radio" name="store[apply_tax]" value="1" id="apply_tax_yes" checked="" >
                                                    <span class="custom-control-label custom-control-label-md"> Yes </span>
                                                </label>
                                                <label class="custom-control custom-radio custom-control-md col-md-6 fl">
                                                    <input type="radio" class="custom-control-input cus_radio" name="store[apply_tax]" id="apply_tax_no" value="0">
                                                    <span class="custom-control-label custom-control-label-md">No</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="phone"><strong>Store Categories</strong></label>
                                            {{Form::select('categories[]',categoryList(),'',['id'=>'categories','class'=>'form-control fw', 'placeholder'=>'Select Categories','multiple'=>true])}}
                                            <span class="error mlt-select"></span>
                                        </div>
                                        <div class="input-group col-md-6 fl mb-2">
                                            <label class="col-12" for="logo"><strong>Certificate</strong></label>
                                            <div class="col-7 fl"><input type="file" id="logo" name="logo" class="form-control fw"></div>
                                            <div class="col-5 fl"><img id="logoImg" src="{{url('storage/app/public/no-avatar.png')}}" alt="Logo" height="70" /></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    {{Form::hidden('can_submit',0,['id'=>'can_submit'])}}
                                    <a class="btn-link font-weight-normal text-white mr-4" href="{{url('/login')}}">Back to login page</a>
                                    <button id="save_btn" type="submit" class="btn btn-primary">Register</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {{Form::close()}}
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } }); 
    $(document).ready(function(){ 
        $('#sellerForm #can_submit').val(0);
        @if(Session::has('success')) toastr.success("{{ Session::get('success')}}"); 
        @elseif(Session::has('error')) toastr.error("{{ Session::get('error')}}");  @endif
        $('body').on('submit','#sellerForm',function(e){ 
            if($('#sellerForm #can_submit').val() > 0){ return true; } 
            else{ 
                e.preventDefault();    
                var formData = new FormData(this); $('#sellerForm .error').html('');
                $('#sellerForm #save_btn').attr('disabled',true); $('#sellerForm #save_btn').text('Validating...'); 
                $.ajax({
                    type: "POST",
                    url: '{{url("/validate/seller")}}',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if(data == 'success'){  $('#js_sec').remove();
                            if($('#categories').val() == ''){ 
                                $('#sellerForm #categories').closest('div').find('.error').html('The categories field is required'); 
                                $('#sellerForm #save_btn').attr('disabled',false); $('#sellerForm #save_btn').text('Register'); return false;
                            }
                            $('#sellerForm #save_btn').text('Registering...'); 
                          //   submitForm(formData); return false;
                             $('#sellerForm #can_submit').val(1); $('#sellerForm').submit();
                        }else{
                            var errKey = ''; var n = 0;
                            $.each(data, function(key,value) { if(n == 0){ errKey = key; n++; }
                                $('#sellerForm #'+key).closest('div').find('.error').html(value);
                            }); 
                            if($('#categories').val() == ''){ $('#sellerForm #categories').closest('div').find('.error').html('The categories field is required'); }
                            $('#'+errKey).focus();
                            $('#sellerForm #save_btn').attr('disabled',false); $('#sellerForm #save_btn').text('Register'); return false;
                        }
                        return false;
                    }
                });
                
                
            }
          return false; 
        });
        
        $('body').on('change','#country_id',function(){
            $.ajax({
                type: "POST",
                url: '{{url("/getDropdown")}}',
                data: {"_token": "{{csrf_token()}}", table: 'states', field: 'country_id', value: this.value, label: 'state_name', placeholder: 'Select State', selected: '' },
                success: function (data) {
                    $('#state_id').html(data);
                } 
            }); return false;
        });
        $('body').on('change','#state_id',function(){
            $.ajax({
                type: "POST",
                url: '{{url("/getDropdown")}}',
                data: {"_token": "{{csrf_token()}}", table: 'cities', field: 'state_id', value: this.value, label: 'city_name', placeholder: 'Select City', selected: '' },
                success: function (data) {
                    $('#city_id').html(data);
                } 
            }); return false;
        });
        $("body").on('change','#sellerForm #logo',function(){ readURL(this); });
    });
    
    function readURL(input) { 
        if (input.files && input.files[0]) { 
            var reader = new FileReader();
            reader.onload = function (e) { $('#sellerForm #'+input.id+'Img').attr('src', e.target.result); $('#sellerForm #'+input.id+'Img').show(); }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<script src="{{URL::asset('admin/assets/js/toastr.min.js')}}"></script>

@endsection