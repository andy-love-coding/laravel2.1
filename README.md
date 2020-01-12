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

- 2.8 字体图标
  - 安装：yarn add @fortawesome/fontawesome-free
  - 载入，在 resources/sass/app.scss 中
    ```
    // Fontawesome
    @import '~@fortawesome/fontawesome-free/scss/fontawesome';
    @import '~@fortawesome/fontawesome-free/scss/regular';
    @import '~@fortawesome/fontawesome-free/scss/solid';
    @import '~@fortawesome/fontawesome-free/scss/brands';
    ```
  - 编译：npm run dev ，编译后在 `public/fonts/vender/../../ ` 中会有字体图标文件

## 注册登录
- 3.1 [用户认证脚手架](https://learnku.com/courses/laravel-intermediate-training/6.x/registration-and-login/5541)
  - 1.执行：php artisan ui:auth
  - 2.修改：routes/web.php 
    ```
    Auth::routes(); // 这一行可替换掉
    Route::get('/home', 'HomeController@index')->name('home'); // 这一行删除
    ```
  - 3.删除
    ```
    rm app/Http/Controllers/HomeController.php
    rm resources/views/home.blade.php
    ```
  - 4.本地化 (中文语言包)
    - composer require "overtrue/laravel-lang:~3.0"
    - confit/app.php中：Illuminate\Translation\TranslationServiceProvider::class 换成Overtrue\LaravelLang\TranslationServiceProvider::class ; 并设置 'locale' => 'zh-CN',
    - 如果想修改扩展包提供的语言文件，可以使用以下命令发布语言文件到 `resources/lang/zh-CN` 文件夹。
      ```
      php artisan lang:publish zh-CN
      ```

- 3.2 用户注册
  - 生成数据表: php artisan migrate
  - 替换 Auth 相关的跳转，即 `App/Providers/RouteServiceProvider.php` 中的 `public const HOME = '/home';` 为 `public const HOME = '/';`
  - 修改导航视图: _header: @guest()  @else  @endguest
  