<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoopDownload extends Model
{
    const UPDATED_AT = null;
    protected $guarded = ['id'];
    protected $casts = ['user_id' => 'integer', 'loop_id' => 'integer'];
}
