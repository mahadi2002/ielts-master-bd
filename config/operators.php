<?php
declare(strict_types=1);

// SINGLE SOURCE OF TRUTH. Update here only.
//
// Per BTRC allocation: 018 is Robi's own range, 016 was Airtel's own range
// before the Robi-Airtel merger — 016 numbers are Robi-operated today, but
// keeping the two prefixes shown separately in marketing copy matches how
// users actually recognise their own number. 017/013 are Grameenphone,
// 019/014 are Banglalink.
return [
    'allowed' => ['robi', 'airtel'],
    'map' => [
        '018' => 'robi',
        '016' => 'airtel',   // Robi-operated post-merger, still shown as Airtel to users
        '017' => 'grameenphone',
        '013' => 'grameenphone',
        '019' => 'banglalink',
        '014' => 'banglalink',
        '015' => 'teletalk',
    ],
    // Marketing copy shown to users (kept separate from validation on purpose)
    'display_note' => 'শুধু Robi (018) ও Airtel (016) Number',
];
