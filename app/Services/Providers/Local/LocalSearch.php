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
        $bpm_from = $request->get('bpm_from');
        $bpm_to = $request->get('bpm_to');
        $instruments = $request->get('instruments');
        $genres = $request->get('genres');
        $subgenre = $request->get('subgenre');

        $sort = $request->get('sort');
        
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
                if ($subgenre) {
                    $soundkits = $soundkits->where('subgenre', $subgenre);
                }

                $soundkits = $soundkits->where('private', false)
                    ->orWhereHas('tags', function (Builder $builder) use($q) {
                        return $builder->where('name', 'like', "$q%");
                    })
                    ->limit($limit);

                switch ($sort) {
                    case 'name':                        
                        $soundkits = $soundkits->orderBy('name', 'asc');
                        break;
                    case 'asc':
                        $soundkits = $soundkits->orderBy('created_at', 'asc');
                        break;
                    case 'desc':
                        $soundkits = $soundkits->orderBy('created_at', 'desc');
                        break;
                }

                $results['albums'] = $soundkits->get();

            } else if ($modelType === Track::class) {
                
                $loops = Loop::with('soundkit', 'soundkit.artist', 'artists')
                    ->where(function ($query) use ($q) {
                        $query->where('name', 'like', '%'.$q.'%')
                            ->orWhere(function ($qry) use ($q) {
                                $qry->whereHas('tags', function($qq) use ($q){
                                    $qq->where('name', 'like', '%'.$q.'%');
                                });
                            });
                    });

                if ($users) {
                    $loops = $loops->whereIn('user_id', $users);
                }
                if ($category_id) {
                    $loops = $loops->where('category_id', $category_id);
                }

                if ($bpm_from && $bpm_to) {
                    $loops = $loops->where(function ($query) use ($bpm_from, $bpm_to) {
                        $query = $query->where('bpm', '>=', $bpm_from)
                            ->where('bpm', '<=', $bpm_to);
                    });
                }
                if ($instruments) {
                    $loops = $loops->where('instruments', $instruments);
                }
                if ($subgenre) {
                    $loops = $loops->where('subgenre', $subgenre);
                }

                $loops = $loops->where('private', false)
                    ->orWhereHas('tags', function (Builder $builder) use($q) {
                        return $builder->where('name', 'like', "$q%");
                    })
                    ->limit($limit);
                
                switch ($sort) {
                    case 'name':    
                        $loops = $loops->orderBy('name', 'asc');                    
                        break;
                    case 'bpm':
                        $loops = $loops->orderBy('bpm', 'asc');     
                        break;
                    case 'key':
                        break;
                    case 'asc':
                        $loops = $loops->orderBy('created_at', 'asc');     
                        break;
                    case 'desc':
                        $loops = $loops->orderBy('created_at', 'desc');     
                        break;
                }

                $results['tracks'] = $loops->get();
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
