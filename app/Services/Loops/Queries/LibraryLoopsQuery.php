<?php

namespace App\Services\Loops\Queries;

use App\Loop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class LibraryLoopsQuery extends BaseLoopQuery
{
    const ORDER_COL = 'added_at';

    public function get($userId)
    {
        return $this->baseQuery()
            ->join('likes', 'tracks.id', '=', 'likes.likeable_id')
            ->where('likes.user_id', $userId)
            ->where('likes.likeable_type', Loop::class)
            ->select('tracks.*', 'likes.created_at as added_at');
    }
}