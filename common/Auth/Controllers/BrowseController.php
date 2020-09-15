<?php namespace Common\Auth\Controllers;

use App;
use App\User;
use App\Loop;
use Illuminate\Http\Request;
use App\Services\Artists\ArtistAlbumsPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Common\Core\BaseController;
use Common\Auth\BaseUser;

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
            case 'top-makers':
                $pagination = $this->topMakers();
                break;
            case 'featured-makers':
                $pagination = $this->featuredMakers();
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
            
        return $pagination;
    }

    private function topMakers()
    {
        $pagination = User::whereHas('subscriptions')
            ->withCount(['followers', 'uploadedLoops'])
            ->paginate(20);

        return $pagination;
    }

    private function featuredMakers()
    {
        $pagination = User::whereHas('subscriptions')
            ->withCount(['followers', 'uploadedLoops'])
            ->paginate(20);

        return $pagination;
    }
}
