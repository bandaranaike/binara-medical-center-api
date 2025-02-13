<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case PATIENT = 'patient';
    case PHARMACY = 'pharmacy';
    case DOCTOR = 'doctor';
    case NURSE = 'nurse';
    case RECEPTION = 'reception';
    case PHARMACY_ADMIN = 'pharmacy_admin';
}
