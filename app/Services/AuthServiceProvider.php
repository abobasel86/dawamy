<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User; // تأكد من استدعاء موديل المستخدم

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // -- START: הוסף את הקוד הזה --
        /**
         * تعريف بوابة لصلاحية المالية
         * افترض أن موديل المستخدم لديه حقل 'role'
         */
        Gate::define('view-finance-reports', function (User $user) {
            return $user->role === 'finance'; // أو أي منطق تستخدمه لتحديد الصلاحية
        });
        // -- END: הוסף את הקוד הזה --
    }
}