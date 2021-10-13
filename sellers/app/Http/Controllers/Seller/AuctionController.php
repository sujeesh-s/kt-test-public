<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Session;
use DB;
use App\Models\Auction;
use App\Models\AuctionHist;
use App\Models\Category;
use App\Models\Store;
use App\Models\Product;
use App\Models\Admin;
use App\Models\SaleOrder;

use App\Rules\Name;
use Validator;

class AuctionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:seller');
    }
   
    
    // user roles and modules
    
    public function auctions()
        { 
        $data['title']              =   'Auctions';
        $data['menu']               =   'auctions';
        $data['auctions']              =   Auction::getAuctions(auth()->user()->id);
        // dd($data);
        return view('seller.auctions.list',$data);
        }



          public function logAuction($acn_id)
        { 
        $data['title']              =   'Auction Logs';
        $data['menu']               =   'auction-log';
        $data['auctions']              =   Auction::getAuctionData($acn_id);
        $data['log']                = AuctionHist::getLog($acn_id);
        $data['acn_id']             = $acn_id;
    
        // dd($data);
 
         return view('seller.auctions.logs',$data);
        }

                public function auctionCreate()
        { 
        $data['title']              =   'Create Auction';
        $data['menu']               =   'create-auction';
        $data['language']      =    DB::table('glo_lang_lk')->where('is_active', 1)->get();
        $data['categories']      =   Category::where('is_active',1)->where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})->get();
        $data['sellers']      =    Store::where('is_active',1)->where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})->get();
        // dd($data);
        return view('seller.auctions.create',$data);
        }

         public function editAuction($acn_id)
        { 
        $data['title']              =   'Edit Auction';
        $data['menu']               =   'edit-auction';
        $data['auction']              =  Auction::getAuctionData($acn_id);
        $data['language']      =    DB::table('glo_lang_lk')->where('is_active', 1)->get();
        $data['categories']      =   Category::where('is_active',1)->where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})->get();
     
        // dd($data);
        return view('seller.auctions.edit',$data);
        }



        public function subcatedata(Request $request)
    {
  
         $input = $request->all();
         $cateid = $input['category_id'];
        if(isset($input['selectid'])) { $selectid = $input['selectid']; }else {  $selectid = '';}
            $sub_data=array();
           
              $squery    =  DB::table('subcategory')->where('category_id', $cateid)->where('parent', 0)->where('is_active', 1)->where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})->get();

            
              if($squery->count()> 0)
                {

                  //$sub_data[]=array();
                  foreach($squery as $srow)
                  { 

                    if($srow->subcategory_id != $selectid)
                    {
                        $kk=array();
                        $kk['id'] = $srow->subcategory_id;
                        $kk['title'] = ucfirst($srow->subcategory_name);
                        $tt=$this->subtree($cateid,$srow->subcategory_id,$selectid);
                        if($tt)
                        {
                          if($selectid=='product')
                          {
                            $kk['isSelectable']=false;
                          }
                          $kk['subs']=$tt;
                        }
                        $sub_data[]=$kk;
                    }
                  }
                }
            
          $result=array('val'=>'1','subdata'=>$sub_data);  
          return json_encode($result);
    }

    function subtree($cateid,$subid,$selectid='')
    {
      $jj=array();
      $squery2    =   DB::table('subcategory')->where('category_id', $cateid)->where('parent', $subid)->where('is_active', 1)->where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})->get();


    
      if($squery2->count() > 0)
      {
        foreach($squery2 as $srow)
        { 
          if($srow->subcategory_id != $selectid)
            {  
                $kk=array();
                $kk['id'] = $srow->subcategory_id;
                $kk['title'] = ucfirst($srow->subcategory_name);
                $tt=$this->subtree($cateid,$srow->subcategory_id,$selectid);
                if($tt)
                {
                  if($selectid=='product')
                  {
                    $kk['isSelectable']=false;
                  }
                  $kk['subs']=$tt;
                }
                $jj[]=$kk;
            }
            
        }
      }
      return $jj;
    }


     public function productdata(Request $request)
    {
  
         $input = $request->all();
         $cateid = $input['category_id'];
         $subcateid = $input['subcategory_id'];
        if(isset($input['selectid'])) { $selectid = $input['selectid']; }else {  $selectid = '';}
            $prod_data=array();
           
              $squery    =  Product::where('seller_id', auth()->user()->id)->where('visible',1)->where('is_approved',1)->where('category_id', $cateid)->where('sub_category_id', $subcateid)->where('is_active', 1)->where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})->get();

            
              if($squery->count()> 0)
                {

                  //$sub_data[]=array();
                  foreach($squery as $srow)
                  { 

                    if($srow->id != $selectid)
                    {
                        $kk=array();
                        $kk['id'] = $srow->id;
                        $kk['title'] = ucfirst($srow->name);

                        $prod_data[]=$kk;
                    }
                  }
                }
            
          $result=array('val'=>'1','subdata'=>$prod_data);  
          return json_encode($result);
    }

