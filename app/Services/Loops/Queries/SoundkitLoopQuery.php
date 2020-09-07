<?php

namespace App\Services\Loops\Queries;

use App\Soundkit;
use App\Services\Soundkits\ShowSoundkit;

class SoundkitLoopQuery extends BaseLoopQuery
{
    const ORDER_COL = 'number';
    const ORDER_DIR = 'asc';

    public function get($albumId)
    {
        // fetch album tracks from spotify, if not fetched already
        app(ShowSoundkit::class)
            ->execute(app(Soundkit::class)->find($albumId), []);

        return $this->baseQuery()
            ->where('tracks.album_id', $albumId);
    }
}
