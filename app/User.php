<?php namespace App;

use App\Traits\DeterminesArtistType;
use App\Services\Messages\NormalizesChannel;
use Common\Auth\BaseUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Notifications\Notifiable;

/**
 * App\User
 *
 * @property-read Collection|Playlist[] $playlists
 * @property-read Collection|Track[] $uploadedTracks
 * @property-read UserProfile $profile
 */
class User extends BaseUser
{
    use Notifiable, DeterminesArtistType, NormalizesChannel;

    protected $appends = [
        'display_name',
        'has_password',
        'model_type',
    ];

    public function followedUsers()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->select(['users.id', 'first_name', 'last_name', 'avatar', 'email']);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->select(['users.id', 'first_name', 'last_name', 'avatar', 'email']);
    }

    /**
     * @return BelongsToMany
     */
    public function likedLoops()
    {
        return $this->morphedByMany(Loop::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function likedAlbums()
    {
        return $this->morphedByMany(Album::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function likedArtists()
    {
        return $this->morphedByMany($this->determineArtistType(), 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return MorphToMany
     */
    public function uploadedTracks()
    {
        return $this->morphToMany(Track::class, 'artist', 'artist_track')
            ->whereNull('album_id')
            ->withCount('likes')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return MorphToMany
     */
    public function uploadedLoops($private = false)
    {
        return $this->morphToMany(Loop::class, 'artist', 'artist_loop')
            ->where('private', $private)
            ->whereNull('soundkit_id')
            ->withCount('likes')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return MorphMany
     */
    public function albums()
    {
        return $this->morphMany(Album::class, 'artist')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return MorphMany
     */
    public function message_channels()
    {
        return $this->morphToMany(MessageChannel::class, 'message_channelable')
            ->with('messages');
    }

    /**
     * @return MorphMany
     */
    public function soundkit()
    {
        return $this->morphMany(Soundkit::class, 'artist')
            ->withCount('reposts')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return BelongsToMany
     */
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class)->withPivot('owner');
    }

    /**
     * @return HasMany
     */
    public function reposts()
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * @return HasOne
     */
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * @return HasMany
     */
    public function links()
    {
        return $this->hasMany(UserLink::class);
    }

    /**
     * @return string
     */
    public function getModelTypeAttribute()
    {
        return User::class;
    }

    public function setRelation($relation, $value)
    {
        if ($relation === 'message_channels') {
            $value = $value->map(function($model) {
               return $this->normalizeChannel($model);
            });
        }
        parent::setRelation($relation, $value);
    }
}
