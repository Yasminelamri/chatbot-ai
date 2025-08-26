<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender', 'content', 'image_path', 'audio_path', 'conversation_id'];
}
