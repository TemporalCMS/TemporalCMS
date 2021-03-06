<?php

use Illuminate\Database\Seeder;
use App\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /**
         * Used for testing
         */
        $role_regular_user = new Role;
        $role_regular_user->name = 'user';
        $role_regular_user->description = 'A regular user';
        $role_regular_user->save();
    
        $role_admin_user = new Role;
        $role_admin_user->name = 'admin';
        $role_admin_user->description = 'An admin user';
        $role_admin_user->save();
    }
}
