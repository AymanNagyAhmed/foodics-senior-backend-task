<?php
return [
    'branches' => [
        1 => [
            'attempts' => 3,
            'decay' => 20,
        ],
        2 => [
            'attempts' => 5,
            'decay' => 30,
        ],

    ],
    'default' => [
        'attempts' => 3,
        'decay' => 20,
    ],
];
