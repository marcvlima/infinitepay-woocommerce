---
name: agency-brainstorm
description: Convene a professional design / marketing / digital-platform agency to run a multi-round, consensus-driven brainstorming session for ANY brand-positioning or UI/UX/experience decision — brand definition or repositioning, creating or changing a UI element, a page or a whole experience (e.g. a landing-page redesign), naming, art direction, motion, AND all user-facing copy. Use WHENEVER such a decision is being made or evaluated. The agency's Managing Partner (President) always chairs; specialists include a Senior Copywriter / UX Writer who gates every string. Analyze brand + design system, research competitors, refine across at least 10 consensus rounds until 1–3 (max 5) optimal options remain. Product-in-ecosystem lines MUST name the product subject (never bare "Part of …").
---

# Agency Brainstorm — the standing creative agency for brand & UI decisions

You are convening **a real, senior, ultra-specialized creative agency** — marketing,
brand, UX/UI, **copywriting / UX writing**, motion and digital-platform experts who define
design and marketing for modern **products, platforms and ecosystems** at the cutting edge.
This is not a casual chat: these are important programs, so the **Managing Partner
(General President) always chairs the room**, and the panel works in disciplined rounds
until it reaches consensus on a small set of excellent options.

**Invoke this skill for every brand/UI/UX/experience decision** — brand positioning or
definition, creating or changing any UI element, a page or a full experience, naming,
art direction, motion, **or any user-facing string** (empty states, auth, banners, CTAs,
ecosystem footers). If you are about to make such a decision without this skill, stop and
convene the agency.

## The room (who is always in it)

The **Managing Partner / President** chairs every session (non-negotiable). The standing
panel is defined in [`references/roster.md`](references/roster.md) — a President plus a
core of specialists (Executive Creative Director, Brand Strategist, UX Director,
UI/Visual Lead, Motion & Interaction Designer, Art Director, **Senior Copywriter / UX
Writer**, Verbal Identity/Naming Lead, Design Researcher/Competitive Intelligence,
Digital-Platform/Product Specialist), with optional specialists (Accessibility,
Growth/Conversion, Design-Ops, Localization) convened as the decision requires.
Program-level decisions (positioning, a full landing) convene the **full standing panel**;
smaller decisions convene the President + a relevant subset — **always including the
Senior Copywriter when any words ship**.

**How the room is run** — two modes (see [`references/process.md`](references/process.md)):
- **Delegated (preferred when the host supports subagents):** spawn one subagent per role
  so perspectives are genuinely independent; the President synthesizes each round.
- **In-context (always available):** the executing agent role-plays the full panel as a
  structured, attributed multi-round transcript.

## Non-negotiable rules

1. **President always participates and chairs.** They set the brief, force a consensus
   every round, break ties, and own the final ranked recommendation.
2. **Ground in the existing brand + design system FIRST.** Before proposing anything,
   load and analyze the brand and design-system skills of the invoking context (see
   grounding below). Never improvise outside them — or flag a deliberate exception with a
   reason. Every proposal is checked against them.
3. **Research the market when it helps.** The Design Researcher searches the internet for
   competitors and cutting-edge references for the *exact* decision, so options are
   benchmarked against the current frontier — never designed in a vacuum. See
   [`references/research-and-output.md`](references/research-and-output.md).
4. **At least 10 rounds of consensus.** The panel runs **≥ 10 rounds**; each round reaches
   an explicit consensus and refines the proposals (divergence → convergence). The agent
   that delegated the session may require **more** rounds if not yet converged.
5. **Converge to 1–3 (max 5) optimal options.** The session ends only when the panel has
   distilled a small set of genuinely excellent, differentiated options for the exact
   evaluation requested — each with rationale, brand/design compliance, competitive
   positioning, motion/interaction notes, **copy strings**, risks, and the President's
   ranked pick.
6. **Senior Copywriter gates every user-facing string (HARD RULE).** No UI/empty-state/
   banner/CTA/ecosystem line ships from this agency without copy review. See
   [`references/roster.md`](references/roster.md) §Copy laws. In particular:
   - **Forbidden:** bare ecosystem lines like `Part of the <Ecosystem> ecosystem` /
     `PART OF …` with **no product subject**.
   - **Required:** `This product is part of the <Ecosystem> ecosystem` or
     `<ProductName> is part of the <Ecosystem> ecosystem`.
   - Product identity first; ecosystem affiliation second. Human chrome — never raw
     machine codes (`AUTH_REQUIRED`, enums) as primary user copy.

## Grounding: brand + design system (or a new brand)

At the start (Phase 0), establish the brand context:

- **Brand skill present?** Look for a brand skill in the invoking repo/agent
  (`.claude/skills/*brand*`, an `apm.yml` entry, or a brand manual). Load it and treat it
  as locked upstream truth.
- **Design-system skill present?** Look for a design-system skill (`.claude/skills/*design-system*`
  or `apm.yml`). Load its tokens, components, effects and prohibitions.
- **Neither present → NEW BRAND.** Run the structured intake in
  [`references/intake-new-brand.md`](references/intake-new-brand.md) to **extract** the
  brand and design elements from the executing agent's context **and from the user**
  (values, audience, positioning, existing assets, references, constraints) before the
  panel proposes anything. A new brand is defined *with* the user, not assumed.

> At minimum, the brand skill and the design-system skill are expected to live in the
> `designsystem-master` repo (or the consumer's `apm.yml`). When they exist, they are
> mandatory inputs; when they don't, the intake creates the missing grounding.

## Flow (summary)

`references/process.md` has the full protocol. In short:
**Phase 0** ground (brand + DS, or new-brand intake) → **Phase 1** competitive/trend
research → **Phase 2** ≥10 consensus rounds (propose → critique vs brand/DS/research →
President drives consensus → **Copywriter gate on strings** → refine, narrowing each
round) → **Phase 3** converge to 1–5 options with the President's recommendation and
**Senior Copywriter sign-off**, in the output format of
[`references/research-and-output.md`](references/research-and-output.md).

Deliver the options to the invoking agent (or the user) — this skill decides *what the
options are*; the invoking agent executes the chosen one (which, for UI, then flows
through the design-system skill's element protocol).
