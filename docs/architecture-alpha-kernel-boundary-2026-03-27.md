# Folio alpha 内核边界冻结（2026-03-27）

> 只冻结当前 alpha 应承诺的边界；不替 #25 的 runtime 主线收敛做决定。

## 先说结论

本轮可以先冻结的，不是“最终唯一 runtime 实现”，而是 **alpha 阶段对外可依赖的内核边界**：

1. **唯一 public HTTP 入口**：`public/index.php`
2. **唯一对外 Application facade**：`Folio\Core\Foundation\Application`
3. **唯一 alpha HTTP kernel contract**：`Folio\Core\Contracts\Http\Kernel`
4. **唯一 alpha provider 基类**：`Folio\Core\Support\ServiceProvider`
5. **唯一 alpha container 交互面**：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`
6. **唯一 alpha 路由注册入口**：`routes/api.php`
7. **唯一 alpha 异常出口**：统一返回 JSON error response，不允许向 HTTP 响应泄漏未处理 Throwable

同时明确：

- `src/Core/Application/Application.php`
- `src/Core/Kernel.php`
- `src/Core/Foundation/HttpKernel.php`

这三者的运行时分工 **仍在收口中**，因此现在**不能**把其中任一条内部实现路径宣称为最终稳定 ABI。

---

## 1. alpha 已冻结的对外边界

## 1.1 Public HTTP 入口

### 冻结内容
- Web 请求从 `public/index.php` 进入。
- 入口职责保持最薄：autoload / capture request / 调 application / send response。

### 不承诺内容
- 不承诺未来入口内部是否直接调 `Application::handle()` 还是显式调 `HttpKernel`。
- 不承诺 bootstrap 细节在入口层可见。

### 原因
对外部署和示例项目需要稳定入口；但 runtime 主线仍由 #25 收口，不能现在锁死内部调用链。

---

## 1.2 Application facade

### 冻结内容
alpha 对外统一以：
- `Folio\Core\Foundation\Application::configure(string $basePath)`
- `->bootstrap()`
- `->handle(Request $request)`
- `->register(ServiceProvider|string $provider)`
- `->config()` / `->make()` / `->bind()` / `->singleton()` / `->instance()` / `->bound()`

作为应用装配与扩展入口。

### 不承诺内容
- 不承诺其内部一定直接等价于当前 `BaseApplication`。
- 不承诺 `container()` 是否进入正式 contract；当前它可以作为 bridge 能力存在，但不应视为 alpha 稳定公开契约。

### 原因
当前 `Foundation\Application` 已经是 public entry facade；`BaseApplication` 仍带明显 runtime 装配细节，不适合直接对外冻结。

---

## 1.3 Container alpha contract

### 冻结内容
alpha 对外只冻结下面 5 个容器动作：
- `bind()`
- `singleton()`
- `instance()`
- `make()`
- `bound()`

这与 `src/Core/Contracts/Container/Container.php` 当前契约一致。

### 明确不冻结
- `has()`
- `set()`
- 任意 string alias 体系
- 自动装配/反射注入的完整行为

### 约束
- class-string key 优先于随意 string key。
- string key 仅允许用于内核保留别名，如 `config`、`translator`、`basePath`；新增别名必须先补文档和测试。
- `bound()` 作为 alpha 公开存在性判断；不要再额外对外推广 `has()` 双轨语义。

### 原因
容器实现已经比 contract 多出 `set()` / `has()`，但 contract 尚未收口。alpha 先冻结最小面，免得外部依赖脏接口。

---

## 1.4 Service Provider alpha contract

### 冻结内容
- 对外 provider 基类是 `Folio\Core\Support\ServiceProvider`
- 生命周期只承诺两阶段：
  - `register()`：注册绑定，不依赖请求上下文
  - `boot()`：在应用 bootstrap 后执行

### 约束
- provider 依赖注入统一走底层 `Container`，不再把 `Foundation\Application` 当作 provider 构造参数契约。
- `provides()` 仅作为 deferred/桥接优化提示，不作为 alpha 必备 contract。

### 不承诺内容
- 不承诺自动发现 provider。
- 不承诺完整 deferred provider 语义。
- 不承诺 provider boot 顺序以外的高级生命周期钩子。

### 原因
这是本轮最值钱的去重点：provider 责任应从 application facade 抽离，直接依赖 container，避免 bootstrap / facade / provider 三者互相缠死。

---

## 1.5 Routing alpha boundary

### 冻结内容
- alpha 路由注册文件入口是 `routes/api.php`
- 路由结果必须统一返回 `Response`
- 已注册 path + 错误 method 应返回 405 JSON，并带 `Allow` header
- 未命中 path 返回 404 JSON

### 不承诺内容
- 不承诺参数路由、分组、命名路由、自动控制器映射
- 不承诺当前 `Router` 的全部内部 API 对外稳定

### 原因
现在真正稳定的是“路由注册入口 + 404/405 行为口径”，不是整套路由 DSL。

---

## 1.6 Exception alpha boundary

### 冻结内容
- HTTP 请求链路中的未处理异常，最终必须收敛为 JSON error response。
- `APP_DEBUG=false` 时不泄漏内部异常细节。
- 404 / 405 / 500 是 alpha 必须稳定的错误口径。

### 不承诺内容
- 不承诺最终由 `Core\Kernel`、`BaseApplication` 还是 `Exceptions\Handler` 渲染。
- 不承诺完整异常类型体系已定稿。

### 原因
异常出口必须先统一，内部 handler 实现可以继续重构；反过来不行。

---

## 1.7 Middleware alpha boundary

### 冻结内容
- alpha 已承诺存在 middleware pipeline 能力。
- middleware 必须支持链式顺序执行与短路返回 Response。

### 不承诺内容
- 不承诺最终只保留 `Http\MiddlewarePipeline` 还是 `Pipeline\Pipeline` 某一个实现。
- 不承诺 route middleware / group middleware / priority 等高级机制。

### 原因
当前重复的是 pipeline 实现，不是 middleware 能力本身。先冻结能力边界，留实现收口给 #25 / #28。

---

## 2. 本轮已完成的责任去重点

基于当前分支事实，本轮已经能确认的去重方向有：

### 2.1 Provider 构造依赖去重
- `ServiceProvider` 构造参数收口为 `Container`
- `TranslationServiceProvider` / `RoutingServiceProvider` 改为直接依赖容器
- `Foundation\Application::register()` 改为把真实 container 传给 provider

**收益**：
- provider 不再耦合 facade application
- provider 注册与底层容器绑定语义一致
- 为后续 bootstrap / runtime 收口减掉一层重复职责

### 2.2 Legacy facade bridge 补测
- `tests/Feature/ApplicationInfrastructureTest.php`
- `tests/Unit/Providers/LegacyProviderBridgeTest.php`

已经开始覆盖：
- facade bootstrap 后 provider 是否真实落到底层 container
- translator / router 等 binding 是否通过 bridge 可解析

**收益**：
- 先把桥接行为钉住，避免后续 runtime 收口时把 provider bridge 弄坏

---

## 3. 本轮明确不碰的边界

下面这些必须留给 #25 主线，不在本轮强行拍板：

1. `Core\Kernel` 是否删除、保留、还是完全转调 `Foundation\HttpKernel`
2. `BaseApplication::handle()` 是否继续保留完整 runtime 能力
3. `Foundation\HttpKernel` 是否成为唯一实际 HTTP kernel 实现
4. `Pipeline\Pipeline` 与 `Http\MiddlewarePipeline` 的最终二选一
5. `Exceptions\Handler` 与旧 `renderThrowable()` 的最终主从关系

原因很简单：这些属于 **runtime 主线收敛**，硬抢就是越权。

---

## 4. alpha 期间的强约束

在 #25 没收口前，新增代码应遵守：

1. **不要新增第三套 runtime 入口**
2. **不要新增 provider 对 application facade 的直接构造依赖**
3. **不要新增 container 公共 API**（尤其 `set()` / `has()` 外溢）
4. **不要把 README/文档写成“内核已经唯一化”**
5. **新增能力优先挂在已冻结边界上**：Application facade / provider base / routes/api.php / JSON exception boundary

---

## 5. 给 alpha 的一句话口径

Folio alpha 当前可以对外承诺的是：

- 有稳定的 public HTTP 入口
- 有稳定的 Application facade / container / provider / routes / JSON exception 基本边界
- 但 runtime 内核内部实现仍在收口，尚未承诺唯一内部主线

这才叫写人话，也没自欺欺人。
