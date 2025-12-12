<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = DB::connection('databaseHrDb')->select("
            SELECT 
                et.*,
                dt.name AS department_name
            FROM employee_table AS et
            LEFT JOIN department_table AS dt ON et.department = dt.id
            WHERE dt.`name` IN (
                    'Administration'
                )
              AND dt.company_id IN (1)
              AND dt.`status` = 1
              AND et.`status` = 'Active';
        ");

        foreach ($employees as $emp) {
            // Try to find existing user by hr_id + company
            $user = User::firstOrNew([
                'email' => $emp->email,
            ]);

            // If new user, set default password
            if (!$user->exists) {
                $user->password = Hash::make('12345678');
            }

            // Always update other fields
            $user->name   = $emp->name;
            $user->status = 1;
            $user->save();

            // Assign role (using Spatie)
            // if (!empty($emp->role)) {
            //     $user->syncRoles('Admin');
            // }

            $user->syncRoles('Admin');
        }
    }
}
