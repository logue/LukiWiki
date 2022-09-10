<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        // !, #, $, &, *, +, :, ;, @, [, ], ~, ?, ヌル文字は不許可
        Route::pattern('page', '^[^\!\#\$\&\*\+\,:;=?\@\[\]\~\0\\\].+$');
        Route::pattern('file', '^[^\!\#\$\&\*\+\,:;=?\@\[\]\~\0\/\\\].+$');  // ファイル名はページの規則以外に/も禁止する
        Route::pattern('age', '^\d+$');

        /*
        // 利用可能なSNSによってフィルタリング
        $availables = [];
        foreach (\Config::get('services') as $key => $value) {
            if (isset($value['client_id']) && !empty($value['client_id'])) {
                $availables[] = $key;
            }
        }
        if (\count($availables) !== 0) {
            Route::pattern('social', implode('|', $availables));
        } else {
            // オイオイ
            Route::pattern('social', '^\w+$');
        }
        */

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
