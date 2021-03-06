@extends('layouts.admin')
@section('title', 'Stock List')
@section('page-header')
@endsection
@section('content')
<div id="list_form"> @include('admin.stock.list.content')</div>
<div id="dtl_form">  </div>
    
@section('js') 

    <script src="{{URL::asset('admin/assets/js/datatable/tables/stock-datatable.js')}}"></script>

    <script>

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }); 
    $(document).ready(function(){ 
        
        
        
        
        $('#addData').on('click',function(){
            $.ajax({
                type: "GET",
                url: '{{url("admin/seller/product-stock/0")}}',
                success: function (data) {
                    $('#dtl_form').html(data); $('#dtl_form').fadeIn(700); $('#list_form').hide(); 
                } 
            }); return false;
        });
        
        $('body').on('click', '#cancel_btn', function(){ $('#dtl_form').hide(); $('#list_form').fadeIn(700); });
        $('body').on('click', '#bc_list', function(){ $('#dtl_form').hide(); $('#list_form').fadeIn(700); return false; });
        
        $('body').on('click','.editForm',function(){ 
            var id          =   this.id.replace('editForm-','');  $('#adminForm .error').text(''); 
            var prdId       =   $(this).data('product');
            var sellerId    =   $(this).data('seller'); 
            $.ajax({
                type: "POST",
                data: {prdId: prdId,sellerId: sellerId},
                url: '{{url("admin/seller/product-stock/add")}}',
                success: function (data) {
                    $('#dtl_form').html(data); $('#dtl_form').fadeIn(700); $('#list_form').hide();
                } 
            });
        });
        $('body').on('submit','#adminForm',function(e){ 
            $('#adminForm .error').html(''); 
            if($('#adminForm #can_submit').val() > 0){ return true; }
            if($('#adminForm #qty').val() == '' || $('#adminForm #qty').val() == 0 ){ $('#adminForm #qty_error').text('Please enter amount'); return false; }
            else{ $('#adminForm #can_submit').val(1); $('#adminForm').submit(); } return false;
                        
          return false; 
        });
        
        $("body").on("change", ".status-btn", function () {
            var id          =   this.id.replace('status-','');
            var bId         =   this.id;
            var sts         =   $(this).prop("checked");
            var url         =   '{{url("admin/attribute/updateStatus")}}';
            var smsg        =   'Attrinute activated successfully!';
            if (sts == true){ var status = 1; }else if (sts == false){var status = 0; smsg = 'Attrinute deactivated successfully!'; }
            updateStatus(id,bId,status,url,'dtrow-','is_active',smsg);
        });
        $('body').on('click','.delBtn',function(){ //alert('sss');
            var id          =   this.id.replace('delBtn-',''); 
            var status      =   1;
            var url         =   '{{url("admin/attribute/updateStatus")}}';
            var smsg        =   'Attrinute deleted successfully!';
            
            swal({
                title: "Delete Confirmation",
                text: "Are you sure you want to delete this Attribute?",
                // type: "input",
                showCancelButton: true,
                closeOnConfirm: true,
                confirmButtonText: 'Yes'
            },function(inputValue){
                if (inputValue == true) {
                    updateStatus(id,'',status,url,'admin','is_deleted',smsg);
                }
            });
        });
//        $('body').on('click','.delUser',function(){  
//            var id          =   $(this).data('id'); 
//            var bId         =   this.id;
//            var status      =   0;
//            var url         =   '{{url("admin/attribute/updateStatus")}}';
//            var smsg        =   'Attrinute deleted successfully!';
//            updateStatus(id,'',status,url,'admin','is_deleted',smsg);
//        });
        
        var row      =   1; 
        $('body').on('change','#adminForm #type',function(){ 
            $('#adminForm #data_type').val(''); 
            if(this.value == 'text' || this.value == 'textarea'){ 
                $('#adminForm #data_type_div').show(); $('#adminForm #filter_div').hide(); $('#adminForm #filter').val(0); $('#adminForm #config_div').hide(); $('#adminForm #configur').val(0);
                $('#adminForm .panel-tabs #nav_tab_3').hide(); 
            }else{ 
                $('#adminForm #data_type_div').hide(); $('#adminForm #filter_div').show(); $('#adminForm #filter').val(0); $('#adminForm #config_div').show(); $('#adminForm #configur').val(0);
                $('#adminForm .panel-tabs #nav_tab_3').show(); 
            }
        });
        
        $('body').on('click','#add_val',function(){ 
            var htmlContent             =   $('#adnl_rows').html();
            htmlContent                 =   htmlContent.replace('attr_val_row_id','attr-val-row-'+row);
            htmlContent                 =   htmlContent.replace('value_id_id','value_id_'+row);
            htmlContent                 =   htmlContent.replace('attr_val_id','val'+row);
            htmlContent                 =   htmlContent.replace('val_error_id','val_error_'+row);
            htmlContent                 =   htmlContent.replace('del_val_id','del_val_'+row);
            $('#adminForm #attr-val-content').append(htmlContent); row++;
        });
        
        $('body').on('click','#adminForm .del_val.del',function(){
            var id      =   this.id.replace('del_val_',''); deleteValue(id);
        });
        
        $('body').on('change','#adminForm #qty',function(){ 
            var qty      =   parseInt(this.value); 
            var rate    =   parseFloat($('#adminForm #rate').val()); // alert(qty+' -- '+rate); 
            $('#adminForm #amount').val((rate*qty));
        });
        @if(Session::has('success')) toastr.success("{{ Session::get('success')}}"); @endif
        @if(Session::has('error')) toastr.error("{{ Session::get('error')}}"); @endif
        
        $(document).ready(function(){  
            $('body').on('change','.number',function(){  
                var id  =   this.id; var val = this.value.replace(/[^0-9\.]/g,''); $(this).val(val); if($(this).val() == ''){ $(this).val(0); }
            });
            $('body').on('change','.numberonly',function(){  
                var id  =   this.id; var val = this.value.replace(/[^0-9]/g,''); $(this).val(val); if($(this).val() == ''){ $(this).val(0); }
            });
        });
    });
    
