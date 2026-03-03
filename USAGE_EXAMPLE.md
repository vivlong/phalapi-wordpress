# PhalApi WordPress 扩展使用示例

## 项目概述

这是一个 PhalApi 2.x 扩展类库，用于操作 WordPress REST API。项目已全面升级到 PHP 8.0+ 规范，支持 JWT 和 Basic 两种认证方式。

## 安装

### 1. 通过 Composer 安装

```bash
composer require vivlong/phalapi-wordpress
```

### 2. 配置

在 `./config/app.php` 文件中添加 WordPress 配置：

```php
return [
    'Wordpress' => [
        // WordPress 站点 URL
        'url' => 'https://your-wordpress-site.com/wp-json',
        
        // API 密钥（用于 JWT 认证）
        'api_key' => 'your_api_key',
        'api_secret' => 'your_api_secret',
        
        // 请求选项
        'options' => [
            'version' => 'wp/v2',        // API 版本
            'verify_ssl' => false,       // SSL 验证（开发环境可设为 false）
            'timeout' => 10,             // 超时时间（秒）
        ],
        
        // 认证方式：'jwt' 或 'basic'
        'auth' => 'jwt',
        
        // JWT 认证配置
        'jwt_token' => 'your_jwt_token',
        
        // Basic 认证配置
        'basic_user' => 'username',
        'basic_pwd' => 'password',
    ],
];
```

## 使用示例

### 1. 基本使用

```php
<?php

declare(strict_types=1);

use PhalApi\Wordpress\Lite;

// 初始化 WordPress 客户端
$wordpress = new Lite();

// 或者传入自定义配置
$config = [
    'url' => 'https://your-wordpress-site.com/wp-json',
    'api_key' => 'your_api_key',
    'api_secret' => 'your_api_secret',
    'auth' => 'jwt',
    'jwt_token' => 'your_jwt_token',
    'options' => [
        'timeout' => 10,
    ],
];

$wordpress = new Lite($config);
```

### 2. 文章管理

```php
// 获取文章列表
$posts = $wordpress->listPosts([
    'per_page' => 10,
    'page' => 1,
    'orderby' => 'date',
    'order' => 'desc',
]);

// 获取单篇文章
$post = $wordpress->retrievePost(123);

// 创建新文章
$newPost = $wordpress->createPost([
    'title' => '新文章标题',
    'content' => '文章内容',
    'status' => 'publish',
    'categories' => [1, 2], // 分类 ID
    'tags' => [3, 4],       // 标签 ID
]);

// 更新文章
$updatedPost = $wordpress->updatePost(123, [
    'title' => '更新后的标题',
    'content' => '更新后的内容',
]);

// 删除文章
$result = $wordpress->deletePost(123);
```

### 3. 页面管理

```php
// 获取页面列表
$pages = $wordpress->listPages([
    'per_page' => 5,
]);

// 获取单个页面
$page = $wordpress->retrievePage(456);

// 创建新页面
$newPage = $wordpress->createPage([
    'title' => '关于我们',
    'content' => '公司介绍...',
    'status' => 'publish',
]);

// 更新页面
$updatedPage = $wordpress->updatePage(456, [
    'content' => '更新后的内容...',
]);
```

### 4. 媒体管理

```php
// 获取媒体列表
$media = $wordpress->listMedia([
    'per_page' => 20,
    'media_type' => 'image',
]);

// 获取单个媒体
$mediaItem = $wordpress->retrieveMedia(789);

// 上传媒体文件
$uploadedMedia = $wordpress->createMedia([
    'file' => '/path/to/image.jpg',
    'title' => '图片标题',
    'caption' => '图片说明',
    'alt_text' => '替代文本',
]);

// 更新媒体信息
$updatedMedia = $wordpress->updateMedia(789, [
    'title' => '新标题',
    'alt_text' => '新替代文本',
]);

// 删除媒体
$result = $wordpress->deleteMedia(789);
```

### 5. 分类和标签管理

```php
// 获取分类列表
$categories = $wordpress->listCategories();

// 获取标签列表
$tags = $wordpress->listTags();

// 创建新分类
$newCategory = $wordpress->createCategory([
    'name' => '技术文章',
    'slug' => 'tech',
    'description' => '技术相关文章',
]);

// 创建新标签
$newTag = $wordpress->createTag([
    'name' => 'PHP',
    'slug' => 'php',
    'description' => 'PHP 相关文章',
]);
```

### 6. 用户管理

