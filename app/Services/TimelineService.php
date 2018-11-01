<?php

namespace App\Services;

use App\Models\Client;
use App\Ninja\Datatables\TimelineDatatable;
use App\Ninja\Repositories\TimelineRepository;

/**
 * Class TimelineService.
 */
class TimelineService extends BaseService
{
    /**
     * @var TimelineRepository
     */
    protected $timelineRepo;

    /**
     * @var DatatableService
     */
    protected $datatableService;

    /**
     * TimelineService constructor.
     *
     * @param TimelineRepository $timelineRepo
     * @param DatatableService   $datatableService
     */
    public function __construct(TimelineRepository $timelineRepo, DatatableService $datatableService)
    {
        $this->timelineRepo = $timelineRepo;
        $this->datatableService = $datatableService;
    }

    /**
     * @param null $clientPublicId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDatatable($clientPublicId = null)
    {
        $clientId = Client::getPrivateId($clientPublicId);

        $query = $this->timelineRepo->findByClientId($clientId);

        return $this->datatableService->createDatatable(new TimelineDatatable(false), $query);
    }
}
