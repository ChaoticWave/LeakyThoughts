<?php
//******************************************************************************
//* LT tools configuration
//******************************************************************************
return [
    /** The base data path under which output_path exists */
    'data_path'     => env('LEAKY_DATA_PATH', storage_path()),
    /** Storage path for CLI output (relative to leaky.data_path) */
    'output_path'   => 'dump',
    'default_index' => 'leaky',
    /** Elasticsearch configuration */
    'elastic'       => [
        'hosts' => ['http://elastic:changeme@localhost:9200',],
    ],
];
