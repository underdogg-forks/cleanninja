<?php

namespace App\Ninja\Repositories;

use App\Models\ExpenseCategory;
use Auth;
use DB;

class ExpenseCategoryRepository extends BaseRepository
{
    public function getClassName()
    {
        return 'App\Models\ExpenseCategory';
    }

    public function all()
    {
        return ExpenseCategory::scope()->get();
    }

    public function find($filter = null)
    {
        $query = DB::table('expenses__categories')
                ->where('expenses__categories.account_id', '=', Auth::user()->account_id)
                ->select(
                    'expenses__categories.name as category',
                    'expenses__categories.public_id',
                    'expenses__categories.user_id',
                    'expenses__categories.deleted_at',
                    'expenses__categories.is_deleted'
                );

        $this->applyFilters($query, ENTITY_EXPENSE_CATEGORY);

        if ($filter) {
            $query->where(function ($query) use ($filter) {
                $query->where('expenses__categories.name', 'like', '%'.$filter.'%');
            });
        }

        return $query;
    }

    public function save($input, $category = false)
    {
        $publicId = isset($data['public_id']) ? $data['public_id'] : false;

        if (! $category) {
            $category = ExpenseCategory::createNew();
        }

        $category->fill($input);
        $category->save();

        return $category;
    }
}
