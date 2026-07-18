# The session protocol — phases and rounds

The council works in disciplined phases. The core is a series of **consensus rounds** that
move from wide divergence to a small set of excellent, honestly-compared options. The Chief
Architect chairs throughout and keeps a running log.

## Two execution modes

Pick based on what the host supports; **state which you used**.

- **Delegated (preferred when subagents are available).** Spawn **one subagent per active
  role**, each given: the brief, the grounding (ADRs + code + live-system facts), the
  research, and its discipline's mandate. Each round, roles respond **independently**; a
  **Sonnet moderator** reads them, resolves conflicts, and writes the round's consensus,
  which the **executive layer (top model) ratifies**. This yields genuinely independent
  perspectives and avoids single-voice bias. Dispatch role subagents **in parallel** each
  round with a **shared brief + shared verified facts** so they cannot diverge on the facts.
  Per decide-high/execute-low: the Sonnet moderator runs the rounds, role subagents may run
  on cheaper models, and the top model stays executive — it ratifies and owns the ADR.
- **In-context (always available).** The executing agent role-plays the whole panel as a
  structured, **attributed** transcript ("Chief Architect:", "Systems Architect:", …). Same
  rounds, same explicit consensus each round, same red-team.

## Phase 0 — Convene & ground

1. **Read the brief.** State exactly what decision is being made, the options on the table (if
   any), the constraints, and what "good" means for this decision (the success criteria).
2. **Chair sizes the room** (see `roster.md`): weighty/program-level → full panel; focused →
   Chair + the disciplines the decision touches.
3. **Ground in the source of truth.** Enumerate the **FULL** ADR index of the owning repo(s)
   AND the organization's source-of-truth repo — every file in `docs/decisions/`, by listing, not by guessed relevance — read
   the titles, and load every ADR that could constrain the decision. Also load the repo
   `AGENTS.md`/`CLAUDE.md` and the code the decision touches. Accepted decisions are
   **locked**; **founder-fixed rulings are immutable inputs** (SKILL.md rule 2b) — a
   conflicting option can only be a labeled SUPERSESSION PROPOSAL, never a ratified decision,
   until the founder explicitly assents. Prefer a **read-only empirical probe** of the live
   system over an assumption about current behavior.
4. **No records yet →** capture the constraints **with the human**: goals, non-goals, hard
   constraints (latency/cost/compliance/team/skills), and the definition of "good".
5. **Record the grounding facts** — the locked constraints + the verified current-state facts
   the council must honor. Everything proposed is checked against these.

## Phase 1 — Primary-source research & empirical validation

Before debating, establish the **facts** the decision rests on. The relevant specialists
(Network/Systems/Security/Data/Performance) each:

- Consult **primary sources** for their load-bearing claims — official docs, RFCs/specs,
  source code, real benchmarks. **Never rely on training memory** for a fact that decides the
  outcome (cutoff/staleness risk).
- **Validate empirically** when feasible — a read-only request, a query, a micro-benchmark,
  a log — to confirm how the system *actually* behaves, not how it's assumed to.
- Identify **where the cost/latency/risk actually lives** on the path in question, so the
  council optimizes the layer that matters and not the obvious-but-wrong one.

Share a concise **fact sheet** with the panel: each key fact tagged **verified** (with the
source/probe) or **assumed** (flagged as a risk to resolve). Assumptions that decide the
outcome must be converted to verified facts before Phase 3.

## Phase 2 — The rounds

Each round has the same shape:

1. **Propose / react** — each active role contributes a proposal or a critique from its
   discipline (independent in delegated mode).
2. **Stress-test against the lenses** — every surviving option is tested against:
   - **Correctness** — does it actually solve the problem, including edge cases?
   - **Failure modes** — what happens when a part fails; blast radius; recovery.
   - **Security / trust** — trust boundaries, authN/Z, secrets, revocation.
   - **Performance & where latency lives** — the real bottleneck, fairly measured.
   - **Operational cost & complexity** — build + run + on-call + cognitive load.
   - **Reversibility** — one-way door or easily undone? (raises the bar for the former).
   - **Fit with the source of truth** — consistent with ADRs/constraints, or a supersession.
   - **Buildability** — can the Spec Engineer turn it into an executable spec?
   Weak options are cut with a **recorded reason in prose** — the specific fact, lens or
   trade-off that killed them, not a bare verdict. Every cut feeds the deliverable's
   rationale record (`output.md` §2b); no option dies silently.
3. **Chair drives consensus** — the Chair states the round's agreed position (what's in, out,
   and what to explore next). Ties are broken by the Chair. An assigned **devil's-advocate /
   red-team** attacks any forced or premature consensus.
4. **Refine** — surviving directions are sharpened for the next round.

The rounds **narrow deliberately**: early rounds diverge widely (many approaches); middle
rounds cluster, stress-test and kill; late rounds refine the finalists to implementation
depth (contracts, failure handling, ops, migration). **Round floor: ≥ 8 rounds** for any
session; a **weighty or hard-to-reverse** decision runs **more** until it converges. If the
panel has not genuinely converged at the floor, continue — and the executive layer may
require more. **Do not fake convergence** to hit a number.

Keep a **running log with reasoning**: per round, the consensus reached **plus 2–5
sentences of rationale** — what was proposed, what was weighed against what, and which
grounding fact or lens drove the outcome — so the narrowing is auditable and a reader who
was not in the room can follow (and challenge) the reasoning. A bare one-line "converged
on X" does not meet the bar; the log feeds the deliverable's rationale record
(`output.md` §2b).

## Phase 3 — Converge & deliver

Distill to **1–3 optimal options (max 5)** for the exact decision. Each option is specified to
the depth the decision needs (approach, rationale, the eight-lens assessment, failure/ops
notes, reversibility, risks). The Chair gives a **ranked recommendation** with the reasoning,
and — for a weighty/irreversible fork — hands it to the **human** rather than executing.

Produce, in the format of [`output.md`](output.md):
- the ranked options with honest trade-offs;
- an **ADR-ready decision** (Context · Decision · Why · Consequences · Status) for the pick;
- for the chosen option, the **Spec Engineer's executable spec** (contracts, interfaces,
  steps, acceptance/validation), so implementation needs no further decisions.

Hand the deliverable to the invoking agent or the user. This skill decides **what the decision
is and why**; execution follows the spec, and an accepted decision is recorded as an ADR in
the owning repo.
