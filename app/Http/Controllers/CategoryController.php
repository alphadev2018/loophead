<?php namespace App\Http\Controllers;

use App;
use Auth;
use App\User;
use App\Category;
use App\Loop;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

class CategoryController extends BaseController {
	
	/**
     * @var Request
     */
    private $request;

    /**
     * @param Message $message
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
	    return $this->success(['categories' => app(Category::class)->all()]);
	}
}
