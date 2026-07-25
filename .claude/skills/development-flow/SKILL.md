# Development Flow — Rebuild & Redeploy Before Retest

Directive for any agent working across repositories: **after changing shared/infrastructure
components (platform, connector, CLI, engine), rebuild and redeploy the affected artifacts
before asking the user to test.** A code edit without redeploy is not yet testable.

## Why this exists

Agent sessions span multiple repositories. A change in one repo (e.g. a platform auth fix)
is invisible to end-to-end tests until the changed component is rebuilt and, when applicable,
reinstalled into the runtime path. Asking the user to retest before this step is complete
wastes their time and breaks trust in the agent's feedback loop.

## The core directive (non-negotiable)

Before any "ready to test" or "please verify" message to the user, the agent MUST ensure all
artifacts changed during the session are rebuild and redeployed. This check is as mandatory
as running lint/tests.

## Redeploy procedures by artifact type

### Go binary (platform, connector, CLI tools)

```
go build -o <output-path> ./cmd/<name>
```

For a project consumed by other components (e.g. a platform API server deployed to dev),
also restart the service or notify the operator.

### CLI tool consumed by VS Code or TUI (e.g. rgai)

The VS Code extension resolves the CLI binary with this precedence (see locate.ts):

1. `risegen.rgaiPath` setting
2. **repo-root binary** — `<workspace>/rgai` when an `opencode.json` sits next to it
   (UNCONDITIONALLY outranks install locations)
3. `/usr/local/bin/rgai` / `~/.local/bin/rgai`, newest mtime
4. Bare `rgai` on PATH

After a CLI change, the required sequence is:

1. Rebuild to the repo-root path (precedence 2, the dev winner):
   `CGO_ENABLED=0 GOOS=darwin GOARCH=arm64 go build -trimpath -o ./rgai ./cmd/rgai`
2. Also refresh install locations (precedence 3) for non-checkout workspaces:
   copy to `/usr/local/bin/rgai` and `~/.local/bin/rgai` (or run `rgai install`)
3. **Kill any running process** (`ps -eo pid,command | grep -E 'rgai|opencode'`)
   — a live session holds the old binary in memory
4. Only then tell the user to reload the window; the respawn picks the fresh binary

### Shared library / internal package

When changing `internal/` packages consumed by the same repo's own binaries, just `go build`
the consuming binary (compiled languages catch staleness at link time). When the internal
package is consumed across repos (e.g. `risegen-ai-platform/api/internal/auth`), the consumer
repos themselves must be rebuild.

## Testing protocol

1. **Unit/integration tests** — run `go test ./...` in the changed repo before claiming the
   change is correct.
2. **Local rebuild** — compile the changed artifact(s) per the procedure above.
3. **Ecosystem consistency** — when changes span multiple repos, rebuild each affected
   binary. A platform-only change does not need all consumers rebuild, but the deployed
   platform binary must reflect the change.
4. **User retest signal** — only after (1) tests pass, (2) the artifact is rebuild, and
   (3) any running process is killed/respanned, tell the user it is ready to test.

## Definition of "redeploy"

- **Go binary**: `go build` to the target path.
- **Shared lib**: `go build` of every consumer binary (or `go install ./...`).
- **VS Code**: `CGO_ENABLED=0 GOOS=darwin GOARCH=arm64 go build -trimpath -o ./rgai ./cmd/rgai`
  + copy to install locations + kill + reload window instruction.
- **Python/node**: the applicable install/build step for the language.

A rebuild is not "done" until the binary at the path the consumer resolves is the new one.
