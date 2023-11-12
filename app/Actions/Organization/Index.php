<?php
namespace App\Actions\Organization;

use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Index implements RouteServiceInterface
{

    public function execute()
    {
        return response()->apiSuccess(Auth::user()->organizations()->paginate(10));
    }
}