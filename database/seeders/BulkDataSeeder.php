<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkDataSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Starting bulk data generation...');

        // Generate 50 Professors
        $this->command->info('Generating 50 professors...');
        $professorPositions = [
            'Professor of Computer Science', 'Professor of Mathematics',
            'Professor of Physics', 'Professor of Chemistry', 'Professor of Biology',
            'Associate Professor of Computer Science', 'Associate Professor of Mathematics',
            'Assistant Professor of Physics', 'Assistant Professor of Chemistry',
            'Lecturer in Biology'
        ];

        $professors = [];
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $this->generateFirstName();
            $lastName  = $this->generateLastName();
            $professors[] = [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => strtolower($firstName . '.' . $lastName . $i . '@university.edu'),
                'phone'      => $this->generatePhoneNumber(),
                'position'   => $professorPositions[array_rand($professorPositions)],
                'salary'     => rand(60000, 150000) + (rand(0, 99) / 100),
                'hire_date'  => $this->generateRandomDate('2010-01-01', '2023-12-31'),
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('employees')->insert($professors);
        $this->command->info('50 professors created successfully!');

        // Generate 1000 Students in chunks of 100
        $this->command->info('Generating 1000 students with profiles...');
        $programs = [
            'Computer Science', 'Information Technology', 'Mathematics',
            'Physics', 'Chemistry', 'Biology', 'Engineering',
            'Business Administration', 'Economics', 'Psychology'
        ];

        $existingStudentIds = DB::table('students')->pluck('student_id')->toArray();
        $insertedIds = []; // track newly inserted student DB ids

        for ($batch = 0; $batch < 10; $batch++) {
            $students = [];
            for ($i = 1; $i <= 100; $i++) {
                $globalIndex = ($batch * 100) + $i;
                $firstName = $this->generateFirstName();
                $lastName  = $this->generateLastName();

                do {
                    $studentId = 'STU' . str_pad($globalIndex, 6, '0', STR_PAD_LEFT);
                } while (in_array($studentId, $existingStudentIds));

                $existingStudentIds[] = $studentId; // prevent duplicates within loop

                $students[] = [
                    'student_id'    => $studentId,
                    'first_name'    => $firstName,
                    'last_name'     => $lastName,
                    'email'         => strtolower($firstName . '.' . $lastName . $globalIndex . '@student.university.edu.ph'),
                    'phone'         => $this->generatePhoneNumber(),
                    'program'       => $programs[array_rand($programs)],
                    'year_level'    => rand(1, 4),
                    'status'        => 'active',
                    'date_enrolled' => $this->generateRandomDate('2020-01-01', '2024-01-01'),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            DB::table('students')->insert($students);

            // Immediately generate and insert profiles for this batch
            $batchDbIds = DB::table('students')
                ->whereIn('student_id', array_column($students, 'student_id'))
                ->pluck('id')
                ->toArray();

            $profiles = [];
            foreach ($batchDbIds as $dbId) {
                $profiles[] = $this->generateProfile($dbId);
            }
            DB::table('student_profiles')->insert($profiles);

            $this->command->info("Batch " . ($batch + 1) . "/10 done — 100 students + profiles inserted.");
        }

        $this->command->info('Bulk data generation completed!');
    }

    // ── Profile builder ────────────────────────────────────────────────────────

    private function generateProfile(int $studentId): array
    {
        return [
            'student_id'               => $studentId,
            'learning_style'           => $this->generateLearningStyle(),
            'academic_strengths'       => $this->generateAcademicStrengths(),
            'academic_weaknesses'      => $this->generateAcademicWeaknesses(),
            'gpa'                      => round(rand(250, 400) / 100, 2),
            'career_aspiration'        => $this->generateCareerAspiration(),
            'personal_goals'           => $this->generatePersonalGoals(),
            'special_needs'            => rand(0, 10) > 8 ? json_encode([$this->generateSpecialNeed()]) : null,
            'counselor_notes'          => rand(0, 10) > 7 ? $this->generateCounselorNotes() : null,
            'needs_intervention'       => rand(0, 10) > 8,
            'intervention_notes'       => rand(0, 10) > 8 ? $this->generateInterventionNotes() : null,
            'extracurricular_activities' => json_encode($this->generateExtracurricularActivities()),
            'leadership_experience'    => rand(0, 10) > 6 ? $this->generateLeadershipExperience() : null,
            'parent_contact_notes'     => rand(0, 10) > 7 ? $this->generateParentContactNotes() : null,
            'academic_history'         => $this->generateAcademicHistory(),
            'non_academic_activities'  => $this->generateNonAcademicActivities(),
            'violations'               => rand(0, 10) > 9 ? $this->generateViolations() : null,
            'affiliations'             => json_encode($this->generateAffiliations()),
            'skills'                   => json_encode($this->generateSkills()),
            'created_at'               => now(),
            'updated_at'               => now(),
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function generateFirstName(): string
    {
        $names = [
            'James','John','Robert','Michael','William','David','Richard','Joseph','Thomas','Charles',
            'Mary','Patricia','Jennifer','Linda','Elizabeth','Barbara','Susan','Jessica','Sarah','Karen',
            'Lisa','Nancy','Betty','Helen','Sandra','Donna','Carol','Ruth','Sharon','Michelle',
            'Laura','Kimberly','Ashley','Amanda','Melissa','Deborah','Stephanie','Rebecca',
        ];
        return $names[array_rand($names)];
    }

    private function generateLastName(): string
    {
        $names = [
            'Smith','Johnson','Williams','Brown','Jones','Garcia','Miller','Davis','Rodriguez','Martinez',
            'Hernandez','Lopez','Gonzalez','Wilson','Anderson','Thomas','Taylor','Moore','Jackson','Martin',
            'Lee','Perez','Thompson','White','Harris','Sanchez','Clark','Ramirez','Lewis','Robinson',
            'Walker','Young','Allen','King','Wright','Scott','Torres','Nguyen','Hill','Flores',
        ];
        return $names[array_rand($names)];
    }

    private function generatePhoneNumber(): string
    {
        return '+1' . rand(200, 999) . '-' . rand(200, 999) . '-' . rand(1000, 9999);
    }

    private function generateRandomDate(string $start, string $end): string
    {
        return date('Y-m-d', mt_rand(strtotime($start), strtotime($end)));
    }

    private function generateLearningStyle(): string
    {
        return ['Visual','Auditory','Kinesthetic','Reading/Writing','Mixed'][array_rand(['Visual','Auditory','Kinesthetic','Reading/Writing','Mixed'])];
    }

    private function generateAcademicStrengths(): string
    {
        $items = [
            'Strong analytical thinking and problem-solving skills',
            'Excellent verbal and written communication',
            'Advanced mathematical abilities',
            'Creative thinking and innovation',
            'Strong research and critical analysis skills',
            'Leadership and teamwork abilities',
            'Technical proficiency in programming',
            'Scientific methodology and experimental design',
        ];
        return $items[array_rand($items)];
    }

    private function generateAcademicWeaknesses(): string
    {
        $items = [
            'Needs improvement in time management',
            'Difficulty with public speaking',
            'Struggles with advanced mathematics',
            'Limited experience in laboratory work',
            'Challenges in group collaboration',
            'Needs to develop better study habits',
            'Difficulty with theoretical concepts',
            'Limited foreign language skills',
        ];
        return $items[array_rand($items)];
    }

    private function generateCareerAspiration(): string
    {
        $items = [
            'Software Engineer at a tech company','Research Scientist in academia',
            'Data Scientist','Medical Doctor','Business Consultant',
            'University Professor','Financial Analyst','Environmental Scientist',
            'Product Manager','Entrepreneur',
        ];
        return $items[array_rand($items)];
    }

    private function generatePersonalGoals(): string
    {
        $items = [
            'Maintain GPA above 3.5 throughout college',
            'Complete internship before graduation',
            'Learn a new programming language',
            'Study abroad for one semester',
            'Publish research paper in peer-reviewed journal',
            'Graduate with honors','Start a student organization',
            'Complete marathon before graduation',
        ];
        return $items[array_rand($items)];
    }

    private function generateSpecialNeed(): string
    {
        $items = ['Extended test time','Note-taking assistance','Preferential seating','Alternative format materials','Assistive technology'];
        return $items[array_rand($items)];
    }

    private function generateCounselorNotes(): string
    {
        $items = [
            'Student shows great potential but needs motivation',
            'Excellent progress this semester',
            'Consider tutoring for mathematics',
            'Strong leadership qualities observed',
            'Needs guidance in career planning',
            'Well-adjusted socially and academically',
            'Shows improvement in time management',
            'Active participant in class discussions',
        ];
        return $items[array_rand($items)];
    }

    private function generateInterventionNotes(): string
    {
        $items = [
            'Weekly academic counseling recommended',
            'Peer tutoring arranged for struggling subjects',
            'Study skills workshop attendance required',
            'Regular progress meetings with advisor',
            'Time management coaching implemented',
            'Stress management techniques introduced',
            'Career counseling sessions scheduled',
            'Academic probation monitoring plan active',
        ];
        return $items[array_rand($items)];
    }

    private function generateExtracurricularActivities(): array
    {
        $items = [
            'Basketball Team','Debate Club','Student Government','Drama Club',
            'Chess Club','Environmental Club','Programming Club','Music Band',
            'Volunteer Group','Photography Club','Writing Club','Science Club',
        ];
        $count   = rand(1, 3);
        $indices = (array) array_rand($items, $count);
        return array_map(fn($i) => $items[$i], $indices);
    }

    private function generateLeadershipExperience(): string
    {
        $items = [
            'President of Student Council','Captain of Basketball Team',
            'Editor of School Newspaper','Leader of Volunteer Group',
            'Coordinator of Charity Event','Head of Programming Club',
            'Team Lead for Class Project','Mentor for Junior Students',
        ];
        return $items[array_rand($items)];
    }

    private function generateParentContactNotes(): string
    {
        $items = [
            'Parents very supportive of academic goals',
            'Regular communication maintained',
            'Parents concerned about recent grades',
            'Family encourages extracurricular participation',
            'Parents request weekly progress updates',
            'Supportive of career counseling',
            'Concerned about stress levels',
            'Appreciate regular communication',
        ];
        return $items[array_rand($items)];
    }

    private function generateAcademicHistory(): string
    {
        $items = [
            'Consistent academic performance throughout high school',
            'Significant improvement in recent semesters',
            'Strong performance in STEM subjects',
            'Excellence in humanities and social sciences',
            'Varied performance with upward trend',
            'Honors student in previous institution',
            'Transfer student with good standing',
            "Consistent Dean's List achievement",
        ];
        return $items[array_rand($items)];
    }

    private function generateNonAcademicActivities(): string
    {
        $items = [
            'Regular volunteer at local food bank',
            'Part-time retail job on weekends',
            'Community sports league participation',
            'Church youth group involvement',
            'Family business assistance',
            'Online content creation',
            'Freelance graphic design work',
            'Community theater participation',
        ];
        return $items[array_rand($items)];
    }

    private function generateViolations(): string
    {
        $items = [
            'Minor attendance issues - resolved',
            'Late assignment submission warning',
            'Library book return delay',
            'Minor classroom disruption - addressed',
            'Parking violation ticket',
            'Dress code reminder',
            'Noise complaint in dormitory',
            'Computer usage policy reminder',
        ];
        return $items[array_rand($items)];
    }

    private function generateAffiliations(): array
    {
        $items = [
            'National Honor Society','Computer Science Association','Engineering Society',
            'Business Club','Pre-Med Society','Environmental Organization',
            'International Student Association','Athletic Association',
        ];
        $count = rand(0, 2);
        if ($count === 0) return [];
        $indices = (array) array_rand($items, $count);
        return array_map(fn($i) => $items[$i], $indices);
    }

    private function generateSkills(): array
    {
        $items = [
            'Python Programming','Java Development','Web Design','Data Analysis',
            'Public Speaking','Project Management','Spanish Language','Graphic Design',
            'Statistical Analysis','Laboratory Techniques','Creative Writing','Leadership',
        ];
        $count   = rand(2, 5);
        $indices = (array) array_rand($items, $count);
        return array_map(fn($i) => $items[$i], $indices);
    }
}
