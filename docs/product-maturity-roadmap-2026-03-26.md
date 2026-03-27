# Folio 0.2.x-alpha 对外路线口径（2026-03-26）

> 这份文档只服务一件事：把 `main` 当前已经落地、以及 alpha 当前明确承诺的边界写清楚。不是 PPT，不掺 P1/P2 愿景。

## 1. 当前 main 真相

当前 `main` 已有：

- `public/index.php` 作为唯一 public HTTP 入口
- `Folio\Core\Foundation\Application` 作为对外应用 facade
- runtime application、router、request、response 组成的最小 HTTP 主链路
- `routes/api.php` 路由注册入口
- `/health`、`/api/v1/ping`、404、405、500 的统一 JSON 行为
- 全局 middleware pipeline，支持链式执行与短路返回
- 基础容器能力：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`
- provider 注册与 boot 生命周期初版
- `config/*.php`、`.env`、`resources/lang/*` 的最小读取能力
- PHPUnit、CS Fixer、GitHub Actions CI、治理检查

当前 `main` 还没有：

- 已冻结为唯一内部实现的 runtime 主线
- 参数路由、路由分组、命名路由、自动控制器映射
- request validation、session、auth、cache、queue、db、console
- 完整稳定的外部扩展生态
- “成熟框架”级别的能力覆盖

结论：**Folio 当前是 0.2.x-alpha 内核，不是 0.1.x 的静态骨架，也不是已经定型的成熟框架。**

---

## 2. 0.2.x-alpha 当前承诺

Folio 0.2.x-alpha 当前对外只承诺下面这些：

1. **public HTTP 入口稳定**
   - `public/index.php`

2. **Application facade 稳定**
   - `Folio\Core\Foundation\Application`

3. **alpha container 最小公开交互面稳定**
   - `bind()`
   - `singleton()`
   - `instance()`
   - `make()`
   - `bound()`

4. **provider 生命周期初版稳定**
   - `register()`
   - `boot()`

5. **routes/api.php 注册入口稳定**
   - 当前 alpha 的公开路由入口就是它

6. **错误出口口径稳定**
   - 404 / 405 / 500 统一 JSON
   - `APP_DEBUG=false` 时不泄漏内部异常细节

7. **middleware 能力稳定到可用**
   - 支持链式顺序执行
   - 支持短路返回 `Response`

---

## 3. 0.2.x-alpha 当前不承诺

这些东西现在不能写成“已定稿”：

- 唯一 runtime 内部主线
- `src/Core/Application/Application.php`、`src/Core/Kernel.php`、`src/Core/Foundation/HttpKernel.php` 的最终主从关系
- 参数路由 / route group / named route / controller dispatch
- 自动发现 provider
- 完整 deferred provider 语义
- request validation / session / auth / cache / queue / db / console
- 面向外部生态的稳定扩展 ABI

一句话：**当前能公开承诺的是 alpha 对外边界，不是内部实现已经彻底收口。**

---

## 4. 当前最值得说的人话版本

如果要用一句话描述 Folio 现在是什么：

> Folio 当前是一个面向中文 API 场景的 PHP 框架 0.2.x-alpha 内核：已经具备可运行 HTTP 主链路、middleware pipeline、基础 container/provider 装配、统一 JSON 异常出口和最小治理/测试基线，但内部 runtime 主线仍在继续收口。

---

## 5. 文档收口原则

这一轮所有对外文档都应遵守：

- 只写 `main` 当前真相
- 不把未完成的内部收口写成“已经稳定”
- 不把 P1/P2 愿景掺进 0.2.x-alpha 说明
- 不再把 README 写成“还没 middleware / 还没容器 / 还没 provider”的旧口径
- 不再把 audit 写成“routes/api.php 还没接入”的过期结论

---

## 6. 对外对应文档

- `README.md`：项目定位、当前能力、alpha 口径
- `docs/architecture-alpha-kernel-boundary-2026-03-27.md`：alpha 对外边界冻结
- `docs/audit/round2.md`：基于当前 main 的事实审计结论
