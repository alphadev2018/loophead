<?php

namespace App\Services\Loops;

use App\Soundkit;
use App\Artist;
use App\Genre;
use App\Notifications\ArtistUploadedMedia;
use App\Services\Providers\SaveOrUpdate;
use App\Loop;
use App\User;
use Common\Tags\Tag;
use DB;
use Exception;
use Illuminate\Support\Arr;
use Notification;
use Storage;

class CrupdateLoop
{
    use SaveOrUpdate;

    /**
     * @var Loop
     */
    private $track;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Genre
     */
    private $genre;

    /**
     * @param Loop $track
     * @param Tag $tag
     * @param Genre $genre
     */
    public function __construct(Loop $track, Tag $tag, Genre $genre)
    {
        $this->track = $track;
        $this->tag = $tag;
        $this->genre = $genre;
    }

    /**
     * @param array $data
     * @param Loop|null $initialLoop
     * @param Soundkit|array|null $soundkit
     * @param bool $loadRelations
     * @return Loop
     */
    public function execute($data, Loop $initialLoop = null, $soundkit = null, $loadRelations = true)
    {
        $track = $initialLoop ?
            $initialLoop :
            $this->track->newInstance();

        $inlineData = Arr::except($data, ['artists', 'tags', 'genres', 'keys', 'instruments', 'album', 'waveData']);

        if ($soundkit) {
            $inlineData['soundkit_name'] = $soundkit['name'];
            $inlineData['soundkit_id'] = $soundkit['id'];
            $inlineData['selling_type'] = $soundkit['selling_type'];
            $inlineData['free'] = $soundkit['free'];
            $inlineData['cost'] = $soundkit['cost'];
            $inlineData['private'] = $soundkit['private'];
        }

        $newArtists = collect($this->getArtists($data, $soundkit) ?: []);

        $track->fill($inlineData)->save();

        // make sure we're only attaching new artists to avoid too many db queries
        if ($track->relationLoaded('artists')) {
            $newArtists = $newArtists->filter(function($newArtist) use ($track) {
                $table = $newArtist['artist_type'] === Artist::class ? 'artists' : 'users';
                return !$track->artists()->where("$table.id", $newArtist['id'])->where('artist_type', $newArtist['artist_type'])->first();
            });
        }

        if ($newArtists->isNotEmpty()) {
            $pivots = $newArtists->map(function($artist, $index) use($track) {
                return [
                    'artist_id' => $artist['id'],
                    'artist_type' => $artist['artist_type'],
                    'loop_id' => $track['id'],
                    'primary' => $index === 0,
                ];
            });

            DB::table('artist_loop')->where('loop_id', $track->id)->delete();
            DB::table('artist_loop')->insert($pivots->toArray());
        }

        $tags = Arr::get($data, 'tags', []);
        $tagIds = $this->tag->insertOrRetrieve($tags)->pluck('id');
        $track->tags()->sync($tagIds);

        $genres = Arr::get($data, 'genres', []);
        $genreIds = $this->genre->insertOrRetrieve($genres)->pluck('id');
        $track->genres()->sync($genreIds);

        if ($loadRelations) {
            $track->load('artists', 'tags', 'genres');
        }

        if ( ! $initialLoop && ! $soundkit) {
            $artist = $track->artists->first();
            if ($artist['artist_type'] === User::class) {
                $followerIds = DB::table('follows')
                    ->where('followed_id', $artist['id'])
                    ->pluck('follower_id');
                $followers = app(User::class)->whereIn('id', $followerIds)->compact()->get();

                try {
                    Notification::send($followers, new ArtistUploadedMedia($track));
                } catch (Exception $e) {
                    //
                }
            }
        }

        if ($waveData = Arr::get($data, 'waveData')) {
            $this->track->getWaveStorageDisk()->put("waves/{$track->id}.json", json_encode($waveData));
        }

        return $track;
    }

    /**
     * @param array $trackData
     * @param Soundkit|array|null $soundkit
     * @return array|void
     */
    private function getArtists($trackData, $soundkit = null)
    {
        if ($trackArtists = Arr::get($trackData, 'artists')) {
            return $trackArtists;
        } else if ($soundkit) {
            return [[
                'id' => $soundkit['artist_id'],
                'artist_type' => $soundkit['artist_type']
            ]];
        }
    }
}
