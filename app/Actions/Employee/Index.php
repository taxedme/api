<?php
namespace App\Actions\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Index implements RouteServiceInterface
{
    public function __construct(public Request $request){}

    public function execute()
    {
        return response()->apiSuccess(Auth::user()
        ->employees()
        ->where('organization_id', $this->request->organization_id)
        ->paginate(10));
    }
}