#!/usr/bin/env python3
import json
import os
import subprocess
from pathlib import Path


pr_number = os.environ['PR_NUMBER']
repository = os.environ['REPOSITORY']
obj = json.loads(Path('merge_gate_result.json').read_text())
body = "\n".join([
    '<!-- governance-merge-gate -->',
    f"## Governance merge gate: {obj['gate']}",
    '```json',
    json.dumps(obj, ensure_ascii=False, indent=2),
    '```',
])
lookup = subprocess.run(
    [
        'gh', 'api', f'repos/{repository}/issues/{pr_number}/comments', '--paginate',
        '--jq', '.[] | select(.body | contains("<!-- governance-merge-gate -->")) | .id'
    ],
    capture_output=True,
    text=True,
    check=True,
)
comment_id = lookup.stdout.strip().splitlines()[0] if lookup.stdout.strip() else ''
if comment_id:
    subprocess.run(['gh', 'api', f'repos/{repository}/issues/comments/{comment_id}', '-X', 'PATCH', '-f', f'body={body}'], check=True)
else:
    subprocess.run(['gh', 'pr', 'comment', pr_number, '--repo', repository, '--body', body], check=True)
