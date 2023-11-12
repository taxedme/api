<?php

use App\Http\Controllers\Api\v1\Settings;
use Illuminate\Http\Request;

use App\Actions\JsonApiAuth\AuthKit;
use App\Http\Controllers\Api\v1\Tax;
use App\Http\Controllers\Api\v1\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Employee;
use App\Http\Controllers\Api\v1\Organization;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get("we", function (Request $request) {
    return "as";
});

require __DIR__ . '/json-api-auth.php';


Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('organization/{action?}', Organization::class)
        ->where('action', 'show')
        ->name('organization.get');


    Route::post('organization/{action}', Organization::class)
        ->where('action', 'store|delete')
        ->name('organization.post');

    Route::middleware(['isOrganization'])->group(function () {

        Route::get('employee/{action?}', Employee::class)
            ->where('action', 'show')
            ->name('employee.get');

        Route::post('employee/{action}', Employee::class)
            ->where('action', 'store|delete|update|upload|destroy')
            ->name('employee.post');

        Route::post('tax/{action}', Tax::class)
            ->where('action', 'store|calculate')
            ->name('tax.post');

        Route::post('settings/{action}', Settings::class)
            ->where('action', 'set')
            ->name('settings.post');
    });


    Route::get('user/{action?}', User::class)
        ->where('action', 'show')
        ->name('user.post');

});

/*
|--------------------------------------------------------------------------
| An example of how to use the verified email feature with api endpoints
|--------------------------------------------------------------------------
|
| Here examples of a route using Sanctum middleware and verified middleware.
| And another route using Passport middleware and verified middleware.
| You can install and use one of this official packages.
|
*/

//Route::get('/verified-middleware-example', function () {
//    return response()->json([
//        'message' => 'the email account is already confirmed now you are able to see this message...',
//    ]);
//})->middleware('auth:sanctum', 'verified');

//Route::get('/verified-middleware-example', function () {
//    return response()->json([
//        'message' => 'the email account is already confirmed now you are able to see this message...',
//    ]);
//})->middleware('auth:api', 'verified');
