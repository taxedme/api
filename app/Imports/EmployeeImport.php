<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class EmployeeImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts
{
    use RemembersRowNumber;


    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        if ($this->getRowNumber() > 100) {
            throw ValidationException::withMessages([
                'Cannot upload more than 100 in v1'
            ]);
        }
        return new Employee([
            'organization_id' => request()->organization_id,
            'names' => $row['names'],
            'salary' => $row['salary'],
            'months' => $row['months'],
        ]);
    }


    public function rules(): array
    {
        return [
            '*.names' => 'required',
            '*.salary' => 'required|integer',
            '*.months' => 'required|integer'
        ];
    }


    public function batchSize(): int
    {
        return 1000;
    }

}
