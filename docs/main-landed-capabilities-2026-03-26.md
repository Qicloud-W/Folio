# Folio main 已落地能力清单（2026-03-26）

> 结论只基于当前 `main` 分支仓库事实，不写空愿景。

## 1. 运行主链路

当前主链路已经打通：

`public/index.php -> Request -> Kernel -> Router -> Response`

仓库内对应事实：
- `public/index.php`：HTTP 入口
- `src/Core/Http/Request.php`：请求对象
- `src/Core/Kernel.php`：启动、装载 env/config/lang、注册路由、统一异常兜底
- `src/Core/Routing/Router.php`：静态路由分发、404、405、Allow header
- `src/Core/Http/Response.php`：JSON / safeJson 响应输出
- `routes/api.php`：API 路由注册入口

当前可直接说明的已落地行为：
- `GET /health` 返回健康检查 JSON
- `GET /api/v1/ping` 返回探活 JSON
- 未命中路径返回统一 404 JSON
- 已注册路径但 method 不匹配时返回 405 JSON，并带 `allowed_methods` / `Allow` header
- Kernel 在异常时返回基础 500 JSON fallback

## 2. config / env

当前已落地：
- `.env.example` 提供最小环境变量样例
- `src/Core/Config/Env.php` 支持读取项目根目录 `.env` 与系统环境变量
- `src/Core/Config/ConfigLoader.php` + `ConfigRepository.php` 支持读取 `config/*.php`
- `config/app.php` 已作为应用配置入口
- Kernel / README 已实际使用 `app.name`、`app.env`、`app.locale`、`app.debug`

当前事实边界：
- 采用轻量自实现 env/config 方案
- 未引入外部 dotenv 依赖
- 还没有更完整的配置缓存、分环境配置组合、密钥管理能力

## 3. i18n 占位

当前已落地：
- `src/Core/I18n/Lang.php`
- `resources/lang/zh-CN/messages.php`
- `/api/v1/ping` 已通过 Lang 读取 `pong` 文案
- locale 当前从应用配置读取，默认落在 `zh-CN`

当前事实边界：
- 这是最小占位，不是完整国际化系统
- 还没有多语言切换、fallback 链、命名空间语言包、格式化能力

## 4. 测试 / CI

当前已落地测试：
- `tests/Feature/SmokeTest.php`
- `tests/Feature/HealthEndpointTest.php`
- `tests/Feature/PingEndpointTest.php`
- `tests/Feature/NotFoundEndpointTest.php`
- `tests/Feature/MethodNotAllowedTest.php`
- `tests/Feature/KernelTestCase.php`
- `tests/TEST_PLAN.md`
- `phpunit.xml`
- `composer.json` 中已定义 `composer test`、`composer cs:check`

当前已落地 CI：
- `.github/workflows/php-ci.yml`
  - setup-php 8.2
  - composer validate
  - composer install
  - composer test
  - composer cs:check

结论：
- 仓库已经从“只写测试文件但本地跑不动”升级到“有真实 GitHub Actions 可执行 PHP CI”
- Feature 测试已经直接走 Kernel，不再依赖重复 require 入口文件的旧做法

## 5. governance / labels / issue/PR workflow

当前已落地治理文件：
- `CONTRIBUTING.md`
- `.github/ISSUE_TEMPLATE/bug.yml`
- `.github/ISSUE_TEMPLATE/feature.yml`
- `.github/pull_request_template.md`
- `.github/workflows/governance-mvp.yml`
- `docs/governance-development-flow.md`
- `docs/governance-issue-lifecycle.md`
- `docs/governance-change-policy.md`
- `docs/governance-blocker-policy.md`
- `docs/governance-labels-and-status.md`
- `docs/governance-automation-mvp.md`
- `docs/governance-state-machine.yaml`

当前已落地规则：
- Issue 要求背景 / 目标 / 验收标准
- Issue 要求 type / priority / status / risk 标签
- `status/blocked` 要求 blocker 原因、owner、解除条件、下次同步时间
- PR 要求标题前缀符合约定类型
- PR body 要求包含：变更摘要 / 为什么改 / 如何验证 / 风险与兼容性
- PR 要求写关联 issue、状态、风险
- workflow 会检查治理关键文件是否存在

当前事实边界：
- 这是 governance MVP，核心是“规则校验 + 门禁”
- 还不是完整状态机执行器

## 6. 目前还没做成的执行层自动化能力

仓库里已经明确写了“未承诺 / 下一轮接口预留”的部分，当前**没有真正做成**的包括：
- 自动 merge
- 自动 release
- 自动派单
- 自动催办 / 自动升级 / 自动分配
- 自动标签回写所有状态
- 完整状态机执行器

对应仓库事实：
- `docs/governance-automation-mvp.md` 明确写了本轮不承诺自动 merge / release / 派单 / 自动改标签回写所有状态
- `docs/governance-automation-next-interfaces.md` 只定义了 merge gate / release candidate / dispatch 的输入输出接口预留，不是已实现能力

## 7. 当前 main 的一句话判断

Folio 当前 `main` 已经具备：
- 最小可运行 PHP API 骨架
- 最小 config/env/i18n 占位
- 404/405/500 基础响应兜底
- 可执行 Feature 测试与 PHP CI
- 第一轮 Issue/PR/governance 门禁基线

但还**不具备**：
- 中间件链
- 完整异常体系
- 容器 / Provider 生命周期
- 自动化执行器（merge/release/dispatch）
- 更完整的 i18n、配置治理与工程化能力
