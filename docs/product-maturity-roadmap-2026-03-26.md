# Folio 从早期骨架走向成熟框架体：产品/技术路线（2026-03-26）

> 只基于当前 `main` 已落地事实制定，不拿 PPT 当代码。

## 0. 当前真相（作为路线起点）

当前 `main` 已有：
- 最小 HTTP 主链路：`Request -> Kernel -> Router -> Response`
- 静态 GET 路由、404/405、基础 500 JSON 兜底
- 轻量 `env/config/lang` 读取
- `/health`、`/api/v1/ping`
- PHPUnit + CS Fixer + GitHub Actions PHP CI
- Issue/PR/governance MVP 基线

当前 `main` 还没有：
- 中间件链
- 服务容器 / 依赖注入
- Provider 生命周期
- 统一异常类型与异常渲染策略
- Request 校验 / session / auth / cache / queue / db / console 等成熟框架基础设施
- 稳定对外扩展契约

结论：Folio 现在不是“成熟框架”，而是“可运行的框架骨架 + 治理起步版”。路线必须先把内核打稳，再长能力，不然只会变成散装功能堆。

---

## 1. 成熟体目标定义

Folio 的“成熟 PHP 开源框架体”定义，不是功能越多越成熟，而是满足下面 6 条：

1. **内核稳定**
   - HTTP 生命周期清晰
   - Router / Middleware / Exception / Container / Provider 有稳定职责边界
   - 对外扩展点可文档化、可测试、可演进

2. **工程可用**
   - 新项目能快速启动
   - 开发、测试、调试、发布链路闭环
   - 目录结构、配置约定、错误处理、日志行为一致

3. **扩展可控**
   - 核心能力可组合，不强迫一次性全家桶
   - 对常见能力（配置、事件、缓存、命令、队列、数据库）有明确接入策略
   - 扩展 API 稳定，不把业务代码绑死在魔法实现上

4. **中文团队友好**
   - 文档、错误信息、脚手架、最佳实践适合国人 API / 后端团队
   - 不做“只是 Laravel 英文化翻译版”
   - 默认路径优先服务 API-first 和中后台业务

5. **质量有基线**
   - 测试分层完整：单元 / Feature / 集成 / 回归
   - BC 变更、异常行为、性能退化有检查点
   - 安全、配置边界、错误脱敏、依赖风险有审计机制

6. **治理可持续**
   - roadmap、issue、PR、release 都可追踪
   - 对外承诺和实际能力一致
   - alpha -> beta -> stable 的阶段门槛清楚

一句话：**Folio 要成为“面向中文 API 团队、内核清楚、扩展克制、工程闭环完整”的 PHP 框架体，而不是另一个重皮肤 Laravel，也不是只有几百行代码的教学玩具。**

---

## 2. Folio 对 ThinkPHP / Laravel 的参照结论

## 2.1 应该借什么

### 借 ThinkPHP 的点

1. **中文开发者友好度**
   - 文档表达直给、上手路径短
   - 对常见中后台/API项目的约定清晰
   - 开箱启动成本低

2. **轻量起步、按需增强**
   - 不必一上来塞满 ORM、事件、队列、广播全家桶
   - 先把核心内核和常用能力打磨稳，再逐步模块化放出

3. **实用主义**
   - 多考虑国人团队真实使用场景：接口返回、配置分环境、错误排查、部署简单、二开成本低

### 借 Laravel 的点

1. **清晰的生命周期设计**
   - 容器、Provider、Middleware、Exception Handler 的职责明确
   - 扩展点稳定，生态才能长起来

2. **工程一致性**
   - 命令行、配置、测试、异常、日志、脚手架尽量统一风格
   - 新手和老手看到结构都知道从哪下手

3. **高质量开发者体验（DX）**
   - 文档、示例、脚手架、测试辅助、错误提示要可用
   - 不是只给 API，还要给“怎么正确地用”

4. **契约优先**
   - 接口、抽象、生命周期先定义清楚，再谈能力堆叠
   - 不让业务层直接耦合实现细节

## 2.2 不该学什么

### 不该照抄 ThinkPHP 的点

1. **过强的历史兼容包袱**
   - Folio 还早，别急着背历史债；早期 API 要宁可少承诺，也别乱承诺

2. **过多隐式行为 / 模糊边界**
   - 方便一时，维护地狱一世
   - Folio 该优先显式配置、显式注册、显式扩展点

3. **先把业务能力堆满再补内核**
   - 这会让框架越来越像插件集市，不像内核稳定的框架体

### 不该照抄 Laravel 的点

1. **过重的全家桶路径依赖**
   - Folio 现阶段不适合复制 Laravel 那套“大而全默认值”
   - 否则团队会被维护成本拖死

2. **过多魔法与隐藏约定**
   - Facade、自动发现、隐式解析这些可以有，但不能在早期就满地开花
   - 否则 debug 和学习成本会失控

3. **为生态而生态**
   - Folio 当前阶段先做“核心可用”，不是先幻想几百个第三方包

## 2.3 参照后的定位结论

