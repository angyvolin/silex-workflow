<?php

return [
    'stm' => [
        'type' => 'state_machine',
        'supports' => ['stdClass'],
        'places' => ['a', 'b', 'c'],
        'marking_store' => [
            'type' => 'multiple_state',
            'arguments' => [],
        ],
        'transitions' => [
            't1' => [
                'from' => 'c',
                'to' => 'b',
            ],
            't2' => [
                'from' => ['b', 'c'],
                'to' => 'a',
            ],
        ],
        'initial_place' => 'c',
    ],
];
