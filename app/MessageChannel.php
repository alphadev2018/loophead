<?php

namespace App;

use Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\MessageChannel
 *
 * @property int $id
 * @property string $channel_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin Eloquent
 */
class MessageChannel extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['model_type'];

    protected $casts = [
        'id' => 'integer'
    ];

    /**
     * @return MorphToMany
     */
    public function users()
    {
        return $this->morphedByMany(User::class, 'message_channelable');
    }

    /**
     * @return MorphToMany
     */
    public function channels()
    {
        return $this->morphedByMany(MessageChannel::class, 'message_channelable');
    }

    /**
     * @return string
     */
    public function getModelTypeAttribute()
    {
        return User::class;
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
