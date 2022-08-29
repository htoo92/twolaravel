<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Hash;
use App\Models\User;
use App\Models\Group;
use App\Models\Highlevelnumberlimit;
use App\Models\ChangeLimit;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $group = Group::create([
            'group_name' => "testGrup",
            'members_limit' => '20',
            'group_voucher' => "test"
        ]);
       

        $user = User::create([
            'name' => 'Hardik Savani', 
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'group_id' => '1',
            'report_to' => '1',
            'ownerdetails_overrate' => '2',
        ]);

        // $user1 = User::create([
        //     'name' => 'supervisor', 
        //     'email' => '091234',
        //     'password' => Hash::make('123456'),
        //     'group_id' => '1',
        //     'report_to' => '1',
        //     'ownerdetails_overrate' => '2',
        // ]);

        // $user2 = User::create([
        //     'name' => 'member1', 
        //     'email' => '09',
        //     'password' => Hash::make('123456'),
        //     'group_id' => '1',
        //     'report_to' => '2',
        //     'ownerdetails_overrate' => '2',
        // ]);

        // $user3 = User::create([
        //     'name' => 'member2', 
        //     'email' => '09123',
        //     'password' => Hash::make('123456'),
        //     'group_id' => '1',
        //     'report_to' => '2',
        //     'ownerdetails_overrate' => '2',
        // ]);

        

        for($i=0; $i<=99; $i++){
            if($i <=9 ){
                Highlevelnumberlimit::create([
                    'numbers' => "0".$i, 
                    'amount' => 1000,
                    'user_id' => "1",
                ]);
            }
            else{
                Highlevelnumberlimit::create([
                    'numbers' => $i, 
                    'amount' => 1000,
                    'user_id' => "1",
                ]);
            }
        }
        
        $changelimit = Changelimit::create([
            'limit_amount' => 1000, 
            'user_id' => '1',
        ]);
        // $changelimit1 = Changelimit::create([
        //     'limit_amount' => 0, 
        //     'user_id' => '2',
        // ]);
        // $changelimit2 = Changelimit::create([
        //     'limit_amount' => 0, 
        //     'user_id' => '3',
        // ]);
        // $changelimit3 = Changelimit::create([
        //     'limit_amount' => 0, 
        //     'user_id' => '4',
        // ]);
        
        $role = Role::create(['name' => 'Admin']);
        $member = Role::create(['name' => 'Member']);
        $supervisor = Role::create(['name' => 'Supervisor']);
     
        $permissions = Permission::pluck('id','id')->all();
   
        $role->syncPermissions($permissions);
        $member->syncPermissions($permissions);
        $supervisor->syncPermissions($permissions);
     
        $user->assignRole([$role->id]);
        // $user1->assignRole([$supervisor->id]);
        // $user2->assignRole([$member->id]);
        // $user3->assignRole([$member->id]);
    }
}