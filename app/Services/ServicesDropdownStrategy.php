<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Service::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->orWhere('key', 'LIKE', '%' . $request->get('search') . '%');
        }


        $query->select(['id', 'name AS label', DB::raw("CONCAT_WS('-',bill_price, system_price) AS extra")]);

        return $query;
    }
}
