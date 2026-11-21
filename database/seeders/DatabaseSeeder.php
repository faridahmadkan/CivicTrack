<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\ProgressUpdate;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'CivicTrack Administrator',
            'email' => 'admin@civictrack.local',
            'password' => Hash::make('Admin@123'),
            'role' => 'admin',
        ]);

        $staff = collect([
            ['name' => 'Aisha Rahman', 'email' => 'aisha.staff@civictrack.local'],
            ['name' => 'Kabir Ahmed', 'email' => 'kabir.staff@civictrack.local'],
            ['name' => 'Nila Khan', 'email' => 'nila.staff@civictrack.local'],
        ])->map(fn ($data) => User::factory()->create([
            ...$data,
            'password' => Hash::make('Staff@123'),
            'role' => 'staff',
        ]));

        $citizens = collect([
            ['name' => 'Samir Hossain', 'email' => 'samir@civictrack.local'],
            ['name' => 'Tania Islam', 'email' => 'tania@civictrack.local'],
            ['name' => 'Rafi Chowdhury', 'email' => 'rafi@civictrack.local'],
            ['name' => 'Maya Das', 'email' => 'maya@civictrack.local'],
        ])->map(fn ($data) => User::factory()->create([
            ...$data,
            'password' => Hash::make('Citizen@123'),
            'role' => 'citizen',
        ]));

        $categoryNames = [
            'Roads and Transportation',
            'Waste Management',
            'Electricity and Street Lighting',
            'Water Supply',
            'Drainage and Sanitation',
            'Public Infrastructure',
            'Environmental Problems',
            'Other Issues',
        ];
        $categories = collect($categoryNames)->map(fn ($name) => Category::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Reports related to '.$name.'.',
            'is_active' => true,
        ]));

        $departments = collect([
            ['name' => 'Public Works Department', 'contact_email' => 'works@civictrack.local'],
            ['name' => 'Sanitation Department', 'contact_email' => 'sanitation@civictrack.local'],
            ['name' => 'Utility Services Department', 'contact_email' => 'utilities@civictrack.local'],
        ])->map(fn ($data) => Department::create([
            ...$data,
            'description' => 'Fictional civic service department.',
        ]));

        $locations = ['Mirpur 10', 'Dhanmondi 27', 'Uttara Sector 7', 'Mohakhali', 'Banani', 'Mohammadpur'];
        $statuses = ['pending_review', 'approved', 'assigned', 'in_progress', 'resolved', 'rejected', 'cancelled'];
        $titles = [
            'Large pothole on main road',
            'Garbage pile outside market',
            'Broken streetlight near junction',
            'Water leak beside bus stop',
            'Blocked roadside drain',
            'Damaged footbridge railing',
            'Illegal waste burning',
            'Missing road sign',
            'Overflowing public bin',
            'Low water pressure',
            'Open manhole cover',
            'Park bench needs repair',
            'Fallen tree blocking lane',
            'Damaged pedestrian crossing',
            'Flooded underpass',
        ];

        foreach ($titles as $index => $title) {
            $status = $statuses[$index % count($statuses)];
            $citizen = $citizens[$index % $citizens->count()];
            $category = $categories[$index % $categories->count()];
            $report = Report::create([
                'reference_number' => 'CT-'.now()->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'citizen_id' => $citizen->id,
                'category_id' => $category->id,
                'department_id' => $departments[$index % $departments->count()]->id,
                'title' => $title,
                'description' => 'A detailed fictional community report describing the issue and its impact on residents.',
                'location' => $locations[$index % count($locations)].', Dhaka',
                'latitude' => 23.8103 + ($index * 0.0007),
                'longitude' => 90.4125 + ($index * 0.0006),
                'priority' => ['low', 'medium', 'high', 'urgent'][$index % 4],
                'status' => $status,
                'rejection_reason' => $status === 'rejected' ? 'The submitted location could not be verified.' : null,
            ]);

            ReportStatusHistory::create([
                'report_id' => $report->id,
                'updated_by' => $admin->id,
                'previous_status' => null,
                'new_status' => 'pending_review',
                'notes' => 'Report submitted for review.',
            ]);

            if (in_array($status, ['assigned', 'in_progress', 'resolved'], true)) {
                $staffMember = $staff[$index % $staff->count()];
                Assignment::create([
                    'report_id' => $report->id,
                    'staff_id' => $staffMember->id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => now()->subDays(3),
                    'notes' => 'Please investigate and provide regular progress updates.',
                    'status' => $status === 'resolved' ? 'completed' : 'active',
                ]);
                if (in_array($status, ['in_progress', 'resolved'], true)) {
                    ProgressUpdate::create([
                        'report_id' => $report->id,
                        'staff_id' => $staffMember->id,
                        'description' => $status === 'resolved'
                            ? 'The issue was inspected and the required corrective work was completed.'
                            : 'Initial investigation completed; corrective work is underway.',
                        'is_internal' => false,
                    ]);
                }
            }

            if ($status === 'resolved') {
                Feedback::create([
                    'report_id' => $report->id,
                    'citizen_id' => $citizen->id,
                    'rating' => 4,
                    'comment' => 'The assigned team responded professionally and resolved the issue.',
                ]);
            }
        }
    }
}
