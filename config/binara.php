<?php
return [
    'channeling' => [
        'other_fee_key' => env('CHANNELING_OTHER_FEE_KEY', 'channeling_other_fee'),
        'default_doctor_fee_key' => env('CHANNELING_DEFAULT_DOCTOR_FEE_KEY', 'channeling_doctor_fee'),
    ],
    'services' => [
        'keys' => ['medicine' => 'medicine'],
    ]
];
