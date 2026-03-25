<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreTrustedSiteRequest;
use App\Http\Requests\UpdateTrustedSiteRequest;
use App\Models\TrustedSite;

class TrustedSiteController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new TrustedSite();
        $this->updateRequest = new UpdateTrustedSiteRequest();
        $this->storeRequest = new StoreTrustedSiteRequest();
    }
}
