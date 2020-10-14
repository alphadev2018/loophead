<?php

namespace App\Notifications;

use App\Services\UrlGenerator;
use App\Loop;
use App\Soundkit;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ProductDownload extends Notification
{
    use Queueable;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $product;

    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @param Comment $newComment
     * @param array $originalComment
     */
    public function __construct($product, $user)
    {
        $this->product = $product;
        $this->user = $user;
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
        return [
            'image' => $this->getImage(),
            'mainAction' => [
                'action' => $this->getMainAction(),
            ],
            'lines' => [
                [
                    'content' => $this->getFirstLine(),
                    'type' => 'secondary'
                ],
                [
                    'content' => $this->product['name'],
                    'icon' => 'play-arrow',
                    'type' => 'primary'
                ],
            ],
        ];
    }

    public function getImage()
    {
        if (is_a($this->product, Loop::class)) {
            return $this->product['image'] ?: Arr::get($this->product, 'album.image') ?: 'audiotrack';
        } else {
            return $this->product['image'] ?: 'album';
        }
    }

    public function getFirstLine()
    {
        $artistName = $this->user['display_name'];
        $type = is_a($this->product, Loop::class) ? 'loop' : 'soundkit';
        return __(":artistName downloaded your $type", ['artistName' => $artistName]);
    }

    public function getMainAction()
    {
        $url = $this->urlGenerator->user($this->user);
        return str_replace(url(''), '/', $url);
    }
}
