<?php

namespace App\Actions\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;
use App\Http\Requests\OrganizatioDeleteRequest;

class Destroy implements RouteServiceInterface
{
    public function __construct(public Request $request)
    {
        $this->request->validate([
            'ids' => ['required']
        ]);
    }

    public function execute()
    {
        try {
            $employee = Auth::user()
                ->employees()
                ->where('organization_id', $this->request->organization_id)
                ->whereIn('employees.id', $this->request->ids)
                ->delete();

            return response()->apiSuccess('Employees deleted');
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }
    }
}