# LaravelTest2.1——LaraBBS

## 2舞台布置

- Composer 加速
  ```
  composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
  ```

- 2.3 创建应用
  ```
  composer create-project laravel/laravel laravel2.1 --prefer-dist "6.*"
  ```

- 2.4 修改配置信息
  - `.env` 文件中
    ```
    APP_NAME=LaraBBS
    APP_URL=http://larabbs.test
    ```
  - `config/app.php` 文件中
    ```
    'timezone' => 'Asia/Shanghai', // 时区
    'locale' => 'zh-CN', // 默认语言
    ```

- 2.5 自定义辅助函数
  - 1.新增辅助函数文件
    ```
    touch app/helpers.php
    ```
  - 2.然后再 `composer.json` 文件中的 autoload 选项中加入：`"files": ["app/helpers.php"]`
  - 3.最后别忘了执行：`composer dump-autolaod` 以加载该文件

- 2.6 基础布局
  - 路由与控制器：Route::get('/', 'PagesController@root')->name('root');
  - 布局视图`resources`：layouts(app、_header、_footer), shared(_messages), pages(root)
  - 获取配置信息：app()->getLocale() 获取的是 config/app.php 中的 locale 选项（此处值为 zh-CN）
    ```
    <html lang="{{ app()->getLocale() }}">
    ```
  - csrf-token 标签是为了方便前端的 JavaScript 脚本获取 CSRF 令牌。
    ```
    <meta name="csrf-token" content="{{ csrf_token() }}">
    ```
  - 将「路由名称」当做「css类名」(route_class()定义为辅助函数)
    ```
    <div id="app" class="{{ route_class() }}-page">
    ```
  - 页面样式（安装bootstrap）
    ```
    composer require laravel/ui --dev
    php artisan ui bootstrap
    npm config set registry=https://registry.npm.taobao.org
    yarn config set registry 'https://registry.npm.taobao.org'
    yarn install --no-bin-links
    yarn add cross-env
    npm run dev
    ```

- 2.7 静态文件浏览器缓存问题
  - webpack.mix.js 中加入 .version(), 然后 default.blade.php 引用css和js时用 mix()函数