---
name: development-policy
description: The RiseGen development escalation policy — the standing operating model for how work gets done across the ecosystem. Read at the START of any non-trivial engineering task to apply decide-high/execute-low correctly, whenever deciding which model tier should do a piece of work, whenever a plan needs founder validation before delegation, whenever a decision might warrant convening a council (architecture-council or product-taxonomy-council), whenever an action would create billable spend, and whenever verifying delegated output. Complements (does not duplicate) the global model-escalation-discipline skill and the architecture-council skill — reference both, read this for the RiseGen-specific contract between them.
---

# RiseGen Development Policy — Decide-High / Execute-Low

This is the standing **operating policy** for how development work happens in the RiseGen
ecosystem — not a workflow for one artifact, but the governing contract between the model
that decides and the model that executes, on every task, in every repo. It **complements**
two other skills and does not duplicate either:

- **`model-escalation-discipline`** (global) — the mandatory policy that forces the
  decide-high/execute-low split at the model level. Invoke it at the start of any non-trivial
  task; it owns the *mechanics* of that split.
- **`architecture-council`** (in `holding-central-ai-assets`) — the standing room for
  hard/hard-to-reverse technical decisions. This policy states **when** to convene it and the
  current round/moderator rule; the council skill owns its own room, process and output format.
- **`product-taxonomy-council`** (in `holding-central-ai-assets`) — the equivalent standing room
  for naming and taxonomy decisions. Same relationship.

Read this skill for: the escalation ladder, the plan→validate→delegate contract, the
best-not-simplest rule, the council-convening rule, the spend-authorization boundary, and the
verify-with-a-command rule.

## The escalation ladder — which tier decides, which tier executes

- **Decide (top-tier model — e.g. Opus, Fable):** complex work — planning, architecture,
  solution envisioning, trade-off analysis, diagnosis, the **why** behind a change, writing
  specs/ADRs/memory-bank decisions. **Never delegated**, regardless of how mechanical a
  sub-step might look in isolation — the judgment that decided the step is not mechanical even
  if the step's output is.
