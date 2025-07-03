<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

if (!function_exists('dynamic_asset')) {
    /**
     * Generate a URL for an asset, making it relative in local environment.
     */
    function dynamic_asset($path, $secure = null)
    {
        if (App::environment('local')) {
            // في البيئة المحلية، قم بتوليد رابط نسبي
            return app('url')->asset($path, $secure);
        }
        // في أي بيئة أخرى (production), استخدم الرابط الكامل
        return asset($path, $secure);
    }
}

if (!function_exists('dynamic_route')) {
    /**
     * Generate a URL for a named route, making it relative in local environment.
     */
    function dynamic_route($name, $parameters = [], $absolute = true)
    {
        if (App::environment('local')) {
            // في البيئة المحلية، قم بتوليد رابط نسبي
            return route($name, $parameters, false); // The 'false' here makes it a relative path
        }
        // في أي بيئة أخرى (production), استخدم الرابط الكامل
        return route($name, $parameters, $absolute);
    }
}