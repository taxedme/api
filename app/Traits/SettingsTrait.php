<?php

namespace App\Traits;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait SettingsTrait
{
    protected $exemptions = [
        "pension" => [
            "percent" => 8,
            "status" => 1
        ],
        "nhis" => [
            "percent" => 5,
            "status" => 1
        ],
        "nhf" => [
            "percent" => 2.5,
            "status" => 1
        ],
        "gratuities" => 0,
        "life_insurance" => 0
    ];

    public function set(Request $request, Setting $setting)
    {
        return $this->{$request->key}($setting);
    }

    public function exemptions($setting)
    {
        $value = collect(request()->value);
        Validator::make($value->toArray(), [
            "pension" => "array",
            "pension.status" => "lte:1|gte:0",
            "nhis" => "array",
            "nhis.status" => "lte:1|gte:0",
            "nhf" => "array",
            "nhf.status" => "lte:1|gte:0",
            "gratuities" => "integer",
            "life_insurance" => "integer"
        ])->validate();

        if (isset($value['pension']) && isset($value['pension']['status'])) {
            $this->exemptions['pension']['status'] = $value['pension']['status'];
        }
        if (isset($value['nhis']) && isset($value['nhis']['status'])) {
            $this->exemptions['nhis']['status'] = $value['nhis']['status'];
        }
        if (isset($value['nhf']) && isset($value['nhf']['status'])) {
            $this->exemptions['nhf']['status'] = $value['nhf']['status'];
        }
        if (isset($value['gratuities'])) {
            $this->exemptions['gratuities'] = $value['gratuities'];
        }
        if (isset($value['life_insurance'])) {
            $this->exemptions['life_insurance'] = $value['life_insurance'];
        }

        $setting->value = $this->exemptions;
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