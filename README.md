# LaravelTest2.1——LaraBBS

## 创建应用

- Composer 加速
  ```
  composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
  ```
- 创建应用
  ```
  composer create-project laravel/laravel laravel2.1 --prefer-dist "6.*"
  ```
- 修改配置信息
  `.env` 文件中
  ```
    APP_NAME=LaraBBS
    APP_URL=http://larabbs.test
  ```
  `config/app.php` 文件中
  ```
    'timezone' => 'Asia/Shanghai', // 时区
    'locale' => 'zh-CN', // 默认语言
  ```
- 自定义辅助函数
  新增辅助函数文件
  ```
    touch app/helpers.php
  ```
  然后再 `composer.json` 文件中的 autoload 选项中加入：`"files": ["app/helpers.php"]`
  最后别忘了执行：`composer dump-autolaod` 以加载该文件