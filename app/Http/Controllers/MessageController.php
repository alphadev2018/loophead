<?php namespace App\Http\Controllers;

use App;
use Auth;
use App\User;
use App\UserLimit;
use App\Messages;
use App\MessageChannel;
use App\Services\Messages\NormalizesChannel;
use Common\Core\BaseController;
use Common\Database\Paginator;
use Common\Settings\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

class MessageController extends BaseController {
    use NormalizesChannel;

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
		$user = app(User::class)
			->with(['message_channels', 
				'message_channels.users',
				'message_channels.messages'
			])
            ->findOrFail(Auth::user()->id)
			->setGravatarSize(220);

		$channels = [];
		$channel_ids = [];
		foreach ($user->message_channels as $channel) {
			array_push($channel_ids, $channel['id']);
		}
		
		$messages = app(Messages::class)
			->whereIn('channel_id', $channel_ids)
			->with('channel', 'channel.users', 'channel.messages')
			->orderBy('created_at', 'desc')
			->get();

		foreach ($messages as $message) {
			$duplicated = false;
			foreach ($channels as $channel) {
				if ($channel['id'] == $message->channel->id) {
					$duplicated = true;
				}
			}
			if (!$duplicated) {
				array_push($channels, $this->normalizeChannel($message->channel));					
			}
		}
		
	    return $this->success(['channels' => $channels]);
	}

	/**
	 * @return JsonResponse
	 */
	public function create(Request $request)
	{
		$channels = app(User::class)
			->with(['message_channels', 
				'message_channels.users'
			])
			->findOrFail(Auth::user()->id)
			->message_channels;
		$data = [Auth::user()->id, $request->userid];

		foreach ($channels as $channel) {
			$users = [];
			foreach ($channel['users'] as $user) {
				array_push($users, $user->id);
			}
			$intersect = array_intersect($users, $data);
			if ( count($intersect) == count($data) ) {
				return $this->error('Already exists!');
			}
		}

		$new_channel = app(MessageChannel::class)->create([
			'channel_id' =>  Str::uuid(),
			'title' => null
		]);
		$new_channel->save();
		$new_channel->users()->sync($data);

		$new_msg = app(Messages::class)->create([
			'content' => $request->msg,
			'channel_id' => $new_channel->id,
			'from_id' => Auth::user()->id
		]);
		$new_msg->save();

		$limit = app(UserLimit::class)->firstOrNew([
			'user_id' => Auth::user()->id
		]);
		$limit->dm ++;
		$limit->save();

		return $this->success(['new_channel' => $new_channel]);
	}

	/**
	 * @return JsonResponse
	 */
	public function find(Request $request)
	{
		$channels = app(User::class)
			->with(['message_channels', 
				'message_channels.users',				
				'message_channels.messages'
			])
			->findOrFail(Auth::user()->id)
			->message_channels;
		$data = [Auth::user()->id, $request->userid];

		foreach ($channels as $channel) {
			$users = [];
			foreach ($channel['users'] as $user) {
				array_push($users, $user->id);
			}
			$intersect = array_intersect($users, $data);
			if ( count($intersect) == count($data) ) {
				return $this->success(['channel' => $channel]);
			}
		}

		$user = app(User::class)->find($request->userid);

		return $this->success(['channel' => null, 'user' => $user]);
	}
}
