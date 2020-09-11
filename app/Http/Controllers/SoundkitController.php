<?php namespace App\Http\Controllers;

use App;
use App\Soundkit;
use App\Http\Requests\ModifySoundkits;
use App\Jobs\IncrementModelViews;
use App\Services\Soundkits\CrupdateSoundkit;
use App\Services\Soundkits\DeleteSoundkits;
use App\Services\Soundkits\ShowSoundkit;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoundkitController extends BaseController {

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
	public function __construct(Request $request)
	{
        $this->request = $request;
    }

	/**
	 * @return JsonResponse
	 */
	public function index()
	{
		$this->authorize('index', Soundkit::class);

        $paginator = (new Paginator(app(Soundkit::class), $this->request->all(), 'pagination.album_count'));
        $paginator
            ->with('artist')
            ->withCount('tracks')
            ->setDefaultOrderColumns('release_date');

        return $this->success(['pagination' => $paginator->paginate()]);
	}

    /**
     * @param Soundkit $album
     * @return JsonResponse
     */
    public function show(Soundkit $soundkit)
    {
        // $this->authorize('show', $album);

        $soundkit = app(ShowSoundkit::class)
            ->execute($soundkit, $this->request->all());

        // dispatch(new IncrementModelViews($album->id, 'album'));

        return $this->success(['soundkit' => $soundkit]);
    }

    /**
     * @param Soundkit $album
     * @param ModifySoundkits $validate
     * @return JsonResponse
     */
	public function update(Soundkit $album, ModifySoundkits $validate)
	{
	    $this->authorize('update', $album);

		$album = app(CrupdateSoundkit::class)->execute($this->request->all(), $album);

	    return $this->success(['album' => $album]);
	}

    /**
     * @param ModifySoundkits $validate
     * @return JsonResponse
     */
    public function store(ModifySoundkits $validate)
    {
        // $this->authorize('store', Soundkit::class);

        $soundkit = app(CrupdateSoundkit::class)->execute($this->request->all());

        return $this->success(['soundkit' => $soundkit]);
    }

	/**
	 *s @return mixed
	 */
	public function destroy()
	{
        $albumIds = $this->request->get('ids');
	    $this->authorize('destroy', [Soundkit::class, $albumIds]);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

        app(DeleteSoundkits::class)->execute($albumIds);

	    return $this->success();
	}
}
