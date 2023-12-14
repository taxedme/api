<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait SettingsTrait
{
    public $exemptions = [
        "pension" => 0,
        "hmo" => 0,
        "nhf" => 0,
        "save_on_update" => 0
    ];

    public function set(Request $request, Setting $setting)
    {
        return $this->{$request->key}($setting);
    }

    public function exemptions($setting)
    {
        Validator::make(request()->value, [
            "pension" => "numeric",
            "hmo" => "numeric",
            "nhf" => "numeric",
            "save_on_update" => "boolean"
        ])->validate();

        $setting->value = collect(json_decode($setting->value))->merge(request()->value);
        $setting->save();
        return $setting;
    }


    public function generateSettings(string $key, $id)
    {
        $settings = $this->{$key};

        if (!isset($settings)) {
            throw new \Exception("Invalid settings");
        }

        return Setting::create([
            "organization_id" => $id,
            "key" => $key,
            "value" => collect($settings),
        ]);
    }
}