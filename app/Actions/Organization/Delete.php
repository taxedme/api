<?php

namespace App\Actions\Organization;

use App\Http\Requests\OrganizatioDeleteRequest;
use Illuminate\Http\Request;
use App\Interfaces\RouteServiceInterface;

class Delete implements RouteServiceInterface
{
    public function __construct(public OrganizatioDeleteRequest $request)
    {

    }

    public function execute()
    {
        try {
            $this->request->Organization()->delete();
            return response()->apiSuccess('organization deleted');
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }
    }
}