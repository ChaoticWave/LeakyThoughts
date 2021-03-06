<?php
//******************************************************************************
//* LT tools configuration
//******************************************************************************
return [
    'default_index'   => env('LEAKY_DEFAULT_INDEX', 'leaky'),
    /** The base data path under which output_path exists */
    'data_path'       => env('LEAKY_DATA_PATH', storage_path()),
    /** Storage path for CLI output (relative to leaky.data_path) */
    'output_path'     => env('LEAKY_OUTPUT_PATH', 'dump'),
    /** Storage path (if different from output_path) for CLI attachment output (relative to leaky.data_path) */
    'attachment_path' => env('LEAKY_ATTACHMENT_PATH', 'attachments'),
    /** Elasticsearch configuration */
    'elastic'         => [
        'hosts'         => ['http://elastic:changeme@localhost:9200',],
        /** Words in this list will be removed from text */
        'exclude-words' => [
            'the',
            'of',
            'and',
            'to',
            'a',
            'in',
            'for',
            'is',
            'on',
            'that',
            'by',
            'this',
            'with',
            'i',
            'you',
            'it',
            'not',
            'or',
            'be',
            'are',
            'from',
            'at',
            'as',
            'your',
            'all',
            'have',
            'new',
            'more',
            'an',
            'was',
            'we',
            'will',
            'home',
            'can',
            'us',
            'about',
            'if',
            'page',
            'my',
            'has',
            'search',
            'free',
            'but',
            'our',
            'one',
            'other',
            'do',
            'no',
            'information',
            'time',
            'they',
            'site',
            'he',
            'up',
            'may',
            'what',
            'which',
            'their',
            'news',
            'out',
            'use',
            'any',
            'there',
            'see',
            'only',
            'so',
            'his',
            'when',
            'contact',
            'here',
            'business',
            'who',
            'web',
            'also',
            'now',
            'help',
            'get',
            'pm',
            'view',
            'online',
            'c',
            'e',
            'first',
            'am',
            'been',
            'would',
            'how',
            'were',
            'me',
            's',
            'services',
            'some',
            'these',
            'click',
            'its',
            'like',
            'service',
            'x',
            'than',
            'find',
            'price',
            'date',
            'back',
            'top',
            'people',
            'had',
            'list',
            'name',
            'just',
            'over',
            'state',
            'year',
            'day',
            'into',
            'email',
            'two',
            'health',
            'n',
            'world',
            're',
        ],
    ],
];
