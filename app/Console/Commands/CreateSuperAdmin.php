<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:superadmin {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin with given username and password';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $existingAdmin = DB::table('admins')->where('username', $username)->first();
        if ($existingAdmin) {
            $this->error('Admin username already exists.');
            return;
        }

        $adminId = DB::table('admins')->insertGetId([
            'username' => $username,
            'password' => Hash::make($password),
            'account_type' => 4,
        ]);

        $permissions = DB::table('permissions')->get();

        $adminPermissions = [];
        foreach ($permissions as $permission) {
            $adminPermissions[] = [
                'admin_id' => $adminId,
                'permission_id' => $permission->id,
            ];
        }

        DB::table('admin_permissions')->insert($adminPermissions);

        $this->info('Super admin created and all permissions assigned successfully.');
    }
}
