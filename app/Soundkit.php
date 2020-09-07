<?php namespace App;

use App\Services\Artists\NormalizesArtist;
use Common\Settings\Settings;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Soundkit
 *
 * @mixin Eloquent
 */
class Soundkit extends Model {
    use NormalizesArtist;

    /**
     * @var array
     */
    protected $casts = [
        'id'            => 'integer',
        'artist_id'     => 'integer',
        'free'          => 'boolean',
        'private'       => 'boolean',
    ];
    
    protected $guarded = ['id', 'views'];
    protected $hidden = ['temp_id'];
    protected $appends = ['model_type', 'created_at_relative'];

    /**
     * @return BelongsTo
     */
    public function artist()
    {
    	return $this->morphTo();
    }

    /**
     * @return MorphMany
     */
    public function reposts()
    {
        return $this->morphMany(Repost::class, 'repostable');
    }

    /**
     * @return BelongsToMany
     */
    public function likes()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function loops()
    {
    	return $this->hasMany(Loop::class, 'soundkit_id')->orderBy('number');
    }

    /**
     * @return HasManyThrough
     */
    public function plays()
    {
        return $this->hasManyThrough(TrackPlay::class, Track::class);
    }

    /**
     * @return MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')
            ->select('tags.name', 'tags.display_name', 'tags.id');
    }

    /**
     * @return MorphToMany
     */
    public function genres()
    {
        return $this->morphToMany(Genre::class, 'genreable')
            ->select('genres.name', 'genres.id');
    }

    public function setRelation($relation, $value)
    {
        if ($relation === 'artist' && $value) {
            $value = new MixedArtist($this->normalizeArtist($value));
        }
        parent::setRelation($relation, $value);
    }

    /**
     * @return bool
     */
    public function needsUpdating()
    {
        if ( ! $this->exists || $this->local_only || ! $this->auto_update) return false;
        if (app(Settings::class)->get('album_provider', 'local') === 'local') return false;

        if ( ! $this->fully_scraped) return true;
        if ( ! $this->tracks || $this->tracks->isEmpty()) return true;

        return false;
    }

    /**
     * @return string
     */
    public function getCreatedAtRelativeAttribute()
    {
        return $this->created_at ? $this->created_at->diffForHumans() : null;
    }

    /**
     * @return string
     */
    public function getModelTypeAttribute()
    {
        return Soundkit::class;
    }
}
