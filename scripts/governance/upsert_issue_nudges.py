#!/usr/bin/env python3
import json
import os
import subprocess
from pathlib import Path


issue_number = os.environ['ISSUE_NUMBER']
repository = os.environ['REPOSITORY']
data = json.loads(Path('assignment_nudger_result.json').read_text())

for action in data.get('actions', []):
    if action.get('type') != 'comment':
        continue
    marker = f"<!-- governance-nudge:{action['key']} -->"
    body = marker + "\n" + action['message']
    lookup = subprocess.run(
        [
            'gh', 'api', f'repos/{repository}/issues/{issue_number}/comments', '--paginate',
            '--jq', f'.[] | select(.body | contains("{marker}")) | .id'
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    comment_id = lookup.stdout.strip().splitlines()[0] if lookup.stdout.strip() else ''
    if comment_id:
        subprocess.run(['gh', 'api', f'repos/{repository}/issues/comments/{comment_id}', '-X', 'PATCH', '-f', f'body={body}'], check=True)
    else:
        subprocess.run(['gh', 'issue', 'comment', issue_number, '--repo', repository, '--body', body], check=True)
