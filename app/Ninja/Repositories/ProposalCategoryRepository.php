<?php

namespace App\Ninja\Repositories;

use App\Models\ProposalCategory;
use Auth;
use DB;
use Utils;

class ProposalCategoryRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\ProposalCategory';
    }

    public function all()
    {
        return ProposalCategory::scope()->get();
    }

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('proposals__categories')
                ->where('proposals__categories.account_id', '=', Auth::user()->account_id)
                ->select(
                    'proposals__categories.name',
                    'proposals__categories.public_id',
                    'proposals__categories.user_id',
                    'proposals__categories.deleted_at',
                    'proposals__categories.is_deleted'
                );

        $this->applyFilters($query, ENTITY_PROPOSAL_CATEGORY);

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->Where('proposals__categories.name', 'like', '%'.$filter.'%');
            });
        }

        return $query;
    }

    public function save($input, $proposal = false)
    {
        $publicId = isset($input['public_id']) ? $input['public_id'] : false;

        if (! $proposal) {
            $proposal = ProposalCategory::createNew();
        }

        $proposal->fill($input);
        $proposal->save();

        return $proposal;
    }
}
