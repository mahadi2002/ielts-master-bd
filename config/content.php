<?php
declare(strict_types=1);

return [
    'task_tag' => [
        'task1'    => 'Writing Task 1',
        'task2'    => 'Writing Task 2',
        'speaking' => 'Speaking',
        'general'  => 'সাধারণ',
    ],
    'quiz_type' => [
        'mcq'           => 'Multiple Choice',
        'fill_blank'    => 'Fill in the Blank',
        'synonym_match' => 'Synonym Match',
    ],
    'word_status' => [
        'new'      => 'নতুন',
        'learning' => 'শিখছি',
        'review'   => 'Review-এ আছে',
        'mastered' => 'আয়ত্ত হয়েছে',
    ],
    'srs_rating' => [
        'again' => 'আবার',
        'hard'  => 'কঠিন',
        'good'  => 'ভালো',
        'easy'  => 'সহজ',
    ],
    'daily_goal_type' => [
        'words'          => 'শব্দ',
        'quiz_questions' => 'Quiz প্রশ্ন',
    ],
    'guide_category' => [
        'writing_task1' => 'Writing Task 1',
        'writing_task2' => 'Writing Task 2',
        'speaking'      => 'Speaking',
        'listening'     => 'Listening',
        'reading'       => 'Reading',
        'vocabulary'    => 'Vocabulary Strategy',
    ],
    'question_status' => [
        'open'     => 'উত্তরের অপেক্ষায়',
        'answered' => 'উত্তর দেওয়া হয়েছে',
    ],
    'sortable' => [  // SQL ORDER BY allowlist — never accept raw user input
        'words'  => ['headword', 'ielts_band_level', 'frequency_rank', 'created_at'],
        'guides' => ['published_at', 'title', 'view_count'],
    ],
];
