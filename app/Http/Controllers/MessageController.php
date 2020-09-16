<?php namespace App\Http\Controllers;

use App;
use App\Messages;
use App\MessageChannel;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseController {

	/**
	 * @var Message
	 */
	private $message;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Message $message
     * @param Request $request
     */
    public function __construct(Messages $message, Request $request)
	{
		$this->message = $message;
        $this->request = $request;
    }

	/**
	 * @return JsonResponse
	 */
	public function index()
	{
	    return $this->success([]);
	}
}
