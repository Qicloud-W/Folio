# Folio 治理执行层 MVP（第一轮）

这次不是再写规则口号，是真把最小执行层接起来。

## 已落地产物

- `.github/workflows/governance-exec-mvp.yml`
- `scripts/governance/merge_gate.py`
- `scripts/governance/issue_router.py`
- `scripts/governance/release_candidate.py`
- `docs/governance-exec-mvp.md`

## 现在自动化具体能干什么

### 1) 自动 merge gate（最小放行逻辑）
PR 事件触发后，会根据以下条件给出 `pass / hold / deny` 结果并回写评论：

- PR body 中必须关联 issue（如 `#12`）
- 必须带 `status/in-review` 或 `status/done`
- 不能带 `risk/high`
- 必须带 `automerge`
- 必须满足 `checks/passed` 标签约定（第一轮先用 label 代替完整 checks API 汇总）
- review decision 不是阻断态

输出：PR 评论中会留下结构化 gate 结果，供人工或下一轮自动 merge 消费。

> 说明：第一轮没有直接执行 `gh pr merge --auto`，先把可执行 gate 决策器做真。避免假自动化把仓库炸了。

### 2) 自动派单 / 分流骨架
Issue 事件触发后，会按 label 做最小路由：

- `type/bug` -> `team/backend`
- `type/feature` -> `team/product`
- `type/chore` -> `team/platform`
- 其他 -> `team/triage`

同时产出动作建议：

- `status/draft` -> `nudge-fill-template`
- `draft/triaged` 且无人认领 -> `assign-triage-owner`
- `priority/p0` / `priority/p1` -> `escalate-priority`
- `status/blocked` -> `notify-blocker-owner`

输出：Issue 评论中会留下 routing JSON，形成第一轮留痕。

### 3) release 候选准备逻辑
当 `main` 收到 push，或手动触发 workflow 时：

- 拉取最近 merged PR
- 跳过 `release/skip`
- 过滤 `risk/high` 为 blocker
- 收集 `status/done` 或 `release/candidate` 的 PR
- 产出 `release_candidate_result.json`
- 同步快照到 `docs/releases/latest-candidate.json`
- 上传 artifact 供下载

这已经是可执行 release candidate 骨架，不是一句“以后再做”。

## 第一轮明确还没做的

- 直接调用 GitHub Merge API 执行合并
- 自动改 label / 自动 assign 到具体人
- release notes 自动生成 markdown 正文
- 基于 Checks API 的严格绿灯判断
- 时间维度 SLA / 超时升级 / 值班轮转

## 为什么这样切

因为这是第一轮执行层 MVP。先把“判断器 + 留痕 + 触发器 + 可运行产物”接起来，比再写十页接口幻觉强得多。
