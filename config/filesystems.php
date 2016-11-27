<?php
return [
    /*
    | Default Filesystem Disk
    */
    'default' => 'local',
    /*
    | Default Cloud Filesystem Disk
    */
    'cloud'   => 's3',
    /*
    | Filesystem Disks
    */
    'disks'   => [
        'local'  => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],
        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'visibility' => 'public',
        ],
        's3'     => [
            'driver' => 's3',
            'key'    => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
        ],
    ],
];
