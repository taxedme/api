<?php
namespace App\Actions\Employee;

use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Http\Requests\OrganizatioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Update implements RouteServiceInterface
{
    public function __construct(public EmployeeUpdateRequest $request)
    {
    }

    public function execute()
    {
        try {
            $employee = Auth::user()->employees();

            $employee = $employee
                ->where("employees.id", $this->request->employee_id)
                ->first();

            if (!$employee) {
                return response()->apiError("Employee not found");
            }
            $employee->update($this->request->validated());

            return response()->apiSuccess($this->request->validated());
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }

    }
}