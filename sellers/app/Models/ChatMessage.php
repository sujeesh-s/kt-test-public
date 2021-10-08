<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;
    protected $table = 'chat_messages';
    protected $fillable = ['chat_id', 'msg_type','message','other_msg','sender_id','sender_role_id','receiver_id','is_deleted','read_status','created_at','updated_at'];
}
