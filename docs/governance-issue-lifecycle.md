# Folio Issue 生命周期（MVP）

## 1. 生命周期目标

统一任务从提出到关闭的状态表达，减少“在做没在做、卡没卡住、要不要变更范围”这类口头协作成本。

## 2. 推荐状态

- `draft`：刚提出，信息未完整
- `triaged`：已分类，待判断优先级/归属
- `ready`：信息充分，可开始执行
- `in_progress`：已开始开发/处理
- `blocked`：被外部依赖、决策或资源卡住
- `in_review`：已提交 PR 或等待评审
- `done`：已完成并合入/确认收尾
- `closed`：关闭，不再继续（重复、废弃、拒绝）

## 3. 状态流转

主流转：

`draft -> triaged -> ready -> in_progress -> in_review -> done`

分支流转：

- `triaged -> closed`
- `ready -> blocked`
- `in_progress -> blocked`
- `blocked -> ready`
- `blocked -> in_progress`
- `in_review -> in_progress`（评审打回）
- `done -> in_progress`（回归缺陷或补做）
- `* -> closed`（明确不做/重复/失效）

## 4. 进入条件

### draft
- 有主题，但信息不完整

### triaged
- 已确定任务类型
- 已有基本背景与目标

### ready
- 范围清楚
- 验收标准明确
- 无关键 blocker

### in_progress
- 已有负责人
- 已开始开发或验证

### blocked
- 当前推进被明确阻断
- 已记录阻断原因、期望解除条件、下一次同步时间

### in_review
- 已提交 PR 或已有待审产物
- 自测已完成

### done
- 代码/文档已合并或成果已确认
- 遗留项已补 issue 或明确放弃

### closed
- 明确不做，或被其他 issue 替代

## 5. 使用规则

- 一个 issue 同一时间只应有一个主状态
- `blocked` 不是情绪描述，而是可说明、可跟踪的阻断状态
- 若 PR 已提但仍在持续修改，可保持 `in_review`，并在评论补充进展
- `closed` 必须写明原因

## 6. 最小字段建议

每个 issue 建议至少具备：

- 类型（type）
- 优先级（priority）
- 状态（status）
- 负责人（assignee，可空）
- 验收标准（acceptance）
- 关联 PR（可空）
- blocker 记录（若有）
