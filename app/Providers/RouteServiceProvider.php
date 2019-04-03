<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        // !, #, $, &, *, +, :, ;, @, [, ], ~, ?, ヌル文字は不許可
        Route::pattern('page', '^[^\!\#\$\&\*\+\,:;=?\@\[\]\~\0\\\].+$');
        Route::pattern('file', '^[^\!\#\$\&\*\+\,:;=?\@\[\]\~\0\/\\\].+$');  // ファイル名はページの規則以外に/も禁止する
        Route::pattern('age', '^\d+$');

        // 利用可能なSNSによってフィルタリング
        $availables = [];
        foreach (\Config::get('services') as $key=>$value) {
            if (isset($value['client_id']) && !empty($value['client_id'])) {
                $availables[] = $key;
            }
        }
        if (count($availables) !== 0) {
            Route::pattern('social', implode('|', $availables));
        } else {
            // オイオイ
            Route::pattern('social', '^\w+$');
        }

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix(':api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
