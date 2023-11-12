<?php

namespace App\Actions\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;
use App\Http\Requests\OrganizatioDeleteRequest;

class Delete implements RouteServiceInterface
{
    public function __construct(public Request $request)
    {
        $this->request->validate([
            'id' => 'required'
        ]);
    }

    public function execute()
    {
        try {
            $employee = Auth::user()
                ->employees()
                ->where('organization_id', $this->request->organization_id)
                ->where('employees.id', $this->request->id);

            if (!$employee->exists()) {
                return response()->apiError('Employee does not exist');
            }

            $employee->delete();

            return response()->apiSuccess('Employee deleted');
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }
    }
}