<?php namespace App\Http\Controllers;

use App;
use App\Loop;
use Illuminate\Http\Request;
use App\Services\Artists\ArtistAlbumsPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Common\Core\BaseController;

class BrowseController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ArtistAlbumsPaginator
     */
    private $paginator;

    /**
     * Create new ArtistController instance.
     *
     * @param Request $request
     * @param ArtistAlbumsPaginator $paginator
     */
	public function __construct(Request $request, ArtistAlbumsPaginator $paginator)
	{
        $this->request = $request;
        $this->paginator = $paginator;
    }

    /**
     * Paginate all artist albums.
     *
     * @param integer $artistId
     * @return LengthAwarePaginator
     */
	public function index(Request $request, $contentType)
	{
		switch ($contentType) {
            case 'top-downloads':
                $pagination = $this->topDownloads();
                break;
        }

        return $this->success(['pagination' => $pagination]);
    }
    
    private function topDownloads()
    {
        $pagination = Loop::whereNull('user_id')
            ->with(['artists', 'genres'])
            ->withCount('plays')
            ->withCount('downloads')
            ->limit(20)
            ->get();

        // $loopUsers = collect([$user]);

        // $pagination->transform(function (Loop $loop) use($loopUsers) {
        //     $loop->setRelation('artists', $loopUsers);
        //     return $loop;
        // });
        return $pagination;
    }
}
