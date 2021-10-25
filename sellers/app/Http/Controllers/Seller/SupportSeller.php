<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Modules;
use App\Models\UserRoles;
use App\Models\Admin;
use App\Models\CustomerInfo;
use App\Models\SellerInfo;
use App\Models\SupportSellerChat;
use App\Models\SupportSellerMessage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;

class SupportSeller extends Controller
{
     public function __construct()
    {
        $this->middleware('auth:seller');
    }

    public function list(Request $request)
    {
        $post                       =   (object)$request->post();
        if(isset($post->viewType))  {   $viewType = $post->viewType; }else{ $viewType = ''; }
        if($viewType=='ajax')
        {
        $ticket_id = "TKTS".date('YmdHis');
        $data          =   ['subject'=>$post->s_subject,
                            'ticket_id'=>$ticket_id,
                            'created_by'=>auth()->user()->id,
                            'role_id' =>3,
                            'created_at'=>date("Y-m-d H:i:s"),
                            'updated_at'=>date("Y-m-d H:i:s")];
        $create_ticket = SupportSellerChat::create($data)->id; 
        if($create_ticket)
        {
            $message = ['support_id'=>$create_ticket,
                        'msg_type'=>'text',
                        'message'=>$post->s_msg,
                        'sender_id'=>auth()->user()->id,
                        'sender_role_id'=>3,
                        'created_at'=>date("Y-m-d H:i:s"),
                        'updated_at'=>date("Y-m-d H:i:s")];
            $create_msg = SupportSellerMessage::create($message)->id;
            
            $from    = auth()->user()->id; 
            $utype   =   2;
            $to      =  1; 
            $ntype   = 'support';
            $title   = 'New Support';
            $desc    = 'New support created by '.sellerData()->fname;
            $refId   = $create_ticket;
            $reflink = 'support';
            $notify  = 'admin';
            addNotification($from,$utype,$to,$ntype,$title,$desc,$refId,$reflink,$notify);
         }

        }    
        $data['title']              =   'Admin support';
        $data['menu']               =   'Admin support';
        $support = SupportSellerChat::where('created_by',auth()->user()->id)->orderBy('id','DESC')->get();
        if(count($support)>0)
        {
        foreach($support as $row)
        {
            $list['support_id'] = $row->id;
            $list['ticket_id']  = $row->ticket_id;
            $list['subject']    = $row->subject;
            $list['created_at'] = date('d M Y,H:i:s',strtotime($row->created_at));
            $support_list[]     = $list;

        }
       }
       else
       {
        $support_list=[];
       }
        $data['list'] = $support_list;
        if($viewType=='ajax')
        {
          return view('seller.support_seller.header',$data);
        }
        else
        {
            return view('seller.support_seller.page',$data);
        }
    }

    public function create(Request $request,$id='',$type='')
    {
        if($type=='view')
        {
            $support = SupportSellerChat::where('id',$id)->first();
            $date =  date('d-m-Y');
            $subject = $support->subject;
            $support_id= $support->id;
            $html ='';
            $html.='<form id="form_submit" method="POST" role="form" enctype="multipart/form-data">'.csrf_field().'<div class="form-group"><input type="hidden" value="'.$support_id.'" name="support_id" id="support_id" class="support_id"><input type="hidden" value="'.$support->created_by.'" name="cust_id" id="cust_id" class="cust_id"><label class="form-control"><b>Date</b></label><input type="text" readonly class="form-control" value="'.$date.'"></div>
                <div class="form-group"><label class="form-control"><b>Subject</b></label><input type="text" readonly class="form-control" value="'.$subject.'"></div>';
            $html.='<div class="form-group"><label class="form-control"><b>Reply</b></label><div id="view_img" class="view_img"></div><textarea id="textareas" name="msg" id="msg" row="2" class="form-control textareas" required></textarea></div>';
            $html.='<div class="row"><div class="col-12"><label class="mr-3">
                                                <span class="btn btn-primary">
                                                    <i class="fe fe-paperclip fs-20"></i> <input type="file" style="display: none;" accept="image/png, image/jpeg" class="img_up" id="img_up" onchange="imgchange(this)">
                                                </span>
                                            </label><button type="button" onclick = "submitThisForm('.$support_id.')" class="sendbtn btn btn-primary" id="submit">Send</button></div></div></form>';   

             return $html;   

        }

        if($type=="create")
        {   
            $support_id = $request->support_id;
            SupportSellerChat::where('id',$support_id)->update([
            'updated_at'=>date("Y-m-d H:i:s")
            ]);
            $image = $request->file('file_data');
            
            if($image)
            {
                $msg_type='image';
                $imgName            =   time().'.'.$image->extension();
                $path               =   '/app/public/support_seller/'.$support_id;
                $destinationPath    =   storage_path($path.'/thumb');
                $img                =   Image::make($image->path());  //echo storage_path().'  '. $destinationPath; die;
                if(!file_exists($destinationPath)) { mkdir($destinationPath, 755, true);}
                $img->resize(250, 250, function($constraint){ $constraint->aspectRatio(); })->save($destinationPath.'/'.$imgName);
                $destinationPath    =   storage_path($path);
                $image->move($destinationPath, $imgName);
                $imgUpload          =   uploadFile('/'.$path,$imgName);

                $image_chat =$path.'/'.$imgName;
            //     $file=$request->file('file_data');
            // $extention=$file->getClientOriginalExtension();
            // $filename=time().'.'.$extention;
            // $file->move(('storage/app/public/support_seller/'),$filename);
            // $image_chat ='storage/app/public/support_seller/'.$filename;
            }
            else
            {
                $image_chat = '';
                $msg_type='text';
            }
            $data = ['support_id' => $support_id,
                     'msg_type'   => $msg_type,
                     'message'    => $request->msg,
                     'sender_id'  => auth()->user()->id,
                     'receiver_id'=>$request->cust_id,
                     'image'      =>$image_chat,
                     'sender_type'=>'seller',
                     'sender_role_id'=>3,
                     'show_role_id'=>2,
                     'created_at'=>date("Y-m-d H:i:s"),
                     'updated_at'=>date("Y-m-d H:i:s")
                    ];
            $create = SupportSellerMessage::create($data);
            $from = auth()->user()->id; 
            $utype = 2;
            $to = 1;
            $ntype= 'support_replay';
            $title = 'Support Replay';
            $desc = 'New support message from '.sellerData()->fname;
            $refId = $support_id;
            $reflink = 'support';
            $notify = 'admin';
            addNotification($from,$utype,$to,$ntype,$title,$desc,$refId,$reflink,$notify);
            
             return response()->json(['success'=>'success']);
        }
    }

