<?php

namespace App\Ninja\Repositories;

use App\Models\ProposalSnippet;
use App\Models\ProposalCategory;
use Auth;
use DB;
use Utils;

class ProposalSnippetRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\ProposalSnippet';
    }

    public function all()
    {
        return ProposalSnippet::scope()->get();
    }

    public function find($filter = null, $userId = false)
    {
        $query = DB::table('proposals__snippets')
                ->leftjoin('proposals__categories', 'proposals__categories.id', '=', 'proposals__snippets.proposal_category_id')
                ->where('proposals__snippets.account_id', '=', Auth::user()->account_id)
                ->select(
                    'proposals__snippets.name',
                    'proposals__snippets.public_id',
                    'proposals__snippets.user_id',
                    'proposals__snippets.deleted_at',
                    'proposals__snippets.is_deleted',
                    'proposals__snippets.icon',
                    'proposals__snippets.private_notes',
                    'proposals__snippets.html as content',
                    'proposals__categories.name as category',
                    'proposals__categories.public_id as category_public_id',
                    'proposals__categories.user_id as category_user_id'
                );

        $this->applyFilters($query, ENTITY_PROPOSAL_SNIPPET);

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('clients.name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.first_name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.last_name', 'like', '%'.$filter.'%')
                      ->orWhere('contacts.email', 'like', '%'.$filter.'%')
                      ->orWhere('proposals__snippets.name', 'like', '%'.$filter.'%');
            });
        }

        if ($userId) {
            $query->where('proposals__snippets.user_id', '=', $userId);
        }

        return $query;
    }

    public function save($input, $proposal = false)
    {
        $publicId = isset($data['public_id']) ? $data['public_id'] : false;

        if (! $proposal) {
            $proposal = ProposalSnippet::createNew();
        }

        $proposal->fill($input);
        $proposal->save();

        return $proposal;
    }
}
