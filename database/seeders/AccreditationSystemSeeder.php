<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccreditationSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Create initial admin user
        $adminId = DB::table('users')->insertGetId([
            'employee_id' => 'admin',
            'first_name' => 'System',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'email' => 'admin@earist.edu.ph',
            'email_verified_at' => $now,
            'password' => Hash::make('admin'),
            'role' => 'admin',
            'status' => 'active',
            'department' => 'Administration',
            'position' => 'System Administrator',
            'phone' => '+63-123-456-7890',
            'permissions' => json_encode([
                'manage_users',
                'manage_parameters',
                'manage_evaluations',
                'generate_reports',
                'system_settings',
                'view_all_data'
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create sample coordinator user
        $coordinatorId = DB::table('users')->insertGetId([
            'employee_id' => 'COORD001',
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Cruz',
            'email' => 'coordinator@earist.edu.ph',
            'email_verified_at' => $now,
            'password' => Hash::make('CoordPass2024!'),
            'role' => 'coordinator',
            'status' => 'active',
            'department' => 'Quality Assurance',
            'position' => 'Accreditation Coordinator',
            'phone' => '+63-123-456-7891',
            'permissions' => json_encode([
                'manage_parameters',
                'review_content',
                'manage_evaluations',
                'generate_reports'
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create sample faculty user
        $facultyId = DB::table('users')->insertGetId([
            'employee_id' => 'FAC001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'email' => 'faculty@earist.edu.ph',
            'email_verified_at' => $now,
            'password' => Hash::make('FacultyPass2024!'),
            'role' => 'faculty',
            'status' => 'active',
            'department' => 'Computer Science',
            'position' => 'Associate Professor',
            'phone' => '+63-123-456-7892',
            'permissions' => json_encode([
                'upload_content',
                'view_parameters',
                'comment_on_content'
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create accreditation parameters based on AACCUP standards
        $parameters = [
            // Category A: Vision, Mission, Goals and Objectives
            [
                'code' => 'A1',
                'title' => 'Vision Statement',
                'description' => 'The institution has a clear, inspiring vision statement that guides its direction and aspirations.',
                'category' => 'A',
                'subcategory' => '1',
                'weight' => 5,
                'required_documents' => json_encode(['vision_document', 'board_resolution', 'publication_materials']),
                'evaluation_criteria' => 'Vision statement should be clear, inspiring, achievable, and well-communicated to stakeholders.'
            ],
            [
                'code' => 'A2',
                'title' => 'Mission Statement',
                'description' => 'The institution has a well-defined mission statement that reflects its purpose and commitment.',
                'category' => 'A',
                'subcategory' => '2',
                'weight' => 5,
                'required_documents' => json_encode(['mission_document', 'board_resolution', 'strategic_plan']),
                'evaluation_criteria' => 'Mission statement should be clear, relevant, and aligned with the institution\'s activities.'
            ],
            [
                'code' => 'A3',
                'title' => 'Goals and Objectives',
                'description' => 'The institution has specific, measurable goals and objectives that support its mission.',
                'category' => 'A',
                'subcategory' => '3',
                'weight' => 4,
                'required_documents' => json_encode(['strategic_plan', 'annual_plans', 'performance_indicators']),
                'evaluation_criteria' => 'Goals should be SMART (Specific, Measurable, Achievable, Relevant, Time-bound).'
            ],

            // Category B: Faculty
            [
                'code' => 'B1',
                'title' => 'Faculty Qualifications',
                'description' => 'Faculty members possess appropriate academic qualifications and professional experience.',
                'category' => 'B',
                'subcategory' => '1',
                'weight' => 8,
                'required_documents' => json_encode(['faculty_profiles', 'transcripts', 'certificates', 'cv_resume']),
                'evaluation_criteria' => 'Faculty should have relevant degrees, certifications, and experience in their field.'
            ],
            [
                'code' => 'B2',
                'title' => 'Faculty Development',
                'description' => 'The institution provides opportunities for faculty professional development and growth.',
                'category' => 'B',
                'subcategory' => '2',
                'weight' => 6,
                'required_documents' => json_encode(['development_programs', 'training_records', 'conference_attendance']),
                'evaluation_criteria' => 'Evidence of systematic faculty development programs and participation.'
            ],
            [
                'code' => 'B3',
                'title' => 'Faculty Performance',
                'description' => 'Faculty performance is regularly evaluated and feedback is provided for improvement.',
                'category' => 'B',
                'subcategory' => '3',
                'weight' => 7,
                'required_documents' => json_encode(['evaluation_forms', 'performance_reports', 'improvement_plans']),
                'evaluation_criteria' => 'Regular evaluation system with clear criteria and improvement mechanisms.'
            ],

            // Category C: Curriculum and Instruction
            [
                'code' => 'C1',
                'title' => 'Curriculum Design',
                'description' => 'The curriculum is well-designed, current, and aligned with program objectives.',
                'category' => 'C',
                'subcategory' => '1',
                'weight' => 9,
                'required_documents' => json_encode(['curriculum_guide', 'course_syllabi', 'learning_outcomes']),
                'evaluation_criteria' => 'Curriculum should be comprehensive, updated, and outcome-based.'
            ],
            [
                'code' => 'C2',
                'title' => 'Instructional Methods',
                'description' => 'Effective and varied instructional methods are employed to enhance student learning.',
                'category' => 'C',
                'subcategory' => '2',
                'weight' => 7,
                'required_documents' => json_encode(['lesson_plans', 'teaching_materials', 'assessment_tools']),
                'evaluation_criteria' => 'Use of diverse, student-centered teaching methodologies.'
            ],

            // Category D: Support Services
            [
                'code' => 'D1',
                'title' => 'Library Services',
                'description' => 'Adequate library resources and services support the academic programs.',
                'category' => 'D',
                'subcategory' => '1',
                'weight' => 6,
                'required_documents' => json_encode(['library_inventory', 'usage_statistics', 'acquisition_records']),
                'evaluation_criteria' => 'Sufficient, current, and accessible library resources.'
            ],
            [
                'code' => 'D2',
                'title' => 'Student Services',
                'description' => 'Comprehensive student services support student success and well-being.',
                'category' => 'D',
                'subcategory' => '2',
                'weight' => 5,
                'required_documents' => json_encode(['service_catalog', 'student_feedback', 'support_programs']),
                'evaluation_criteria' => 'Range and quality of student support services available.'
            ]
        ];

        foreach ($parameters as $param) {
            DB::table('parameters')->insert(array_merge($param, [
                'status' => 'active',
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Create system settings
        $settings = [
            [
                'key' => 'system_name',
                'value' => 'Accreditation Management System',
                'type' => 'string',
                'description' => 'The name of the accreditation system',
                'is_public' => true
            ],
            [
                'key' => 'institution_name',
                'value' => 'EARIST',
                'type' => 'string',
                'description' => 'Name of the institution using this system',
                'is_public' => true
            ],
            [
                'key' => 'accreditation_body',
                'value' => 'AACCUP',
                'type' => 'string',
                'description' => 'Primary accreditation body',
                'is_public' => true
            ],
            [
                'key' => 'max_file_size',
                'value' => '10485760', // 10MB in bytes
                'type' => 'integer',
                'description' => 'Maximum file upload size in bytes',
                'is_public' => false
            ],
            [
                'key' => 'allowed_file_types',
                'value' => json_encode(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif']),
                'type' => 'json',
                'description' => 'Allowed file types for uploads',
                'is_public' => false
            ],
            [
                'key' => 'evaluation_scale',
                'value' => json_encode([
                    'excellent' => ['min' => 90, 'max' => 100],
                    'very_good' => ['min' => 80, 'max' => 89],
                    'good' => ['min' => 70, 'max' => 79],
                    'satisfactory' => ['min' => 60, 'max' => 69],
                    'needs_improvement' => ['min' => 0, 'max' => 59]
                ]),
                'type' => 'json',
                'description' => 'Evaluation rating scale',
                'is_public' => true
            ],
            [
                'key' => 'notification_email',
                'value' => 'notifications@accreditation.system',
                'type' => 'string',
                'description' => 'Email address for system notifications',
                'is_public' => false
            ],
            [
                'key' => 'auto_backup_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable automatic database backups',
                'is_public' => false
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'System maintenance mode status',
                'is_public' => true
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Create initial announcement
        DB::table('announcements')->insert([
            'title' => 'Welcome to the Accreditation System',
            'content' => 'The Accreditation Management System is now live! Please upload your parameter content and begin the evaluation process. For assistance, contact the system administrator.',
            'type' => 'info',
            'priority' => 'medium',
            'is_active' => true,
            'starts_at' => $now,
            'ends_at' => $now->copy()->addDays(30),
            'target_roles' => json_encode(['admin', 'coordinator', 'faculty', 'staff']),
            'created_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create sample parameter content for demonstration
        $sampleParameterId = DB::table('parameters')->where('code', 'A1')->first()->id;
        
        DB::table('parameter_contents')->insert([
            'parameter_id' => $sampleParameterId,
            'uploaded_by' => $coordinatorId,
            'title' => 'EARIST Vision Statement Document',
            'description' => 'Official vision statement as approved by the Board of Trustees',
            'content_type' => 'text',
            'content' => 'To be a leading technological university in the ASEAN region, recognized for excellence in engineering, science, technology, and innovation that contributes to sustainable development.',
            'status' => 'approved',
            'review_notes' => 'Vision statement is clear, inspiring, and well-aligned with institutional goals.',
            'reviewed_by' => $adminId,
            'reviewed_at' => $now,
            'version' => 1,
            'is_current_version' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create sample evaluation
        DB::table('evaluations')->insert([
            'parameter_id' => $sampleParameterId,
            'evaluator_id' => $adminId,
            'score' => 95.00,
            'rating' => 'excellent',
            'comments' => 'The vision statement is comprehensive, inspiring, and well-communicated across the institution.',
            'recommendations' => 'Continue to promote the vision through various institutional communications.',
            'evidence_reviewed' => json_encode([1]), // parameter_content ID
            'status' => 'final',
            'evaluation_date' => $now->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Log the seeding activity
        DB::table('activity_logs')->insert([
            'log_name' => 'system',
            'description' => 'Initial system data seeded',
            'subject_type' => null,
            'subject_id' => null,
            'causer_type' => 'App\\Models\\User',
            'causer_id' => $adminId,
            'properties' => json_encode([
                'users_created' => 3,
                'parameters_created' => count($parameters),
                'settings_created' => count($settings),
                'announcements_created' => 1
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}