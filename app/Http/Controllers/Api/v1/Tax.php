<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Interfaces\RouteServiceInterface;

class Tax extends Controller
{
    public function __invoke(RouteServiceInterface $action)
    {
        return $action->execute();
    }
}
