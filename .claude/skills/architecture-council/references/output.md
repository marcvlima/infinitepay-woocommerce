# The deliverable — format of a council session

A session produces one document with five parts. Keep it decision-grade: honest trade-offs,
verified facts, no faked confidence. State the **execution mode** used (delegated / in-context)
and the **round count** at the top.

**The governing standard: the deliverable EXPLAINS, it does not just conclude.** This is an
architectural review, and rationale exposition is the norm of the craft: every consensus,
every elimination and every ranking position is written out **in prose, traceable to the
grounding facts**, so a reader who was not in the room can follow the reasoning — and
disagree on the merits. Verdicts without their "why" (bare lens bullets, one-word
assessments, unexplained rankings) are an incomplete deliverable.

## 1. Brief & grounding (short)

- **Decision:** the exact question and the definition of "good" (success criteria).
- **Room:** the roles convened (and why that sizing).
- **Grounding facts:** locked constraints (ADRs/conventions) + current-state facts, each
  tagged **verified** (source/probe) or **assumed** (flagged risk).
- **ADR compliance table (MANDATORY — SKILL.md rule 2b):** one row per constraining ADR:
  `ADR · clause · verdict (complies | CONFLICTS → supersession proposal, pending founder
  assent)`. A deliverable without this table is incomplete; a decision ranked #1 with an
  unresolved CONFLICTS row is void.

## 2. Options (1–3, max 5)

For each option, a compact block:

```
### Option <N> — <name>
Approach: <one-paragraph description>
Rationale: <WHY this option exists and what problem-shape it fits — the reasoning that
  earned it a place among the finalists, in prose, anchored to the grounding facts>
Assessment (eight lenses — each entry states its reasoning, not just a verdict):
  - Correctness:        …
  - Failure modes:      …
  - Security / trust:   …
  - Performance/latency: … (where the cost actually lives)
  - Operational cost/complexity: …
  - Reversibility:      one-way door | reversible — …
  - Fit with source of truth: consistent | supersedes ADR <x> because …
  - Buildability:       …
Trade-offs vs the other finalists: <narrative, pairwise where it matters: choosing this
  option GAINS <what> and GIVES UP <what> relative to Option <M>… — the comparison a
  reviewer needs to weigh the options themselves, not just read the panel's verdict>
Best when: <the conditions under which this option wins>
Risks / unknowns: <and how to retire them>
```

Options must be **genuinely differentiated** — not one idea with cosmetic variants. If two
"options" collapse under stress-testing into the same trade-off, say so and drop one.

## 2b. Rationale record (the reasoning trail)

The audit trail that makes the review reviewable — two parts, both mandatory:

- **Round log with reasoning:** per round, the consensus reached **plus 2–5 sentences of
  the rationale behind it** — what was proposed, what was weighed against what, which
  grounding fact or lens drove the outcome. A bare one-line "converged on X" is not enough.
- **Eliminated approaches:** every approach cut before the finalists, each with: what it
  was (one line), **why it lost** (the specific fact/lens/trade-off that killed it, in
  prose), and in which round. A reader must be able to check that no strong option died
  silently or for an unstated reason.

## 3. Recommendation (the Chair)

- The **ranked** order with **pairwise reasoning written out**: why #1 beats #2, why #2
  beats #3 — each in prose, anchored to the decisive facts/lenses, not just asserted.
- The **decisive factor** (the lens or constraint that broke the tie).
- **What would change the ranking** (the condition under which #2 wins) — so the reader can
  sanity-check against their own weighting.
- For a **weighty/irreversible** fork: explicitly hand the choice to the human — present the
  recommendation and **wait**; do not execute a one-way door or incur new paid cost unasked.

## 4. ADR-ready decision + executable spec

For the recommended option, produce:

**ADR-ready decision** (drop-in for the owning repo's `docs/decisions/`):
```
# NNNN — <title>
Status: proposed
Context: <what forced the decision>
Decision: <what is decided>
Why: <rationale + the key trade-off>
Consequences: <good and bad; what follows; what it supersedes>
```

**Executable spec** (the Spec Engineer's output — only for the chosen option):
- exact files/components to change or create, and their responsibilities;
- contracts: interfaces, request/response shapes, data schemas, error semantics;
- sequencing / dependencies (what must land first);
- acceptance criteria + the validation commands/probes that prove it works.

The spec must be executable by another agent/engineer **with no further decisions to make** —
if a "decide X later" remains, the council has not finished. Anything that needs verifying to
trust the spec is verified **now**, during the session, not deferred to the implementer.
