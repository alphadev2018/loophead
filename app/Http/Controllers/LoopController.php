<?php namespace App\Http\Controllers;

use App;
use App\Http\Requests\ModifyLoops;
use App\Services\Loops\CrupdateLoop;
use App\Services\Loops\PaginateLoopComments;
use App\Loop;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoopController extends BaseController {

	/**
	 * @var Loop
	 */
	private $loop;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Loop $loop
     * @param Request $request
     */
    public function __construct(Loop $loop, Request $request)
	{
		$this->loop = $loop;
        $this->request = $request;
    }

	/**
	 * @return JsonResponse
	 */
	public function index()
	{
        // $this->authorize('index', Loop::class);

	    $paginator = (new Paginator($this->loop, $this->request->all(), 'pagination.loop_count'));
	    $paginator->with('soundkit');
	    $paginator->withCount('plays');
	    // $paginator->setDefaultOrderColumns('spotify_popularity', 'desc');

	    return $this->success(['pagination' => $paginator->paginate()]);
	}

	/**
	 * @param  int  $id
	 * @return JsonResponse
	 */
	public function show($id)
	{
	    $loop = $this->loop
            ->with('artists', 'soundkit.artist', 'soundkit.loops.artists', 'tags', 'genres')
            ->withCount('comments', 'plays', 'reposts', 'likes')
            ->findOrFail($id);

	    // $this->authorize('show', $loop);

        $comments = app(PaginateLoopComments::class)->execute($loop);

	    return $this->success([
	        'loop' => $loop,
            'comments' => isset($comments) ? $comments : []
        ]);
	}

    /**
     * @param int $id
     * @param ModifyLoops $validate
     * @return JsonResponse
     */
	public function update($id, ModifyLoops $validate)
	{
		$loop = $this->loop->findOrFail($id);

		// $this->authorize('update', $loop);

        $loop = app(CrupdateLoop::class)->execute($this->request->all(), $loop, $this->request->get('album'));

        return $this->success(['loop' => $loop]);
	}

    /**
     * @param ModifyLoops $validate
     * @return JsonResponse
     */
    public function store(ModifyLoops $validate)
    {
        // $this->authorize('store', Loop::class);

        $loop = app(CrupdateLoop::class)->execute($this->request->all(), null, $this->request->get('album'));

        return $this->success(['loop' => $loop]);
    }

	/**
	 * @return mixed
	 */
	public function destroy()
	{
		$loopIds = $this->request->get('ids');
	    $this->authorize('destroy', [Loop::class, $loopIds]);

        $this->validate($this->request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|integer'
        ]);

        $this->loop->destroy($loopIds);

        // delete waves
        $paths = array_map(function($id) {
            return "waves/{$id}.json";
        }, $this->request->get('ids'));
        $this->loop->getWaveStorageDisk()->delete($paths);

	    return $this->success();
	}
}
