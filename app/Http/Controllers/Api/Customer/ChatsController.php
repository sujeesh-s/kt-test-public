<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Session;
use DB;
use App\Models\Admin;
use App\Models\CustomerInfo;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Modules;
use App\Models\Store;
use App\Models\SellerInfo;
use App\Models\UserRoles;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use App\Rules\Name;
use Validator;

class ChatsController extends Controller
{
    public function send_message(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $validator=  Validator::make($request->all(),[
            'seller_id'    => ['required','numeric'],
            'prd_id'       => ['nullable','numeric'],
            'message'      => ['required_without:image'],
            'image'        => ['required_without:message','image','mimes:jpeg,png,jpg'],

        ]);
        if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
        $input = $request->all();
        $chat = Chat::where('seller_id',$input['seller_id'])->where('created_by',$user_id)->first();
        $image = $request->file('image');
        if($image)
        {
                $msg_type='image';
                $imgName            =   time().'.'.$image->extension();
                $path               =   '/app/public/chat_customer';
                $destinationPath    =   storage_path($path.'/thumb');
                $img                =   Image::make($image->path());  //echo storage_path().'  '. $destinationPath; die;
                if(!file_exists($destinationPath)) { mkdir($destinationPath, 755, true);}
                $img->resize(250, 250, function($constraint){ $constraint->aspectRatio(); })->save($destinationPath.'/'.$imgName);
                $destinationPath    =   storage_path($path);
                $image->move($destinationPath, $imgName);
                $imgUpload          =   uploadFile('/'.$path,$imgName);

                $image_chat =$path.'/'.$imgName;
            
        }
            else{ $msg_type='text'; 
            $image_chat =''; }

        if($chat)
        {
            $update =ChatMessage::where('chat_id',$chat->id)->where('sender_role_id',3)->update(['read_status'=>1]);
           
            $message = ['chat_id'=>$chat->id,
                        'msg_type'=>$msg_type,
                        'message'=>$input['message'],
                        'other_msg'=>$image_chat,
                        'sender_id'=>$user_id,
                        'sender_role_id'=>5,
                        'receiver_id'=>$input['seller_id'],
                        'created_at'=>date("Y-m-d H:i:s"),
                        'updated_at'=>date("Y-m-d H:i:s")];
                        $create_msg = ChatMessage::create($message)->id;
                        
            return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['response'=>'success']];            
        }

        else
        {
            $data   = ['created_by'=>$user_id,
                      'prd_id'=>'',
                      'seller_id'=>$input['seller_id'],
                      'sender_role_id'=>5,
                      'created_at'=>date("Y-m-d H:i:s"),
                      'updated_at'=>date("Y-m-d H:i:s")];
        $create = Chat::create($data)->id; 
        if($create)
        {
            
            $message = ['chat_id'=>$create,
                        'msg_type'=>$msg_type,
                        'message'=>$input['message'],
                        'other_msg'=>$image_chat,
                        'sender_id'=>$user_id,
                        'sender_role_id'=>5,
                        'receiver_id'=>$input['seller_id'],
                        'created_at'=>date("Y-m-d H:i:s"),
                        'updated_at'=>date("Y-m-d H:i:s")];
            $create_msg = ChatMessage::create($message)->id;
            return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['response'=>'success']];
         }
         else
         {
            return ['httpcode'=>400,'status'=>'error','message'=>'Something went wrong','data'=>['errors'=>'something went wrong']];
         }//else if not insert
        }

    }

    }

    //List

    public function list(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];

        $chat = Chat::where('created_by',$user_id)->orderBy('id','DESC')->get();
        $msg_list = [];
        foreach($chat as $row)
        {   $store=Store::where('is_active',1)->where('is_deleted',0)->first();
            $msg_count  =ChatMessage::where('chat_id',$row->id)->where('read_status',0)->where('sender_id','!=',$user_id)->count();
            if($store)
            {
            $seller_name = $row->seller_info->fname."".$row->seller_info->mname.$row->seller_info->lname;
            $list['chat_id']    = $row->id;
            $list['unread_msg'] = $msg_count;
            $list['seller_id']  = $row->seller_id;
            $list['seller_name']= $seller_name;
            $list['store_name'] = $row->Store($row->seller_id)->store_name;
            $list['logo']       = url($store->logo);
            $msg_list[]         = $list;
            }
        }

        return ['httpcode'=>200,'status'=>'success','message'=>'success','data'=>['list'=>$msg_list]];
    }

    public function chat_message(Request $request)
    {
        if(!$user = validateToken($request->post('access_token'))){ return invalidToken(); }
        $user_id = $user['user_id'];
        $validator=  Validator::make($request->all(),[
            'chat_id'    => ['required_without:seller_id','nullable','numeric'],
            'seller_id'    => ['required_without:chat_id','nullable','numeric']

        ]);
        if ($validator->fails()) 
    {    
      return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>$validator->messages()]];
    }
    else
    {
        $input = $request->all();
        if($request->chat_id!=''){
        $chat= Chat::where('id',$input['chat_id'])->first();
        }
        else
        {
          $chat= Chat::where('seller_id',$input['seller_id'])->first();  
        }
        if($chat)
        {   
            $store=Store::where('is_active',1)->where('is_deleted',0)->first();
            $chat_msg  =ChatMessage::where('chat_id',$chat->id)->get();
            $seller_name = $chat->seller_info->fname."".$chat->seller_info->mname.$chat->seller_info->lname;
            $list['chat_id']    = $chat->id;
            $list['seller_id']  = $chat->seller_id;
            $list['seller_name']= $seller_name;
            $list['store_name'] = $chat->Store($chat->seller_id)->store_name;
            $list['logo']       = url($store->logo);
            $list['messages']   = $this->get_chat_msgs($chat->id,$user_id);
            
            $msg_list         = $list;

            return ['httpcode'=>200,'status'=>'success','message'=>'Chat','data'=>['messages'=>$msg_list]];
        }
        else
        {
            return ['httpcode'=>400,'status'=>'error','message'=>'Invalid parameters','data'=>['errors'=>'Invalid Chat ID']];
        }
    }

    }

    function get_chat_msgs($chat_id,$user_id)
    {
        $all = [];
        $chat_msgs= ChatMessage::where('chat_id',$chat_id)->get();
        foreach($chat_msgs as $row)
            {
                if($row->sender_id==$user_id)
                {
                    $from = "You"; $align ="right"; $me = 1;

                }
                else{
                    $from = "Seller"; $align ="left"; $me = 0;
                }
                if($row->msg_type       ==  'image')
                { $image = config('app.storage_url').$row->other_msg; }
                else if($row->msg_type       ==  'emoji')
                { $message = url('storage'.$row->other_msg); }
                else if($row->msg_type       ==  'video')
                { $message = url('storage'.$row->other_msg); }
                else{ $image = ''; }

                   $msgs['from']       = $from;
                   $msgs['me']         = $me;
                   $msgs['align']      = $align;
                   $msgs['message']    = $row->message;
                   $msgs['image']      = $image;
                   $msgs['created_at'] = date('Y-m-d H:i:s', strtotime($row->created_at));
                   $msgs['read_status']= $row->read_status;
                   $all[]              = $msgs;
            }

            return $all;
    }
}
