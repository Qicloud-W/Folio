#!/usr/bin/env python3
import json
import os
import re
import sys
from pathlib import Path


def env(name, default=""):
    return os.environ.get(name, default)


def parse_csv(value):
    if not value:
        return []
    return [item.strip() for item in value.split(',') if item.strip()]


def has_any_prefix(labels, prefix):
    return any(label.startswith(prefix) for label in labels)


def main():
    labels = parse_csv(env('PR_LABELS'))
    issue_refs = env('PR_ISSUE_REFS')
    mergeable = env('PR_MERGEABLE', '').lower()
    review_decision = env('PR_REVIEW_DECISION', '').upper()
    head_sha = env('PR_HEAD_SHA')
    event_name = env('GITHUB_EVENT_NAME')
    result = {
        'gate': 'deny',
        'reasons': [],
        'mergeable': False,
        'checks': {
            'issue_linked': False,
            'status_ready': False,
            'risk_allowed': False,
            'automerge_label': False,
            'review_decision': review_decision,
            'mergeable_state': mergeable,
            'required_checks_ok': False,
        },
    }

    result['checks']['issue_linked'] = bool(re.search(r'#\d+', issue_refs or ''))
    result['checks']['status_ready'] = 'status/in-review' in labels or 'status/done' in labels
    result['checks']['risk_allowed'] = 'risk/high' not in labels
    result['checks']['automerge_label'] = 'automerge' in labels
    result['checks']['required_checks_ok'] = env('CHECKS_OK', '').lower() == 'true'

    if not result['checks']['issue_linked']:
        result['reasons'].append('missing linked issue')
    if not result['checks']['status_ready']:
        result['reasons'].append('missing status/in-review or status/done label')
    if not result['checks']['risk_allowed']:
        result['reasons'].append('risk/high is not eligible for automerge')
    if not result['checks']['automerge_label']:
        result['reasons'].append('missing automerge label')
    if review_decision not in ('APPROVED', ''):
        result['reasons'].append(f'review decision is {review_decision.lower()}')
    if mergeable and mergeable != 'mergeable':
        result['reasons'].append(f'pr mergeable state is {mergeable}')
    if not result['checks']['required_checks_ok']:
        result['reasons'].append('required checks not green')

    if not result['reasons']:
        result['gate'] = 'pass'
        result['mergeable'] = True
    elif result['checks']['issue_linked'] and result['checks']['status_ready']:
        result['gate'] = 'hold'

    out = json.dumps(result, ensure_ascii=False, indent=2)
    Path(env('GITHUB_OUTPUT_JSON', 'merge_gate_result.json')).write_text(out + '\n', encoding='utf-8')
    print(out)

    if event_name == 'pull_request' and result['gate'] == 'pass' and head_sha:
        sys.exit(0)
    if event_name == 'workflow_dispatch':
        sys.exit(0)
    sys.exit(0)


if __name__ == '__main__':
    main()