- **Moderate (Sonnet):** round-by-round synthesis inside a council session (see "Council
  trigger" below), and delegated execution that carries **residual judgment** — interpreting
  an already-decided spec, not making a fresh decision.
- **Execute (cheapest capable model — Haiku for pure-mechanical, Sonnet when residual
  judgment is needed):** applying an already-decided change — file edits, renames,
  boilerplate, running an install, a repetitive sweep, mirroring a directory, applying a
  config value someone else already chose.

The deciding model **chooses the tier for delegated work** — it, not the executor, knows the
task's real difficulty. When in doubt between two tiers, escalate one tier; re-running a
poorly executed task costs more than the extra tier would have.

## Hierarchical delegation — the top-tier model does not track simple tasks

The ladder is **not two flat levels**. For any non-trivial slice, insert a **coordinator**
tier so the top-tier model plans and validates the *outcome*, and never supervises the
individual simple tasks:

- **Top-tier (executive):** draws the plan, defines the slice + spec, and validates only the
  **finished, verified deliverable**. It does NOT babysit the mechanical sub-tasks.
- **Sonnet coordinator** — a **delegation-capable** subagent (an agent type that itself has
  the Agent tool, e.g. `general-purpose`): takes the spec'd slice, **decomposes it, delegates
  the mechanical parts to Haiku workers, supervises and verifies them with a command**
  (build/tests/grep), and returns **one** finished, verified deliverable upward.
- **Haiku workers:** the pure-mechanical execution the coordinator hands down and checks.

Rule: the executive carves work into slices and picks the entry tier per slice. A slice with
a mechanical interior goes to a **Sonnet coordinator that runs its own Haiku workers** — not
to the top-tier model to track step by step. The executive closes the loop on the **slice
outcome** (verify-with-a-command on the deliverable), not on each worker's step. Reserve a
direct top-tier→Haiku dispatch only for a genuinely atomic mechanical task with no interior to
coordinate.

## The plan → validate → delegate contract

1. **Plan on the top-tier model, from primary sources.** Exact files, functions, commands,
   config values — never "adjust as needed" or "validate X later." Every fact the plan
   depends on is verified **now**, during planning, not deferred to whoever executes it.
2. **Validate the plan with the founder** before execution starts on anything non-trivial or
   hard to reverse — present findings, trade-offs and a recommendation, and **wait** for
   explicit go before executing the irreversible.
3. **Delegate execution to the cheapest capable model**, once validated — a self-contained
   **instruction + spec**, never "here's a decision to make." A subagent that has to decide
   something is a sign the plan was incomplete, not a sign the subagent needs more autonomy.
4. **Dispatch independent delegated tasks in parallel, in a single message**, sharing one
   brief/spec/glossary — without a shared spec, parallel executors diverge because none of
   them sees the others' work.
5. **Verify delegated output with a command**, not with confidence (see "Verify-with-a-command"
   below) — the deciding model closes the loop; a subagent saying "done" is a claim, not
   evidence.

## Best-not-simplest

Optimize for the **best** solution the codebase and constraints actually support — not the
first simplest-looking one. "Simplest" is a hypothesis to stress-test (does it actually hold
up against the real constraints, or does it just look cheap to write?), never a default to
ship without checking. This pairs directly with the architecture-council's red-team lens and
its rule to find where cost/risk **actually** lives before optimizing the obvious-but-wrong
layer.

## Council trigger — when to convene, and the current rule

Any **weighty or hard-to-reverse** decision must be decided by the relevant standing council,
not by the top-tier model reasoning alone in a single pass — structured, primary-sourced,
multi-round, red-teamed deliberation catches what even a very capable single pass misses.

- **Hard technical/architecture decisions** (system topology, protocol, data model, API
  contract, auth/trust boundary, build-vs-buy, framework/runtime, performance/scaling,
  migration strategy) → convene **`architecture-council`**.
- **Naming/taxonomy decisions** (a new product/service/feature name, a name under question, a
  catalog/taxonomy change) → convene **`product-taxonomy-council`**.

**Current standing rule for every council session** (applies to both of the above, and to any
future standing council): a session runs a **minimum of 8 consensus rounds**; each round is
synthesized by a **Sonnet moderator** — a process function, not a domain vote — and the
**top-tier model stays executive**: it sizes the room, sets the brief, ratifies every round,
breaks ties, and owns the final decision. This is decide-high/execute-low applied to a
council's own internal operation — round synthesis is high-volume structured work fit for a
cheaper model; the decision is not, and never moves off the top-tier model.

`architecture-council` **v1.1.0+** already encodes this rule (≥8-round floor, Sonnet
moderator, top-tier model executive) — no mismatch to work around; run it as its own
documents state.

## Authorization boundary — paid infra & external spend

No agent may create billable spend — instance/GPU rental, new cloud resources, a paid API
tier, a plan upgrade, or any action with immediate or recurring cost — **without explicit,
specific authorization from the founder for that exact action and its cost.** Holding the
credential that makes an action possible is not holding the authorization that makes it
allowed. An ambiguous reply (an ID, an "ok" answering something else) is not authorization —
when in doubt, ask; the cost of asking is one message, the cost of an unauthorized spend is
trust.

## Verify-with-a-command

Never accept a delegated subagent's "done" as evidence that the work is correct. Verify with
a command — run the test, grep for the symbol, check the build, curl the endpoint, diff the
file. This is the same discipline the `verify` skill applies to code changes; apply it to
**every** delegated task, not only ones that touch source code. An agent's summary describes
what it intended to do, not necessarily what it did.

## Traceable commits

Every commit that lands a completed implementation carries `Session:` + `Agent:` in its body.
Never auto-commit without explicit authorization — present the change and the exact commit
command, and wait. One commit per completed unit of work; do not fragment a single
implementation across many commits or silently batch several conclusions into one.

## Rebuild and redeploy after every change

After any code change to a product or service — **binary, extension, library, or
configuration** — the default is to **rebuild and redeploy locally** before considering the
task done. This is not optional; it is the last step of every implementation, as fundamental
as running the tests.

**The sequence is always:** edit → test → rebuild → redeploy → verify the deployed artifact.

- **Binary (Go, Rust, etc.):** rebuild to all install paths the product resolves at runtime
  (e.g. repo-root, `~/.local/bin`, `~/.risegen/bin`, symlinks). Kill stale processes so the
  next invocation picks the fresh binary.
- **VS Code extension:** `npm run package` → `code --install-extension <vsix>` → reload
  window.
- **TypeScript library/plugin:** `npm run build` (or equivalent) so downstream consumers see
  the compiled output.
- **Platform service:** rebuild the container/binary and restart the local instance.

**When to ask the user instead of auto-deploying:** only when the deploy itself carries
**observable risk** — deploying to a shared staging/production environment, restarting a
service that other people are using, or any action that could cause data loss. A local dev
redeploy to the developer's own machine is **never risky enough to ask** — just do it and
report the result.

**An implementation without a verified deploy is incomplete.** Do not tell the user to test
a change when the running artifact still has the old code.

