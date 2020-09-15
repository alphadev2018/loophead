<?php

namespace App\Notifications;

use App\Services\UrlGenerator;
use App\Loop;
use App\User;
use Common\Comments\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommentReceivedReply extends Notification
{
    use Queueable;

    /**
     * @var array
     */
    public $newComment;

    /**
     * @var array
     */
    private $originalComment;

    /**
     * @var array
     */
    private $loop;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @param Comment $newComment
     * @param array $originalComment
     */
    public function __construct($newComment, $originalComment)
    {
        $this->newComment = $newComment;
        $this->originalComment = $originalComment;
        $loop = app(Loop::class)
            ->select(['name', 'id'])
            ->find($newComment['commentable_id']);
        $this->loop = ['name' => $loop->name, 'id' => $loop->id];
        $this->urlGenerator = app(UrlGenerator::class);
    }

    /**
     * @param User $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * @param User $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $username = $this->newComment['user']['display_name'];

        return [
            'image' => $this->originalComment['user']['avatar'],
            'mainAction' => [
                'action' => $this->relativeUrl($this->urlGenerator->loop($this->loop)),
            ],
            'lines' => [
                [
                    'content' => __(':username replied to your comment:', ['username' => $username]),
                    'action' => ['action' => $this->relativeUrl($this->urlGenerator->user($this->newComment['user'])), 'label' => __('View user')],
                    'type' => 'secondary'
                ],
                [
                    'content' => '"'.Str::limit($this->newComment['content'], 180).'"',
                    'icon' => 'comment', 'type' => 'primary'
                ],
                [
                    'content' => __('on') . " {$this->loop['name']}",
                    'type' => 'secondary'
                ],
            ],
        ];
    }

    private function relativeUrl($url) {
        return str_replace(url(''), '/', $url);
    }
}
