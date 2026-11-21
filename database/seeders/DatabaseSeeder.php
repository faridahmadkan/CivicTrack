<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\ProgressUpdate;
use App\Models\ProgressAttachment;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // 1. CLEAN UP NON-AFGHAN DEMO DATA (preserve primary admin)
        // ============================================================

        // Identify primary admin (must be preserved)
        $primaryAdminEmail = 'farid@civictrack.com';
        $primaryAdmin = User::where('email', $primaryAdminEmail)->first();

        // Delete old demo data in reverse dependency order
        // Find reports by old Bangladeshi citizens/staff
        $oldCitizenEmails = ['samir@civictrack.local', 'tania@civictrack.local', 'rafi@civictrack.local', 'maya@civictrack.local'];
        $oldStaffEmails = ['aisha.staff@civictrack.local', 'kabir.staff@civictrack.local', 'nila.staff@civictrack.local'];
        $oldAdminEmail = 'admin@civictrack.local';

        $oldCitizenIds = User::whereIn('email', $oldCitizenEmails)->pluck('id');
        $oldStaffIds = User::whereIn('email', $oldStaffEmails)->pluck('id');
        $oldAdminId = User::where('email', $oldAdminEmail)->value('id');

        // Get report IDs associated with old citizens
        $oldReportIds = Report::whereIn('citizen_id', $oldCitizenIds)->pluck('id');

        // Clean up dependent records
        if ($oldReportIds->isNotEmpty()) {
            ProgressAttachment::whereHas('progressUpdate', function ($q) use ($oldReportIds) {
                $q->whereIn('report_id', $oldReportIds);
            })->delete();

            ProgressUpdate::whereIn('report_id', $oldReportIds)->delete();
            ReportAttachment::whereIn('report_id', $oldReportIds)->delete();
            Feedback::whereIn('report_id', $oldReportIds)->delete();
            Assignment::whereIn('report_id', $oldReportIds)->delete();
            ReportStatusHistory::whereIn('report_id', $oldReportIds)->delete();
            Report::whereIn('id', $oldReportIds)->delete();
        }

        // Clean up assignments by old staff
        $oldAssignmentIds = Assignment::whereIn('staff_id', $oldStaffIds)->pluck('id');
        if ($oldAssignmentIds->isNotEmpty()) {
            ProgressAttachment::whereHas('progressUpdate', function ($q) use ($oldAssignmentIds) {
                $q->whereIn('report_id', Assignment::whereIn('id', $oldAssignmentIds)->pluck('report_id'));
            })->delete();

            ProgressUpdate::whereIn('report_id', Assignment::whereIn('id', $oldAssignmentIds)->pluck('report_id'))->delete();
            Assignment::whereIn('id', $oldAssignmentIds)->delete();
        }

        // Clean up progress updates by old staff
        ProgressUpdate::whereIn('staff_id', $oldStaffIds)->delete();

        // Delete old users (non-primary admin, old staff, old citizens)
        User::whereIn('email', [...$oldCitizenEmails, ...$oldStaffEmails, $oldAdminEmail])->delete();

        // Delete old departments (keep only Afghan-context ones)
        Department::whereIn('contact_email', ['works@civictrack.local', 'sanitation@civictrack.local', 'utilities@civictrack.local'])->delete();

        // ============================================================
        // 2. PRIMARY ADMINISTRATOR - Farid Ahmad Khan (PRESERVED)
        // ============================================================
        $admin = User::firstOrCreate(
            ['email' => 'farid@civictrack.com'],
            [
                'name' => 'Farid Ahmad Khan',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // ============================================================
        // 3. STAFF MEMBERS (Afghan names, @civictrack.com domain)
        // ============================================================
        $staffData = [
            ['name' => 'Ahmad Wali', 'email' => 'ahmad.wali@civictrack.com'],
            ['name' => 'Mohammad Zahir', 'email' => 'mohammad.zahir@civictrack.com'],
            ['name' => 'Hamidullah Rahimi', 'email' => 'hamid.rahimi@civictrack.com'],
            ['name' => 'Jawad Ahmadi', 'email' => 'jawad.ahmadi@civictrack.com'],
            ['name' => 'Bilal Sediq', 'email' => 'bilal.sediq@civictrack.com'],
            ['name' => 'Maryam Safi', 'email' => 'maryam.safi@civictrack.com'],
            ['name' => 'Fatima Noori', 'email' => 'fatima.noori@civictrack.com'],
            ['name' => 'Laila Mohammadi', 'email' => 'laila.mohammadi@civictrack.com'],
        ];

        $staff = collect($staffData)->map(fn ($data) => User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('Staff@123'),
                'role' => 'staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        ));

        // ============================================================
        // 4. CITIZENS (Afghan names, @civictrack.com domain)
        // ============================================================
        $citizenData = [
            ['name' => 'Ahmad Wali', 'email' => 'ahmad.wali.citizen@civictrack.com'],
            ['name' => 'Mohammad Zahir', 'email' => 'mohammad.zahir.citizen@civictrack.com'],
            ['name' => 'Hamidullah Rahimi', 'email' => 'hamid.rahimi.citizen@civictrack.com'],
            ['name' => 'Jawad Ahmadi', 'email' => 'jawad.ahmadi.citizen@civictrack.com'],
            ['name' => 'Bilal Sediq', 'email' => 'bilal.sediq.citizen@civictrack.com'],
            ['name' => 'Maryam Safi', 'email' => 'maryam.safi.citizen@civictrack.com'],
            ['name' => 'Fatima Noori', 'email' => 'fatima.noori.citizen@civictrack.com'],
            ['name' => 'Laila Mohammadi', 'email' => 'laila.mohammadi.citizen@civictrack.com'],
            ['name' => 'Zahra Azimi', 'email' => 'zahra.azimi@civictrack.com'],
            ['name' => 'Nasir Mustafa', 'email' => 'nasir.mustafa@civictrack.com'],
        ];

        $citizens = collect($citizenData)->map(fn ($data) => User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make('Citizen@123'),
                'role' => 'citizen',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        ));

        // ============================================================
        // 5. CATEGORIES
        // ============================================================
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
        $categories = collect($categoryNames)->map(fn ($name) => Category::firstOrCreate(
            ['name' => $name],
            [
                'slug' => Str::slug($name),
                'description' => 'Reports related to '.$name.'.',
                'is_active' => true,
            ]
        ));

        // ============================================================
        // 6. DEPARTMENTS (Afghan context)
        // ============================================================
        $departmentsData = [
            ['name' => 'Kabul Municipal Services Department', 'contact_email' => 'municipal@civictrack.com', 'description' => 'Handles roads, infrastructure, and public works in Kabul.'],
            ['name' => 'Public Works Department', 'contact_email' => 'publicworks@civictrack.com', 'description' => 'Manages construction and maintenance of public infrastructure.'],
            ['name' => 'Water Supply Department', 'contact_email' => 'watersupply@civictrack.com', 'description' => 'Responsible for water distribution and quality.'],
            ['name' => 'Sanitation and Waste Management Department', 'contact_email' => 'sanitation@civictrack.com', 'description' => 'Manages waste collection, disposal, and public sanitation.'],
            ['name' => 'Electricity and Street Lighting Department', 'contact_email' => 'electricity@civictrack.com', 'description' => 'Handles power distribution and street lighting maintenance.'],
            ['name' => 'Environmental Protection Department', 'contact_email' => 'environment@civictrack.com', 'description' => 'Monitors environmental issues and pollution.'],
        ];

        $departments = collect($departmentsData)->map(fn ($data) => Department::firstOrCreate(
            ['name' => $data['name']],
            [
                'contact_email' => $data['contact_email'],
                'description' => $data['description'],
            ]
        ));

        // ============================================================
        // 7. AFGHAN LOCATIONS
        // ============================================================
        $locations = [
            'Shahr-e-Naw, Kabul',
            'Karte Seh, Kabul',
            'Karte Char, Kabul',
            'Wazir Akbar Khan, Kabul',
            'Dasht-e-Barchi, Kabul',
            'Khair Khana, Kabul',
            'Jada-e-Maiwand, Kabul',
            'Deh Afghanan, Kabul',
            'Microrayan, Kabul',
            'Qala-e-Fathullah, Kabul',
            'Herat City Center, Herat',
            'Injil District, Herat',
            'Kandahar City Center, Kandahar',
            'Mazar-i-Sharif Center, Mazar-i-Sharif',
            'Jalalabad Center, Jalalabad',
        ];

        // ============================================================
        // 8. REALISTIC REPORT TITLES (Afghan context)
        // ============================================================
        $titles = [
            'Large pothole on main road near Shahr-e-Naw intersection',
            'Garbage pile accumulating outside local market in Karte Seh',
            'Broken streetlight on Jada-e-Maiwand avenue',
            'Water leak from main pipe beside bus stop in Dasht-e-Barchi',
            'Blocked roadside drain causing flooding in Khair Khana',
            'Damaged footbridge railing in Microrayan residential area',
            'Illegal waste burning in vacant lot near Deh Afghanan',
            'Missing road sign at busy intersection in Wazir Akbar Khan',
            'Overflowing public bin in commercial area of Karte Char',
            'Low water pressure in residential buildings of Qala-e-Fathullah',
            'Open manhole cover on pedestrian walkway in Herat',
            'Park bench needs repair in public garden of Mazar-i-Sharif',
            'Fallen tree blocking lane after storm in Jalalabad',
            'Damaged pedestrian crossing near school in Kandahar',
            'Flooded underpass during heavy rain in Kabul',
            'Cracked water main causing street flooding in Shahr-e-Naw',
            'Non-functional traffic lights at major intersection',
            'Abandoned construction debris on public sidewalk',
            'Sewage overflow in residential area of Karte Seh',
            'Damaged playground equipment in neighborhood park',
        ];

        $statuses = ['pending_review', 'approved', 'assigned', 'in_progress', 'resolved', 'rejected', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        foreach ($titles as $index => $title) {
            $status = $statuses[$index % count($statuses)];
            $citizen = $citizens[$index % $citizens->count()];
            $category = $categories[$index % $categories->count()];
            $department = $departments[$index % $departments->count()];
            $priority = $priorities[$index % count($priorities)];

            $baseDate = now()->subDays(count($titles) - $index);
            $referenceNumber = 'CT-'.$baseDate->format('Ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            $report = Report::firstOrCreate(
                ['reference_number' => $referenceNumber],
                [
                    'citizen_id' => $citizen->id,
                    'category_id' => $category->id,
                    'department_id' => $department->id,
                    'title' => $title,
                    'description' => $this->generateDescription($title, $locations[$index % count($locations)]),
                    'location' => $locations[$index % count($locations)],
                    'latitude' => $this->generateLatitude($index),
                    'longitude' => $this->generateLongitude($index),
                    'priority' => $priority,
                    'status' => $status,
                    'rejection_reason' => $status === 'rejected' ? 'The reported location could not be verified or the issue falls outside municipal jurisdiction.' : null,
                ]
            );

            ReportStatusHistory::firstOrCreate(
                ['report_id' => $report->id, 'new_status' => 'pending_review'],
                [
                    'updated_by' => $admin->id,
                    'previous_status' => null,
                    'new_status' => 'pending_review',
                    'notes' => 'Report submitted for review.',
                    'created_at' => $baseDate,
                ]
            );

            if (in_array($status, ['approved', 'assigned', 'in_progress', 'resolved'], true)) {
                $this->createStatusHistory($report, $admin, 'pending_review', 'approved', 'Report approved for assignment.', $baseDate->addHours(2));

                $staffMember = $staff[$index % $staff->count()];
                $assignment = Assignment::firstOrCreate(
                    ['report_id' => $report->id, 'staff_id' => $staffMember->id],
                    [
                        'assigned_by' => $admin->id,
                        'assigned_at' => $baseDate->addHours(4),
                        'notes' => 'Please investigate and provide regular progress updates.',
                        'status' => in_array($status, ['in_progress', 'resolved']) ? 'active' : 'pending',
                    ]
                );

                $this->createStatusHistory($report, $admin, 'approved', 'assigned', 'Assigned to '.$staffMember->name.'.', $baseDate->addHours(4));
            }

            if (in_array($status, ['in_progress', 'resolved'], true)) {
                $staffMember = $staff[$index % $staff->count()];
                $assignment = Assignment::where('report_id', $report->id)->where('staff_id', $staffMember->id)->first();
                if ($assignment) {
                    $assignment->update(['status' => 'active']);
                }

                ProgressUpdate::firstOrCreate(
                    ['report_id' => $report->id, 'staff_id' => $staffMember->id, 'description' => 'Initial investigation completed; corrective work is underway.'],
                    [
                        'is_internal' => false,
                        'created_at' => $baseDate->addDays(1),
                    ]
                );

                $this->createStatusHistory($report, $staffMember, 'assigned', 'in_progress', 'Investigation started.', $baseDate->addDays(1));
            }

            if ($status === 'resolved') {
                $staffMember = $staff[$index % $staff->count()];
                $assignment = Assignment::where('report_id', $report->id)->where('staff_id', $staffMember->id)->first();
                if ($assignment) {
                    $assignment->update(['status' => 'completed']);
                }

                ProgressUpdate::firstOrCreate(
                    ['report_id' => $report->id, 'staff_id' => $staffMember->id, 'description' => 'The issue was inspected and the required corrective work was completed. Resolution evidence attached.'],
                    [
                        'is_internal' => false,
                        'created_at' => $baseDate->addDays(3),
                    ]
                );

                $this->createStatusHistory($report, $staffMember, 'in_progress', 'resolved', 'Resolved by assigned staff; awaiting citizen feedback.', $baseDate->addDays(3));

                Feedback::firstOrCreate(
                    ['report_id' => $report->id, 'citizen_id' => $citizen->id],
                    [
                        'rating' => rand(3, 5),
                        'comment' => 'The assigned team responded professionally and resolved the issue in a timely manner.',
                        'created_at' => $baseDate->addDays(4),
                    ]
                );
            }

            if ($status === 'cancelled') {
                $this->createStatusHistory($report, $citizen, 'pending_review', 'cancelled', 'Cancelled by the reporting citizen.', $baseDate->addHours(1));
            }
        }
    }

    private function generateDescription(string $title, string $location): string
    {
        $descriptions = [
            'Large pothole on main road' => 'A significant pothole has formed on the main road near {location}, causing damage to vehicles and creating a safety hazard for motorists and pedestrians. The pothole is approximately 60cm in diameter and 15cm deep. It has been worsening over the past two weeks due to heavy traffic and recent rainfall.',
            'Garbage pile accumulating' => 'A large pile of household and commercial waste has been accumulating outside the local market in {location} for over a week. The waste is attracting stray animals and creating unpleasant odors. Residents are concerned about health hazards and the visual impact on the neighborhood.',
            'Broken streetlight' => 'The streetlight on {location} has been non-functional for approximately three weeks. The area becomes very dark after sunset, creating safety concerns for pedestrians and vehicles. The lamp appears to have electrical issues rather than physical damage.',
            'Water leak from main pipe' => 'A visible water leak is occurring from a main supply pipe beside the bus stop in {location}. Water has been flowing continuously for several days, creating a large puddle and causing water wastage. The leak appears to be from a joint in the pipe.',
            'Blocked roadside drain' => 'The roadside drainage system in {location} is completely blocked, causing rainwater to accumulate on the road surface. This creates flooding during even light rainfall and makes the road impassable for pedestrians. The blockage appears to be caused by accumulated debris and sediment.',
            'Damaged footbridge railing' => 'The safety railing on the pedestrian footbridge in {location} has been damaged, with several sections missing or loose. This poses a serious fall hazard, especially for children and elderly residents who use the bridge daily.',
            'Illegal waste burning' => 'Residents have reported regular burning of waste in a vacant lot near {location}. The smoke is causing respiratory issues for nearby residents and the practice violates environmental regulations. The burning typically occurs in the evening hours.',
            'Missing road sign' => 'A critical road sign at the busy intersection in {location} is missing. This creates confusion for drivers and increases the risk of accidents. The sign was reportedly damaged in a recent storm and has not been replaced.',
            'Overflowing public bin' => 'The public waste bin in the commercial area of {location} is consistently overflowing. Waste spills onto the surrounding pavement, creating an unsanitary environment and attracting pests. The bin requires more frequent collection or a larger capacity container.',
            'Low water pressure' => 'Residents in {location} have been experiencing significantly low water pressure for the past two weeks. The issue affects multiple buildings and makes daily activities difficult. The problem appears to be widespread in the area rather than isolated to a single building.',
            'Open manhole cover' => 'A manhole cover on the pedestrian walkway in {location} is missing, leaving an open hole approximately 60cm in diameter. This poses a serious safety hazard, especially at night. Temporary barriers have been placed by residents but a permanent fix is needed.',
            'Park bench needs repair' => 'Several park benches in the public garden of {location} are damaged with broken slats and loose frames. The benches are heavily used by community members and the damage makes them unsafe to use.',
            'Fallen tree blocking lane' => 'A large tree has fallen across a residential lane in {location} following recent stormy weather. The tree completely blocks vehicle access and makes pedestrian passage difficult. The tree needs to be cut and removed.',
            'Damaged pedestrian crossing' => 'The marked pedestrian crossing near the school in {location} has faded significantly and the traffic calming measures are damaged. This creates a safety risk for children crossing the road during school hours.',
            'Flooded underpass' => 'The underpass in {location} floods during heavy rain, making it impassable for both vehicles and pedestrians. The drainage pumps appear to be non-functional or insufficient for the volume of water.',
            'Cracked water main causing street flooding' => 'A cracked water main in {location} is causing significant street flooding. Water has been flowing for several days, undermining the road surface and creating a large pool. The leak is wasting substantial amounts of treated water.',
            'Non-functional traffic lights' => 'The traffic lights at the major intersection in {location} have been non-functional for over a week. Traffic police are occasionally present but the intersection is chaotic during peak hours without signal control.',
            'Abandoned construction debris' => 'Construction debris including concrete blocks, rebar, and packaging materials has been abandoned on the public sidewalk in {location}. This forces pedestrians onto the road and creates a tripping hazard.',
            'Sewage overflow in residential area' => 'Sewage is overflowing from a manhole in the residential area of {location}, creating a serious health hazard. The odor is pervasive and there are concerns about contamination of nearby water sources.',
            'Damaged playground equipment' => 'Several pieces of playground equipment in the neighborhood park of {location} are damaged. The slide has a crack, the swing set has broken chains, and the climbing frame has loose bolts. Children cannot safely use the equipment.',
        ];

        foreach ($descriptions as $key => $desc) {
            if (str_contains($title, $key)) {
                return str_replace('{location}', $location, $desc);
            }
        }

        return 'A detailed community report describing the issue at '.$location.' and its impact on local residents. The issue requires municipal attention and resolution.';
    }

    private function generateLatitude(int $index): float
    {
        // Kabul approximate coordinates with variation
        return 34.5553 + ($index * 0.0015) - 0.01;
    }

    private function generateLongitude(int $index): float
    {
        // Kabul approximate coordinates with variation
        return 69.2075 + ($index * 0.0012) - 0.008;
    }

    private function createStatusHistory(Report $report, User $user, string $from, string $to, string $notes, $timestamp): void
    {
        ReportStatusHistory::firstOrCreate(
            ['report_id' => $report->id, 'new_status' => $to, 'updated_by' => $user->id],
            [
                'previous_status' => $from,
                'notes' => $notes,
                'created_at' => $timestamp,
            ]
        );
    }
}