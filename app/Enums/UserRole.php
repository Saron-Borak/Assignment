<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Lecturer = 'lecturer';
    case Student = 'student';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Lecturer => 'Lecturer',
            self::Student => 'Student',
        };
    }

    /**
     * The route each role lands on after signing in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Lecturer => 'lecturer.dashboard',
            self::Student => 'student.dashboard',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Admin => 'bi-shield-lock',
            self::Lecturer => 'bi-person-video3',
            self::Student => 'bi-mortarboard',
        };
    }
}