    public function chat(Request $request,$id='',$type='')
    {
        if($type=='chat')
        {
            $support = SupportSellerChat::where('id',$id)->first();
            $date =  date('d-m-Y');
            $subject = $support->subject;
            $support_id= $support->id;
            $html ='';
            $html.='<div class="action-header">
            <div class="float-left hidden-xs d-flex ml-2">
                                <div class="align-items-center mt-2 text-white">
                                    <h5 class="mb-0">'.$support->ticket_id.'</h5>
                                    <span class="mr-3 fs-12">Subject: '.$subject.'</span>
                                </div>
                            </div><ul class="ah-actions actions align-items-center">
                                <li>
                                    <a href=""  class="" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true"><i class="fe fe-x fs-20"></i></span>
                                    </a>
                                </li>
                            </ul>
                        </div>';
            $html.='<div class="card-body msg_card_body">
                            <div class="chat-box-single-line">
                                <abbr class="timestamp">Registered On: '.date('d M Y,H:i:s',strtotime($support->created_at)).'</abbr>
                            </div>';

             //card body statrted   
           $support_message = SupportSellerMessage::where('is_deleted',0)->where('support_id',$support_id)->get();
           foreach($support_message as $row)
           {  
             $row_date  = date('d-m-Y',strtotime($row->created_at));
             $diff_date = Carbon::parse($row->created_at)->diffForHumans();  
             if($row->sender_role_id==2)
             {        
               $admin = Admin::where('id',$row->sender_id)->first(); 
               if($admin->avatar)
               {
                $image=config('app.storage_url').$admin->avatar;
               } 
               else
               {
                $image=url('/public/admin/assets/images/users/2.jpg');
               }
               
               if($row->msg_type=='text')
               {
            $html.='<div class="d-flex justify-content-start">
                                <div class="img_cont_msg">
                                    <img src="'.$image.'" class="rounded-circle user_img_msg" alt="img">
                                </div>
                                <div class="msg_cotainer mb-2">'.$row->message.'<span class="msg_time" style="bottom:-31px;"><br>'.$diff_date.','.$row_date.'</span>
                                </div>
                            </div>';  
               }
               else
               {
                   $msg_image= config('app.storage_url').$row->image;
                   $html.='<div class="d-flex justify-content-start">
                                <div class="img_cont_msg">
                                    <img src="'.$image.'" class="rounded-circle user_img_msg" alt="img">
                                </div>
                                <div class="msg_cotainer mb-2">'.$row->message.'<div class="row mt-2"><div class="col-12"><img class="img-fluid rounded" src="'.$msg_image.'" alt="image"></div></div><span class="msg_time" style="bottom:-31px;"><br>'.$diff_date.','.$row_date.'</span>
                                </div>
                            </div>';
               }
              }
              else
              {
                $seller = SellerInfo::where('seller_id',$row->sender_id)->first();
                
               if($seller->store($seller->id)->logo == NULL)
               {
               $image=url('/public/admin/assets/images/users/2.jpg');
               } 
               else
               {
                 $image=url('storage'.$seller->store($seller->id)->logo);
               }
               
               if($row->msg_type=='text')
               {
             $html.='<div class="d-flex justify-content-end ">
                                <div class="msg_cotainer_send mb-2">'.$row->message.'<span class="msg_time_send" >'.$diff_date.','.$row_date.'</span>
                                </div>
                                <div class="img_cont_msg">
                                    <img src="'.$image.'" class="rounded-circle user_img_msg" alt="img">
                                </div>
                            </div>'; 
               }
               else
               {
                   $msg_image= config('app.storage_url').$row->image;
                   $html.='<div class="d-flex justify-content-end ">
                                <div class="msg_cotainer_send mb-2">'.$row->message.'<div class="row mt-2"><div class="col-12"><img class="img-fluid rounded" src="'.$msg_image.'" alt="image"></div></div><span class="msg_time_send" >'.$diff_date.','.$row_date.'</span>
                                </div>
                                <div class="img_cont_msg">
                                    <img src="'.$image.'" class="rounded-circle user_img_msg" alt="img">
                                </div>
                            </div>'; 
               }
              }

              
            } 
            $html.='</div></div>';
             return $html;   

        }

      
    }
}
