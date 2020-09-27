<?php

namespace App\Http\Controllers;

use App\Services\Loops\PaginateLoopComments;
use App\Loop;
use Common\Core\BaseController;

class LoopCommentsController extends BaseController
{
    public function index(Loop $loop)
    {
        // $this->authorize('show', $loop);

        $pagination = app(PaginateLoopComments::class)->execute($loop);

        return $this->success(['pagination' => $pagination]);
    }
}