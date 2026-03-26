#!/usr/bin/env python3
import json
import os
from pathlib import Path


def parse_json(name, default):
    raw = os.environ.get(name, '')
    if not raw:
        return default
    return json.loads(raw)


def main():
    prs = parse_json('MERGED_PRS_JSON', [])
    candidate = []
    blockers = []
    for pr in prs:
        labels = pr.get('labels', [])
        if 'release/skip' in labels:
            continue
        if 'risk/high' in labels:
            blockers.append({'pr': pr.get('number'), 'reason': 'risk/high requires manual review'})
            continue
        if 'status/done' in labels or 'release/candidate' in labels:
            candidate.append({
                'number': pr.get('number'),
                'title': pr.get('title'),
                'labels': labels,
                'issue_refs': pr.get('issue_refs', []),
            })

    result = {
        'release_candidate': bool(candidate),
        'candidate_count': len(candidate),
        'items': candidate,
        'blockers': blockers,
    }
    out = json.dumps(result, ensure_ascii=False, indent=2)
    Path(os.environ.get('GITHUB_OUTPUT_JSON', 'release_candidate_result.json')).write_text(out + '\n', encoding='utf-8')
    print(out)


if __name__ == '__main__':
    main()
