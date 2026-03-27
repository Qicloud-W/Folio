# P0 tail debt：core foundation 收口与边界现状（2026-03-27）

> 只基于当前 `main` 真相。不是愿景文档。

## 结论先说

当前 `main` 在 PR #23 合并后，P0 最大尾债不是“少一个功能”，而是 **runtime 主链路重复、Application/Kernel 双轨并存、Container/Provider/Exception/Middleware 的边界还没完全收口**。

如果现在继续开 P1 新功能，只会把脏边界固化。

## 1. 当前运行时真实结构

### 仍并存的两套 Application 语义
- `src/Core/Application/Application.php`
  - 持有底层 `Container`
  - 自己做 `bootstrap()`
  - 自己注册 provider 并 `boot()`
  - 自己在 `handle()` 内做 route 装载、middleware pipeline、exception render
- `src/Core/Foundation/Application.php`
  - 实现 `Contracts\\Foundation\\Application`
  - 作为 facade/bridge 包装 base application
  - 再次承担 provider 注册与部分 container API 暴露

### 仍并存的多条 runtime / kernel 路径
- `src/Core/Kernel.php`
  - 直接 new 旧 `Application`
  - 自带异常渲染逻辑
- `src/Core/Foundation/HttpKernel.php`
  - 依赖新 `Contracts\\Foundation\\Application`
  - 再次负责 bootstrap / middleware / exception handler
- `src/Core/Application/Application::handle()`
  - 也是一条完整 runtime 路径

结论：**官方 runtime 入口还没真正唯一化。**

## 2. Container API / contract 现状

### 已有事实
- 实现：`src/Core/Container/Container.php`
  - `bind()` / `singleton()` / `instance()` / `make()` / `bound()` / `has()` / `set()`
- contract：`src/Core/Contracts/Container/Container.php`
  - `bind()` / `singleton()` / `instance()` / `make()` / `bound()`

### 当前脏点
1. **contract 与实现能力不完全对齐**
   - 实现有 `has()` / `set()`，contract 没有
   - `Foundation\\Application` 只能桥接部分 API
2. **命名语义未收口**
   - `bound()` 与 `has()` 是否同义，没写清
   - `set()` 与 `instance()` 是否都应公开，没写清
3. **服务 key 风格混用**
   - class key：`ConfigRepository::class` / `Router::class` / `Lang::class`
   - string key：`config` / `translator` / `basePath`
   - 哪些允许公开依赖、哪些只是内部别名，没冻结

结论：**Container 已能跑，但公开面还是半成品。**

## 3. Provider 生命周期与 bootstrap 边界现状

### 已有事实
- base application 的 `bootstrap()`：
  - load env
  - load config
  - set router
  - register `AppServiceProvider`
  - 遍历 providers 执行 `boot()`
- foundation application 的 `bootstrap()`：
  - 调 base application `bootstrap()`
  - 再额外 `register(TranslationServiceProvider::class)`

### 当前脏点
1. **provider 注册入口不唯一**
   - 有的在 base bootstrap 里注册
   - 有的在 foundation bootstrap 后补注册
2. **register / boot 边界不稳定**
   - base application 有 boot provider 的职责
   - foundation application 的 `register()` 又承担 deferred-like 补偿行为
3. **路由装载未收口**
   - 旧 `Application::handle()` 内直接 require `routes/api.php`
   - `RoutingServiceProvider` 也能承担路由装载

结论：**Provider 机制已经出现，但生命周期还是松的。**

## 4. Middleware / Exception / Bootstrap 边界现状

### middleware
- 旧 `Application::handle()` 使用 `Http\\MiddlewarePipeline`
- 新 `Foundation\\HttpKernel` 使用 `Pipeline\\Pipeline`
- 两套 pipeline 并存

### exception
- 旧 `Application` 内置 `renderThrowable()`
- `Core\\Kernel` 也有自己的 `renderThrowable()`
- `Core\\Exceptions\\Handler` 是新 contract 路线下的官方 handler 候选

### bootstrap
- `Foundation\\HttpKernel::handle()` 会调用 `$app->bootstrap()`
- 旧 `Core\\Kernel` 构造时直接 `(new Application(...))->bootstrap()`
- 旧 `Application` 自己 handle 时又隐含 runtime 装配

结论：**bootstrap / middleware / exception 现在都不是单一入口。**

## 5. P0 优先级判断

### P0-最高：Application / Kernel 单一路径收敛
原因：这是所有尾债的总闸门。不先定唯一 runtime，container/provider/exception 边界永远会漂。

### P0-高：Middleware / Exception / Bootstrap 责任去重
原因：现在重复实现最明显，继续开发最容易造成行为分叉。

### P0-高：Provider 生命周期与 bootstrap 边界收口
原因：provider 已进入主链路，如果 register/boot/deferred 继续模糊，后面所有核心服务都会被污染。

### P0-中高：Container contract / API 收口
原因：已经能用，但越晚收口，越容易形成外部依赖债。

## 6. 建议顺序
1. 先做 **Application / Kernel 单一路径收敛**
2. 再做 **Middleware / Exception / Bootstrap 去重**
3. 再做 **Provider 生命周期 / bootstrap 边界收口**
4. 最后做 **Container contract / API 精修与文档冻结**

原因很简单：先定主链路，再定配套边界；别反过来。

## 7. 留痕
- 根 issue：#24
- 子 issue：#25 #26 #27 #28 #29
- 关联 PR：#23
