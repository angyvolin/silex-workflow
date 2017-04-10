<?php

return [
    'flow' => [
        'type' => 'workflow',
        'supports' => ['stdClass'],
        'places' => ['a', 'b', 'c', 'd'],
        'marking_store' => [
            'type' => 'single_state',
            'arguments' => [],
        ],
        'transitions' => [
            't1' => [
                'from' => 'a',
                'to' => 'b',
            ],
            't2' => [
                'from' => 'b',
                'to' => 'c',
            ],
            't3' => [
                'from' => ['b', 'c'],
                'to' => 'd',
            ],
        ],
        'initial_place' => 'a',
    ],
];
