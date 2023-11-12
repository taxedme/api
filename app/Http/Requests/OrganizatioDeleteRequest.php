<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class OrganizatioDeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "id" => "required"
        ];
    }

    public function Organization()
    {
        $organization = Auth::user()->organization()->where("id", $this->id)->first();

        if (!$organization) {
            throw new \Exception("Organization does not exist");
        }
        return $organization;
    }
}
