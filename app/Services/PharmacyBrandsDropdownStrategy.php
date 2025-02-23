<?php

namespace App\Services;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharmacyBrandsDropdownStrategy implements DropdownStrategyInterface
{
    /**
     * Get the results for the allergies dropdown.
     *
     * @param Request $request
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        $query = Brand::query();

        // If a search query is present, filter by the name of the allergy
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->whereHas('stocks', function (Builder $query) use ($request) {
            $query->where('quantity', '>', 0);
        })->select(
            [
                'brands.name AS label',
                'brands.id',
                DB::raw("(SELECT stocks.unit_price FROM stocks WHERE stocks.brand_id = brands.id AND stocks.quantity > 0 ORDER BY brands.created_at DESC LIMIT 1) as extra")
            ]
        );

        return $query;
    }
}
