<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;
use App\Interfaces\RouteServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(RouteServiceInterface::class, function () {

            $api = explode("\\", request()->route()->getControllerClass());
            $api = $api[count($api) - 1];

            $action = 'App\Actions\\' . $api . "\\";
            $action = $action . ucfirst(request()->action ?? "Index");

            return App($action);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro("apiSuccess", function ($message, $code = 200) {
            return Response::json(["data" => $message], $code);
        });

        Response::macro("apiError", function ($message, $code = 400) {
            return Response::json(["message" => $message], $code);
        });
    }
}
