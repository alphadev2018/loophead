<?php

namespace App\Services\Messages;

use Auth;
use App\User;
use App\Messages;

trait NormalizesChannel
{
    /**
     * @param User $model
     * @return array
     */
    public function normalizeChannel($model)
    {
        $title = '';
        if (!$model->title && $model->users) {
            foreach ($model->users as $user) {
                if ($user->id == Auth::user()->id) {
                    continue;
                }
                $title .= $user->first_name.' '.substr($user->last_name, 0, 1).'. @'.$user->display_name.',';
            }
            $title = substr($title, 0, strlen($title)-1);
        }
        $messages = Messages::where('channel_id', $model->id)
            ->orderBy('created_at', 'desc')            
            ->limit(10)
            ->get();
        
        return [
            'id' => $model->id,
            'channel_id' => $model->channel_id,
            'title' => $title,
            'messages' => $messages,
            'users' => $model->users ? $model->users : []
        ];
    }
}