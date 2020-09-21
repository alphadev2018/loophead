<?php namespace App;

use App\Services\Artists\NormalizesArtist;
use App\Traits\DeterminesArtistType;
use App\Traits\OrdersByPopularity;
use Common\Comments\Comment;
use Common\Tags\Tag;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Filesystem\FilesystemAdapter;
use Storage;

/**
 * App\Loop
 *
 * @mixin Eloquent
 */
class Loop extends Model {
    use OrdersByPopularity, NormalizesArtist, DeterminesArtistType;

    /**
     * @var array
     */
    protected $guarded = [
        'id',
        'formatted_duration',
        'plays'
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'fully_scraped',
        'temp_id',
        'pivot'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'id'       => 'integer',
        'soundkit_id' => 'integer',
        'number'   => 'integer',
        'duration' => 'integer',
        'auto_update' => 'boolean',
        'position' => 'integer',
        'local_only' => 'boolean',
    ];

    protected $appends = ['model_type', 'created_at_relative'];

    /**
     * @return BelongsToMany
     */
    public function likes()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes')
            ->withTimestamps();
    }

    /**
     * @return MorphMany|Comment
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return MorphMany
     */
    public function reposts()
    {
        return $this->morphMany(Repost::class, 'repostable');
    }

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function soundkit()
    {
        return $this->belongsTo(Soundkit::class);
    }

    /**
     * @return BelongsToMany
     */
    public function artists()
    {
        return $this->morphedByMany(User::class, 'artist', 'artist_loop')
            ->select(['users.id', 'first_name', 'last_name', 'email', 'username', 'avatar']);
    }

    public function plays()
    {
        return $this->hasMany(LoopPlay::class);
    }

    public function downloads()
    {
        return $this->hasMany(LoopDownload::class);
    }

    public function setRelation($relation, $value)
    {
        if ($relation === 'artists') {
            $value = $value->map(function($model) {
               return $this->normalizeArtist($model);
            });
        }
        parent::setRelation($relation, $value);
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
            ->select('genres.name', 'genres.display_name', 'genres.id');
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
        return Loop::class;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getWaveStorageDisk()
    {
        return Storage::disk(config('common.site.wave_storage_disk'));
    }
}