```php
// 获取用户列表
$users = $wordpress->listUsers([
    'per_page' => 10,
    'role' => 'administrator',
]);

// 获取单个用户
$user = $wordpress->retrieveUser(1);

// 创建新用户
$newUser = $wordpress->createUser([
    'username' => 'newuser',
    'email' => 'newuser@example.com',
    'password' => 'secure_password',
    'name' => '新用户',
    'roles' => ['subscriber'],
]);

// 更新用户信息
$updatedUser = $wordpress->updateUser(1, [
    'name' => '更新后的用户名',
    'email' => 'updated@example.com',
]);
```

### 7. 评论管理

```php
// 获取评论列表
$comments = $wordpress->listComments([
    'post' => 123, // 文章 ID
    'per_page' => 20,
]);

// 获取单个评论
$comment = $wordpress->retrieveComment(999);

// 创建新评论
$newComment = $wordpress->createComment([
    'post' => 123,
    'author_name' => '评论者',
    'author_email' => 'commenter@example.com',
    'content' => '评论内容',
]);

// 更新评论
$updatedComment = $wordpress->updateComment(999, [
    'content' => '更新后的评论内容',
]);

// 删除评论
$result = $wordpress->deleteComment(999);
```

### 8. 搜索功能

```php
// 搜索文章
$searchResults = $wordpress->search([
    'search' => '关键词',
    'per_page' => 10,
    'type' => 'post', // 搜索类型：post, page, media 等
]);
```

### 9. 自定义端点

```php
// 调用自定义 WordPress REST API 端点
$customData = $wordpress->custom->get('custom-endpoint', [
    'param1' => 'value1',
    'param2' => 'value2',
]);

// POST 请求到自定义端点
$response = $wordpress->custom->post('custom-endpoint', [
    'data' => '要发送的数据',
]);
```

## 错误处理

```php
try {
    $posts = $wordpress->listPosts();
} catch (Throwable $e) {
    // 记录错误日志
    \PhalApi\DI()->logger->error('WordPress API 错误', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
    
    // 返回错误信息
    return [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
    ];
}
```

## 分页处理

WordPress REST API 返回的分页信息会自动提取：

```php
$result = $wordpress->listPosts(['per_page' => 10]);

// 结果结构
[
    'data' => [...], // 文章数据
    'pagination' => [
        'total' => 100,      // 总记录数
        'total_pages' => 10, // 总页数
        'current_page' => 1, // 当前页
        'per_page' => 10,    // 每页数量
    ],
]
```

## 认证方式

### JWT 认证（推荐）

WordPress 5.6+ 支持 Application Password 功能：

1. 在 WordPress 后台生成 Application Password
2. 使用生成的密码作为 JWT token
3. 配置 `auth` 为 `'jwt'`

### Basic 认证

传统的基本认证方式：

1. 配置 `auth` 为 `'basic'`
2. 提供 WordPress 用户名和密码

## 配置选项

| 选项 | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| url | string | 必填 | WordPress REST API 地址 |
| api_key | string | 必填 | API 密钥 |
| api_secret | string | 必填 | API 密钥 |
| auth | string | 'jwt' | 认证方式：'jwt' 或 'basic' |
| jwt_token | string | '' | JWT token |
| basic_user | string | '' | Basic 认证用户名 |
| basic_pwd | string | '' | Basic 认证密码 |
| options.version | string | 'wp/v2' | API 版本 |
| options.verify_ssl | bool | false | SSL 验证 |
| options.timeout | int | 10 | 请求超时时间（秒） |

## 注意事项

1. **SSL 验证**：开发环境可以关闭 SSL 验证（`verify_ssl => false`），生产环境应设为 `true`
2. **超时设置**：根据网络情况调整超时时间
3. **错误处理**：建议使用 try-catch 包装所有 API 调用
4. **分页**：大量数据时使用分页参数避免性能问题
5. **缓存**：频繁访问的数据建议添加缓存机制

## 支持的 WordPress REST API 端点

- `/wp/v2/posts` - 文章管理
- `/wp/v2/pages` - 页面管理
- `/wp/v2/media` - 媒体管理
- `/wp/v2/categories` - 分类管理
- `/wp/v2/tags` - 标签管理
- `/wp/v2/users` - 用户管理
- `/wp/v2/comments` - 评论管理
- `/wp/v2/taxonomies` - 分类法管理
- `/wp/v2/search` - 搜索功能

## 版本要求

- PHP 8.0+
- PhalApi 2.x
- WordPress 5.6+（推荐）

## 许可证

本项目使用木兰宽松许可证第2版（Mulan PSL v2）。