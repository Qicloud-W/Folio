# Round 2 Audit Notes

## 结论

当前 `main` 已经从“最小可跑通骨架”推进到“0.2.x-alpha 可运行内核”。

这不是成熟框架，也不是只有静态演示接口的 0.1 骨架；更准确地说，当前仓库已经具备：可运行 HTTP 主链路、middleware pipeline、基础 container/provider 装配、统一 JSON 异常出口，以及最小 CI / 治理 / 测试基线。

## 当前 main 已确认事实

- `public/index.php` 是唯一 public HTTP 入口。
- `Folio\Core\Foundation\Application` 是当前对外应用 facade。
- `routes/api.php` 已接入真实加载。
- `/health`、`/api/v1/ping`、404、405、500 已统一到 JSON 行为。
- 已存在全局 middleware pipeline，并覆盖顺序执行与短路返回场景。
- 已存在基础容器交互面：`bind()` / `singleton()` / `instance()` / `make()` / `bound()`。
- 已存在 provider 注册与 boot 生命周期初版。
- 已有 PHPUnit、CS Fixer、GitHub Actions PHP CI 与治理检查。

## 主要风险与未收口点

1. **runtime 内部主线仍在收口**
   - `src/Core/Application/Application.php`
   - `src/Core/Kernel.php`
   - `src/Core/Foundation/HttpKernel.php`
   之间的最终职责边界还没完全定稿。

2. **alpha 对外边界应冻结，内部实现不应冒充稳定 ABI**
   - README 和其余文档不该把某一条内部实现路径写成“最终唯一内核”。

3. **路由能力仍然是 alpha 最小集**
   - 当前稳定的是 `routes/api.php` 入口、404/405 口径与统一 `Response` 收敛。
   - 参数路由、路由分组、命名路由、控制器映射都还不该宣称已稳定。

4. **容器 API 需要继续克制**
   - 对外只该承诺 `bind()` / `singleton()` / `instance()` / `make()` / `bound()`。
   - `set()` / `has()` 一类实现细节不该继续外扩成公开口径。

## 审计判断

### 已经可以对外说的
- Folio 当前是 0.2.x-alpha 可运行内核。
- 它已经有 middleware、基础 container/provider、JSON exception 边界和 CI 治理基线。
- 它适合继续围绕 alpha 边界收口，而不是再把文档写回 0.1 骨架时代。

### 现在还不能对外说的
- runtime 内部实现已经完全唯一化。
- 路由、扩展、基础设施已经达到成熟框架级别。
- alpha 之外的能力已经可以当作稳定承诺。

## 建议

- README、roadmap、architecture、audit 统一只写当前 main 真相。
- 所有 0.2.x-alpha 对外材料统一成“冻结对外边界，不冻结唯一内部实现”。
- 后续若继续收口 runtime，实现可以改，但外部口径不能再来回摇摆。
