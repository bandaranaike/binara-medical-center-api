<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new User();
        $this->updateRequest = new UpdateUserRequest();
        $this->storeRequest = new StoreUserRequest();
        $this->relationships = ['role:id,name'];
        $this->resource = UserResource::class;
    }
}
