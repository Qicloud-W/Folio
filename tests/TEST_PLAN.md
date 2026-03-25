# Folio 最小功能测试计划

## 覆盖目标

当前测试覆盖最小主链路上的 3 个关键行为：

- `GET /health`
- `GET /api/v1/ping`
- 未命中路由时的 `404` JSON 结构

## 当前测试策略

当前仓库已具备最小 `Request -> Kernel -> Router -> Response` 链路，因此测试继续采用 **通过 `public/index.php` 走真实入口的黑盒 Feature 测试**，目的不是绕开主流程，而是直接验证实际启动结果。

测试方式：

1. 设置 `$_SERVER['REQUEST_METHOD']` 与 `$_SERVER['REQUEST_URI']` 模拟请求
2. `require public/index.php` 触发真实入口
3. 用输出缓冲捕获响应 body
4. 断言 `http_response_code()`、`headers_list()` 与 JSON 内容

这类测试当前能够覆盖：

- 入口文件加载
- 自动加载注册
- `Kernel` 启动
- 配置读取
- `/health` 内置路由
- `routes/api.php` 注册的 `/api/v1/ping`
- Router 未命中时的 404 JSON

## 用例清单

### 1. `/health`

- 预期状态码：`200`
- 预期响应头：`Content-Type: application/json; charset=utf-8`
- 预期 body：

```json
{
  "status": "ok",
  "app": "Folio",
  "env": "local"
}
```

说明：`env` 字段来自 `config/app.php` / `.env`，当前实现会随配置变化。

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

说明：该路由由 `routes/api.php` 注册，`message` 来自 `resources/lang/zh-CN/messages.php`。

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
composer test
composer cs:check
```

## CI 对应检查

GitHub Actions 中的 PHP CI workflow 会执行：

1. `setup-php`
2. `composer install`
3. `composer test`
4. `composer cs:check`

因此本测试计划与仓库 CI 保持一致，不再是“只写文档不落地”的状态。

## 当前已知限制

- 仍属于最小黑盒 Feature 覆盖，暂未细分更底层的 Router / Config / Lang 单元测试
- 当前测试依赖 PHP CLI 的 `header()` / `headers_list()` 行为，后续如果引入专门测试响应对象，可减少对全局状态的依赖
- 异常处理路径目前未单独补充失败注入测试

## 结论

本轮测试计划与当前代码实现一致：

- `/health` 由 `Kernel` 内注册
- `/api/v1/ping` 由 `routes/api.php` 注册
- 404 由 `Router` 统一返回
- 文档、测试命令与 CI 检查项已对齐
