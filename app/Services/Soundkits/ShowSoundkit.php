<?php

namespace App\Services\Soundkits;

use App\Soundkit;
use App\Services\Artists\ArtistSaver;
use App\Services\Providers\ProviderResolver;
use App\Services\Providers\Spotify\SpotifyTrackSaver;
use Arr;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShowSoundkit
{
    /**
     * @var ProviderResolver
     */
    private $resolver;

    /**
     * @var ArtistSaver
     */
    private $saver;

    /**
     * @param ProviderResolver $resolver
     * @param ArtistSaver $saver
     */
    public function __construct(ProviderResolver $resolver, ArtistSaver $saver)
    {
        $this->resolver = $resolver;
        $this->saver = $saver;
    }

    /**
     * @param Soundkit $soundkit
     * @param array $params
     * @return Soundkit
     */
    public function execute(Soundkit $soundkit, $params)
    {
        $simplified = filter_var(Arr::get($params, 'simplified'), FILTER_VALIDATE_BOOLEAN);
        if ($soundkit->needsUpdating() && !$simplified) {
            $this->updateSoundkit($soundkit);
        }

        $soundkit->load(['artist', 'loops' => function(HasMany $builder) {
            return $builder->with('artists')->withCount('plays');
        }, 'tags', 'genres']);

        // need to load loops here so morphed relation works properly
        $soundkit->loops->load('artists');
        return $soundkit;
    }

    /**
     * @param Soundkit $album
     */
    private function updateSoundkit(Soundkit $album)
    {
        $spotifySoundkit = $this->resolver->get('album')->getSoundkit($album);
        if ( ! $spotifySoundkit) return;

        // if album artist is not in database yet, fetch and save it
        // fetching artist will get all his albums as well
        if ($spotifySoundkit['artist'] && ! $album->artist) {
            $artist = $this->resolver->get('artist')->getArtist($spotifySoundkit['artist']['spotify_id']);
            if ($artist) $this->saver->save($artist);
        } else {
            $this->saver->saveSoundkits(collect([$spotifySoundkit]), $album->artist);
            app(SpotifyTrackSaver::class)->save(collect([$spotifySoundkit]), collect([$album]));
        }
    }
}
