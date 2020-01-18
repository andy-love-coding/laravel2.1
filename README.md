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

## 3注册登录

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

- 3.3 注册验证码
  - 安装：composer require "mews/captcha:~3.0"
  - 生成验证码配置文件：`config/captcha.php`, 运行一些命令
    ```
    php artisan vendor:publish --provider='Mews\Captcha\CaptchaServiceProvider' // 也可以省略 "--"参数，后续再用数字选择发布的文件
    // 执行发布配置文件，其实复制文件到 config 文件夹，即：Copied File [/vendor/mews/captcha/config/captcha.php] To [/config/captcha.php]
    ```
  - 前端显示，在 resources/views/auth/register.blade.php 中：captcha_src() 生成验证码图片链接
  - 后端验证，在app/Http/Controllers/Auth/RegisterController.php 中增加：'captcha' => ['required', 'captcha'], 及自定义翻译

- 3.5 [邮箱验证](https://learnku.com/courses/laravel-intermediate-training/6.x/email-verify/5545)
  - 移动 user modle: `mv app/User.php app/Models/User.php` , 修改User.php的命名空间为`App\Models` ,替换 `App\User` 为 `App\Models\User`
  - 修改 User 模型
    - 实现 `MustVerifyEmail` 契约
      ```
      use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
      class User extends Authenticatable implements MustVerifyEmailContract
      ```
    - 使用 `MustVerifyEmail` Trait
      ```
      use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
      class User extends Authenticatable implements MustVerifyEmailContract
      {
          use Notifiable, MustVerifyEmailTrait;
          ...
      }
      ```
    - 强制用户认证
      - 创建中间件：`php artisan make:middleware EnsureEmailIsVerified`
      - 注册中间件：app/Http/Kernel.php → `\App\Http\Middleware\EnsureEmailIsVerified::class,`

- 3.6 [认证后的提示](https://learnku.com/courses/laravel-intermediate-training/6.x/tips-after-certification/5546)
  - 验证过程
    ```
    app/Http/Controllers/Auth/VerificationController.php 
    → use Illuminate\Foundation\Auth\VerifiesEmails;
    → 验证成功，触发事件：event(new Verified($request->user()));
    → 注册事件：在 app/Providers/EventServiceProvider.php 中：\Illuminate\Auth\Events\Verified::class => [\App\Listeners\EmailVerified::class,]
    → 生成监听器：php artisan event:generate // 此命令将生成 app/Listeners/EmailVerified.php 监听器
    → 修改监听器（EmailVerified.php）：session()->flash('success', '邮箱验证成功 ^_^');
    ```

- 3.7 [重置密码成功的提示](https://learnku.com/courses/laravel-intermediate-training/6.x/password-reset/5547)
  - 在 app/Http/Controllers/Auth/ResetPasswordController.php 中重写 ResetsPasswords Trait中的方法 sendResetResponse
    ```
    use Illuminate\Http\Request;
    class ResetPasswordController extends Controller
    {
        use ResetsPasswords;
        protected $redirectTo = '/';
        public function __construct()
        {
            $this->middleware('guest');
        }
        protected function sendResetResponse(Request $request, $response)
        {
            session()->flash('success', '密码更新成功，您已成功登录！');
            return redirect($this->redirectPath());
        }
    }
    ```
    - $this->redirectPath() 将获取 $redirectTo 的值；
    - $redirectTo 跳转至 `app/Providers/RouteServiceProvider` 中定义的常量HOME `public const HOME = '/';`
  - 此处解题思路：这里用了重写 Trait 中的方法来实现，同样可以参照3.6章节，监听在Trait中触的发事件：event(new PasswordReset($user));

## 4 用户相关
  - 4.1 个人页面
 
  - 4.2 编辑个人资料
    - Users表中添加字段：avatar、introduction, 添加字段后，记得在User模型中添加 $fillable
    - 导航中增加入口：`<a class="dropdown-item" href="{{ route('users.edit', Auth::id()) }}">编辑资料</a>`
    - UsersController 中使用 [表单请求验证(FormRequest)](https://learnku.com/docs/laravel/6.x/validation/5144#form-request-validation): public function update(UserRequest $request, User $user)
      - 生成 UserRequest: `php artisan make:request UserRequest`
      - 编写生成 UserRequest 规则: public function rules(){return ['name' => 'required']}
      - 自定义翻译 UserRequest 报错信息: public function messages(return ['name.unique' => '用户名已被占用，请重新填写'])
    - 知识点:表单验证
      - `unique:table,column,except,idColumn` 在 table 数据表里检查 column ，除了 idColumn 为 except 的数据。except 一般在「更新」时使用。
      - 用 `with()` 代替 session()->flash(): 
        ```
        return redirect()->route('users.show', $user->id)->with('success', '个人资料更新成功！');
        ```
 
  - 4.4 上传头像
    - 创建工具类（图片处理器）：app/Handlers/ImageUploadHandler.php
    - 在类引入，即可使用：use App\Handlers\ImageUploadHandler;
    - git 版本管理中，忽略图片文件夹：public/uploads/images/avatars/.gitignore
      ```
      *
      !.gitignore
      ```
      上面的两行代码意为：当前文件夹下，忽略所有文件，除了 .gitignore。
 
  - 4.5 显示头像&现在图片分辨率
    - 在导航和个人页面中显示头像
    - 在表单验证类 UserRequest 中验证图片的类型和分辨率
      ```
      'avatar' => 'mimes:jpeg,bmp,png,gif|dimensions:min_width=208,min_height=208',     
      ```
 
  - 4.7 [裁剪头像](https://learnku.com/courses/laravel-intermediate-training/6.x/avatar-croping/5555)
    - 安装 [Intervention/image](https://github.com/Intervention/image) 扩展包来处理图片裁切的逻辑
      ```
      composer require intervention/image
      ```
    - 获取配置信息
      ```
      php artisan vendor:publish // 然后通过数字选择要发布的文件，也可以直接带上参数如：--provider="Intervention\Image\ImageServiceProviderLaravelRecent"
      ```
    - 修改图片处理类：app/Handlers/ImageUploadHandler.php
      ```php
      use Image;
      public function save($file, $folder, $file_prefix, $max_width = false)
      $image->resize($max_width, null, function ($constraint){}
      ```
 
  - 4.8 授权访问
    - 限制游客访问，在 UsersController 中： `$this->middleware('auth', ['except' => ['show']]);`
    - 只有自己能编辑自己，app/Policies/UserPolicy.php：`return $currentUser->id === $user->id;`

## 5帖子列表
  - 5.1 [帖子分类模型及真数据填充](https://learnku.com/courses/laravel-intermediate-training/6.x/post-categories/5558)
    - 创建分类模型 Category ，**-m** 表示创建模型的同时，顺便创建迁移文件（migration）
    ```
    php artisan make:model Models/Category -m
    ```
      - 分类模型 Category
      ```
      public $timestamps = false; // 数据表为生成时间戳，此处告知 Laravel 不需要维护 created_at 和 updated_at
      protected $fillable = [ 'name', 'description', ];
      ``` 
    - 填充：用迁移文件（migration）来填充分类数据，而不是用 seeder 填充文件来填充。
      原因是：填充文件一般用来填充假数据，生产环境不执行；而分类数据是真数据，所以放在迁移文件中来填充，迁移文件在生产环境中也会执行。
      ```
      php artisan make:migration seed_categories_data // 一般命名规范为：seed_(数据库表名称)_data
      ```
      database/migrations/{timestamp}_seed_categories_data.php 关键代码如下：
      ```
      up()中：DB::table('categories')->insert($categories);
      down()中：DB::table('categories')->truncate();
      ```
  
  - 5.2 [代码生成器（Laravel 5.x Scaffold Generator）](https://learnku.com/courses/laravel-intermediate-training/6.x/code-generator/5559)
    - 安装代码生成器： `composer require "summerblue/generator:6.*" --dev`

  - 5.3 [生成话题骨架](https://learnku.com/courses/laravel-intermediate-training/6.x/generate-topic/5560)
    - 生成骨架命令
      ```
      php artisan make:scaffold Topic --schema="
      title:string:index,body:text,
      user_id:bigInteger:unsigned:index,
      category_id:integer:unsigned:index,
      reply_count:integer:unsigned:default(0),
      view_count:integer:unsigned:default(0),
      last_reply_user_id:integer:unsigned:default(0),
      order:integer:unsigned:default(0),
      excerpt:text:nullable,slug:string:nullable"
      ```
 
  - 5.4 [填充用户和话题数据](https://learnku.com/courses/laravel-intermediate-training/6.x/seeding-data/5561)
    - TopicFactory
      ```
      // 随机取一个月以内的时间
      $updated_at = $faker->dateTimeThisMonth();
      // 传参为生成最大时间不超过，因为创建时间需永远比更改时间要早
      $created_at = $faker->dateTimeThisMonth($updated_at);
      ```
  
  - 5.5 话题列表页面
    - resources/views/topics/index.blade.php
    - resources/views/topics/_topic_list.blade.php
    - resources/views/topics/_sidebar.blade.php

  - 5.6 [性能优化(避免N+1问题)] (https://learnku.com/courses/laravel-intermediate-training/6.x/improve-performance/5563)
    - 安装 Debugbar 工具
      ```
      composer require "barryvdh/laravel-debugbar:~3.2" --dev // 主版本号.次版本号.修订号， ~ 表示: 3.2 <= 版本号 < 4.0
      ```
      - 发布
        ```
        php artisan vendor:publish // 选择对应数字后，Copied File [/vendor/barryvdh/laravel-debugbar/config/debugbar.php] To [/config/debugbar.php]
        ```
      - 设置 config/debugbar.php
        ```
        'enabled' => env('APP_DEBUG', false),
        ```
  
  - 5.7 [分类下的话题列表](https://learnku.com/courses/laravel-intermediate-training/6.x/category-topics/5564)
    - app/Http/Controllers/CategoriesController.php，分类类别共用 topics.index 视图
      ```
      return view('topics.index', compact('topics', 'category'));
      ```
    - 修改视图：topics._topic_list，topics.index
    - 因为 categories.show 与 topics.index 共用了视图，也需要共用css，所以需修改 resoures/sass/app.scss
      ```
      /* Topic Index Page */
      .topics-index-page, .categories-show-page { ...}
      ```
    - 修改网页title，在 topics.index 视图中：@section('title', isset($category) ? $category->name : '话题列表')
    - 增加顶部导航栏 及 导航栏 Active 状态
      - 安装 laravel active
        ```
        composer require "summerblue/laravel-active:6.*"
        ```
      - 利用 laravel active，在 app/helpers.php 创建一个辅助函数
        ```
        function category_nav_active($category_id)
        {
            return active_class((if_route('categories.show') && if_route_param('category', $category_id)));
        }
        ```
      - 在 _header.balde.php 中增加导航栏
      ```
      <li class="nav-item {{ active_class(if_route('topics.index')) }}"><a class="nav-link" href="{{ route('topics.index') }}">话题</a></li>
      <li class="nav-item {{ category_nav_active(1) }}"><a class="nav-link" href="{{ route('categories.show', 1) }}">分享</a></li>
      <li class="nav-item {{ category_nav_active(2) }}"><a class="nav-link" href="{{ route('categories.show', 2) }}">教程</a></li>
      ```
  
  - 5.8 [话题列表排序（本地作用域）](https://learnku.com/courses/laravel-intermediate-training/6.x/topic-order/5565)
    - 由于 Topics.index 和 Categories.show 中都要对 topic 进行排序，所以在 Topic 模型中定义**本地作用域**
      ```
      public function scopeWithOrder($query, $order)
      ```
    - TopicsController.php 中使用定义好的「本地作用域」
      ```
      $topics = $topic->withOrder($request->order)->with('user', 'category')->paginate(20);
      ```
    - CategoriesController 中使用定义好的「本地作用域」
      ```
      $topics = $topic->withOrder($request->order)->where('category_id', $category->id)->with('user', 'category')->paginate(20);
      ```
    - 修改视图 topics.index
      ```
      <a class="nav-link {{ active_class( ! if_query('order', 'recent')) }}" href="{{ Request::url() }}?order=default">
        最后回复
      </a>
      ```
      Request::url() 获取的是当前请求的 URL
    - [本地作用域](https://learnku.com/docs/laravel/6.x/eloquent/5176#4330c1)说明文档
  
  - 5.9 用户发布的话题列表
    - 导航栏加icon
    - User模型新增一对多关联模型：return $this->hasMany(Topic::class);
    - 修改视图 users.show
      ```
      @include('users._topics', ['topics' => $user->topics()->recent()->paginate(5)])
      ```
    - 新建视图：resources/views/users/_topics.blade.php
      ```
      <li class="list-group-item pl-2 pr-2 border-right-0 border-left-0 @if($loop->first) border-top-0 @endif">
      ```
## 6帖子的 CRUD
  - 6.1 [新建话题](https://learnku.com/courses/laravel-intermediate-training/6.x/new-posts/5568)
    - 新建话题入口：`_header.blade.php`、`views/opics/_sidebar.blade.php`
    - 数据模型 Topic：`protected $fillable = [ 'title', 'body', 'category_id', 'excerpt', 'slug' ];` 去掉了：user_id 等字段
    - 分类数据传入视图：`return view('topics.create_and_edit', compact('topic', 'categories'));`
    - 修改视图：resources/views/topics/create_and_edit.blade.php
    - TopicController 创建话题
      ```
      $topic->fill($request->all());      $topic->user_id = Auth::id();      $topic->save(); // fill 方法会将传参的键值数组填充到模型的属性中
      ```
    - [模型观察器](https://learnku.com/courses/laravel-intermediate-training/6.x/new-posts/5568#9a3609)
      - app/Observers/TopicObserver.php
        ```
        public function saving(Topic $topic)
        {
            $topic->excerpt = make_excerpt($topic->body); // make_excerpt() 是我们自定义的辅助方法
        }
        ```
        - app/helpers.php
          ```
          function make_excerpt($value, $length = 200)
          {
              $excerpt = trim(preg_replace('/\r\n|\r|\n+/', ' ', strip_tags($value)));
              return Str::limit($excerpt, $length);
          }
          ```
      - 修改表单验证类：app/Http/Requests/TopicRequest.php
 
  - 6.2 [simditor编辑器](https://learnku.com/courses/laravel-intermediate-training/6.x/editor/5569)
    - 下载 simditor-2.3.6 [github下载链接](https://github.com/mycolorway/simditor/releases)
    - 将下载的 simditor.css 放置于 `resources/editor/css` 文件夹
    - 将 hotkeys.js, module.js, simditor.js, uploader.js 四个文件放置于 `resources/editor/js` 文件夹中
    - 修改 webpack.mix.js，使用 [Mix]() 的 copyDirectory 方法，将编辑器的 CSS 和 JS 文件复制到 public 文件夹下
      ```
      mix.js('resources/js/app.js', 'public/js')
      .sass('resources/sass/app.scss', 'public/css')
      .version()
      .copyDirectory('resources/editor/js', 'public/js')
      .copyDirectory('resources/editor/css', 'public/css');
      ```
    - 在 resources/views/layouts/app.blade.php 中加锚点：  @yield('styles')、@yield('scripts')
    - 在 resources/views/topics/create_and_edit.blade.php 中加载 simditor 文件，并初始化 simditor 编辑器
      ```
      @section('styles')
      <link rel="stylesheet" type="text/css" href="{{ asset('css/simditor.css') }}">
      @stop

      @section('scripts')
        <script type="text/javascript" src="{{ asset('js/module.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/hotkeys.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/uploader.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/simditor.js') }}"></script>

        <script>
          $(document).ready(function() {
            var editor = new Simditor({
              textarea: $('#editor'),
            });
          });
        </script>
      @stop
      ```
 
  - 6.3 [simditor编辑器上传图片](https://learnku.com/courses/laravel-intermediate-training/6.x/upload-pictures/5570)
    - 路由：Route::post('upload_image', 'TopicsController@uploadImage')->name('topics.upload_image');
    - JS脚本设置编辑器，在resources/views/topics/create_and_edit.blade.php 中
      ```
      <script>
        $(document).ready(function() {
          var editor = new Simditor({
            textarea: $('#editor'),
            upload: {
              url: '{{ route('topics.upload_image') }}', // 处理上传图片的 URL；
              params: {
                _token: '{{ csrf_token() }}'
              },
              fileKey: 'upload_file', // 是服务器端获取图片的键值，我们设置为 upload_file;
              connectionCount: 3, // 最多只能同时上传 3 张图片；
              leaveConfirm: '文件上传中，关闭此页面将取消上传。'
            },
            pasteImage: true, // 设定是否支持图片黏贴上传，这里我们使用 true 进行开启
          });
        });
      </script>
      ```
    - 控制器处理图片，根据 [simditor编辑器上传图片文档](https://simditor.tower.im/docs/doc-config.html#anchor-upload)
      ```
      {
        "success": true/false,
        "msg": "error message", # optional
        "file_path": "[real file path]"
      }
      ```
      tips: 在 Laravel 的控制器方法中，如果直接返回数组，将会被自动解析为 JSON
    - public/uploads/images/topics/.gitignore，避免topics图片纳入 Git 版本控制中
      ```
      *
      !.gitignore
      ```
  
  - 6.4 [显示帖子](https://learnku.com/courses/laravel-intermediate-training/6.x/topics-show/5571)
    - 修改模板：resources/views/topics/show.blade.php
      - 在 resources/views/layouts/app.blade.php 中设置 SEO 的 description 锚点
        ```
        <title>@yield('title', 'LaraBBS') - Laravel 进阶教程</title>
        <meta name="description" content="@yield('description', 'LaraBBS 爱好者社区')" />
        ```
    - 新建样式：resources/sass/_topic_body.scss
      ```
      .simditor-body, .topic-body {...}
      ```
    - 在 resources/sass/app.scss 引入新建的样式 _topic_body.scss
      ```
      /* Topic Show Page */
      @import "topic_body";
      .topics-show-page {...}
      ```
  
  - 6.5 [XSS 安全漏洞](https://learnku.com/courses/laravel-intermediate-training/6.x/safety-problem/5572)
    - XSS 也称跨站脚本攻击 (Cross Site Scripting)，一种比较常见的 XSS 攻击是 Cookie 窃取，到你的 Cookie 以后即可伪造你的身份登录网站。
    - 有两种方法可以避免 XSS 攻击：
      - 第一种，对用户提交的数据进行过滤；
      - 第二种，Web 网页显示时对数据进行特殊处理，一般使用 htmlspecialchars() 输出。Laravel 的 Blade 语法 {{ }} 会自动调用 PHP htmlspecialchars 函数来避免 XSS 攻击。
    - 我们的话题内容不是用`{{ }}`转义输出的，而是用`{!! $topic->body !!}`原义输出的。所以必须用第一种方法，确保存储的数据就是安全的，而不是指望在输出时进行处理。
    - 虽然编辑器 Simditor 默认为我们的内容提供转义（存储时转义了），但是这样并不安全，因为存储内容并不一定要通过网页(编辑器)，比如通过chrome控制台也可以向服务器发送请求存储内容，此时就跳过了网页编辑器的转义过滤，存储了不安全的内容。
    - 使用 [HTMLPurifier](http://htmlpurifier.org/) 过滤数据，可以防止各种 XSS 变种攻击。
      - 安装：composer require "mews/purifier:~3.0"
      - 生成配置文件：php artisan vendor:publish // 数字选择，也可以 --provider="Mews\Purifier\PurifierServiceProvider"
      - 配置 config/purifier.php
        ```
        <?php
        return [
            'encoding'      => 'UTF-8',
            'finalize'      => true,
            'cachePath'     => storage_path('app/purifier'),
            'cacheFileMode' => 0755,
            'settings'      => [
                'user_topic_body' => [
                    'HTML.Doctype'             => 'XHTML 1.0 Transitional',
                    'HTML.Allowed'             => 'div,b,strong,i,em,a[href|title],ul,ol,ol[start],li,p[style],br,span[style],img[width|height|alt|src],*[style|class],pre,hr,code,h2,h3,h4,h5,h6,blockquote,del,table,thead,tbody,tr,th,td',
                    'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,margin,width,height,font-family,text-decoration,padding-left,color,background-color,text-align',
                    'AutoFormat.AutoParagraph' => true,
                    'AutoFormat.RemoveEmpty'   => true,
                ],
            ],
        ];
        ```
        配置里的 user_topic_body 是我们为话题内容定制的，配合 clean() 方法使用，如下
        在 app/Observers/TopicObserver.php：
        ```
        public function saving(Topic $topic)
        {
            // 用 HTMLPurifier 过滤内容，避免 XSS 攻击
            $topic->body = clean($topic->body, 'user_topic_body');

            $topic->excerpt = make_excerpt($topic->body);
        }
        ```
    - **服务器端原则是**：只要是用户提交的数据并且显示时不做 HTML 转义的，入库存储是都必须做 XSS 过滤。
    - [浅谈 XSS 攻击的那些事（附常用绕过姿势）](https://zhuanlan.zhihu.com/p/26177815)
  
  - 6.6 编辑帖子
    - TopicsController 中 edit() 方法传送 `categories` 变量到视图 `topics.create_and_edit` 中
    - `opics/create_and_edit.blade` 中选中与 $topic->category_id 一致的分类
      ```
      @foreach ($categories as $value)
        <option value="{{ $value->id }}" {{ $topic->category_id == $value->id ? 'selected' : '' }}>
          {{ $value->name }}
        </option>
      @endforeach
      ```
    - 权限控制 `app/Policies/TopicPolicy.php`：return $topic->user_id == $user->id;
 
  - 6.7 删除帖子
    - app/Models/User.php 中 编写可读性更强的代码
      ```
      public function isAuthorOf($model)
      {
          return $this->id == $model->user_id;
      }
      ```
    - app/Policies/TopicPolicy.php 中使用可读性更强的授权代码
      ```
      public function destroy(User $user, Topic $topic)
      {
          return $user->isAuthorOf($topic);
      }
      ```
    - resources/views/topics/show.blade.php 构建「删除表单」，并使用 @can 做「编辑和删除」的判断

  - 6.8 [SEO 友好的 URL(强制跳转)](https://learnku.com/courses/laravel-intermediate-training/6.x/seo-friendly-url/5575)
    - 翻译处理器 app/Handlers/SlugTranslateHandler.php
      - 安装依赖：[Guuzle](https://github.com/guzzle/guzzle)
        ```
        composer require "guzzlehttp/guzzle:~6.3"
        ```
      - 安装依赖 [PinYin](https://github.com/overtrue/pinyin)
        ```
        composer require "overtrue/pinyin:~4.0"
        ```
      - 配置百度翻译API信息，config/services.php
        ```
        return [
            ...
            'baidu_translate' => [
                'appid' => env('BAIDU_TRANSLATE_APPID'),
                'key'   => env('BAIDU_TRANSLATE_KEY'),
            ],
        ];
        ```
      - 配置 .env
        ```
        BAIDU_TRANSLATE_APPID=201703xxxxxxxxxxxxx
        BAIDU_TRANSLATE_KEY=q0s6axxxxxxxxxxxxxxxxx
        ```
      - 复制一份到 .env.example
        ```
        BAIDU_TRANSLATE_APPID=
        BAIDU_TRANSLATE_KEY=
        ```
    - 翻译调用 app/Observers/TopicObserver.php
      ```
      public function saving(Topic $topic)
      {
          // 如 slug 字段无内容，即使用翻译器对 title 进行翻译
          if ( ! $topic->slug) {
              $topic->slug = app(SlugTranslateHandler::class)->translate($topic->title);
          }
      }
      ```
    - 显示修改
      - 修改路由文件 web.php
        ```
        Route::get('topics/{topic}/{slug?}', 'TopicsController@show')->name('topics.show');
        ```
      - 新建 link() 方法，在 app/Models/Topic.php 中
        ```
        public function link($params = [])
        {
            return route('topics.show', array_merge([$this->id, $this->slug], $params));
        }
        ```
      - 全局搜索 'topics.show'
        1.修改控制器中的跳转
        ```
        把：    return redirect()->route('topics.show', $topic->id)->with('success', '成功创建话题！');
        修改成： return redirect()->to($topic->link())->with('success', '成功创建话题！');
        ```
        2.修改模板里的跳转
        ```
        把：    <a href="{{ route('topics.show', [$topic->id]) }}" title="{{ $topic->title }}">
        修改成： <a href="{{ $topic->link() }}" title="{{ $topic->title }}">
        ```
    - 强制跳转：TopicsController.php
      ```
      public function show(Request $request, Topic $topic)
      {
          // URL 矫正
          if ( ! empty($topic->slug) && $topic->slug != $request->slug) {
              return redirect($topic->link(), 301);
          }

          return view('topics.show', compact('topic'));
      }
      ```