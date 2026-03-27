#!/usr/bin/env python3
import json
import os
import re
from pathlib import Path


def env(name, default=""):
    return os.environ.get(name, default)


def parse_csv(value):
    if not value:
        return []
    return [item.strip() for item in value.split(',') if item.strip()]


def truthy(value):
    return str(value).strip().lower() in {"1", "true", "yes", "on"}


def main():
    labels = parse_csv(env('PR_LABELS'))
    issue_refs_raw = env('PR_ISSUE_REFS')
    mergeable = env('PR_MERGEABLE', '').lower()
    review_decision = env('PR_REVIEW_DECISION', '').upper()
    required_checks_ok = truthy(env('CHECKS_OK', ''))
    issue_refs = sorted(set(re.findall(r'#(\d+)', issue_refs_raw or '')))

    checks = {
        'issue_linked': bool(issue_refs),
        'status_ready': any(label in labels for label in ('status/in-review', 'status/done')),
        'blocked': 'status/blocked' in labels,
        'risk_allowed': 'risk/high' not in labels,
        'required_checks_ok': required_checks_ok,
        'review_decision': review_decision,
        'mergeable_state': mergeable,
    }

    deny_reasons = []
    hold_reasons = []

    if not checks['issue_linked']:
        deny_reasons.append('missing linked issue')
    if checks['blocked']:
        deny_reasons.append('status/blocked present')
    if not checks['risk_allowed']:
        deny_reasons.append('risk/high requires manual override')

    if not checks['status_ready']:
        hold_reasons.append('missing status/in-review or status/done label')
    if not checks['required_checks_ok']:
        hold_reasons.append('required checks not green')
    if review_decision not in ('', 'APPROVED'):
        hold_reasons.append(f'review decision is {review_decision.lower()}')
    if mergeable and mergeable != 'mergeable':
        hold_reasons.append(f'pr mergeable state is {mergeable}')

    gate = 'eligible'
    reasons = []
    if deny_reasons:
        gate = 'deny'
        reasons = deny_reasons + hold_reasons
    elif hold_reasons:
        gate = 'hold'
        reasons = hold_reasons

    result = {
        'gate': gate,
        'mergeable': gate == 'eligible',
        'issue_refs': issue_refs,
        'labels': labels,
        'checks': checks,
        'reasons': reasons,
    }

    out = json.dumps(result, ensure_ascii=False, indent=2)
    Path(env('GITHUB_OUTPUT_JSON', 'merge_gate_result.json')).write_text(out + '\n', encoding='utf-8')
    print(out)


if __name__ == '__main__':
    main()