//    function confirmDelete(id,row){
//        var cnf      =   confirm("Are you sure?!");
//        if(cnf){ 
//            $.ajax({
//                type: "POST",
//                url: "{{url('attribute/value/delete')}}",
//                data: { "_token": "{{csrf_token()}}", id: id},
//                success: function (data) { deleteValue(row); }
//            });
//        }else{ return false; }
//    }
    function deleteValue(id){ $('#adminForm #attr-val-row-'+id).remove();}
    
    function updateStatus(id,rowId,status,url,row,field,smsg){ // alert(id+' -- '+rowId+' -- '+status+' -- '+url+' -- '+row+' -- '+field+' -- '+smsg);
        $.ajax({
            type: "POST",
            url: url,
            data: {id: id, value: status,field: field, field, page: row},
            success: function (data) { // alert(data.type);
                if(field == 'is_deleted'){
                    $('#list_form').html(data); toastr.success(smsg);
                }else{ 
                    if (data.type == 'warning' || data.type == 'error'){ toastr.error(smsg); }else{ toastr.success(smsg); }
                } 
            }
        }); return false;
    }
    function submitForm(postValues){
        $.ajax({
            type: "POST",
            url: '{{url("admin/attribute/save")}}',
            data: postValues,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) { 
              $('#adminForm #save_btn').attr('disabled',false); $('#adminForm #save_btn').text('Save');
              if($('#adminForm #id').val() > 0){ var msg = 'Atribute updated successfully!'; }else{ msg = 'Atribute added successfully!'; }
              $('#dtl_form').hide(); $('#list_form').fadeIn(700); $('#list_form').html(data); toastr.success(msg);
  return false;
            //  setTimeout(function(){ $('#allert_success').fadeOut(); }, 3000);
            } 
        }); return false;
    }
    function getStateDropdown(cId,selected){ 
        $.ajax({
            type: "POST",
            url: '{{url("admin/getDropdown/states/")}}',
            data: {field: 'country_id', value:cId, label:'name',selected: selected, placeholder:'Select State','_token': '{{ csrf_token()}}'},
            success: function (data) {
                $('#adminForm #state').html(data);
            }
        });
    }
</script>
@endsection
@endsection