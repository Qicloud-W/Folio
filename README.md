# Folio

Folio 是一个**面向国人**、**API 优先**、**轻量可扩展**的 PHP 应用框架骨架。

当前仓库已经打通最小可运行主链路：`public/index.php -> Request -> Kernel -> Router -> Response`，并接入了配置加载、`.env` 读取、基础 i18n 文案读取，以及通过 `routes/api.php` 注册 API 路由的能力。

## 当前 MVP 能力

- `/health`：健康检查 JSON
- `/api/v1/ping`：基础 API 探活 JSON
- 未命中路由：统一 404 JSON
- `config/*.php`：配置读取
- `.env`：基础环境变量装载
- `resources/lang/zh-CN/messages.php`：语言文案读取
- `routes/api.php`：API 路由注册入口

## MVP 范围

当前版本聚焦 5 件事：

1. **HTTP 入口与请求捕获**
2. **Kernel 启动与配置加载**
3. **Router 分发与统一 JSON 响应**
4. **基础扩展点预留（app/Providers、routes、lang）**
5. **最小自动化测试与治理基线**

## 当前目录结构

```text
app/
bootstrap/
config/
public/
resources/
  lang/
routes/
src/
  Core/
tests/
docs/
```

## 本地开发

### 运行要求

- PHP 8.2+
- Composer 2+

### 安装

```bash
git clone https://github.com/Qicloud-W/Folio.git
cd Folio
cp .env.example .env
composer install
```

### 启动开发服务器

```bash
php -S 127.0.0.1:9501 -t public
```

然后访问：

- `http://127.0.0.1:9501/health`
- `http://127.0.0.1:9501/api/v1/ping`

### 运行测试与检查

```bash
composer test
composer cs:check
```

## 请求与路由说明

### `GET /health`

返回应用基础健康状态：

```json
{
  "status": "ok",
  "app": "Folio",
  "env": "local"
}
```

其中 `app` 与 `env` 来自 `config/app.php` / `.env`。

### `GET /api/v1/ping`

通过 `routes/api.php` 注册，返回：

```json
{
  "message": "pong",
  "locale": "zh-CN"
}
```

其中 `message` 来自 `resources/lang/<locale>/messages.php`，`locale` 来自应用配置。

### 未命中路由

返回统一 404 JSON：

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Route not found"
  }
}
```

## 环境变量策略（当前版）

- 优先读取项目根目录 `.env`
- 其次读取系统环境变量
- `config/*.php` 中通过 `env()` 获取值
- 当前故意保持轻量，只覆盖 MVP 所需能力，不引入外部 dotenv 依赖

## i18n 说明

当前仅接入最小语言文件读取能力：

- `resources/lang/zh-CN/messages.php`
- `Lang::get($locale, $group, $key, $default)`

目前只用于 `/api/v1/ping` 的 `pong` 文案读取，完整国际化能力放在后续版本推进。

## 测试与 CI

仓库当前包含：

- Feature 测试：覆盖 `/health`、`/api/v1/ping`、404 JSON
- Composer 脚本：`composer test`、`composer cs:check`、`composer cs:fix`
- GitHub Actions：PR 打开/更新时执行治理检查与 PHP CI

CI 当前会执行：

1. `setup-php`
2. `composer install`
3. `composer test`
4. `composer cs:check`

## 已知限制

- 目前仅支持最小静态路由映射，尚未引入中间件链
- 异常处理为基础实现，尚未统一异常类型与渲染策略
- 自动加载目前由入口文件中的轻量 PSR-4 注册完成，后续可收敛为更正式的 bootstrap 流程

## 治理基线

仓库已补充第一轮治理产物（MVP）：

- `docs/governance-development-flow.md`
- `docs/governance-issue-lifecycle.md`
- `docs/governance-change-policy.md`
- `docs/governance-blocker-policy.md`
- `docs/governance-labels-and-status.md`
- `docs/governance-automation-mvp.md`
- `docs/governance-state-machine.yaml`

这些内容用于为后续自动检查、自动派单、自动 merge / release 门禁打地基；当前仅落地 MVP，不代表完整自动编排已实现。

## 路线图（首轮）

- `0.1.0-alpha`：最小可跑通骨架 + 治理结构
- `0.2.0-alpha`：中间件链、异常处理增强、bootstrap 收敛
- `0.3.0-alpha`：容器增强、Provider 生命周期、i18n 初版

## License

MIT
