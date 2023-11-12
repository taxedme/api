<?php
namespace App\Actions\Employee;

use App\Imports\EmployeeImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Interfaces\RouteServiceInterface;

class Upload implements RouteServiceInterface
{
    public function execute()
    {
        Excel::import(new EmployeeImport, request()->file('excel'));
    }
}