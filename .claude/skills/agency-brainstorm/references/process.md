# The session protocol — phases and rounds

The agency works in disciplined phases. The core is **≥ 10 consensus rounds** of debate
that move from divergence to a small set of excellent options. The President chairs
throughout.

## Two execution modes

Pick based on what the host supports; state which you used.

- **Delegated (preferred when subagents are available).** Spawn **one subagent per active
  role**, each given: the brief, the grounding (brand + design system), the research, and
  its discipline's mandate. Each round, roles respond independently; the President (the
  main agent) reads them, resolves conflicts, and writes the round's consensus. This gives
  genuinely independent perspectives and avoids single-voice bias. Dispatch role subagents
  **in parallel** each round with a shared brief so they don't diverge on facts.
- **In-context (always available).** The executing agent role-plays the whole panel as a
  structured, **attributed** transcript ("President:", "UX Director:", …). Still ≥10 rounds,
  still explicit consensus each round.

## Phase 0 — Convene & ground

1. **Read the brief** — exactly what decision is being made and what "good" means for it.
2. **President sizes the room** (see `roster.md`): program-level → full panel; focused →
   President + subset.
3. **Ground in brand + design system.** Load the brand skill and design-system skill from
   the invoking context (`.claude/skills/*`, `apm.yml`, brand manual). Extract the rules,
   tokens, components, effects and prohibitions that bind this decision.
4. **If neither exists → NEW BRAND:** run `intake-new-brand.md` to extract the brand and
   design elements from the executing agent **and the user** before proposing anything.
5. Record the **grounding facts** (locked constraints) the panel must honor.

## Phase 1 — Competitive & trend research

The Design Researcher searches the internet for (a) direct and aspirational competitors,
(b) current best-in-class references for this exact surface/decision, and (c) cutting-edge
patterns and what is now table-stakes vs differentiating. Share a concise benchmark with
the panel. This is **mandatory whenever the decision benefits from market grounding**
(almost always for positioning, landing pages, and any "be modern/cutting-edge" ask).

## Phase 2 — The rounds (≥ 10)

Each round has the same shape:

1. **Propose / react** — each active role contributes a proposal or a critique from its
   discipline (independent in delegated mode).
2. **Critique against the three lenses** — every idea is tested against: the **brand**, the
   **design system**, and the **research/competitive frontier**. Weak ideas are cut.
3. **President drives consensus** — the President states the round's agreed position
   (what's in, what's out, what to explore next). Ties are broken by the President; forced
   consensus early is challenged by an assigned devil's-advocate.
4. **Copy gate (when words are in play)** — the Senior Copywriter lists every proposed
   user-visible string and marks **pass / fail** against the copy laws in `roster.md`
   (subject-before-affiliation, no orphan clauses, product first, human chrome). Failures
   block that option until rewritten.
5. **Refine** — surviving directions are sharpened for the next round.

The rounds **narrow deliberately**: early rounds diverge widely (many directions); middle
rounds cluster and kill; late rounds refine the finalists to craft level (type, color,
layout, motion timing, copy). **Minimum 10 rounds.** If the panel has not genuinely
converged by round 10, continue — and the **delegating agent may require more rounds**.
Do not fake convergence to hit 10.

Keep a short **running log**: one line per round with the consensus reached, so the
narrowing is auditable.

## Phase 3 — Converge & deliver

Distill to **1–3 optimal options (max 5)** for the exact evaluation. Each option is
specified to the depth the decision needs (concept, rationale, brand/DS compliance,
competitive positioning, motion/interaction, **verbatim copy strings with Copywriter
sign-off**, risks). Options that include user-facing text without Senior Copywriter
sign-off are **void**. The President gives a **ranked recommendation** with the
reasoning. Deliver in the format of
[`research-and-output.md`](research-and-output.md).

Hand the options to the invoking agent or the user. This skill decides **what the options
are**; execution of the chosen option (for UI) then goes through the design-system skill's
element/new-element protocol — the agency does not bypass the design system, it feeds it.
