<?php

namespace App\Services\Loops\Queries;

use App\Artist;
use App\Loop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryLoopQuery extends BaseLoopQuery
{
    const ORDER_COL = 'track_plays.created_at';

    public function get($userId)
    {
        return $this->baseQuery()
            ->join('track_plays', 'tracks.id', '=', 'track_plays.track_id')
            ->where('track_plays.user_id', $userId)
            ->select('tracks.*', 'track_plays.created_at as added_at');
    }
}