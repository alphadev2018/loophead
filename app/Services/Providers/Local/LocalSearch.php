<?php namespace App\Services\Providers\Local;

use App\Album;
use App\Soundkit;
use App\Services\Search\UserSearch;
use App\Track;
use App\Loop;
use App\Artist;
use App\Services\Search\SearchInterface;
use App\Traits\DeterminesArtistType;
use App\User;
use Common\Settings\Settings;
use Illuminate\Database\Eloquent\Builder;

class LocalSearch implements SearchInterface {

    use DeterminesArtistType;

    /**
     * @param string $q
     * @param int $limit
     * @param array $modelTypes
     * @return array
     */
    public function search($request, $limit, $modelTypes) {
        $q = urldecode($request->get('query'));
        $users = $request->get('users');
        if ($users) {
            $users = explode(',', $users);
        }
        $category_id = $request->get('category_id');
        
        $limit = $limit ?: 10;

        $results = [];

        foreach ($modelTypes as $modelType) {
            if ($modelType === Artist::class) {
                $results['artists'] = $this->findArtists($q, $limit);
            } else if ($modelType === Album::class) {
                // $results['albums'] = Album::with('artist')
                $soundkits =  Soundkit::with('artist')
                    ->where('name' ,'like', '%'.$q.'%');
                
                if ($users) {
                    $soundkits = $soundkits->whereIn('artist_id', $users);
                }
                if ($category_id) {
                    $soundkits = $soundkits->where('category_id', $category_id);
                }

                $soundkits = $soundkits->where('private', false)
                    ->orWhereHas('tags', function (Builder $builder) use($q) {
                        return $builder->where('name', 'like', "$q%");
                    })
                    ->limit($limit)
                    ->get();

                $results['albums'] = $soundkits;
            } else if ($modelType === Track::class) {
                // $results['tracks'] = Track::with('album', 'artists')
                $loops = Loop::with('soundkit', 'soundkit.artist', 'artists')
                    ->where('name', 'like', '%'.$q.'%');

                if ($users) {
                    $loops = $loops->whereIn('user_id', $users);
                }
                if ($category_id) {
                    $loops = $loops->where('category_id', $category_id);
                }

                $loops = $loops->where('private', false)
                    ->orWhereHas('tags', function (Builder $builder) use($q) {
                        return $builder->where('name', 'like', "$q%");
                    })
                    ->limit($limit)
                    ->get();
                $results['tracks'] = $loops;
            }
        }

        return $results;
    }

    private function findArtists($q, $limit)
    {
        if ($this->determineArtistType() === User::class) {
            return app(UserSearch::class)->search($q, $limit);
        } else {
            return Artist::where('name', 'like', $q.'%')->limit($limit)->get();
        }
    }
}
