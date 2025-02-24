<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define the schedules based on the provided data
        $schedules = [
            // Prof.Chathura Rathnayake - Tuesday & Saturday @ 05:30 PM
            [
                'doctor_id' => 1, // Assuming Prof.Chathura Rathnayake has id = 1
                'weekday' => 'Tuesday',
                'time' => '17:30',
                'recurring' => 'Weekly',
                'seats' => 0, // Assuming no seat limit
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 1,
                'weekday' => 'Saturday',
                'time' => '17:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Wimalasiri Abeykoon - Sunday @ 09:30 AM
            [
                'doctor_id' => 2, // Assuming Dr.Wimalasiri Abeykoon has id = 2
                'weekday' => 'Sunday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Ayodhya Senanayake - Mon to Fri @ 05:00 PM, Sat @ 02:00 PM, Sun @ 10:30 AM
            [
                'doctor_id' => 3, // Assuming Dr.Ayodhya Senanayake has id = 3
                'weekday' => 'Monday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 3,
                'weekday' => 'Tuesday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 3,
                'weekday' => 'Wednesday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 3,
                'weekday' => 'Thursday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 3,
                'weekday' => 'Friday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 3,
                'weekday' => 'Saturday',
                'time' => '14:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 3,
                'weekday' => 'Sunday',
                'time' => '10:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Prof.Udaya Ralapanawa - Wednesday @ 07:30 PM & Sunday @ 06:00 AM
            [
                'doctor_id' => 4, // Assuming Prof.Udaya Ralapanawa has id = 4
                'weekday' => 'Wednesday',
                'time' => '19:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 4,
                'weekday' => 'Sunday',
                'time' => '06:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Senthil Chandrasekaram - Every other day
            [
                'doctor_id' => 5, // Assuming Dr.Senthil Chandrasekaram has id = 5
                'weekday' => 'Monday',
                'time' => '00:00', // Time not specified
                'recurring' => 'Bi-Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Sunil Bowattage - Saturday @ 04:00 PM
            [
                'doctor_id' => 6, // Assuming Dr.Sunil Bowattage has id = 6
                'weekday' => 'Saturday',
                'time' => '16:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Nanda Amarasekara - Tuesday & Thursday @ 04:00 PM, Sunday @ 02:00 PM
            [
                'doctor_id' => 7, // Assuming Dr.Nanda Amarasekara has id = 7
                'weekday' => 'Tuesday',
                'time' => '16:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 7,
                'weekday' => 'Thursday',
                'time' => '16:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 7,
                'weekday' => 'Sunday',
                'time' => '14:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.R D K Rajapaksha - Wednesday @ 04:00 PM & Sunday @ 07:30 AM
            [
                'doctor_id' => 8, // Assuming Dr.R D K Rajapaksha has id = 8
                'weekday' => 'Wednesday',
                'time' => '16:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 8,
                'weekday' => 'Sunday',
                'time' => '07:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Pradeep Narangoda - Friday @ 03:45 PM
            [
                'doctor_id' => 9, // Assuming Dr.Pradeep Narangoda has id = 9
                'weekday' => 'Friday',
                'time' => '15:45',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.E A D Udaya Kumara - Sunday @ 07:30 AM
            [
                'doctor_id' => 10, // Assuming Dr.E A D Udaya Kumara has id = 10
                'weekday' => 'Sunday',
                'time' => '07:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Kosala Somarathne - Monday @ 03:30 PM
            [
                'doctor_id' => 11, // Assuming Dr.Kosala Somarathne has id = 11
                'weekday' => 'Monday',
                'time' => '15:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Chandana Wijekoon - Tuesday @ 07:00 PM
            [
                'doctor_id' => 12, // Assuming Dr.Chandana Wijekoon has id = 12
                'weekday' => 'Tuesday',
                'time' => '19:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Deepani Munidasa - Thursday @ 05:00 PM
            [
                'doctor_id' => 13, // Assuming Dr.Deepani Munidasa has id = 13
                'weekday' => 'Thursday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Priyanka Seneviwickrama - Sunday @ 03:30 PM
            [
                'doctor_id' => 14, // Assuming Dr.Priyanka Seneviwickrama has id = 14
                'weekday' => 'Sunday',
                'time' => '15:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Priyani Wanigasekara - Wednesday @ 05:00 PM
            [
                'doctor_id' => 15, // Assuming Dr.Priyani Wanigasekara has id = 15
                'weekday' => 'Wednesday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Kamalika Gamage - Saturday Every Other Week @ 06:00 PM
            [
                'doctor_id' => 16, // Assuming Kamalika Gamage has id = 16
                'weekday' => 'Saturday',
                'time' => '18:00',
                'recurring' => 'Bi-Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Chandima Abeysinghe - Friday @ 05:30 PM
            [
                'doctor_id' => 17, // Assuming Dr.Chandima Abeysinghe has id = 17
                'weekday' => 'Friday',
                'time' => '17:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Mudith Samaraweera - Sunday @ 02:30 PM
            [
                'doctor_id' => 18, // Assuming Dr.Mudith Samaraweera has id = 18
                'weekday' => 'Sunday',
                'time' => '14:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Nilushi Hewawasam - Wednesday @ 05:00 PM
            [
                'doctor_id' => 19, // Assuming Dr.Nilushi Hewawasam has id = 19
                'weekday' => 'Wednesday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Kapila Rajasinghe - Sunday @ 10:30 AM
            [
                'doctor_id' => 20, // Assuming Dr.Kapila Rajasinghe has id = 20
                'weekday' => 'Sunday',
                'time' => '10:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Ganga C Pathirana - Everyday
            [
                'doctor_id' => 21, // Assuming Dr.Ganga C Pathirana has id = 21
                'weekday' => 'Monday',
                'time' => '00:00', // Time not specified
                'recurring' => 'Daily',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Kamal Walgama - Sunday @ 02:00 PM
            [
                'doctor_id' => 22, // Assuming Dr.Kamal Walgama has id = 22
                'weekday' => 'Sunday',
                'time' => '14:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Shanika Medagama - Monday, Wednesday & Friday @ 06:00 PM
            [
                'doctor_id' => 23, // Assuming Dr.Shanika Medagama has id = 23
                'weekday' => 'Monday',
                'time' => '18:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 23,
                'weekday' => 'Wednesday',
                'time' => '18:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 23,
                'weekday' => 'Friday',
                'time' => '18:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Shanika Ekanayake - Tuesday & Friday @ 05:00 PM
            [
                'doctor_id' => 24, // Assuming Dr.Shanika Ekanayake has id = 24
                'weekday' => 'Tuesday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 24,
                'weekday' => 'Friday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.H L Horadagoda - Friday @ 06:30 PM
            [
                'doctor_id' => 25, // Assuming Dr.H L Horadagoda has id = 25
                'weekday' => 'Friday',
                'time' => '18:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr.Samadara Nakandala - Sunday @ 08:00 AM
            [
                'doctor_id' => 26, // Assuming Dr.Samadara Nakandala has id = 26
                'weekday' => 'Sunday',
                'time' => '08:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Prof.Dushantha Medagedara - Sunday Every Other Week @ 04:00 PM
            [
                'doctor_id' => 27, // Assuming Prof.Dushantha Medagedara has id = 27
                'weekday' => 'Sunday',
                'time' => '16:00',
                'recurring' => 'Bi-Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr Janak B Abayakoon - Monday, Tuesday, Wednesday 09:30 AM #28
            [
                'doctor_id' => 28,
                'weekday' => 'Monday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 28,
                'weekday' => 'Tuesday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 28,
                'weekday' => 'Wednesday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr Ranjith Rajasinghe - Thursday, Friday 09:30 AM, Friday 04:30 PM #29
            [
                'doctor_id' => 29,
                'weekday' => 'Thursday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 29,
                'weekday' => 'Friday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 29,
                'weekday' => 'Friday',
                'time' => '16:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr Ranjith Waidyathilake - Saturday 09:30 AM #30
            [
                'doctor_id' => 30,
                'weekday' => 'Saturday',
                'time' => '09:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr Mrs. Sureni Manoratna - Sunday 08:00 AM, 05:00 PM, 04:30 PM #31
            [
                'doctor_id' => 31,
                'weekday' => 'Sunday',
                'time' => '08:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 31,
                'weekday' => 'Sunday',
                'time' => '17:00',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 31,
                'weekday' => 'Sunday',
                'time' => '16:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Dr A Chandrathilake - Sunday, Saturday 03:30 PM #32
            [
                'doctor_id' => 32,
                'weekday' => 'Sunday',
                'time' => '15:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 32,
                'weekday' => 'Saturday',
                'time' => '15:30',
                'recurring' => 'Weekly',
                'seats' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // Insert the schedules into the database
        DB::table('doctor_schedules')->truncate();
        DB::table('doctor_schedules')->insert($schedules);
    }
}

/**
 */
