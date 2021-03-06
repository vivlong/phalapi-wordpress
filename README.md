# PhalApi 2.x 的Wordpress扩展
PhalApi 2.x扩展类库，操作wordpress。

## 安装和配置
修改项目下的composer.json文件，并添加：  
```
    "vivlong/phalapi-wordpress":"dev-master"
```
然后执行```composer update```，如果PHP版本过低，可使用```composer update --ignore-platform-reqs```。  

安装成功后，添加以下配置到./config/app.php文件：  
```php
    'Wordpress' => array(
        'url' => '<yourSiteUrl>',
        'api_key' => '<userApiKey>',
        'api_secret' => '<userApiSecret>',
        'options' => [
            'version' => 'wp/v2',
            'verify_ssl' => false,
            'timeout' => 10,
        ],
        'auth' => 'jwt',
        'jwt_token' => '<JwtToken>',
        'basic_user' => '<username>',
        'basic_pwd' => '<password>',
    ),
```
针对WP5.6后推出的Application Password功能，接口配置auth可选jwt或basic  

## 使用
在文件中，使用服务：  
```php
$wordpress = new \PhalApi\Wordpress\Lite();
```

