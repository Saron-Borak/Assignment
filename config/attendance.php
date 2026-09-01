<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attendance Policy
    |--------------------------------------------------------------------------
    |
    | University-wide rules applied when calculating attendance percentages and
    | deciding whether a check-in is on time.
    |
    */

    'university_name' => 'East Asia Management University',
    'university_short_name' => 'EAMU',

    // Students below this percentage are flagged as at risk.
    'min_percentage' => (int) env('ATTENDANCE_MIN_PERCENTAGE', 75),

    // Minutes after a session's start time before a check-in is marked late.
    'late_after_minutes' => (int) env('ATTENDANCE_LATE_AFTER_MINUTES', 15),

    // Whether a late arrival still counts towards the attended total.
    'count_late_as_present' => true,

    /*
    |--------------------------------------------------------------------------
    | QR Check-in
    |--------------------------------------------------------------------------
    |
    | Tokens rotate so a screenshot of the projected code cannot be shared with
    | absent classmates. The kiosk refreshes slightly before the token expires.
    |
    */

    'qr_ttl_seconds' => (int) env('ATTENDANCE_QR_TTL', 60),
    'qr_refresh_seconds' => (int) env('ATTENDANCE_QR_REFRESH', 45),

];
