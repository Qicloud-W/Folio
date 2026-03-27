#!/usr/bin/env python3
import os
import re
import sys


def parse_csv(value: str) -> list[str]:
    if not value:
        return []
    return [item.strip() for item in value.split(',') if item.strip()]


def has_line(body: str, *patterns: str) -> bool:
    return any(re.search(pattern, body, re.IGNORECASE | re.MULTILINE) for pattern in patterns)


issue_title = os.environ.get('ISSUE_TITLE', '').strip()
issue_body = os.environ.get('ISSUE_BODY', '') or ''
labels = parse_csv(os.environ.get('ISSUE_LABELS', ''))

errors: list[str] = []

if not issue_title:
    errors.append('issue title is required')

if not has_line(issue_body, r'^#+\s*背景\b', r'^#+\s*Background\b'):
    errors.append('issue body must include 背景/Background section')

if not has_line(issue_body, r'^#+\s*目标\b', r'^#+\s*Goal\b'):
    errors.append('issue body must include 目标/Goal section')

if not has_line(issue_body, r'^#+\s*验收标准\b', r'^#+\s*Acceptance( Criteria)?\b'):
    errors.append('issue body must include 验收标准/Acceptance section')

for prefix in ('type/', 'priority/', 'status/', 'risk/'):
    if not any(label.startswith(prefix) for label in labels):
        errors.append(f'missing required label prefix: {prefix}')

if 'status/blocked' in labels:
    blocker_checks = {
        'blocker.reason': r'blocker\.reason|阻塞原因',
        'blocker.owner': r'blocker\.owner|阻塞责任人',
        'blocker.unblock_condition': r'blocker\.unblock_condition|解除条件',
        'blocker.next_sync_at': r'blocker\.next_sync_at|下次同步时间',
    }
    for name, pattern in blocker_checks.items():
        if not re.search(pattern, issue_body, re.IGNORECASE):
            errors.append(f'missing blocked issue field: {name}')

if errors:
    for error in errors:
        print(error)
    sys.exit(1)

print('issue governance contract OK')
