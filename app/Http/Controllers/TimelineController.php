<?php

namespace App\Http\Controllers;

use App\Services\TimelineService;

class TimelineController extends BaseController
{
    protected $timelineService;

    public function __construct(TimelineService $timelineService)
    {
        //parent::__construct();

        $this->timelineService = $timelineService;
    }

    public function getDatatable($clientPublicId)
    {
        return $this->timelineService->getDatatable($clientPublicId);
    }
}
