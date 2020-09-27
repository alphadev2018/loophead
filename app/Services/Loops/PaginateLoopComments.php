<?php

namespace App\Services\Loops;

use App\Loop;
use Common\Comments\Comment;
use Common\Comments\LoadChildComments;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaginateLoopComments
{
    /**
     * @param Loop $loop
     * @return array
     */
    public function execute(Loop $loop)
    {
        $pagination = $loop->comments()
            ->rootOnly()
            ->with(['user' => function(BelongsTo $builder) {
                $builder->compact();
            }])
            ->paginate(3);

        $pagination->transform(function(Comment $comment) {
            $comment->relative_created_at = $comment->created_at->diffForHumans();
            return $comment;
        });

        $comments = app(LoadChildComments::class)
            ->execute($loop, collect($pagination->items()));

        $pagination = $pagination->toArray();
        $pagination['data'] = $comments;

        return $pagination;
    }
}