<?php
namespace App\Actions\Tax;

use App\Models\Setting;
use App\Models\Settings;
use App\Traits\SettingsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Calculate implements RouteServiceInterface
{
    use SettingsTrait;

    private $user = [];
    protected $employee = [];

    public function __construct(public Request $request)
    {
        Collection::macro("subtract", function (array|Collection $values) {
            $arr = array(50, 6, 8, 9);
            $arr_len = count($arr) - 1;

            sort($arr);

            $result = $arr[$arr_len];
            for ($i = $arr_len - 1; $i >= 0; $i--) {
                $result -= gettype($arr[$i]) == "string" ? (int) ($arr[$i]) : ($arr[$i]);
            }
            return $result;
        });
    }

    function cast(string|int|float $v)
    {
        if (gettype($v) == "string") {
            return (int) $v;
        }
        return $v;
    }

    public function execute()
    {

        $exemptions = Setting::where('organization_id', $this->request->organization_id)
            ->where('key', "exemptions")
            ->first();

        if (!$exemptions) {
            $exemptions = $this->generateSettings("exemptions", $this->request->organization_id);
        }

        $this->exemptions = json_decode($exemptions->value);

        $this->employee = Auth::user()
            ->employees()
            ->where('organization_id', $this->request->organization_id)
            ->get();

        if ($this->request->employee_id) {
            $fetch = $this->employee->firstWhere('id', $this->request->employee_id);
            $this->employee = $fetch ? [$fetch] : [];
        }

        if (collect($this->employee)->isEmpty()) {
            return response()->apiSuccess([]);
        }
       
        return response()->apiSuccess($this->calculator());
    }



    public function float(int|float $n)
    {
        return number_format((float) $n, 2, '.', '');
    }

    public function calculator()
    {
        $em = [];

        foreach ($this->employee as $k => $v) {
            $em[$k]["id"] = $v->id;
            $em[$k]["names"] = $this->request->names ?? $v->names;


            $em[$k]["months"] = $this->request->months ?? 12;
            $em[$k]['salary'] = $this->float($this->request->salary ?? $v->salary);

            $em[$k]['total_pay'] = $em[$k]['salary'] * $em[$k]["months"];
            $em[$k]['salary_grossed_up'] = $this->float(($em[$k]['total_pay'] / $em[$k]["months"]) * 12);

            // preloaded exemptions
            $em[$k]['nhf'] = $this->float($this->request->nhf ?? $this->exemptions->nhf);
            $em[$k]['hmo'] = $this->float($this->request->hmo ?? $this->exemptions->hmo);
            $em[$k]['pension'] = $this->float($this->request->pension ?? $this->exemptions->pension);

            $em[$k]['nhf_grossed_up'] = ($em[$k]['nhf'] / $em[$k]["months"]) * 12;
            $em[$k]['hmo_grossed_up'] = ($em[$k]['hmo'] / $em[$k]["months"]) * 12;



            // // Pension
            $em[$k]['computed_pension'] = $this->float($em[$k]['total_pay'] * 8);
            $em[$k]['allowed_pension'] = ($em[$k]['pension'] <= $em[$k]['computed_pension'] ? $em[$k]['pension'] : $em[$k]['computed_pension']);
            $em[$k]['allowed_pension_grossed_up'] = $this->float(($em[$k]['allowed_pension'] / $em[$k]["months"]) * 12);


            //CRA
            $em[$k]['cra1'] = $this->float($em[$k]['salary_grossed_up'] <= 20000000 ? 200000 : ($em[$k]['salary_grossed_up'] / 100) * 1);
            $em[$k]['cra2'] = $this->float(((($em[$k]['salary_grossed_up'] - $em[$k]['allowed_pension_grossed_up'] - $em[$k]['nhf_grossed_up'] - $em[$k]['hmo_grossed_up'])) / 100) * 20);


            $em[$k]['total_reliefs'] = $this->float($em[$k]['cra1'] + $em[$k]['cra2'] + $em[$k]['allowed_pension_grossed_up'] + $em[$k]['nhf_grossed_up'] + $em[$k]['hmo_grossed_up']);
            $em[$k]['taxable_income'] = $this->float($em[$k]['salary_grossed_up'] - $em[$k]['total_reliefs']);



            $em[$k]['first_300'] = $this->float(($em[$k]['taxable_income'] > 0 ? (($em[$k]['taxable_income'] > 300000 / 12 * $em[$k]["months"]) ? 300000 / 12 * $em[$k]["months"] : $em[$k]['taxable_income']) : 0) * 0.07);
            $em[$k]['next_300'] = $this->float((($em[$k]['taxable_income'] - (300000 / 12 * $em[$k]["months"]) > 300000 / 12 * $em[$k]["months"]) ? 300000 / 12 * $em[$k]["months"] : (($em[$k]['taxable_income'] - (300000 / 12 * $em[$k]["months"]) > 0) ? $em[$k]['taxable_income'] - (300000 / 12 * $em[$k]["months"]) : 0)) * 0.11);
            $em[$k]['first_500'] = $this->float(($em[$k]['taxable_income'] - (600000 / 12 * $em[$k]["months"]) > 500000 / 12 * $em[$k]["months"] ? 500000 / 12 * $em[$k]["months"] : ($em[$k]['taxable_income'] - (600000 / 12 * $em[$k]["months"]) > 0 ? $em[$k]['taxable_income'] - (600000 / 12 * 12) : 0)) * 0.15);
            $em[$k]['next_500'] = $this->float(($em[$k]['taxable_income'] - (1100000 / 12 * $em[$k]["months"]) > 500000 / 12 * $em[$k]["months"] ? 500000 / 12 * $em[$k]["months"] : ($em[$k]['taxable_income'] - (1100000 / 12 * $em[$k]["months"]) > 0 ? $em[$k]['taxable_income'] - (1100000 / 12 * $em[$k]["months"]) : 0)) * 0.19);
            $em[$k]['next_1600'] = $this->float(($em[$k]['taxable_income'] - (1600000 / 12 * $em[$k]["months"]) > 1600000 / 12 * $em[$k]["months"] ? 1600000 / 12 * $em[$k]["months"] : ($em[$k]['taxable_income'] - (1600000 / 12 * $em[$k]["months"]) > 0 ? $em[$k]['taxable_income'] - (1600000 / 12 * $em[$k]["months"]) : 0)) * 0.21);

            $em[$k]['balance'] = $this->float(($em[$k]['taxable_income'] - (300000 / 12 * $em[$k]["months"]) - (300000 / 12 * $em[$k]["months"]) - (500000 / 12 * $em[$k]["months"]) - (500000 / 12 * $em[$k]["months"]) - (1600000 / 12 * $em[$k]["months"])) > 0 ? (($em[$k]['taxable_income'] - (300000 / 12 * $em[$k]["months"]) - (300000 / 12 * $em[$k]["months"]) - (500000 / 12 * $em[$k]["months"]) - (500000 / 12 * $em[$k]["months"]) - (1600000 / 12 * $em[$k]["months"]))) * 0.24 : 0);
            $em[$k]['annual_tax_payable'] = $this->float(array_sum([$em[$k]['first_300'], $em[$k]['next_300'], $em[$k]['first_500'], $em[$k]['next_500'], $em[$k]['next_1600'], $em[$k]['balance']]));

            $em[$k]['pro_rated_tax_payable'] = $this->float(($em[$k]['annual_tax_payable'] / 12) * $em[$k]["months"]);
            $em[$k]['final_pro_rated_tax_payable'] = $this->float($em[$k]['salary_grossed_up'] <= 360000 ? 0 : $em[$k]['pro_rated_tax_payable']);

        }


        return $em;
    }
}