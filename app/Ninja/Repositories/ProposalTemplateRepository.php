<?php

namespace App\Ninja\Repositories;

use App\Models\ProposalTemplate;
use Auth;
use DB;
use Utils;

class ProposalTemplateRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\ProposalTemplate';
    }

    public function all()
    {
        return ProposalTemplate::scope()->get();
    }

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('proposals__templates')
                ->where('proposals__templates.account_id', '=', Auth::user()->account_id)
                ->select(
                    'proposals__templates.name',
                    'proposals__templates.public_id',
                    'proposals__templates.user_id',
                    'proposals__templates.deleted_at',
                    'proposals__templates.is_deleted',
                    'proposals__templates.html as content',
                    'proposals__templates.private_notes'
                );

        $this->applyFilters($query, ENTITY_PROPOSAL_TEMPLATE);

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('clients.name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.first_name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.last_name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.email', 'like', '%'.$filter.'%')
                      ->orWhere('proposals__templates.name', 'like', '%'.$filter.'%');
            });
        }

        if ($userId) {
            $query->where('proposals__templates.user_id', '=', $userId);
        }

        return $query;
    }

    public function save($input, $proposal = false)
    {
        $publicId = isset($data['public_id']) ? $data['public_id'] : false;

        if (! $proposal) {
            $proposal = ProposalTemplate::createNew();
        }

        $proposal->fill($input);
        $proposal->save();

        return $proposal;
    }
}
