<?php

namespace App\Services\Soundkits;

use App\Soundkit;
use App\Loop;
use Illuminate\Support\Collection;

class DeleteSoundkits
{
    /**
     * @param array[]|Collection $albumIds
     */
    public function execute($albumIds)
    {
        app(Soundkit::class)->whereIn('id', $albumIds)->delete();

        $trackIds = app(Loop::class)->whereIn('soundkit_id', $albumIds)->pluck('id');
        app(Loop::class)->whereIn('id', $trackIds)->delete();

        // delete waves
        $paths = $trackIds->map(function($id) {
            return "waves/{$id}.json";
        });
        app(Loop::class)->getWaveStorageDisk()->delete($paths->toArray());
    }
}
