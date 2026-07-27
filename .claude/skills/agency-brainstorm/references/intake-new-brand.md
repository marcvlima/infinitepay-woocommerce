# New-brand intake — when no brand/design-system skill exists

If the invoking context has **no brand skill and no design-system skill**, the decision is
for a **new (or undocumented) brand**. The agency must not assume an identity — it
**extracts** the grounding from the executing agent's context and from the **user**, then
proceeds. Run this before Phase 1.

## Who leads the intake

The **Brand Strategist** leads, the **President** chairs, the **Design Researcher** and
**Verbal Identity Lead** assist. Keep it tight — enough to ground excellent proposals, not
a full brand project (unless the user asks for one).

## Extract from the executing agent's context first (cheap, no questions)

Before asking the user anything, mine what's already available:
- Repo/product docs, READMEs, `AGENTS.md`, any `docs/`, product naming, existing screens,
  assets, colors, fonts, copy, prior decisions (ADRs, memory-bank).
- Any partial brand cues (a logo file, an accent color, a tagline) already in the repo.

Summarize what you inferred and mark confidence, so the user only fills real gaps.

## Then ask the user (only what's missing)

Ask compactly (batch the questions). Cover:
1. **What it is** — product/platform/ecosystem, in one line.
2. **Audience** — who it's for; who it's *not* for.
3. **Positioning** — the single differentiating idea; 2–3 competitors/references they
   admire or want to beat.
4. **Personality** — 3–5 adjectives; what to avoid.
5. **Assets in hand** — any existing logo, colors, fonts, name, domain, prior material.
6. **Constraints** — platforms, accessibility, tech, timeline, must-haves/never-dos.
7. **This decision's success** — what a great outcome looks like for the exact ask.

## Produce a grounding brief

Output a short **Grounding Brief** the panel treats as the (provisional) brand + design
truth for this session:
- One-line positioning, audience, personality adjectives.
- Provisional visual direction cues (color/type/mood) extracted or agreed.
- Known constraints and prohibitions.
- Open questions flagged (to confirm with the user).

Recommend (via the President) that, once decisions land, they be **promoted into real
`brand` and `design-system` skills** in `designsystem-master` (or the brand's repo) so the
next session is grounded, not re-extracted. A new brand is defined **with** the user —
never assumed by the agency.
