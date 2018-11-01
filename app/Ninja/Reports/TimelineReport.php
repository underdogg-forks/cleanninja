<?php

namespace App\Ninja\Reports;

use App\Models\Timeline;
use Auth;

class TimelineReport extends AbstractReport
{
    public function getColumns()
    {
        return [
            'date' => [],
            'client' => [],
            'user' => [],
            'timeline' => [],
        ];
    }

    public function run()
    {
        $account = Auth::user()->account;

        $startDate = $this->startDate;;
        $endDate = $this->endDate;
        $subgroup = $this->options['subgroup'];

        $timeline = Timeline::scope()
            ->with('client.contacts', 'user', 'invoice', 'payment', 'credit', 'task', 'expense', 'account')
            ->whereRaw("DATE(created_at) >= \"{$startDate}\" and DATE(created_at) <= \"$endDate\"")
            ->orderBy('id', 'desc');

        foreach ($timeline->get() as $timeline) {
            $client = $timeline->client;
            $this->data[] = [
                $timeline->present()->createdAt,
                $client ? ($this->isExport ? $client->getDisplayName() : $client->present()->link) : '',
                $timeline->present()->user,
                $this->isExport ? strip_tags($timeline->getMessage()) : $timeline->getMessage(),
            ];

            if ($subgroup == 'category') {
                $dimension = trans('texts.' . $timeline->relatedEntityType());
            } else {
                $dimension = $this->getDimension($timeline);
            }

            $this->addChartData($dimension, $timeline->created_at, 1);
        }

        //dd($this->getChartData());
    }
}
