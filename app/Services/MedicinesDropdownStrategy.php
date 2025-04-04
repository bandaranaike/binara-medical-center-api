<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MedicinesDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Brand::query()
            ->leftJoin('drugs', 'drugs.id', '=', 'brands.drug_id')
            ->select(['brands.id', 'brands.name AS label', 'drugs.name AS extra']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('brands.name', 'LIKE', '%' . $search . '%')
                ->orWhere('drugs.name', 'LIKE', '%' . $search . '%');
        }

        return $query;
    }
}
