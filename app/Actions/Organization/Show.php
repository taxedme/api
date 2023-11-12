<?php
namespace App\Actions\Organization;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\RouteServiceInterface;

class Show implements RouteServiceInterface
{
    public function __construct(public Request $request)
    {
        $this->request->validate([
            'id' => 'required'
        ]);
    }

    public function execute()
    {
        return response()->apiSuccess(
            Auth::user()
                ->organizations()
                ->with(['settings'])
                ->find($this->request->id)
        );
    }
}