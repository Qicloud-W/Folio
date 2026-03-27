#!/usr/bin/env python3
import os
import re
import sys


def parse_csv(value: str) -> list[str]:
    if not value:
        return []
    return [item.strip() for item in value.split(',') if item.strip()]


pr_title = os.environ.get('PR_TITLE', '').strip()
pr_body = os.environ.get('PR_BODY', '') or ''
labels = parse_csv(os.environ.get('PR_LABELS', ''))

errors: list[str] = []

if not re.match(r'^(feat|fix|docs|refactor|test|chore|build|ci|perf|revert|governance)(\(.+\))?: .+', pr_title):
    errors.append('PR title must follow conventional commit style')

required_sections = [
    '## 变更摘要',
    '## 为什么改',
    '## 如何验证',
    '## 风险与兼容性',
]
for section in required_sections:
    if section not in pr_body:
        errors.append(f'missing PR section: {section}')

if not re.search(r'关联 issue[:：]\s*#?\d+', pr_body, re.IGNORECASE):
    errors.append('PR body must link an issue')

if not re.search(r'状态[:：]\s*(draft|triaged|ready|in-progress|blocked|in-review|done|closed)', pr_body, re.IGNORECASE):
    errors.append('PR body must declare governance status')

if not re.search(r'风险[:：]\s*(low|medium|high)', pr_body, re.IGNORECASE):
    errors.append('PR body must declare risk level')

if labels and not any(label.startswith('risk/') for label in labels):
    errors.append('PR labels must include risk/* when labels are set')

if errors:
    for error in errors:
        print(error)
    sys.exit(1)

print('PR governance contract OK')
