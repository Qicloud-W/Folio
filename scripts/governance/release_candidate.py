#!/usr/bin/env python3
import json
import os
from datetime import datetime, timezone
from pathlib import Path


def parse_json(name, default):
    raw = os.environ.get(name, '')
    if not raw:
        return default
    return json.loads(raw)


def main():
    prs = parse_json('MERGED_PRS_JSON', [])
    commit_sha = os.environ.get('GITHUB_SHA', '')
    run_url = os.environ.get('GITHUB_SERVER_URL', '') + '/' + os.environ.get('GITHUB_REPOSITORY', '') + '/actions/runs/' + os.environ.get('GITHUB_RUN_ID', '') if os.environ.get('GITHUB_RUN_ID') else ''

    candidate = []
    blockers = []
    notes_lines = ['# Release Candidate Snapshot', '']

    for pr in prs:
        labels = pr.get('labels', [])
        if 'release/skip' in labels:
            continue
        item = {
            'number': pr.get('number'),
            'title': pr.get('title'),
            'labels': labels,
            'issue_refs': pr.get('issue_refs', []),
            'merged_at': pr.get('mergedAt', ''),
        }
        if 'risk/high' in labels:
            blockers.append({'pr': pr.get('number'), 'reason': 'risk/high requires manual review'})
            continue
        if 'status/done' in labels or 'release/candidate' in labels:
            candidate.append(item)

    notes_lines.append(f"Generated at: {datetime.now(timezone.utc).isoformat()}")
    if commit_sha:
        notes_lines.append(f"Commit: `{commit_sha}`")
    if run_url:
        notes_lines.append(f"Run: {run_url}")
    notes_lines.append('')
    notes_lines.append('## Candidate PRs')
    if candidate:
        for item in candidate:
            refs = ', '.join(f"#{ref}" for ref in item['issue_refs']) or 'none'
            notes_lines.append(f"- PR #{item['number']}: {item['title']} | labels={','.join(item['labels']) or 'none'} | issues={refs}")
    else:
        notes_lines.append('- none')
    notes_lines.append('')
    notes_lines.append('## Blockers')
    if blockers:
        for blocker in blockers:
            notes_lines.append(f"- PR #{blocker['pr']}: {blocker['reason']}")
    else:
        notes_lines.append('- none')
    notes_lines.append('')
    notes = '\n'.join(notes_lines) + '\n'

    result = {
        'release_candidate': bool(candidate),
        'candidate_count': len(candidate),
        'items': candidate,
        'blockers': blockers,
        'commit': commit_sha,
        'run_url': run_url,
        'notes_path': 'docs/releases/latest-candidate.md',
    }

    out = json.dumps(result, ensure_ascii=False, indent=2)
    Path(os.environ.get('GITHUB_OUTPUT_JSON', 'release_candidate_result.json')).write_text(out + '\n', encoding='utf-8')
    Path('docs/releases/latest-candidate.json').write_text(out + '\n', encoding='utf-8')
    Path('docs/releases/latest-candidate.md').write_text(notes, encoding='utf-8')
    print(out)


if __name__ == '__main__':
    main()
