<?php
namespace App\Actions\Employee;

use App\Http\Requests\EmployeeCreateRequest;
use App\Http\Requests\OrganizatioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Store implements RouteServiceInterface
{
    public function __construct(public EmployeeCreateRequest $request)
    {
    }

    public function execute()
    {
        try {
            $employee = Auth::user()->employees();            
            return response()->apiSuccess($employee->create($this->request->validated()));
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }

    }
}