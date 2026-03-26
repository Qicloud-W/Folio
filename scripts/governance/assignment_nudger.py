#!/usr/bin/env python3
import json
import os
from pathlib import Path


def parse_csv(value):
    if not value:
        return []
    return [item.strip() for item in value.split(',') if item.strip()]


def main():
    labels = parse_csv(os.environ.get('ITEM_LABELS', ''))
    assignees = parse_csv(os.environ.get('ITEM_ASSIGNEES', ''))
    status = next((label for label in labels if label.startswith('status/')), 'status/draft')

    actions = []
    if status == 'status/triaged' and not assignees:
        actions.append({'type': 'comment', 'key': 'triaged-unassigned', 'message': 'This item is triaged but has no assignee yet. Please assign an owner before it stalls.'})
    if status == 'status/blocked':
        actions.append({'type': 'comment', 'key': 'blocked-followup', 'message': 'This item is blocked. Please update the blocker, owner, and next unblock checkpoint.'})
    if status == 'status/in-review':
        actions.append({'type': 'comment', 'key': 'review-followup', 'message': 'This item is in review. Please confirm reviewer/decision and either merge or relabel within 24h.'})

    result = {
        'status': status,
        'labels': labels,
        'assignees': assignees,
        'actions': actions,
    }

    out = json.dumps(result, ensure_ascii=False, indent=2)
    Path(os.environ.get('GITHUB_OUTPUT_JSON', 'assignment_nudger_result.json')).write_text(out + '\n', encoding='utf-8')
    print(out)


if __name__ == '__main__':
    main()