public function auctionSave(Request $request)
        { 
        $input = $request->all();
      
        // dd($input);

        if($input['id']>0){

     
      $validator= $request->validate([
      'cat_id'   =>  ['required'],
      'subcat_id' => ['required'],
      'product_id' => ['required'],
      'auction_desc' => ['required'],
      'auct_start' => ['required'],
      'auct_end' => ['required'],
      'min_bid_price' => ['required']

      ], [], 
      [
      'cat_id' => 'Category',
      'subcat_id' => 'Subcategory',
      'product_id' => 'Product',
      'auction_desc' => 'Description',
      'auct_start' => 'Start Date',
      'auct_end' => 'End Date',
      'min_bid_price' => 'Min Bid Price',
      ]);

        if (DB::table('cms_content')->where('cnt_id',$input['auction_desc_cid'])->where('lang_id',$input['glo_lang_cid'])->exists()) {
        DB::table('cms_content')->where('cnt_id',$input['auction_desc_cid'])->where('lang_id',$input['glo_lang_cid'])
        ->update(['content' => $input['auction_desc']]);
        $acn_desc_cid=$input['auction_desc_cid'];
        } else {

        $latest = DB::table('cms_content')->orderBy('cnt_id', 'DESC')->first();
        $acn_desc_cid=++$latest->cnt_id;
        DB::table('cms_content')->insertGetId([
        'org_id' => 1, 
        'lang_id' => $input['glo_lang_cid'],
        'cnt_id'=>$acn_desc_cid,
        'content' => $input['auction_desc'],
        'is_active'=>1,
        'created_by'=>auth()->user()->id,
        'updated_by'=>auth()->user()->id,
        'is_deleted'=>0,
        'created_at'=>date("Y-m-d H:i:s"),
        'updated_at'=>date("Y-m-d H:i:s")
        ]);
     

        }

    
        $acn_id = $input['id'];

        if($acn_desc_cid !="" && $acn_id !="") {

        $auction_code = Auction::where('id',$acn_id)->first()->auction_code;
        $coupon =  Auction::where('id',$acn_id)->update([
        'org_id' => 1, 
        'auction_code' => $auction_code,
        'seller_id' => auth()->user()->id,
        'auction_desc_cid' => $acn_desc_cid,
        'cat_id'=>$input['cat_id'],
        'subcat_id'=>$input['subcat_id'],
        'product_id'=>$input['product_id'],
        'min_bid_price'=>$input['min_bid_price'],
        'auct_start'=>$input['auct_start'],
        'auct_end'=>$input['auct_end'],
        'is_active'=>$input['is_active'],
        'is_deleted'=>0,
        'created_by'=>auth()->user()->id,
        'updated_by'=>auth()->user()->id,
        'created_at'=>date("Y-m-d H:i:s"),
        'updated_at'=>date("Y-m-d H:i:s")

        ]); 
        Session::flash('message', ['text'=>'Coupon updated successfully','type'=>'success']); 
        }else {
        Session::flash('message', ['text'=>'Coupon updation failed','type'=>'danger']);
        }







        }else{

     $validator= $request->validate([
        'cat_id'   =>  ['required'],
        'subcat_id' => ['required'],
        'product_id' => ['required'],
        'auction_desc' => ['required'],
        'auct_start' => ['required'],
        'auct_end' => ['required'],
        'min_bid_price' => ['required']

        ], [], 
        [
        'cat_id' => 'Category',
        'subcat_id' => 'Subcategory',
        'product_id' => 'Product',
         'auction_desc' => 'Description',
          'auct_start' => 'Start Date',
           'auct_end' => 'End Date',
            'min_bid_price' => 'Min Bid Price',
        ]);


  // dd($input);

        $latest = DB::table('cms_content')->orderBy('id', 'DESC')->first();
        $acn_desc_cid=++$latest->cnt_id;

        $acn_desc= DB::table('cms_content')->insertGetId([
        'org_id' => 1, 
        'lang_id' => $input['glo_lang_cid'],
        'cnt_id'=>$acn_desc_cid,
        'content' => $input['auction_desc'],
        'is_active'=>1,
        'created_by'=>auth()->user()->id,
        'updated_by'=>auth()->user()->id,
        'is_deleted'=>0,
        'created_at'=>date("Y-m-d H:i:s"),
        'updated_at'=>date("Y-m-d H:i:s")
        ]);


        if($acn_desc !="") {
          $latest_id = Auction::max('id');$latest_id++;

          $auction_code = date('Ym', time()).$latest_id;

        $auction =  Auction::create([
        'org_id' => 1, 
        'auction_code' => $auction_code,
        'seller_id' => auth()->user()->id,
        'auction_desc_cid' => $acn_desc_cid,
        'cat_id'=>$input['cat_id'],
        'subcat_id'=>$input['subcat_id'],
        'product_id'=>$input['product_id'],
        'min_bid_price'=>$input['min_bid_price'],
        'auct_start'=>$input['auct_start'],
        'auct_end'=>$input['auct_end'],
        'is_active'=>$input['is_active'],
        'is_deleted'=>0,
        'created_by'=>auth()->user()->id,
        'updated_by'=>auth()->user()->id,
        'created_at'=>date("Y-m-d H:i:s"),
        'updated_at'=>date("Y-m-d H:i:s")

        ]);   
        $lastId = $auction->id;
        if($lastId) {
        Session::flash('message', ['text'=>'Auction created successfully','type'=>'success']);  
        }else {
        Session::flash('message', ['text'=>'Auction creation failed','type'=>'danger']);
        }
        }else {
        Session::flash('message', ['text'=>'Auction creation failed','type'=>'danger']);
        }

        }
        $data['title']              =   'Auctions';
        $data['menu']               =   'auctions';
        $data['auctions']              =   Auction::getAuctions(auth()->user()->id);
        return redirect(route('seller.auctions'));

        }
 

           public function auctionStatus(Request $request)
        {
        $input = $request->all();
        
        if($input['id']>0) {
        $deleted =  Auction::where('id',$input['id'])->update(array('is_active'=>$input['status']));
        
        return '1';
        }else {
        
        return '0';
        }
        
        }


        public function auctionDelete(Request $request)
        {
        $input = $request->all();
        
        if($input['id']>0) {
        $deleted =  Auction::where('id',$input['id'])->update(array('is_deleted'=>1,'is_active'=>0));
        Session::flash('message', ['text'=>'Auction deleted successfully.','type'=>'success']);
        return true;
        }else {
        Session::flash('message', ['text'=>'Auction failed to delete.','type'=>'danger']);
        return false;
        }

        }
               public  function wordlimit($str, $limit=100, $strip = false) {
            $str = ($strip == true)?strip_tags($str):$str;
            if (strlen ($str) > $limit) {
                $str = substr ($str, 0, $limit - 3);
                return (substr ($str, 0, strrpos ($str, ' ')).'...');
            }
            return trim($str);
        }

             public function auctionfilter(Request $request)
        {
            $input = $request->all();
            $startdate = $input['startdate'];
            $enddate = $input['enddate'];
            $auctions = Auction::getAuctions(auth()->user()->id);


            if($auctions && count($auctions) > 0) {
            $acn_html = "";
            foreach($auctions as $row) {

                        $valid_till = date('Y-m-d', strtotime($row['auct_end']));
                        $valid_from = date('Y-m-d', strtotime($row['auct_start']));
                        $prod_img=url('storage/app/public/product/'.$row['product_img']);
                        if($row['is_active'] ==1){ $actv= "Active"; $chkd= "checked"; }else{ $actv="Inactive"; $chkd= ""; }
                     
                          if($startdate !="" && $enddate !="") {

                            // if((strtotime($startdate) <= strtotime($valid_from) || strtotime($startdate) <= strtotime($valid_till)) && (strtotime($enddate) <= strtotime($valid_till) || strtotime($enddate) >= strtotime($valid_from)) )

                               if((strtotime($startdate) <= strtotime($valid_from) || strtotime($startdate) <= strtotime($valid_till)) && (strtotime($enddate) >= strtotime($valid_from) ) ) {

                                
                            $acn_html .='<tr>
                                  <td class="align-middle select-checkbox" id="moduleid" data-value="'.$row['id'].'">
                                    <label class="custom-control custom-checkbox">

                                    </label>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                    <p>'.$row['auction_code'].'</p>
                                  </div>
                                  </td>

                                  <td class="align-middle" >
                                      
                                      <div class="d-flex">';

                                      if($row['product_img']) {
                               $acn_html .='<span class="avatar brround avatar-md d-block" style="background-image: url('.$prod_img.')"></span>';
                                      }else {
                                  $acn_html .='<span class="avatar brround avatar-md d-block" ></span>';
                                      }
                                  
                              
                                     $acn_html .=' <div class="ml-3 mt-1">
                                        <h6 class="mb-0 font-weight-bold">'.$row['product_name'].'</h6>
                                      </div>
                                    </div>
                                  
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                    <p>'.$this->wordlimit($row['auct_desc'], 0, 20).'</p>
                                  </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.date('Y-m-d', strtotime($row['auct_start'])).'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.date('Y-m-d', strtotime($row['auct_end'])).'</p>
                                    </div>
                                  </td>
                                  
                                  <td class="text-nowrap align-middle"  data-search="'.$actv.'">
                                    
                                  <div class="switch">
                                  <input class="switch-input status-btn ser_status" data-selid="'.$row['id'].'"  id="status-'.$row['id'].'"  type="checkbox"  '.$chkd.' >
                                  <label class="switch-paddle" for="status-'.$row['id'].'">
                                  <span class="switch-active" aria-hidden="true">Active</span>
                                  <span class="switch-inactive" aria-hidden="true">Inactive</span>
                                  </label>
                                  </div>                    
                  
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.$row['min_bid_price'].'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.$row['shipping_cost_id'].'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.count($row['bids']).'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.$row['bid_allocated_to_user'].'</p>
                                    </div>
                                  </td>
                                  

                                  <td class="align-middle">
                                    <div class="btn-group align-top">
                                      
                                      <a href="'.url('/auctions/log/').'/'.$row['id'].'"   class="mr-2 btn btn-info btn-sm editmodule"><i class="fe fe-edit mr-1"></i> View</a>
                                      
                                    </div>
                                  </td>
                                  <td class="align-middle">
                                    <div class="btn-group align-top">
                                      
                                      <a href="'. url('/auctions/edit/') .'/'.$row['id'].'"   class="mr-2 btn btn-info btn-sm editmodule"><i class="fe fe-edit mr-1"></i> Edit</a>
                                      <button  class="btn btn-secondary btn-sm deletemodule" onclick="deletecpn('.$row['id'].');" type="button"><i class="fe fe-trash-2  mr-1"></i>Delete</button>
                                    </div>
                                  </td> 
                                </tr>';

                               }
                          }else {

                           
                             $acn_html .='<tr>
                                  <td class="align-middle select-checkbox" id="moduleid" data-value="'.$row['id'].'">
                                    <label class="custom-control custom-checkbox">

                                    </label>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                    <p>'.$row['auction_code'].'</p>
                                  </div>
                                  </td>

                                  <td class="align-middle" >
                                      
                                      <div class="d-flex">';

                                      if($row['product_img']) {
                                  $acn_html .='<span class="avatar brround avatar-md d-block" style="background-image: url('.$prod_img.')"></span>';
                                      }else {
                                  $acn_html .='<span class="avatar brround avatar-md d-block" ></span>';
                                      }
                                  
                              
                                     $acn_html .=' <div class="ml-3 mt-1">
                                        <h6 class="mb-0 font-weight-bold">'.$row['product_name'].'</h6>
                                      </div>
                                    </div>
                                  
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                     <p>'.$this->wordlimit($row['auct_desc'], 0, 20).'</p>
                                  </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.date('Y-m-d', strtotime($row['auct_start'])).'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.date('Y-m-d', strtotime($row['auct_end'])).'</p>
                                    </div>
                                  </td>
                                  
                                  <td class="text-nowrap align-middle"  data-search="'.$actv.'">
                                    
                                  <div class="switch">
                                  <input class="switch-input status-btn ser_status" data-selid="'.$row['id'].'"  id="status-'.$row['id'].'"  type="checkbox"  '.$chkd.' >
                                  <label class="switch-paddle" for="status-'.$row['id'].'">
                                  <span class="switch-active" aria-hidden="true">Active</span>
                                  <span class="switch-inactive" aria-hidden="true">Inactive</span>
                                  </label>
                                  </div>                    
                  
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.$row['min_bid_price'].'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.$row['shipping_cost_id'].'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.count($row['bids']).'</p>
                                    </div>
                                  </td>
                                  <td class="align-middle" >
                                    <div class="d-flex">
                                      <p>'.$row['bid_allocated_to_user'].'</p>
                                    </div>
                                  </td>
                                  

                                  <td class="align-middle">
                                    <div class="btn-group align-top">
                                      
                                      <a href="'.url('/auctions/log/').'/'.$row['id'].'"   class="mr-2 btn btn-info btn-sm editmodule"><i class="fe fe-edit mr-1"></i> View</a>
                                      
                                    </div>
                                  </td>
                                  <td class="align-middle">
                                    <div class="btn-group align-top">
                                      
                                      <a href="'. url('/auctions/edit/') .'/'.$row['id'].'"   class="mr-2 btn btn-info btn-sm editmodule"><i class="fe fe-edit mr-1"></i> Edit</a>
                                      <button  class="btn btn-secondary btn-sm deletemodule" onclick="deletecpn('.$row['id'].');" type="button"><i class="fe fe-trash-2  mr-1"></i>Delete</button>
                                    </div>
                                  </td> 
                                </tr>';
                          }

                            
                             }
                           }else {
                            $acn_html = "0";
                           }


        return $acn_html;
        
        }
        
        public function auctionsCron()
        { 

        $start_date = date('Y-m-d 00:00:00');
        $end_date = date('Y-m-d 11:59:00');
        $auctions  =   Auction::where(function ($query) { $query->where('is_deleted', '=', NULL)->orWhere('is_deleted', '=', 0);})
        ->where("is_active",1)
        ->whereBetween('auct_end', [$start_date, $end_date])
        ->orderBy('id', 'DESC')
        ->get();

        if($auctions){ 
            foreach($auctions    as  $row){
              $bids =   AuctionHist::getLog($row->id); 
              $winner =   AuctionHist::where('auction_id',$row->id)->orderBy('bid_price', 'DESC')->first();
              foreach($bids as $bid_k=>$bid_v){
                if($bid_v['user_id'] !=$winner->user_id){
                  $sale_arr = array();
              $sale_arr['payment_status'] = "refund";
              $sale_arr['updated_at'] = date("Y-m-d H:i:s");
                  SaleOrder::where("id",$bid_v['sale_id'])->update($sale_arr);
                }
                
              }
              $auct_arr = array();
              $auct_arr['bid_allocated_to'] = $winner->user_id;
              $auct_arr['updated_at'] = date("Y-m-d H:i:s");
              Auction::where("id",$row->id)->update($auct_arr);
            }
          }
        
      
        }
    

   
}
