<?php    
//    $id         =   ($seller)?  $seller->id : 0;                                $email      =   ($seller)?  $seller->teleEmail->value : '';
//    $phone      =   ($seller)?  $seller->telePhone->value : '';                 $refCode    =   ($seller)?  $seller->ref_code : '';
//    $name       =   ($info)?    $info->fname : '';                              $icNo       =   ($info)?    $info->ic_number : '';
//    $bName      =   ($store)?   $store->business_name : '';                     $sName      =   ($store)?   $store->store_name : '';
//    $licence    =   ($store)?   $store->licence : '';                           $address    =   ($store)?   $store->address : '';
//    $latitude   =   ($store)?   $store->latitude : '';                          $longitude  =   ($store)?   $store->longitude : '';
//    $country    =   ($store)?   $store->country_id : '';                        $state      =   ($store)?   $store->state_id : '';
//    $city       =   ($store)?   $store->city_id : '';                           $zip        =   ($store)?   $store->zip_code : '';
//    $comi       =   ($store)?   $store->commission : '';                        $icName     =   ($store)?   $store->incharge_name : '';
//    $icPhone    =   ($store)?   $store->incharge_phone : '';                    $shipMtd    =   ($store)?   $store->ship_method : '';
//    $packOption =   ($store)?   $store->pack_option : '';                       $pickOption =   ($store)?   $store->pickup_option : '';
//    $isPikChrg  =   ($store)?   $store->is_pickup_chrge : 0;                    $pikChrg    =   ($store)?   $store->pickup_chrge : 0;
//    $discount   =   ($store)?   $store->discount : 0;                           $limitType  =   ($store)?   $store->limit_type : '';
//    $purLimit   =   ($store)?   $store->purchase_limit : '';                    $trackLink  =   ($store)?   $store->tracking_link : '';
//    $active     =   ($store)?   $store->is_active : 1;                          $storeId    =   ($store)?   $store->id : 0;
//    $logo       =   ($store)?   $store->logo : NULL;                            $banner     =   ($store)?   $store->banner : NULL;
//    $packOption =   ($store)?   $store->pack_option : 0;
//    $catIds     =   $assSlotIds = $assSpotIds = [];
//    if($storeId     >   0){ foreach($assCategories as $strCat){    $catIds[]       =   $strCat->category_id; } }
//    if($assSlots    &&  count($assSlots) > 0){  foreach($assSlots as $row){ $assSlotIds[]   =   $row->slot_id;  }   }
//    if($assSpots    &&  count($assSpots) > 0){  foreach($assSpots as $row){ $assSpotIds[]   =   $row->slot_id;  }   }
//    if($packOption  >   0){ $packChargeY = true; $packChargeN = false; }else{ $packChargeY  =   false; $packChargeN = true; }   
//    if($logo        !=  NULL && $logo   != ''){ $logoImg    = url('storage'.$logo); }else{ $logoImg = url('storage/app/public/no-avatar.png'); }
//    if($banner      !=  NULL && $banner != ''){ $bannerImg  = url('storage'.$banner); }else{ $bannerImg = url('storage/app/public/no-banner.png'); }
?>  <?php // echo '<pre>'; print_r($catIds); echo '</pre>'; ?>
<div class="page-header">
    <div class="page-leftheader">
        <h4 class="page-title mb-0">{{$title}}</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="fe fe-grid mr-2 fs-14"></i>Seller Management</a></li>
            <li class="breadcrumb-item"><a href="#" id="bc_list"><i class="fe fe-grid mr-2 fs-14"></i>Product List</a></li>
            <li class="breadcrumb-item active" aria-current="page"><a href="#">{{$title}}</a></li>
        </ol>
    </div>
</div>
<div class="col-lg-12 col-md-12">
    <div class="card">
        <div class="card-body pb-2">
            {{ Form::open(array('url' => "admin/seller/product/save", 'id' => 'adminForm', 'name' => 'adminForm', 'class' => '','files'=>'true')) }}
                 {{Form::hidden('id',0,['id'=>'id'])}} 

                <div class="tabs-menu mb-4">
                    <ul class="nav panel-tabs">
                        <li><a href="#tab1" data-toggle="tab" id="nav_tab_1" class="active"><span>General Info.</span></a></li>
                        <li><a href="#tab2" data-toggle="tab" id="nav_tab_2"><span>Price & Tax</span></a></li>
                        <li><a href="#tab3" data-toggle="tab" id="nav_tab_3"><span>Images</span></a></li>
                        <li><a href="#tab4" data-toggle="tab" id="nav_tab_4"><span>Attributes</span></a></li><!--
                        <li><a href="#tab5" data-toggle="tab" id="nav_tab_5"><span>Bank Details</span></a></li>-->
                   </ul>
                </div>
                <div class="row panel-body tabs-menu-body">
                    <div class="tab-content col-12">
                        @include('admin.seller_product.details.general')
                        @include('admin.seller_product.details.price_tax')
                        @include('admin.seller_product.details.image')
                        @include('admin.seller_product.details.attribute')
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card-footer text-right">
                        {{Form::hidden('can_submit',0,['id'=>'can_submit'])}}
                        <button id="cancel_btn" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button id="save_btn" type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
           {{Form::close()}}
        </div>
    </div>
</div>
<!-- INTERNAL WYSIWYG Editor js -->
<script src="{{URL::asset('admin/assets/js/form-editor.js')}}"></script>
		