Folio 的方向应该是：
- **借 ThinkPHP 的中文团队适配与轻启动**
- **借 Laravel 的生命周期设计与工程一致性**
- **不抄它们的历史负担、魔法复杂度、全家桶体量**

最终定位：**比纯骨架强很多，比 Laravel 克制很多，比传统国产轻框架更重视内核契约与工程治理。**

---

## 3. 分阶段路线

## P0：把骨架升级成“可扩展内核”（当前到 `0.2.x-alpha`）

### 阶段目标
把 Folio 从“能跑”升级到“内核职责成型、扩展边界初步稳定”。

### 必做结果
- Middleware Pipeline
- Exception Handler / Exception Renderer
- 基础服务容器（绑定、单例、解析）
- Application / Bootstrap 流程收敛
- Service Provider 初版
- Route 注册机制标准化（至少 API 路由入口规范）
- Request / Response 契约补齐最小必要能力
- 单元测试基础盘铺开
- 文档改成“事实 + 路线 + 扩展约定”三层结构

### P0 结束标志
- 内核关键类职责稳定
- 新增一个中间件 / Provider / Route 扩展不需要改一圈核心文件
- 异常、404/405、debug 脱敏行为可测试
- README 不再把 Folio 描述成单纯骨架，而是 alpha 内核

## P1：把内核升级成“可用于真实 API 项目”（目标 `0.3.x ~ 0.5.x-alpha/beta`）

### 阶段目标
围绕 API-first 场景补足工程基础设施，让真实项目能落。

### 必做结果
- 配置缓存 / 环境隔离策略
- 日志抽象与默认实现
- Request 输入读取、基础校验、错误结构统一
- 更完整路由能力：分组、前缀、命名、参数占位
- Console 命令系统初版
- Cache 抽象
- 事件机制或最小 hook 机制
- 数据库接入策略定稿（先抽象、后具体实现）
- 官方 app skeleton / starter project
- 文档补“从 0 到一个 API 服务”完整教程

### P1 结束标志
- 可用 Folio 生成并维护一个中小型 API 项目
- 不靠翻源码也能完成常见开发任务
- 回归测试开始覆盖更多真实业务流

## P2：把“可用框架”升级成“可持续开源框架体”（目标 beta/stable 前）

### 阶段目标
建立稳定扩展面、发布节奏和质量治理，让外部团队敢用。

### 必做结果
- BC 政策 / deprecation 策略
- Release note / upgrade guide 标准化
- 官方扩展清单与兼容矩阵
- 性能基线与回归监控
- 安全响应流程
- 文档站信息架构完善
- 更完整测试矩阵（PHP 版本 / OS / 最小依赖 / 最高依赖）
- 样例项目、最佳实践、迁移指南

### P2 结束标志
- 有明确 alpha/beta/stable 门槛
- 升级风险可评估
- 新版本不是“玄学发布”，而是规则发布

---

## 4. 第一阶段任务包（P0）

> 这一段不是空话，直接可以发给开发 / 测试 / 审计 / 治理。

## 4.1 开发任务包

### D1. 引入 Application 与 Bootstrap 流程收敛
**目标**：把 `public/index.php`、`Kernel`、config/env/lang/router 装载流程收敛成明确启动顺序。

**交付要求**：
- 新增 `Application` 核心对象（或等价命名）
- 明确 bootstrap 阶段：env -> config -> providers -> routes -> request handle
- `public/index.php` 只保留最薄入口
- README 与 docs 更新启动流程图

**验收标准**：
- 入口文件不再直接承担过多装配逻辑
- 现有 `/health`、`/api/v1/ping` 行为不回归
- 测试覆盖 bootstrap 成功路径

### D2. Middleware Pipeline 初版
**目标**：建立成熟框架必备的请求处理链。

**交付要求**：
- 支持全局中间件注册
- 定义 middleware 处理签名
- 至少提供 1 个示例中间件（如 request id / timing / json header guard）
- 文档说明中间件执行顺序

**验收标准**：
- 路由前后都可插入处理逻辑
- 中间件可短路返回 Response
- Feature 测试覆盖链式执行与短路行为

### D3. Exception Handling 统一化
**目标**：别再靠散落 try/catch 硬顶。

**交付要求**：
- 定义异常渲染入口（ExceptionHandler / Renderer）
- 区分 debug / non-debug 输出
- 统一 500 JSON 结构
- 为常见框架异常预留类型（如 RouteNotFound / MethodNotAllowed / ConfigurationException）

**验收标准**：
- Router 不再私自吞异常并硬编码 500
- `APP_DEBUG=false` 不泄漏内部 message/stack
- `APP_DEBUG=true` 至少可返回受控调试信息

### D4. Container 初版
**目标**：给 Provider 和后续扩展打地基。

**交付要求**：
- 支持 bind / singleton / make
- 支持简单闭包工厂
- 至少接管 config / router / lang / exception handler 中的部分实例创建

**验收标准**：
- 核心对象创建不再全靠手写 new 串起来
- 单元测试覆盖容器基本行为

### D5. Provider 初版
**目标**：把“核心能力装配”从硬编码迁到可管理生命周期。

