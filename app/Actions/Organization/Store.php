<?php
namespace App\Actions\Organization;

use App\Models\Setting;
use App\Traits\SettingsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\OrganizatioRequest;
use App\Interfaces\RouteServiceInterface;

class Store implements RouteServiceInterface
{
    use SettingsTrait;
    public function __construct(public OrganizatioRequest $request)
    {
    }

    public function execute()
    {
        try {
            DB::beginTransaction();

            $organization = Auth::user()->organization();

            if ($organization->where('title', $this->request->title)->exists()) {
                return response()->apiError("Organization already exist");
            }

            $store = $organization->create($this->request->validated());

            $this->generateSettings("exemptions", $store->id);

            DB::commit();

            return response()->apiSuccess($store);
        } catch (\Exception $e) {
            return response()->apiError($e->getMessage());
        }

    }
}