# Folio 治理自动化下一轮接口预留

> 这不是已实现功能，只是给下一轮自动 merge / release / 派单引擎留清楚接口，避免继续空喊。

## 1. 自动 merge 门禁接口

输入：
- PR 关联 issue 编号
- PR 风险等级（`risk/*`）
- PR 当前检查结果
- 是否存在 review 阻塞

建议输出：
- `merge_gate=pass|hold|deny`
- `merge_reason=<text>`

最低规则建议：
- 必须关联 issue
- 必须有唯一风险标签
- 所有 governance checks 通过
- 高风险 PR 不自动 merge，只给 `hold`

## 2. release 候选接口

输入：
- merge 到 `main` 的 PR 列表
- 对应 issue 类型 / 风险 / 优先级
- 版本里程碑信息

建议输出：
- `release_candidate=true|false`
- `release_notes_scope=<generated text>`
- `release_blockers=<list>`

最低规则建议：
- 仅 `status/done` 的事项进入 release 汇总
- 若存在 `risk/high` 未复核项，则 release hold

## 3. 派单 / 提醒接口

输入：
- issue 当前状态
- 最后更新时间
- assignee
- 是否 blocked

建议输出：
- `dispatch_action=none|nudge|assign|escalate`
- `dispatch_reason=<text>`

最低规则建议：
- `status/blocked` 超过阈值自动提醒 owner
- `status/triaged` 长时间无人认领则提醒派单
- `status/in-review` 超过阈值提醒 reviewer

## 4. 建议事件源

- GitHub issue opened/edited/labeled
- GitHub pull_request opened/edited/synchronize/labeled
- merge 到 main
- 定时巡检（仅提醒，不自动乱改状态）

## 5. 数据来源约束

下一轮尽量只消费这些稳定字段：
- 标题
- body 关键段落
- labels（type/priority/status/risk）
- assignee
- linked issue / PR
- state machine 文件

别再让人手工写一堆 bot 专用隐式规则。先把结构定住，再加执行器。
