<?php
namespace App\Actions\User;

use App\Interfaces\RouteServiceInterface;

class Index implements RouteServiceInterface
{
    public function __construct()
    {
        // sleep(100);
    }
    public function execute()
    {
        return response()->apiSuccess(auth()->user());
    }
}