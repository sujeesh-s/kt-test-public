@php    
    $id         =   ($seller)?  $seller->id : 0;                                $email      =   ($seller)?  $seller->teleEmail->value : '';
    $phone      =   ($seller)?  $seller->telePhone->value : '';                 $refCode    =   ($seller)?  $seller->ref_code : '';
    $name       =   ($info)?    $info->fname : '';                              $icNo       =   ($info)?    $info->ic_number : '';
    $bName      =   ($store)?   $store->business_name : '';                     $sName      =   ($store)?   $store->store_name : '';
    $licence    =   ($store)?   $store->licence : '';                           $address    =   ($store)?   $store->address : '';
    $latitude   =   ($store)?   $store->latitude : '';                          $longitude  =   ($store)?   $store->longitude : '';
    $country    =   ($store)?   $store->country_id : '';                        $state      =   ($store)?   $store->state_id : '';
    $city       =   ($store)?   $store->city_id : '';                           $zip        =   ($store)?   $store->zip_code : '';
    $comi       =   ($store)?   $store->commission : '';                        $icName     =   ($store)?   $store->incharge_name : '';
    $icPhone    =   ($store)?   $store->incharge_phone : '';                    $shipMtd    =   ($store)?   $store->ship_method : '';
    $packOption =   ($store)?   $store->pack_option : '';                       $pickOption =   ($store)?   $store->pickup_option : '';
    $isPikChrg  =   ($store)?   $store->is_pickup_chrge : 0;                    $pikChrg    =   ($store)?   $store->pickup_chrge : 0;
    $discount   =   ($store)?   $store->discount : 0;                           $limitType  =   ($store)?   $store->limit_type : '';
    $purLimit   =   ($store)?   $store->purchase_limit : '';                    $trackLink  =   ($store)?   $store->tracking_link : '';
    $active     =   ($store)?   $store->is_active : 1;                          $storeId    =   ($store)?   $store->id : 0;
    $logo       =   ($store)?   $store->logo : NULL;                            $banner     =   ($store)?   $store->banner : NULL;
    $packOption =   ($store)?   $store->pack_option : 0;
    $catIds     =   $assSlotIds = $assSpotIds = [];
     $icPhoneISD    =   ($store)?   $store->incharge_isd_code : '';   $isd_code    =   ($seller)?   $seller->isd_code : ''; 
    if($storeId     >   0){ foreach($assCategories as $strCat){    $catIds[]       =   $strCat->category_id; } }
    if($assSlots    &&  count($assSlots) > 0){  foreach($assSlots as $row){ $assSlotIds[]   =   $row->slot_id;  }   }
    if($assSpots    &&  count($assSpots) > 0){  foreach($assSpots as $row){ $assSpotIds[]   =   $row->slot_id;  }   }
    if($packOption  >   0){ $packChargeY = true; $packChargeN = false; }else{ $packChargeY  =   false; $packChargeN = true; }   
    if($logo        !=  NULL && $logo   != ''){ $logoImg    = url('storage'.$logo); }else{ $logoImg = url('storage/app/public/no-avatar.png'); }
    if($banner      !=  NULL && $banner != ''){ $bannerImg  = url('storage'.$banner); }else{ $bannerImg = url('storage/app/public/no-banner.png'); }
        $bank_id       =   (isset($bank['id']))?    $bank['id'] : 0;  $ac_no       =   (isset($bank['ac_no']))?    $bank['ac_no'] : '';  
    $ac_holder       =   (isset($bank['ac_holder']))?    $bank['ac_holder'] : '';  $bank_name       =   (isset($bank['bank_name']))?    $bank['bank_name'] : '';  
    $acc_type       =   (isset($bank['acc_type']))?    $bank['acc_type'] : 0;  $ifsc       =   (isset($bank['ifsc']))?    $bank['ifsc'] : '';  
    $branch       =   (isset($bank['branch']))?    $bank['branch'] : '';  
@endphp  <?php // echo '<pre>'; print_r($catIds); echo '</pre>'; ?>
<div class="page-header nonPrintable" >
    <div class="page-leftheader">
        <h4 class="page-title mb-0">{{$title}}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>User Management</a></li>
            <li class="breadcrumb-item"><a href="#" id="bc_list"><i class="fe fe-grid mr-2 fs-14"></i>Seller List</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="#">{{$title}}</a></li>
        </ol>
    </div>
    <div class="page-rightheader" >
       
        <div class="btn btn-list">
            <a  data-target="#qr-modal" data-toggle="modal"  class="btn btn-primary addmodule"> View QR Code</a>
        </div>
    </div>
