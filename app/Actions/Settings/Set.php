<?php

namespace App\Actions\Settings;

use App\Traits\SettingsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Set implements RouteServiceInterface
{
    use SettingsTrait;

    public function __construct(public Request $request)
    {
    }
    public function execute()
    {
        $setting = Auth::user()
            ->settings()
            ->where("organization_id", $this->request->organization_id)
            ->where('key', $this->request->key)
            ->first();

        if (!$setting) {
            return response()->apiError('settings not found');
        }

        return response()->apiSuccess($this->set($this->request, $setting));
    }
}