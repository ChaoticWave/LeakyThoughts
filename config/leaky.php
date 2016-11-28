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
        'hosts' => ['http://elastic:changeme@localhost:9200',],
    ],
];