</div>
<div class="col-lg-12 col-md-12 nonPrintable" >
    <div class="card">
        <div class="card-body pb-2">
            {{ Form::open(array('url' => "/profile/save", 'id' => 'adminForm', 'name' => 'adminForm','novalidate', 'class' => '','files'=>'true')) }}
                {{Form::hidden('storeId',$storeId,['id'=>'storeId'])}} {{Form::hidden('id',$id,['id'=>'id'])}} {{Form::hidden('formType','',['id'=>'formType'])}} 
                @if($filters && count($filters) > 0) @foreach($filters as $k=>$filter) {{Form::hidden('filter['.$k.']',$filter,['id'=>$k])}} @endforeach @endif
                <div class="tabs-menu mb-4">
                    <ul class="nav panel-tabs">
                        <li><a href="#tab1" data-toggle="tab" id="nav_tab_1" class="active"><span>Basic Info.</span></a></li>
                        <li><a href="#tab2" data-toggle="tab" id="nav_tab_2"><span>Store Address.</span></a></li>
                        <li><a href="#tab4" data-toggle="tab" id="nav_tab_4"><span>Security</span></a></li>
                        <li><a href="#tab3" data-toggle="tab" id="nav_tab_3"><span>Store Settings</span></a></li>
                       <li><a href="#tab5" data-toggle="tab" id="nav_tab_5"><span>Bank Details</span></a></li>
                      <!--   <li><a href="#tab5" data-toggle="tab" id="nav_tab_5"><span>Bank Details</span></a></li>-->
                   </ul>
                </div>
                <div class="row panel-body tabs-menu-body">
                    <div class="tab-content col-12">
                        @include('seller.profile.details.basic_info')
                        @include('seller.profile.details.security')
                        @include('seller.profile.details.store_address')
                        @include('seller.profile.details.store_settings')
                        @include('seller.profile.details.bank')
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card-footer text-right">
                        {{Form::hidden('can_submit',0,['id'=>'can_submit'])}}{{Form::hidden('page','admin',['id'=>'admin'])}}
                        <!-- <button id="cancel_btn" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button> -->
                        <button id="save_btn" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
           {{Form::close()}}
        </div>
    </div>
</div>

    <div class="modal fade" role="dialog" tabindex="-1" id="qr-modal">
    <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
    <div class="modal-header">
    <h5 class="modal-title tc-name">{{ $sName }}</h5>
    <button type="button" class="close" data-dismiss="modal">
    <span aria-hidden="true">×</span>
    </button>
    </div>
    <div class="modal-body">
    <div class="qr-container pt-5 pb-5">
{!! QrCode::size(250)->generate("seller_id:$id"); !!}
    </div> 
    </div>
    <div class="modal-footer" class="noPrint">
                                        
                                        <button id="btnPrint" class="btn btn-primary " onclick="print_this();">Print</button>
                                       
                                    </div>
    </div>
    </div>
    </div>

    <style type="text/css">
        .qr-container {
            text-align: center;
            margin: auto;
            display: block;
        }
        .tc-name {
            text-align: center;
            margin: auto;
              display: block;
        }

@media print {


#qr-modal .modal-content {
width: 160% !important; 
}
#qr-modal .modal-dialog {
margin: 0px 15% !important;
}
#qr-modal .modal-title {
font-size: 48px !important;
}
#qr-modal .modal-body img {
width: 650px !important; 
}
#qr-modal .foot-sec .modal-title {
font-size: 43px !important;
}
}


 .noPrint{
      display: none;
    }
    </style>
    <script type="text/javascript">
    

  function print_this(){
jQuery(".nonPrintable").addClass("noPrint");
jQuery("footer.footer").addClass("noPrint");
jQuery("#qr-modal .close").addClass("noPrint");
jQuery("#qr-modal #btnPrint").addClass("noPrint");

window.print();
    
}

function handlePrint(){
jQuery("footer.footer").removeClass("noPrint");
jQuery("#qr-modal .close").removeClass("noPrint");
jQuery("#qr-modal #btnPrint").removeClass("noPrint");
jQuery(".nonPrintable").removeClass("noPrint");


}
    
</script>