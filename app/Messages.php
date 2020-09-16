<?php

namespace App;

use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Messages
 *
 * @property int $id
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class Messages extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['model_type'];

    protected $casts = [
        'id' => 'integer',
        'from_id' => 'integer'
    ];

    /**
     * @return MorphToMany
     */
    public function from()
    {
        return $this->hasOne(User::class, 'id', 'from_id');
    }

    /**
     * @return MorphToMany
     */
    public function channel()
    {
        return $this->hasOne(MessageChannel::class, 'id', 'channel_id');
    }

    /**
     * @return string
     */
    public function getModelTypeAttribute()
    {
        return Messages::class;
    }

    /**
     * @return $this
     */
    // public function loadContent()
    // {
    //     $channelContent = app(LoadChannelContent::class)->execute($this);
    //     $this->setRelation('content', $channelContent);
    //     return $this;
    // }
}
