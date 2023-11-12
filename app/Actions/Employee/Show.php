<?php
namespace App\Actions\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Show implements RouteServiceInterface
{
    public function __construct(public Request $request)
    {
        $this->request->validate([
            'id' => 'required'
        ]);
    }

    public function execute()
    {
        return response()->apiSuccess(Auth::user()
            ->employees()
            ->where('organization_id', $this->request->organization_id)
            ->where('employees.id', $this->request->id)
            ->first());
    }
}