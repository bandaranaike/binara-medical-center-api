<?php

namespace App\Services;

use App\Models\Medicine;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MedicinesDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Medicine::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->orWhere('drug_name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
