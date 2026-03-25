# Folio 最小功能测试计划

## 覆盖目标

- `GET /health`
- `GET /api/v1/ping`
- 未命中路由时的 `404` JSON 结构

## 当前测试策略

由于第一版骨架尚未引入完整 HTTP Kernel / Router / Test Client，先采用 **直接包含 `public/index.php` 的黑盒入口测试**：

1. 通过设置 `$_SERVER['REQUEST_URI']` 模拟请求路径
2. 使用输出缓冲捕获响应体
3. 使用 `http_response_code()` 与 `headers_list()` 断言状态码和响应头
4. 对 JSON body 做精确结构断言

## 用例清单

### 1. `/health`
- 预期状态码：`200`
- 预期响应头：`Content-Type: application/json; charset=utf-8`
- 预期 body：

```json
{
  "status": "ok",
  "app": "Folio"
}
```

### 2. `/api/v1/ping`
- 预期状态码：`200`
- 预期响应头：`Content-Type: application/json; charset=utf-8`
- 预期 body：

```json
{
  "message": "pong",
  "locale": "zh-CN"
}
```

### 3. 404 JSON
- 请求示例：`/missing-route`
- 预期状态码：`404`
- 预期响应头：`Content-Type: application/json; charset=utf-8`
- 预期 body：

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Route not found"
  }
}
```

## 可执行命令

```bash
cd /Users/qicloud/.openclaw/workspace/Folio
composer install
./vendor/bin/phpunit tests/Feature
# 或
composer test
```

## 当前阻塞

当前机器缺少：
- `php`
- `composer`

因此本轮无法在本机实际执行 PHPUnit。

## 前提检查结论

- `public/index.php` 已实现 `/health`、`/api/v1/ping`、404 JSON 响应。
- `routes/api.php` 仅声明了 `/api/v1/ping`，但当前入口文件并未真正读取该路由表；也就是说：
  - `/api/v1/ping` 现在能工作，依赖的是 `public/index.php` 中的硬编码判断。
  - `/health` 当前未在 `routes/api.php` 中声明，但仍能工作，因为同样写死在入口文件里。
- 因此本轮测试满足“功能验证”前提，但**尚未满足“路由定义与运行行为一致”**这一更高层要求。
