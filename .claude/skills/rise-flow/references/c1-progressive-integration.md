# Capability 1 — Progressive Integration Discipline

## Trigger

- Start of ANY task in a git repository, including any worktree of it.
- Before creating a new branch, worktree, or clone for feature work.
- Periodically during a long-running session — at minimum before starting a new sub-feature or
  distinct file set within the same session, not only at the very start.
- Whenever a session is handed a repo it hasn't touched recently, or a continuation prompt
  claims a state that hasn't been re-verified this session (see Hard invariant 1 in `SKILL.md`).

## Pre-flight drift check (mandatory, cheap — run before relying on any claimed state)

1. `git fetch --quiet` on the repo's primary remote.
2. Compare the current branch to its upstream: commits behind, commits ahead
   (`git rev-list --left-right --count <branch>...<upstream>`, or `git status -sb` for a quick
   read).
3. `git status --porcelain` — count modified + untracked paths.
4. `git worktree list` — enumerate every worktree of this repo and run steps 2–3 for each of
   them too, not only the one currently open. If the repo has a sibling
   `<repo-name>-worktrees/` directory (a convention already in active use, e.g.
   `risegen-ai-platform-worktrees/`), treat every entry there as a worktree to check.
5. If the repo has a `memory-bank/`, cross-reference open topics against active remote branch
   names — a branch that echoes an open topic which ALSO looks closed/merged elsewhere is the
   exact shape of the `ecosystem-app-registration-{si4rm0,reeval-s36288}` duplication found
   2026-07-25; flag it explicitly.

## Drift budget

Defaults below; a consumer repo may tighten them in its own `AGENTS.md`, never silently loosen
them.

- **Behind by more than 20 commits**, OR **more than 10 modified/untracked paths**, OR **any
  worktree of the same repo left untouched for more than 7 days while still carrying uncommitted
  changes** → STOP. Surface the exact numbers to the user before starting the requested feature
  work. This is a blocking finding, not a background note — silently proceeding past this
  threshold is exactly how the 110-commit / ~300-file incident happened.
- Below the budget: proceed, but still run the pre-flight check every time — cheap insurance,
  never skipped just because the repo "was fine last time."

## Reconciling a repo already past budget

1. **Never assume drift is safe to discard.** Classify every changed/untracked path: does it
   exist on the upstream branch at all? If yes, is it byte-identical? Only paths that are BOTH
   absent upstream AND not byte-identical are candidates for real, at-risk local work —
   everything else is stale noise, safe to align.
2. **For every at-risk path, decide integrate-vs-discard on the evidence, never on a vibe.** Show
   the user the actual diff (not a summary) for anything ambiguous before proposing an action.
3. **Stash before any history-rewriting operation** (`reset --hard`, `rebase`, a force-push) as a
   reversible safety net — even when confident nothing is at risk. The safety net costs one
   command; a wrong assumption costs the work.
4. **`reset --hard`, `checkout .`, `restore .`, `clean -f`, `branch -D`, and any `push --force`
   require the user's explicit, specific authorization for that exact action.** This is not new —
   it is the standing git-safety rule every agent already operates under. Rise-Flow does not
   relax it; it exists to make sure the pre-work that justifies asking for that authorization
   (steps 1–2 above) is actually done, evidenced, and shown before the ask.
5. **Record the reconciliation.** A short entry in the repo's own `memory-bank/` — what drift was
   found, how it was classified, what was discarded vs. integrated, and the evidence for the
   call — so the next session never has to re-derive it from scratch.

## Multi-worktree / multi-clone hygiene

- A repo with N concurrent worktrees or branches touching related feature areas is a standing
  risk of the exact duplication found 2026-07-25 (two branches independently re-doing the same
  epic). When starting feature work that touches an area another active branch/worktree also
  touches, say so before proceeding — the user decides whether to consolidate, or proceeds
  knowingly.
- A worktree whose branch tip is already an ancestor of the upstream default branch
  (`git merge-base --is-ancestor <tip> origin/main`) is done — flag it as a candidate for removal
  (`git worktree remove`). Never remove it unasked.

## What this capability does NOT do

- It does not replace `session-handoff`'s end-of-session gate — that gate still runs at handoff
  time regardless of how clean the pre-flight check found things during the session.
- It does not itself authorize any destructive git operation — see step 4 above. It only makes
  sure the decision to run one, when the moment comes, is evidence-based rather than assumed.
