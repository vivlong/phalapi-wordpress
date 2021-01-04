<?php

return [
    /*
     * 相关配置
    */
    'Wordpress' => array(
        'url' => '<yourSiteUrl>',
        'api_key' => '<yourApiKey>',
        'api_secret' => '<yourApiSecret>',
        'options' => [
            'version' => 'wp/v2',
            'verify_ssl' => false,
            'timeout' => 120,
        ],
        'auth' => 'jwt',
        'basic_user' => 'username',
        'basic_pwd' => 'password',
    ),
];
