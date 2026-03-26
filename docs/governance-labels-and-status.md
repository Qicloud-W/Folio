# Folio 标签与状态建议（MVP）

## 1. 设计原则

- 中文团队易懂
- 名称稳定，可被自动化消费
- 尽量按维度拆分，避免一个标签表达多个语义

## 2. 标签维度建议

### 类型标签（type/*）
- `type/feature`
- `type/bug`
- `type/chore`
- `type/docs`
- `type/governance`
- `type/refactor`
- `type/test`

### 优先级标签（priority/*）
- `priority/p0`
- `priority/p1`
- `priority/p2`
- `priority/p3`

### 状态标签（status/*）
- `status/draft`
- `status/triaged`
- `status/ready`
- `status/in-progress`
- `status/blocked`
- `status/in-review`
- `status/done`

### 风险标签（risk/*）
- `risk/low`
- `risk/medium`
- `risk/high`

### 其他辅助标签
- `needs/decision`
- `needs/design`
- `needs/test`
- `needs/docs`
- `blocked/external`
- `blocked/internal`

## 3. 使用建议

- 每个 issue 至少应有 1 个类型标签
- 每个 issue 建议最多只有 1 个状态标签
- 优先级标签应保持唯一
- blocker 类标签用于补充阻断来源，不替代主状态

## 4. 状态与标签关系

issue 的业务状态以状态机为准，GitHub label 是其可视化投影。后续自动化可基于 label 或 issue 表单字段进行同步。

## 5. 后续自动化方向

后续可实现：

- PR 建立后自动从 `status/in-progress` 切到 `status/in-review`
- PR merge 后自动打 `status/done`
- 带 `blocked/*` 且超过阈值的 issue 自动提醒
