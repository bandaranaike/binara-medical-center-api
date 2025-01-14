<?php

namespace App\Services;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface DropdownStrategyInterface
{
    public function getQuery(Request $request): Builder;
}
