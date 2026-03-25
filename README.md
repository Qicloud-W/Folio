# Folio

Folio 是一个**面向国人**、**API 优先**、**轻量可扩展**的 PHP 应用框架骨架。

这版不是空喊口号，已经打通了第一刀最小主链路：`public/index.php -> Request -> Kernel -> Router -> Response`，并补上了配置加载、基础 env 策略与 `zh-CN` i18n 占位。

## 当前 MVP 能力

- `/health`：健康检查 JSON
- `/api/v1/ping`：基础 API 探活 JSON
- 未命中路由：统一 404 JSON
- `config/*.php`：配置读取
- `.env`：基础环境变量装载
- `resources/lang/zh-CN`：中文语言包占位

## MVP 范围

第一版只解决 5 件事：

1. **HTTP 入口与路由分发**
2. **配置系统**
3. **统一响应与异常处理（当前为基础版）**
4. **服务扩展点预留**
5. **测试与治理结构**

## 目录结构

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

### 运行测试

```bash
composer test
```

## 环境变量策略（当前版）

- 优先读取 `.env`
- 其次读取系统环境变量
- `config/*.php` 中通过 `env()` 获取值
- 当前故意保持轻量，只覆盖 MVP 所需能力，不引入复杂解析器

## i18n 说明

当前仅提供 `resources/lang/zh-CN/messages.php` 占位，用于验证目录与读取接口。完整国际化能力放在后续版本推进。

## 已知限制

- `routes/api.php` 还未接入自动注册
- 异常处理仍需统一封装
- 当前测试依赖本机具备 PHP / Composer

## 路线图（首轮）

- `0.1.0-alpha`：最小可跑通骨架 + 治理结构
- `0.2.0-alpha`：中间件链、route 文件加载、基础异常处理
- `0.3.0-alpha`：容器增强、Provider 生命周期、i18n 初版

## License

MIT
