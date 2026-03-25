# Contributing to Folio

## 开发约定

- 新功能优先围绕 API 场景设计
- 默认中文文档优先，必要时补英文
- 核心保持轻量，非必要能力不要直接塞进内核
- 新增组件前先定义扩展点与替换策略
- 原则上遵循 issue 驱动开发，避免无来源改动

## 治理文档入口

首轮治理文档见：

- `docs/governance-development-flow.md`
- `docs/governance-issue-lifecycle.md`
- `docs/governance-change-policy.md`
- `docs/governance-blocker-policy.md`
- `docs/governance-labels-and-status.md`
- `docs/governance-automation-mvp.md`
- `docs/governance-state-machine.yaml`

## 提交流程

1. 关联 issue 或在 PR 中说明例外原因
2. 创建分支
3. 补充测试与必要文档
4. 通过 `composer test` 与 `composer cs:check`
5. 发起 PR，说明动机、范围、验证方式、兼容性影响
