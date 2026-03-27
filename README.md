# Folio

Folio 是一个**面向国人**、**API 优先**、**轻量可扩展**的 PHP 应用框架 `0.2.x-alpha` 内核。

当前 `main` 的对外口径应该只有一句人话：**已经能跑 HTTP 主链路，已经有 middleware / container / provider / JSON exception 的 alpha 边界，但内部 runtime 实现还在收口，别装成熟框架。**

## 当前 main 真相

当前 `main` 已确认落地：

- `public/index.php` 是唯一 public HTTP 入口
- `Folio\Core\Foundation\Application` 是唯一对外 Application facade
- `routes/api.php` 是当前公开路由注册入口
- `/health` 与 `/api/v1/ping` 可直接访问
- 未命中路由返回统一 404 JSON
- 已注册 path 使用错误 method 返回统一 405 JSON，并带 `Allow` header
- 未处理异常统一收敛为 JSON error response，`APP_DEBUG=false` 时不泄漏内部异常细节
- 已有全局 middleware pipeline，支持顺序执行与短路返回 `Response`
- 已有 alpha 最小容器交互面：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`
- 已有 provider 注册与 boot 生命周期初版
- 已有 `config/*.php`、`.env`、`resources/lang/*` 的最小读取能力
- 已有 PHPUnit、PHP-CS-Fixer、GitHub Actions PHP CI 与治理检查

当前 `main` 没承诺：

- 唯一内部 runtime 主线已经最终定稿
- 参数路由、路由分组、命名路由、控制器自动映射
- request validation、session、auth、cache、queue、db、console
- 面向外部生态的稳定扩展 ABI

## 当前可验证行为

### `GET /health`

返回应用健康状态 JSON，并包含 middleware 注入的 request trace：

```json
{
  "status": "ok",
  "app": "Folio",
  "env": "local",
  "meta": {
    "alpha": {
      "request_trace": "GET /health"
    }
  }
}
```

### `GET /api/v1/ping`

通过 `routes/api.php` 注册，返回：

```json
{
  "message": "pong",
  "locale": "zh-CN",
  "meta": {
    "alpha": {
      "request_trace": "GET /api/v1/ping"
    }
  }
}
```

### 未命中路由

返回统一 404 JSON。

### 错误 HTTP 方法

对已注册 path 使用错误 method 时返回统一 405 JSON，并带 `Allow` header。

### 未处理异常

HTTP 请求链路中的未处理异常会统一收敛为 JSON error response；`APP_DEBUG=false` 时不泄漏内部异常细节。

## 0.2.x-alpha 对外边界

当前对外只冻结这些边界：

- public HTTP 入口：`public/index.php`
- Application facade：`Folio\Core\Foundation\Application`
- provider 基类：`Folio\Core\Support\ServiceProvider`
- container 最小公开交互面：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`
- 路由注册入口：`routes/api.php`
- 错误出口口径：404 / 405 / 500 统一 JSON
- middleware 能力口径：链式执行 + 短路返回 `Response`

这里故意**不**冻结：

- `src/Core/Application/Application.php`
- `src/Core/Kernel.php`
- `src/Core/Foundation/HttpKernel.php`

它们之间谁是最终唯一内部主线，当前 `main` 还没资格对外吹成稳定 ABI。

## 当前目录结构

```text
app/
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

## 对应文档

- `docs/product-maturity-roadmap-2026-03-26.md`
- `docs/architecture-alpha-kernel-boundary-2026-03-27.md`
- `docs/audit/round2.md`

## 当前限制

- 文档只冻结 alpha 对外边界，不冻结唯一内部 runtime 实现
- 路由能力当前还是静态 path + method、404/405 JSON 这一层
- 容器与 provider 只承诺 alpha 最小公开交互面，不额外扩散 `set()` / `has()` 一类实现细节
- i18n 目前只覆盖最小语言文件读取与 ping 文案读取

## License

MIT
