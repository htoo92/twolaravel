<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'groups-list',
            'groups-create',
            'groups-edit',
            'groups-delete',
            'bet-list',
            'bet-create',
            'bet-delete',
            'clear-all',
            'lucky-number-list',
            'ownerdetails-create',
            'ownerdetails-edit',
            'ownerdetails-list'
        ];
        foreach($permissions as $permission){
            Permission::create(['name' => $permission]);
        }
    }
}
