<?php

namespace App\Enums;

enum UserRole: string
{
    case Student = 'student';
    case Admin = 'admin';
    /** A team member who prepares guidance and feedback materials. */
    case Expert = 'expert';
}
