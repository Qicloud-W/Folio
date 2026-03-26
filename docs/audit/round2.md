# Round 2 Audit Notes

## 结论

当前仓库已从“静态 index.php 占位”推进到“最小可跑通内核链路雏形”。方向基本正确，尚未偏离轻量骨架目标，但仍需尽快补齐自动化与边界约束。

## 已完成正向结果

- 引入 `Kernel / Router / Request / Response` 最小主链路。
- 引入 `ConfigLoader + Env`，`config/*.php` 可统一读取。
- 预留 `resources/lang/zh-CN` 与简单 `Lang` 读取能力。
- `public/index.php` 从硬编码分支切换为内核启动入口。
- README 已需要跟上，否则代码与文档会脱节。

## 发现的问题

1. 目前没有 Composer 安装依赖与 CI 实跑，测试只能做静态/有限动态验证。
2. 路由仍是代码内注册，`routes/api.php` 尚未接入真实加载。
3. `Response::send()` 使用 `JSON_THROW_ON_ERROR`，未来需要统一异常兜底。
4. `Env` 解析很轻，只适合作为 MVP；复杂 `.env` 语法还未覆盖。
5. 分支治理、版本节奏、CI 徽章与状态说明还未形成闭环。

## 建议

- 分支：继续使用 `main` + feature 分支，首个可演示版本打 `0.1.0-alpha.1`。
- CI：补一个最小 GitHub Actions，先做 `composer validate`、`phpunit`、代码风格检查。
- 命名：当前 `src/Core/*` 基本统一，保持住，别引入花里胡哨的目录层级。
- 版本：下一刀应该是异常处理 + route 文件加载 + 更稳的测试 harness，而不是过早加容器花活。
