# Testing Round 2

## 测试1执行结论

目标接口：

- `/health`
- `/api/v1/ping`
- 404 JSON

## 本机阻塞

当前环境缺少：

- `php`
- `composer`

因此**无法在本机执行真实 PHP 进程测试或 phpunit**。

## 已完成的替代验证

1. 检查 `public/index.php` 已切换到 `Request -> Kernel -> Response` 启动链。
2. 检查 `Kernel` 已注册 `/health` 与 `/api/v1/ping`。
3. 检查 `Router::dispatch()` 对未命中路由返回 JSON 404。
4. 补写 `tests/Feature/SmokeTest.php`，为具备 PHP 的环境提供最小回归测试样例。

## 建议的下一步真实执行命令

在有 PHP/Composer 的机器上运行：

```bash
cp .env.example .env
composer install
composer test
php -S 127.0.0.1:9501 -t public
```

然后人工验证：

```bash
curl -i http://127.0.0.1:9501/health
curl -i http://127.0.0.1:9501/api/v1/ping
curl -i http://127.0.0.1:9501/not-found
```
