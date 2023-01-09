<?php

return [
    /*
     * 相关配置
    */
    'Wordpress' => array(
        'url' => '<yourSiteUrl>',
        'api_key' => '<userApiKey>',
        'api_secret' => '<userApiSecret>',
        'options' => [
            // 'version' => 'wp/v2',
            // 'verify_ssl' => false,
            'verify' => false,
            'timeout' => 10,
        ],
        'auth' => 'jwt',
        'jwt_token' => '<JwtToken>',
        'basic_user' => '<username>',
        'basic_pwd' => '<password>',
    ),
];
