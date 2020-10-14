<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\DownloadVerified;
use App\Notifications\ProductDownload;

use Mail;
use App\User;
use App\Order;
use App\Download;

class DownloadNotificationToUser
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(DownloadVerified $event)
    {
        $product = $event->product;
        $user = $event->user;

        if ($product->model_type === 'App\Loop') {
            $author = User::find($product->user_id);
        } else {
            $author = User::find($product->artist_id);
        }

        if ($user->id === $author->id)
            return;

        $log = Download::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_type' => $product->model_type
        ]);
        $log->save();

        $logs = Download::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('product_type', $product->type)
            ->count();

        if ($logs > 1)
            return;
        
        if (!$author->settings || !$author->settings->notification) {

            Mail::send('welcome', [
            ], function ($mail) use ($author) { 
                $mail->subject('Download Notification')
                    ->from('noreply@loophead.net', 'Loophead.net')
                    ->to($author->email); 
            });
            
        }

        $author->notify(new ProductDownload($product, $user));
    }
}
