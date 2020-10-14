<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\DownloadVerified;

use Mail;
use App\User;
use App\Order;

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
        $order = $event->order;
        $user = $event->user;

        // Mail::send('emails.receipt', [
        //     'order'=> $order, 
        //     'user_name' => $user->first_name.' '.$user->last_name,
        //     'user_email' => $user->email, 
        //     'download_url' => url('/download/'.($order->product_type == 'App\Loop' ? 'loop':'soundkit').'/'.$order->product_id)
        // ], function ($mail) use ($user) { 
        //     $mail->subject('Purchase Receipt')
        //         ->from('noreply@loophead.net', 'Loophead.net')
        //         ->to($user->email); 
        // });

        if ($order->product_type === 'App\Loop') {
            $author = User::find($order->product->user_id);
        } else {
            $author = User::find($order->product->artist_id);
        }

        if (!$author->settings || !$author->settings->notification) {

            Mail::send('welcome', [
            ], function ($mail) use ($author) { 
                $mail->subject('Download Notification')
                    ->from('noreply@loophead.net', 'Loophead.net')
                    ->to($author->email); 
            });

            
            
        }
    }
}
