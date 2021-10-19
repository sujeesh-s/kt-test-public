@extends('layouts.admin')
@section('title', 'Profile')
@section('content')
<style>
#logo {
    padding: 3px;
    font-size: 14px;
}
#banner {
     padding: 3px;
}
</style>
<div id="pg_content"  >
    @include('seller.profile.details')
</div>
<div id="loader" class="d-none"><div class="spinner1 content-spin"><div class="double-bounce1"></div><div class="double-bounce2"></div></div></div>
@endsection
@section('js') 
<script src="{{URL::asset('admin/assets/js/jquery.validate.min.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function(){
 

    });

</script>
<script type="text/javascript">
 $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }); 
    $(document).ready(function(){ 
        $('#adminForm #can_submit').val(0); 
        @if(Session::has('success')) toastr.success("{{ Session::get('success')}}"); 
        @elseif(Session::has('error')) toastr.error("{{ Session::get('error')}}");  @endif
        $("body").on('change','#adminForm #logo',function(){ readURL(this); });
        $("body").on('change','#adminForm #banner',function(){ readURL(this); });
        $('body').on('click','#cancel_btn',function(){ $('#content_list').fadeIn(700); $('#content_detail').hide();  });
        $('body').on('click','#bc_list',function(){ $('#content_list').fadeIn(700); $('#content_detail').hide(); return false;  });
     
        $('body').on('submit','#adminForm',function(e){ 
            
            if($('#adminForm #can_submit').val() > 0){ return true; }
            else{ 
                e.preventDefault();    
                var formData = new FormData(this); $('body #adminForm .error').html('');
                $('#adminForm #save_btn').attr('disabled',true); $('#adminForm #save_btn').text('Validating...'); 
                $.ajax({
                    type: "POST",
                    url: '{{url("profile/validate")}}',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if(data == 'success'){  $('#js_sec').remove();


                            $('#adminForm #save_btn').text('Saving...');
                            
                             submitForm(formData); return false;
                           //  $('#adminForm #can_submit').val(1); $('#adminForm').submit();
                        }else{
                            var errKey = ''; var n = 0;
                            $.each(data, function(key,value) { if(n == 0){ errKey = key; n++; }
                                $('#adminForm #'+key).closest('div').find('.error').html(value);
                                if(key == 'error' && value == 'info'){ $('#nav_tab_1').trigger('click'); }
                                else if(key == 'error' && value == 'store'){ $('#nav_tab_2').trigger('click'); }
                                else if(key == 'error' && value == 'storeSet'){ $('#nav_tab_3').trigger('click'); }
                                 else if(key == 'error' && value == 'bank'){ $('#nav_tab_5').trigger('click'); }
                            }); 
                            $('#'+errKey).focus();
                            $('#adminForm #save_btn').attr('disabled',false); $('#adminForm #save_btn').text('Save'); return false;
                        }
                        return false;
                    }
                });
                
                
            }
          return false; 
        });
    
        $('body').on('click','button.close',function(){ $('#allert_success').fadeOut(); });


        
        $('body').on('change','#country_id',function(){
            $.ajax({
                type: "POST",
                url: '{{url("admin/getDropdown")}}',
                data: {table: 'states',field: 'country_id', value:this.value, label:'state_name',selected: '', placeholder:'Select State','_token': '{{ csrf_token()}}'},
                success: function (data) {
                    $('#state_id').html(data);
                }
            });
        });
        $('body').on('change','#state_id',function(){
            $.ajax({
                type: "POST",
                url: '{{url("admin/getDropdown")}}',
                data: {table: 'cities',field: 'state_id', value:this.value, label:'city_name',selected: '', placeholder:'Select City','_token': '{{ csrf_token()}}'},
                success: function (data) {
                    $('#city_id').html(data);
                }
            });
        });




    });

    function updateStatus(id,rowId,status,url,row,field,smsg){
        $.ajax({
            type: "POST",
            url: url,
            data: { "_token": "{{csrf_token()}}", id: id, value: status,field: field, field, page: row},
            success: function (data) {
                if(field == 'is_deleted'){ 
                  $('#active_filter').trigger('change'); toastr.success(smsg);
                }else{ 
                    if($('#active_filter').val() != ''){ $('#active_filter').trigger('change'); }
                    if (data.type == 'warning' || data.type == 'error'){ toastr.error(smsg); }else{ toastr.success(smsg); }
                } 
            }
        });
    }
    function submitForm(postValues){
        $.ajax({
            type: "POST",
            url: '{{url("profile/save")}}',
            data: postValues,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) { 
              $('#adminForm #save_btn').attr('disabled',false); $('#adminForm #save_btn').text('Save');
              if($('#adminForm #id').val() > 0){ var msg = 'Seller updated successfully!'; }else{ msg = 'Seller added successfully!'; }
              // $('#content_list').fadeIn(700); $('#content_detail').hide();
              $('#active_filter').trigger('change'); toastr.success(msg);  return false;
            } 
        });
    }
    function getStateDropdown(cId,selected){ 
        $.ajax({
            type: "POST",
            url: '{{url("admin/getDropdown/states/")}}',
            data: {field: 'country_id', value:cId, label:'name',selected: selected, placeholder:'Select State','_token': '{{ csrf_token()}}'},
            success: function (data) {
                $('#userForm #state').html(data);
            }
        });
    }
    
    function readURL(input) { 
        if (input.files && input.files[0]) { 
            var reader = new FileReader();
            reader.onload = function (e) { $('#adminForm #'+input.id+'Img').attr('src', e.target.result); $('#adminForm #'+input.id+'Img').show(); }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

@endsection