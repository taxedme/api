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

        if ($this->request->employee_id) {

            $fetch = Auth::user()
                ->employees()
                ->where('organization_id', $this->request->organization_id)
                ->where('employees.id', $this->request->employee_id)
                ->first();


            if ($fetch) {
                $this->employee[] = ($fetch);
            }

            if ($this->request->has('pension')) {
                $this->exemptions->pension->status = $this->request->pension;
            }
            if ($this->request->has('nhf')) {
                $this->exemptions->nhf->status = $this->request->nhf;
            }
            if ($this->request->has('nhis')) {
                $this->exemptions->nhis->status = $this->request->nhis;
            }
            if ($this->request->has('gratuities')) {
                $this->exemptions->gratuities = $this->request->gratuities;
            }
            if ($this->request->has('life_insurance')) {
                $this->exemptions->life_insurance = $this->request->life_insurance;
            }
        } else {
            $this->employee = Auth::user()
                ->employees()
                ->where('organization_id', $this->request->organization_id)
                ->get();
        }

        if (collect($this->employee)->count() < 1) {
            return response()->apiSuccess([]);
        }

        return response()->apiSuccess($this->calculator());
    }


    public function calculator()
    {
        foreach ($this->employee as $k => $v) {

            // prerequisites
            $this->user[$k]["id"] = $v->id;
            $this->user[$k]["names"] = $v->names;
            $this->user[$k]["salary"] = $this->cast($this->request->salary ?? $v->salary);
            $this->user[$k]["months"] = $this->cast($this->request->months ?? 12);
            $this->user[$k]["total_pay"] = ($this->cast($this->user[$k]["salary"]) * $this->user[$k]["months"]);


            // Exemptions
            $this->user[$k]["pension"] = $this->exemptions->pension->status ? ($this->user[$k]["total_pay"] / 100) * $this->exemptions->pension->percent : 0;
            $this->user[$k]["nhf"] = $this->exemptions->nhf->status ? ($this->user[$k]["total_pay"] / 100) * $this->exemptions->nhf->percent : 0;
            $this->user[$k]["nhis"] = $this->exemptions->nhis->status ? ($this->user[$k]["total_pay"] / 100) * $this->exemptions->nhis->percent : 0;
            $this->user[$k]["gratuities"] = $this->exemptions->gratuities > 0 ? ($this->user[$k]["total_pay"] / 100) * $this->exemptions->gratuities : 0;
            $this->user[$k]["life_insurance"] = $this->exemptions->life_insurance > 0 ? ($this->user[$k]["total_pay"] / 100) * $this->exemptions->life_insurance : 0;

            $exemptions = collect([
                $this->user[$k]["pension"],
                $this->user[$k]["nhf"],
                $this->user[$k]["nhis"],
                $this->user[$k]["gratuities"],
                $this->user[$k]["life_insurance"]
            ]);

            // Derive earned income
            $this->user[$k]["earned_income"] = $this->user[$k]["total_pay"] - $exemptions->sum();


            // Computation based on salary 
            if ($this->user[$k]["salary"] > 30000 && $this->user[$k]["salary"] !== 30000) {

                // consolidated allowance
                if (($this->user[$k]["earned_income"] / 100) * 1 > 200000) {
                    $this->user[$k]["consolidated"] = ((($this->user[$k]["earned_income"] / 100) * 1)) + (($this->user[$k]["earned_income"] / 100) * 20);
                } else {
                    $this->user[$k]["consolidated"] = 200000 + (($this->user[$k]["earned_income"] / 100) * 20);
                }

                // Taxable
                $taxable = ($this->user[$k]["total_pay"] - $this->user[$k]["pension"] - $this->user[$k]["nhf"] - $this->user[$k]["nhis"] - $this->user[$k]["gratuities"] - $this->user[$k]["life_insurance"] - $this->user[$k]["consolidated"]);

                // Derive taxable Income
                $fir = (300000 / 100) * 7;
                $sec = (300000 / 100) * 11;
                $thi = (500000 / 100) * 15;
                $fou = (500000 / 100) * 19;
                $fiv = (1600000 / 100) * 21;

                if ($taxable < 300000) {
                    $this->user[$k]["tax_payable"] = ($taxable / 100) * 7;
                } else {

                    $lastPercent = 11;
                    $standardPercent = $fir;
                    $taxableIncome = $taxable - 300000;

                    if ($taxableIncome > 300000) {
                        $lastPercent = 15;
                        $taxableIncome = $taxableIncome - 300000;
                        $standardPercent += $sec;
                    }

                    if ($taxableIncome > 500000) {
                        $lastPercent = 19;
                        $taxableIncome = $taxableIncome - 500000;
                        $standardPercent += $thi;
                    }

                    if ($taxableIncome > 500000) {
                        $lastPercent = 21;
                        $taxableIncome = $taxableIncome - 500000;
                        $standardPercent += $fou;
                    }

                    if ($taxableIncome > 1600000) {
                        $lastPercent = 24;
                        $taxableIncome = $taxableIncome - 1600000;
                        $standardPercent += $fiv;
                    }

                    $taxableIncome = ($taxableIncome / 100) * $lastPercent;
                    $this->user[$k]["tax_payable"] = $taxableIncome + $standardPercent;
                }
            } else {
                $this->user[$k]["consolidated"] = 0;
                $this->user[$k]["earned_income"] = 0;
                $this->user[$k]["tax_payable"] = 0;
            }

            $this->user[$k]["exemptions"] = [
                "nhf" => $this->exemptions->nhf->status,
                "nhis" => $this->exemptions->nhis->status,
                "pension" => $this->exemptions->pension->status,
                "gratuities" => $this->exemptions->gratuities,
                "life_insurance" => $this->exemptions->life_insurance
            ];

        }

        return $this->user;

    }
}