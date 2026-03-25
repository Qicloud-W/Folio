# Folio

Folio 是一个**面向国人**、**中型**、**API 优先**、**轻量可扩展**的 PHP 应用框架骨架。

目标不是再造一个空泛大框架，而是提供一套能快速起项目、便于团队协作、天然适合接口服务与后台系统的工程起点。

## MVP 范围

第一版只解决 5 件事：

1. **HTTP 入口与路由分发**：支持 `/health`、`/api/v1/ping` 等基础接口。
2. **配置系统**：支持环境变量与 `config/*.php` 配置加载。
3. **统一响应与异常处理**：JSON 响应、错误码、基础异常捕获。
4. **服务容器与扩展点**：给后续数据库、缓存、队列、i18n 留出标准位置。
5. **测试与治理结构**：目录规范、贡献规范、CI、代码风格、Issue/PR 模板。

## 暂不进入 MVP

- ORM / 数据库抽象
- 队列、事件总线、定时任务
- 权限系统 / 用户系统
- 多语言完整实现（仅预留 `lang/` 与接口）
- 前后端一体化脚手架
- 微服务套件

## 设计原则

- **API First**：优先服务 REST/JSON 接口场景
- **Lean Core**：核心尽量薄，能力通过组件扩展
- **Convention with Escape Hatches**：给默认规范，也允许替换
- **China Friendly**：中文文档优先，目录命名和示例贴近国内团队使用习惯
- **i18n Ready**：预留国际化能力，而不是一开始堆复杂度

## 计划中的目录

```text
app/
  Application/
  Http/
    Controllers/
    Middleware/
  Providers/
bootstrap/
config/
public/
resources/
  lang/
routes/
src/
  Core/
tests/
  Feature/
  Unit/
docs/
.github/
```

## 路线图（首轮）

- `0.1.0-alpha`：最小可跑通骨架 + 治理结构
- `0.2.0-alpha`：请求/响应对象、中间件链、配置缓存雏形
- `0.3.0-alpha`：容器增强、Provider 生命周期、i18n 初版

## 本地启动（占位）

后续将支持：

```bash
composer install
php -S 127.0.0.1:9501 -t public
```

## License

MIT
