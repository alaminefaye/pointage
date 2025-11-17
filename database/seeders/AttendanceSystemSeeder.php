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
                'standard_hours_per_day' => 8,
                'overtime_threshold_hours' => 10,
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
                'standard_hours_per_day' => 8,
                'overtime_threshold_hours' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($employees as $emp) {
            Employee::create($emp);
        }

        // Set default geolocation (example coordinates - update with your actual location)
        // null = paramètres globaux (non spécifiques à un site)
        AttendanceSetting::setValue(null, 'allowed_latitude', '14.7167');
        AttendanceSetting::setValue(null, 'allowed_longitude', '-17.4677');
        AttendanceSetting::setValue(null, 'allowed_radius', '50');
        AttendanceSetting::setValue(null, 'overtime_threshold_hours', '10');
    }
}

