# Folio alpha 内核边界冻结（2026-03-27）

> 这份文档只冻结 `0.2.x-alpha` 当前已经可以对外承诺的边界，不替内部 runtime 收口抢结论。

## 先说结论

Folio `0.2.x-alpha` 当前可以对外冻结的，是下面这些边界：

1. **唯一 public HTTP 入口**：`public/index.php`
2. **唯一对外 Application facade**：`Folio\Core\Foundation\Application`
3. **唯一 alpha provider 基类**：`Folio\Core\Support\ServiceProvider`
4. **唯一 alpha container 最小交互面**：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`
5. **唯一 alpha 路由注册入口**：`routes/api.php`
6. **唯一 alpha 错误出口口径**：404 / 405 / 500 统一 JSON
7. **唯一 alpha middleware 能力口径**：链式执行 + 短路返回 `Response`

同时必须明确：

- `src/Core/Application/Application.php`
- `src/Core/Kernel.php`
- `src/Core/Foundation/HttpKernel.php`

这些内部实现之间的最终主从关系，**当前还没有对外冻结**。因此 `0.2.x-alpha` 文档不能把其中任何一条内部实现路径写成最终稳定 ABI。

## 1. Public HTTP 入口

### 已冻结
- Web 请求从 `public/index.php` 进入。
- 入口职责保持最薄：autoload、capture request、调 application、send response。

### 未冻结
- 入口内部最终到底直接调 `Application::handle()`，还是显式调某个 `HttpKernel` 实现。
- bootstrap 细节是否继续藏在 facade 背后。

## 2. Application facade

### 已冻结
对外统一入口是：

- `Folio\Core\Foundation\Application::configure(string $basePath)`
- `->bootstrap()`
- `->handle(Request $request)`
- `->register(ServiceProvider|string $provider)`
- `->config()` / `->make()` / `->bind()` / `->singleton()` / `->instance()` / `->bound()`

### 未冻结
- facade 内部是否最终与某个 runtime application 一一对应。
- `container()` 是否进入正式 alpha 对外 contract。
- 任何内部 bridge 实现细节。

## 3. Container alpha contract

### 已冻结
alpha 对外只冻结下面 5 个容器动作：

- `bind()`
- `singleton()`
- `instance()`
- `make()`
- `bound()`

### 未冻结
- `set()`
- `has()`
- 随意扩散的 string alias
- 完整自动装配 / 反射注入语义

### 约束
- class-string key 优先于任意 string key。
- string key 当前只用于内核保留别名，如 `config`、`translator`、`basePath`。
- 不把实现里存在的脏接口继续外扩成 README 口径。

## 4. Service Provider alpha contract

### 已冻结
- 对外 provider 基类是 `Folio\Core\Support\ServiceProvider`
- 生命周期只承诺两阶段：
  - `register()`：注册绑定
  - `boot()`：bootstrap 后执行

### 未冻结
- 自动发现 provider
- 完整 deferred provider 机制
- 更多高级生命周期钩子

## 5. Routing alpha boundary

### 已冻结
- `routes/api.php` 是 alpha 当前唯一公开路由注册入口。
- 已注册 path + 错误 method 返回 405 JSON，并带 `Allow` header。
- 未命中 path 返回 404 JSON。
- 路由处理结果统一收敛到 `Response`。

### 未冻结
- 参数路由
- 路由分组
- 命名路由
- 自动控制器映射
- 当前 router 全部内部 API 的对外稳定性

## 6. Exception alpha boundary

### 已冻结
- HTTP 请求链路中的未处理异常最终收敛为 JSON error response。
- `APP_DEBUG=false` 时不泄漏内部异常细节。
- 404 / 405 / 500 是当前 alpha 必须稳定的错误口径。

### 未冻结
- 最终由谁负责渲染异常的内部分工。
- 完整异常类型体系是否定稿。

## 7. Middleware alpha boundary

### 已冻结
- alpha 已具备 middleware pipeline 能力。
- middleware 支持链式顺序执行。
- middleware 支持短路返回 `Response`。

### 未冻结
- route middleware / group middleware / priority 等高级机制。
- pipeline 内部实现是否继续调整。

## 8. 对 README / roadmap / audit 的同步要求

为了不让文档继续互相打架，`0.2.x-alpha` 对外文档必须统一成下面口径：

- README 说“当前 `main` 已有 middleware / container 最小面 / provider / JSON exception 出口”
- roadmap 只写 `0.2.x-alpha` 当前事实与承诺，不混 P1 / P2 愿景
- audit 只写当前 `main` 的真实风险，不复读已经过期的旧问题

## 9. 一句话口径

Folio alpha 当前可以对外承诺的是：

- 有稳定的 public HTTP 入口
- 有稳定的 Application facade / container / provider / routes / JSON exception 基本边界
- 有可工作的 middleware pipeline
- 但 runtime 内部实现仍在收口，尚未承诺唯一内部主线
