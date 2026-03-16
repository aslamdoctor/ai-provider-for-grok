---
name: deploy
description: Deploy a new version of the plugin to WordPress.org via GitHub Actions
user_invocable: true
---

# Deploy Plugin to WordPress.org

Deploy the plugin by bumping the version, committing, tagging, and pushing to trigger the GitHub Actions workflow.

## Steps

1. **Ask for the new version number** if not provided as an argument. Show the current version from `readme.txt` (the `Stable tag:` line) for reference.

2. **Update version numbers** in these files:
   - `readme.txt`: Update `Stable tag:` to the new version
   - `aslams-provider-for-grok-ai.php`: Update `Version:` in the plugin header
   - `readme.txt`: Add a new changelog entry under `== Changelog ==` (ask the user for changelog notes, or summarize recent commits since the last tag)

3. **Show the diff** to the user and ask for confirmation before committing.

4. **Commit** the version bump with message: `Bump version to {version}`

5. **Push** the commit to `origin master`.

6. **Create and push the tag**:
   ```
   git tag {version}
   git push origin {version}
   ```

7. **Monitor the deployment** by checking `gh run list --limit 1` and report the status to the user. If the run fails, show the logs with `gh run view --log-failed`.
