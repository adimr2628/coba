<?php
$config = [
    'EMAIL_CLIENT'  => '',
    'NAMA_CLIENT'   => '',
    'SITE_URL'      => 'http://localhost/ams/api/',
    'SITE_IMG'      => 'http://localhost/ams/img/',
    'PATH_IMG'      => '../img/',
    'DB'            => [
        'db' => [
            'DB_HOST'        => 'localhost',
            'DB_USER'        => 'root',
            'DB_PASS'        => '',
            'DB_NAME'        => 'db_ams',
            'DB_CHARSET'     => 'utf8',
            'CREATED_USER'   => 'created_by',
            'CREATED_TIME'   => 'created_at',
            'CREATED_TYPE'   => 'int',
            'MODIFIED_USER'  => 'modified_by',
            'MODIFIED_TIME'  => 'modified_at',
            'MODIFIED_TYPE'  => 'int',
            'DISPLAY_ERRORS' => true,
            'USER_ID'        => isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : 0,
            'USER_NAMA'      => isset($_SESSION['user']['nama']) ? $_SESSION['user']['nama'] : 'User belum disetting',
            'USER_LOG'       => false,
            'LOG_FOLDER'     => 'userlog',
        ],
    ],
    'DISPLAY_ERROR' => true,
];
