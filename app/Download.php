<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    const UPDATED_AT = null;
    protected $guarded = ['id'];
    protected $casts = ['user_id' => 'integer'];

    /**
     * @return BelongsTo
     */
    public function product()
    {
    	return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
