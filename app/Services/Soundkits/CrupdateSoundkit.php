<?php

namespace App\Services\Soundkits;

use App\Soundkit;
use App\Genre;
use App\Notifications\ArtistUploadedMedia;
use App\Services\Loops\CrupdateLoop;
use App\Loop;
use App\User;
use Common\Tags\Tag;
use DB;
use Illuminate\Support\Arr;
use Notification;

class CrupdateSoundkit
{
    /**
     * @var Album
     */
    private $soundkit;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var CrupdateLoop
     */
    private $createLoop;

    /**
     * @var Loop
     */
    private $track;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @param Soundkit $soundkit
     * @param CrupdateLoop $createLoop
     * @param Tag $tag
     * @param Loop $track
     * @param Genre $genre
     */
    public function __construct(Soundkit $soundkit, CrupdateLoop $createLoop, Tag $tag, Loop $track, Genre $genre)
    {
        $this->soundkit = $soundkit;
        $this->tag = $tag;
        $this->createLoop = $createLoop;
        $this->track = $track;
        $this->genre = $genre;
    }

    /**
     * @param array $data
     * @param Soundkit|null $initialAlbum
     * @return Soundkit
     */
    public function execute($data, Soundkit $initialAlbum = null)
    {
        $soundkit = $initialAlbum ? $initialAlbum : $this->soundkit->newInstance();

        $inlineData = Arr::except($data, ['tracks', 'tags', 'genres', 'keys', 'instruments']);

        $soundkit->fill($inlineData)->save();

        $tags = Arr::get($data, 'tags', []);
        $tagIds = $this->tag->insertOrRetrieve($tags)->pluck('id');
        $soundkit->tags()->sync($tagIds);

        $genres = Arr::get($data, 'genres', []);
        $genreIds = $this->genre->insertOrRetrieve($genres)->pluck('id');
        $soundkit->genres()->sync($genreIds);

        $this->saveLoops($data, $soundkit);

        $soundkit->load('loops', 'artist', 'genres', 'tags');
        $soundkit->loops->load('artists');

        if ( ! $initialAlbum) {
            $artist = $soundkit->artist;
            if ($artist['artist_type'] === User::class) {
                $followerIds = DB::table('follows')
                    ->where('followed_id', $artist['id'])
                    ->pluck('follower_id');
                $followers = app(User::class)->whereIn('id', $followerIds)->compact()->get();
                Notification::send($followers, new ArtistUploadedMedia($soundkit));
            }
        }

        return $soundkit;
    }

    private function saveLoops($soundkitData, Soundkit $soundkit)
    {
        $tracks = collect(Arr::get($soundkitData, 'tracks', []));
        if ($tracks->isEmpty()) return;

        $trackIds = $tracks->pluck('id')->filter();
        $savedLoops = collect([]);
        if ($trackIds->isNotEmpty()) {
            $savedLoops = $soundkit->tracks()->whereIn('id', $trackIds)->get();
            $savedLoops->load('artists');
        }

        $tracks->each(function($trackData) use($soundkit, $savedLoops) {
            $trackModel = $trackData['id'] ? $savedLoops->find($trackData['id']) : null;
            $this->createLoop->execute(Arr::except($trackData, 'album'), $trackModel, $soundkit, false);
        });
    }
}
