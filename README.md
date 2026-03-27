# Folio

Folio 是一个**面向国人**、**API 优先**、**轻量可扩展**的 PHP 应用框架 alpha 内核。

当前 `main` 已经不是“只有几个演示接口的骨架 README”，也还不是“内部主线完全唯一化的成熟框架”。更准确的说法是：**Folio 0.2.x-alpha 当前已经具备可运行的 HTTP 主链路、middleware pipeline、基础容器与 provider 装配、统一 JSON 异常出口，以及最小治理/测试基线。**

## 当前 main 事实

当前 `main` 已落地：

- `public/index.php` 作为唯一 public HTTP 入口
- `Folio\Core\Foundation\Application` 作为对外应用 facade
- `Folio\Core\Application\Application` 作为当前 runtime application 实现之一
- `routes/api.php` 路由注册入口
- `/health`、`/api/v1/ping`、404、405、500 的统一 JSON 行为
- 全局 middleware pipeline，支持顺序执行与短路返回
- 基础容器能力：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`
- provider 装配与 bootstrap 过程中的注册/boot
- `config/*.php`、`.env`、`resources/lang/*` 的最小读取能力
- PHPUnit、CS Fixer、GitHub Actions CI 与治理检查

当前 `main` 尚未承诺：

- 唯一内部 runtime 主线已经最终定稿
- 参数路由、路由分组、命名路由、控制器自动映射
- request validation、session、auth、cache、queue、db、console 等基础设施
- 稳定完整的外部扩展生态

## 当前可直接验证的行为

### `GET /health`

返回应用健康状态 JSON，并包含 alpha middleware 注入的 request trace：

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

## alpha 对外口径

Folio 0.2.x-alpha 当前可以对外承诺的是：

- 有稳定的 public HTTP 入口
- 有稳定的 Application facade / container / provider / routes / JSON exception 基本边界
- 有可工作的 middleware pipeline 与最小 bootstrap 装配能力
- 但 runtime 内部实现仍在收口，**不能**把当前某一条内部调用链写成最终稳定 ABI

详细边界见：

- `docs/architecture-alpha-kernel-boundary-2026-03-27.md`
- `docs/product-maturity-roadmap-2026-03-26.md`
- `docs/audit/round2.md`

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

## 当前限制

- runtime 内部主线仍在收口，文档只冻结对外边界，不冻结唯一内部实现
- 路由能力当前以静态 path + method、404/405 JSON 口径为主
- 容器与 provider 只承诺 alpha 最小公开交互面，不额外扩散 `set()` / `has()` 等脏接口
- i18n 目前只覆盖最小语言文件读取与 ping 文案读取

## License

MIT
