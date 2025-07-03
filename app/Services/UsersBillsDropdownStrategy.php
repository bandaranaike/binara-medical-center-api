<?php

namespace App\Services;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersBillsDropdownStrategy implements DropdownStrategyInterface
{
    /**
     * Get the results for the bill dropdown.
     *
     * @param Request $request
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        $query = Bill::query();

        if ($request->has('search')) {
            $query->where(fn(Builder $query) => $query->where('id', 'LIKE', '%' . $request->get('search') . '%')
                ->orWhere('date', 'LIKE', '%' . $request->get('search') . '%')
            );
        }

        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->get('patient_id'));
        }

        $query->select(['id', DB::raw("CONCAT_WS('-',CONCAT_WS('','#',id), SUBSTRING(date,1,10)) AS label")]);

        return $query;
    }
}
