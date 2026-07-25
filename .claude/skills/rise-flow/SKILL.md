---
name: rise-flow
description: Rise-Flow — the holding's generic, git-flow-like repository integration & branch/worktree lifecycle discipline. Capability 1 (Progressive Integration) prevents long-lived local/branch divergence from origin/main before it snowballs — a pre-flight drift check at the start of any task, concrete drift budgets, escalate-don't-route-around on any blocker, multi-worktree/multi-clone awareness, and a diff-by-diff (never vibes-based) reconciliation procedure once a repo is already past budget. Use at the START of any task in a git repository (the main checkout or any worktree of it), before creating a new branch/worktree/clone for feature work, when a repo hasn't been touched in a while, or when a repo is found already diverged/dirty. More capabilities get added to this same skill over time — see the capability index below; each ships as its own reference doc. Complements (never duplicates) memory-bank and session-handoff — those own topic knowledge and the end-of-session persistence gate; Rise-Flow owns the ongoing git-mechanics discipline that keeps branches and worktrees from silently drifting apart in the first place.
---

# Rise-Flow — Repository Integration & Branch Lifecycle Discipline

A generic (brand-agnostic) workflow discipline, named and structured the way **git-flow**
named branching: git-flow gives every team one shared vocabulary for branch roles
(`main`/`develop`/`feature/*`/`release/*`/`hotfix/*`) instead of reinventing branch hygiene per
repo. Rise-Flow does the same for agent-driven development across every repository in the
holding — a growing set of **named capabilities**, each governing one part of how work moves
from an agent's local changes back into the shared trunk. Capability 1 exists because of a real
incident (below); every future capability is added as a new reference doc and indexed here,
never bolted onto an existing capability's document.

## Why this exists (the incident that produced Capability 1)

`risegen-ai-platform`'s local `main` checkout was found **110 commits behind `origin/main`**,
carrying **~300 modified/untracked files**, after several feature branches and worktrees
(`ecosystem-app-registration-{si4rm0,reeval-s36288}`, `nexus-remote-oidc`, a dozen parallel
worktrees under `risegen-ai-platform-worktrees/`) were developed and merged upstream through
other clones/sessions without this checkout ever syncing back. Reconciling it safely required a
manual, diff-by-diff forensic pass — classifying all 270 changed paths and confirming every one
already existed, in an equal-or-more-advanced form, in `origin/main` — before it was safe to
reset. The same repository's own memory bank had **already** recorded one prior ad-hoc rescue for
a similar drift (`backup-20260725-*` branches). A near-identical shape showed up independently in
`holding-central-ai-assets` itself the same day: a global skill mirror had drifted from its
canonical source and been patched in place, unsynced, for a period before anyone caught it. This
is a **structural gap**, not a one-off mistake — hence a durable skill, not a one-time fix.

## Capability index

| # | Capability | Governs | Reference |
|---|---|---|---|
| 1 | Progressive Integration | Drift budgets, pre-flight sync checks, multi-worktree awareness, escalate-don't-route-around, diff-by-diff reconciliation | `references/c1-progressive-integration.md` |

Add a row + a new `references/cN-<slug>.md` file for every future capability. Never grow this
table by rewriting an existing capability's own reference doc's meaning — that is a breaking
change to that capability's contract and gets its own row/version note instead. Bump
`skill.json`'s `version` per this repo's `AGENTS.md` semver rule (patch for wording, minor for a
new capability, major for a breaking change to an existing one's contract).

## Hard invariants (apply across every capability, present and future)

1. **Verify with a command, never with a claim.** A continuation prompt, a commit message, or a
   prior session's "done" is a LEAD, not a fact — confirm real state with `git fetch` plus
   `git status` / `git log` / an actual diff. Same discipline `research-before-asserting` and
   `session-handoff` already apply to their own domains; Rise-Flow applies it to git state
   specifically.
2. **Escalate a blocker, never route around it.** If a check can't run — access denied, an
   unexpected permission error, an ambiguous merge base — say so plainly and stop. This is the
   git-shaped variant of the "assume-instead-of-ask" failure mode already registered in
   `risegen-lucensmind`'s memory bank (Sample #001); Rise-Flow exists partly to catch it before
   it snowballs into the kind of drift described above.
3. **One capability, one reference doc.** `SKILL.md` stays a stable index plus these invariants;
   all procedural detail lives under `references/`, so adding capability N never requires
   touching capabilities `1..N-1`'s own documents.
4. **Brand-agnostic, always.** Every capability here must apply to any repository in any of the
   holding's organizations without modification. A rule that only makes sense for one brand's
   process belongs in that brand's own tooling repo (e.g. RiseGen's `development-policy`), never
   here.

## Relationship to other skills

- **`memory-bank`** owns persistent per-topic knowledge (decisions, progress). Rise-Flow never
  duplicates that content — it points at the relevant topic when a drift-reconciliation event
  needs recording (see Capability 1's closing step).
- **`session-handoff`** owns the END-of-session, point-in-time persistence gate (`git status`
  and `git log @{u}..` both empty before a handoff ships). Rise-Flow's Capability 1 owns the
  COMPLEMENTARY start-of-work and during-a-long-session discipline: the pre-flight check that
  catches drift BEFORE new work starts, and the sync-back cadence that keeps a long session from
  ever reaching session-handoff's gate with a 110-commit backlog left to reconcile.
- **`research-before-asserting`** and RiseGen's `development-policy` (`verify-with-a-command`)
  state the same evidence-over-confidence posture that Capability 1 applies specifically to git
  state.

## Reference implementation

The `risegen-ai-platform` reconciliation performed 2026-07-25 (recorded in that repository's own
memory bank) is Capability 1's first real-world application.
