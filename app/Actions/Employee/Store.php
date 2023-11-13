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

            $data = $this->request->validated();
            $data['salary'] = (float) str_replace(',', '', $data['salary']);


            return response()->apiSuccess($employee->create($data));
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }

    }
}