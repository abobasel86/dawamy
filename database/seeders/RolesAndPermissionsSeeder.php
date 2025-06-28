<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مسح الكاش الخاص بالصلاحيات لضمان عدم حدوث تضارب
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // إنشاء الصلاحيات (Roles)
        // يمكنك إضافة أي صلاحيات أخرى هنا بنفس الطريقة
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'HR']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'employee']);
        Role::create(['name' => 'secretary_general']);
        Role::create(['name' => 'assistant_secretary_general']);
        
        // يمكنك أيضاً إنشاء أذونات محددة (Permissions) إذا احتجت ذلك
        // مثال: Permission::create(['name' => 'edit articles']);

        // إنشاء مستخدم المدير (Admin User)
        $adminUser = User::create([
            'name' => 'admin',
            'email' => 'admin@dawamy.test',
            'password' => bcrypt('11111111'), // استبدل 'password' بكلمة مرور قوية
            'email_verified_at' => now(),
            'hire_date' => now(),
            'is_active' => true,
        ]);

        // إعطاء صلاحية 'admin' للمستخدم المدير
        $adminUser->assignRole('admin');
        
        // يمكنك إنشاء مستخدمين آخرين وإعطائهم صلاحيات مختلفة
        $managerUser = User::create([
            'name' => 'Manager',
            'email' => 'manager@dawamy.test',
            'password' => bcrypt('11111111'),
            'email_verified_at' => now(),
            'hire_date' => now(),
            'is_active' => true,
        ]);

        $managerUser->assignRole('manager');
    }
}