<?php

namespace App\Enums;

enum MessageChannel: string
{
    /** Student ↔ Zytecs Research team. Team members never see this thread. */
    case Student = 'student';
    /** Team member ↔ administrators. Students never see this thread. */
    case Team = 'team';
}
