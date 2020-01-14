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

- 3.6 认证后的提示
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

## 4用户相关
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

## 帖子列表
  - [5.1帖子分类模型及真数据填充](https://learnku.com/courses/laravel-intermediate-training/6.x/post-categories/5558)
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
  - [5.2代码生成器（Laravel 5.x Scaffold Generator）](https://learnku.com/courses/laravel-intermediate-training/6.x/code-generator/5559)
    - 安装代码生成器： `composer require "summerblue/generator:6.*" --dev`

  - [5.3生成话题骨架](https://learnku.com/courses/laravel-intermediate-training/6.x/generate-topic/5560)
    - 
