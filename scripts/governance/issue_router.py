#!/usr/bin/env python3
import json
import os
from pathlib import Path

ROUTES = {
    'type/bug': 'team/backend',
    'type/feature': 'team/product',
    'type/chore': 'team/platform',
}

ESCALATE_LABELS = {'priority/p0', 'priority/p1'}


def parse_csv(value):
    if not value:
        return []
    return [item.strip() for item in value.split(',') if item.strip()]


def main():
    labels = parse_csv(os.environ.get('ISSUE_LABELS', ''))
    assignees = parse_csv(os.environ.get('ISSUE_ASSIGNEES', ''))
    status = next((label for label in labels if label.startswith('status/')), 'status/draft')
    issue_type = next((label for label in labels if label.startswith('type/')), 'type/unknown')

    route = ROUTES.get(issue_type, 'team/triage')
    actions = []

    if status == 'status/draft':
        actions.append('nudge-fill-template')
    if status in ('status/draft', 'status/triaged') and not assignees:
        actions.append('assign-triage-owner')
    if any(label in ESCALATE_LABELS for label in labels):
        actions.append('escalate-priority')
    if status == 'status/blocked':
        actions.append('notify-blocker-owner')

    result = {
        'route': route,
        'status': status,
        'issue_type': issue_type,
        'actions': actions,
        'labels': labels,
        'assignees': assignees,
    }

    out = json.dumps(result, ensure_ascii=False, indent=2)
    Path(os.environ.get('GITHUB_OUTPUT_JSON', 'issue_router_result.json')).write_text(out + '\n', encoding='utf-8')
    print(out)


if __name__ == '__main__':
    main()
