<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Interfaces\RouteServiceInterface;

class User extends Controller
{
    public function __invoke(RouteServiceInterface $action)
    {
        return $action->execute();
    }
}
