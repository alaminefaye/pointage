<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Hash;

class AttendanceSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create departments
        $departments = [
            ['name' => 'Ressources Humaines', 'description' => 'Gestion du personnel'],
            ['name' => 'Informatique', 'description' => 'Développement et maintenance'],
            ['name' => 'Comptabilité', 'description' => 'Gestion financière'],
            ['name' => 'Commercial', 'description' => 'Ventes et marketing'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create sample employees
        $employees = [
            [
                'employee_code' => 'EMP001',
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'password' => Hash::make('password'),
                'department_id' => 1,
                'position' => 'Manager RH',
                'standard_start_time' => '08:00:00',
                'standard_end_time' => '17:00:00',
                'standard_hours_per_day' => 8,
                'is_active' => true,
            ],
            [
                'employee_code' => 'EMP002',
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'email' => 'marie.martin@example.com',
                'password' => Hash::make('password'),
                'department_id' => 2,
                'position' => 'Développeuse',
                'standard_start_time' => '09:00:00',
                'standard_end_time' => '18:00:00',
                'standard_hours_per_day' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($employees as $emp) {
            Employee::create($emp);
        }

        // Set default geolocation (example coordinates - update with your actual location)
        AttendanceSetting::setValue('allowed_latitude', '14.7167');
        AttendanceSetting::setValue('allowed_longitude', '-17.4677');
        AttendanceSetting::setValue('allowed_radius', '50');
        AttendanceSetting::setValue('overtime_threshold_hours', '10');
    }
}

