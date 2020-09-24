<?php

namespace App;

use Common\Tags\Tag as BaseTag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends BaseTag
{
    /**
     * @return MorphToMany
     */
    public function tracks()
    {
        return $this->morphedByMany(Track::class, 'taggable');
    }

    /**
     * @return MorphToMany
     */
    public function albums()
    {
        return $this->morphedByMany(Album::class, 'taggable');
    }

    /**
     * @return MorphToMany
     */
    public function loops()
    {
        return $this->morphedByMany(Loop::class, 'taggable');
    }

    /**
     * @return MorphToMany
     */
    public function soundkits()
    {
        return $this->morphedByMany(Soundkit::class, 'taggable');
    }
}
