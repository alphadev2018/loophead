<?php

namespace App\Services\Loops\Queries;

use App\Artist;
use App\Services\Artists\ArtistsRepository;
use App\Traits\DeterminesArtistType;

class ArtistLoopQuery extends BaseLoopQuery
{
    use DeterminesArtistType;

    const ORDER_COL = 'spotify_popularity';

    public function get($artistId)
    {
        $artist = app(Artist::class)->find($artistId);
        $repo = app(ArtistsRepository::class);

        if ($artist && $repo->needsUpdating($artist)) {
            $repo->fetchAndStoreArtistFromExternal($artist);
        }

        return $this->baseQuery()
            ->join('artist_track', 'tracks.id', '=', 'artist_track.track_id')
            ->where('artist_track.artist_id', $artistId)
            ->where('artist_track.artist_type', $this->determineArtistType())
            ->select('tracks.*');
    }
}
