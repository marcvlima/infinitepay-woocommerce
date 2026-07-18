# The deliverable — format of a council session

A session produces one document with five parts. Keep it decision-grade: honest trade-offs,
verified facts, no faked confidence. State the **execution mode** used (delegated /
in-context) and the **round count** at the top (exactly 2 — diverge, then converge; a 3rd
only for a foundational family decision, with the reason recorded). Include the captured
**business/positioning brief** the candidates were judged against, and — on a re-run —
**what was previously rejected and why** (to show the new proposals are fresh, not anchored).

## 1. Brief & grounding (short)

- **Decision:** the exact naming question and the definition of "good" (success criteria —
  audience, family, expected lifespan).
- **Room:** the roles convened (and why that sizing).
- **Grounding facts:** locked names/families (catalog + brand + prior naming ADRs) +
  current-state facts, each tagged **verified** (source) or **assumed** (flagged risk).

## 2. Naming decision

```
### Decision — <chosen name>
Family: <the taxonomy family/parent this belongs to — existing or new>
Rationale: <one paragraph — why this name, tied to the grounding facts and the research>
Assessment (eight lenses):
  - Distinctiveness:                 …
  - Clarity / information scent:     …
  - Verbal-identity fit:             …
  - Taxonomy / family fit:           …
  - Brand-architecture fit:          …
  - Developer/technical-identifier coherence: … (marketing name -> technical identifier)
  - Reversibility:                   cheap to change | costly to change — …
  - Governance buildability:         …
```

## 3. Alternatives considered

For each alternative seriously debated (not every name generated in Phase 2 — only the ones
that survived to a late round):

```
### Alternative — <name>
Why it was in contention: <one line>
Why it lost: <the decisive lens or fact>
```

Alternatives must be **genuinely differentiated** finalists, not cosmetic variants of the
winner. If two candidates collapse under stress-testing into the same trade-off, say so and
drop one.

## 4. Catalog entry / entries

The exact row(s) to add or update in the ecosystem's product/service catalog:

| Field | Value |
|---|---|
| Name | |
| Family | |
| Type (product / service / feature) | |
| Status (proposed / active / deprecated) | |
| One-line description | |
| Technical identifier(s) (API/CLI/package) | |
| Aliases / do-not-use | |
| Supersedes (if any) | |

## 5. Consistency checks + ADR-ready decision

**Consistency checks** (run and record the result of each — do not skip):
- **Catalog collision check:** no existing name/family collides with the new one.
- **Brand-voice check:** the name matches the verbal-identity guide's tone and rules.
- **Technical-identifier check:** the mapped API/CLI/package identifier is unambiguous and
  consistent with the ecosystem's existing identifier conventions.
- **Legal/trademark flag:** cleared by the Legal/Trademark Screener, or explicitly flagged as
  an open risk requiring founder sign-off before the name ships publicly.

**ADR-ready decision** (drop-in for the owning repo's `docs/decisions/`):
```
# NNNN — <title>
Status: proposed
Context: <what forced the naming decision>
Decision: <the name, its family, its technical identifier>
Why: <rationale + the key trade-off vs the strongest alternative>
Consequences: <good and bad; what it supersedes, if anything; migration notes if renaming>
```

For a **weighty/irreversible** fork (a brand-new top-level family, or renaming a shipped,
customer-facing product), the Chair hands the recommendation to the **founder** and **waits**
— it does not apply the rename or file the trademark unasked. Anything that needs verifying to
trust the decision (a collision check, a trademark screen) is verified **now**, during the
session, not deferred to whoever implements it.
