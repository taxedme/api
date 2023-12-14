<?php

namespace App\Actions\Set;

use Illuminate\Http\Request;
use App\Interfaces\RouteServiceInterface;

class Get implements RouteServiceInterface
{

    public function __construct(public Request $request)
    {
    }
    public function execute()
    {

    }
}