**交付要求**：
- 定义 `ServiceProvider` 基类或接口
- 支持 `register` / `boot` 两阶段
- 提供 `RouteServiceProvider` 或 `CoreServiceProvider` 示例

**验收标准**：
- 路由注册至少可以通过 provider 接入
- 文档说明 provider 生命周期

### D6. Route 注册机制标准化
**目标**：让后续 API 组织方式可扩展。

**交付要求**：
- 固化 `routes/api.php` 注册契约
- 评估并实现最小 Route Group（前缀）能力，若本轮不做，必须明确推迟原因
- 为后续参数路由预留接口，不硬塞进本轮

**验收标准**：
- 路由文件返回/注册协议文档化
- Feature 测试覆盖至少一个 group/prefix 或契约加载场景

## 4.2 测试任务包

### T1. 建立分层测试目录与基线
**交付要求**：
- `tests/Unit`：Config / Env / Router / Container
- `tests/Feature`：健康检查、ping、404、405、middleware、exception
- 补充 `tests/TEST_PLAN.md` 到 P0 版本

### T2. 新增关键回归用例
必须新增：
- middleware 执行顺序
- middleware 短路响应
- debug=false 的异常脱敏
- debug=true 的受控错误输出
- container bind/singleton/make
- provider register/boot 顺序
- route 注册失败时的受控报错

### T3. CI 基线增强
**交付要求**：
- 继续保留 phpunit + cs check
- 增加覆盖率门槛策略评估文档（本轮可先不强卡）
- 至少把 unit + feature 都跑进 CI

## 4.3 审计任务包

### A1. 内核边界审计
**检查项**：
- 是否存在重复职责：Kernel / Router / Application / Provider
- 是否还有散落异常吞掉的点
- 是否存在不必要全局状态污染

### A2. 安全与配置审计
**检查项**：
- `APP_DEBUG=false` 下是否还有内部错误泄漏
- `.env` 解析边界是否被文档化
- 错误响应、header、编码是否一致

### A3. 扩展契约审计
**检查项**：
- 中间件、Provider、Container API 是否已够稳定到可以公开文档
- 如果不稳定，哪些标注 internal，哪些允许对外使用

## 4.4 治理任务包

### G1. 建立“成熟化路线根 issue”
**要求**：
- 根 issue 负责目标、分阶段、完成定义、子任务归档
- P0/P1/P2 拆成子 issue，不准所有事堆一个 issue

### G2. 建立 alpha 里程碑口径
**要求**：
- 说明 `0.2.x-alpha` 的准入条件
- 说明哪些能力“已承诺”、哪些“明确不承诺”

### G3. 文档架构收敛
**要求**：
- `README`：定位 + 快速开始 + 当前能力
- `docs/architecture/*`：内核结构与生命周期
- `docs/roadmap/*`：路线与阶段目标
- `docs/audit/*`：审计结论

---

## 5. issue / commit / PR / 文档落点

## 5.1 建议 issue 结构

### 根 issue
- **标题**：Folio 成熟框架体路线（P0/P1/P2）
- **用途**：统一追踪从骨架到成熟体的分阶段路线与验收标准

### P0 子 issue（本轮应立即创建）
1. P0-1：Application/bootstrap 流程收敛
2. P0-2：Middleware Pipeline 初版
3. P0-3：统一异常处理与错误渲染
4. P0-4：Container 初版
5. P0-5：Service Provider 初版
6. P0-6：P0 测试基线补齐
7. P0-7：P0 安全/边界审计
8. P0-8：文档架构与 alpha 口径收敛

## 5.2 文档落点

本次已新增：
- `docs/product-maturity-roadmap-2026-03-26.md`

建议后续继续新增：
- `docs/architecture/http-lifecycle.md`
- `docs/architecture/container-and-provider.md`
- `docs/roadmap/p0-alpha-kernel.md`
- `docs/audit/p0-kernel-boundary-review.md`

## 5.3 commit / PR 落点建议

建议首个落实 PR：
- **PR 标题**：`docs: add maturity roadmap and p0 execution plan`
- **关联**：成熟化路线根 issue

后续开发 PR 按能力拆开，不要一个 PR 把 middleware/container/provider/exception 全塞一起。

---

## 6. 直接执行顺序（给老板/负责人看的落地顺序）

1. 先开“成熟框架体路线”根 issue
2. 同步开 8 个 P0 子 issue，并在根 issue 下挂接
3. 先提文档 PR，冻结路线口径
4. 开发按顺序推进：
   - bootstrap/application
   - middleware
   - exception
   - container
   - provider
   - route 规范补充
5. 测试与审计并行插入，不等开发做完才补
6. P0 完成后再决定 P1 的 database / cache / console 进入顺序

---

## 7. 最终判断

Folio 现在最缺的不是“再加两个功能演示接口”，而是：
- 内核生命周期
- 扩展契约
- 异常与中间件
- 容器与 provider
- 与这些能力配套的测试/审计/文档

所以路线明确如下：
- **P0 先做内核成型**
- **P1 再做真实 API 工程化能力**
- **P2 再做开源框架体稳定化**

这才是从早期骨架走向成熟框架体的正确顺序。