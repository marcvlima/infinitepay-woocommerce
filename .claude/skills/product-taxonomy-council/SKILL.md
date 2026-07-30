---
name: product-taxonomy-council
description: Convene a standing product-naming and taxonomy-governance council WHENEVER a new product, service or feature is being named, an existing name is questioned, or the product/service catalog or taxonomy is touched. The Chief Product Architect always chairs; specialists (Product Taxonomy/Information Architect, Naming & Verbal-Identity Lead, Brand Architect, Platform/DevRel Strategist, Product Manager, Catalog/Governance Steward) ground in the existing catalog and brand system, research PRIMARY sources on disciplined cloud-provider service naming (AWS/GCP/Azure), product-taxonomy/information-architecture practice and naming/verbal-identity practice, brief on the business/positioning first, generate meaningful non-trivial names (think outside the box, not dictionary-obvious), run 2 focused consensus rounds (Sonnet moderates; the top-tier model stays executive), stay unbiased when the founder rejects a proposal (generate fresh valid alternatives, never re-defend the reject), and converge to a naming decision, catalog entries, and an ADR-ready record.
---

# Product Taxonomy Council — the standing decision room for naming & catalog coherence

You are convening **a real, senior naming and taxonomy council** — a product taxonomy /
information architect, a naming & verbal-identity lead, a brand architect, a platform/DevRel
strategist, a product manager and a catalog/governance steward, modeled on the discipline
that cloud providers apply to their own service portfolios (AWS EC2/EKS/RDS/S3, GCP, Azure).
This is not a casual naming brainstorm: every name the ecosystem ships becomes **brand
equity and a public surface that is expensive to change**, so the **Chief Product Architect
always chairs the room**, and the council works in disciplined rounds until it reaches
consensus on a naming decision that protects the business identity.

**Invoke this skill whenever a new product, service or feature is being named, an existing
name is questioned, or the product/service catalog or taxonomy is touched** — a brand-new
top-level offering, a feature that will live inside an existing family, a rename or
consolidation, a sub-brand/variant, or a restructuring of the taxonomy itself. If a name is
about to ship without having been checked against the catalog and the naming standard —
**stop and convene the council.**

This skill exists to make **naming coherence repeatable**, the same way AWS, GCP and Azure
keep thousands of service names legible as a system rather than a pile of one-off marketing
decisions: ground in what already exists, research how disciplined naming programs actually
work (never from training memory), red-team every candidate, and bring the founder a decision
instead of a label.

## The room (who is always in it)

The **Chief Product Architect** chairs every session (non-negotiable). The standing panel is
defined in [`references/roster.md`](references/roster.md) — a Chief Product Architect plus a
core of specialists: **Product Taxonomy / Information Architect** (hierarchy, categorization,
findability), **Naming & Verbal-Identity Lead** (candidate names, linguistics, tone),
**Brand Architect** (naming-architecture model, family discipline, anti-sprawl), **Platform /
DevRel Strategist** (how the name reads in docs, APIs, CLIs and SDKs), **Product Manager**
(business/roadmap context, variant trajectory) and **Catalog / Governance Steward** (the
living system of record, collision checks, the ADR-ready record). Optional specialists —
Legal/Trademark Screener, Localization Reviewer, Customer/Support-Experience rep — are
convened as the decision requires.

The Chief Product Architect **sizes the room**: a new top-level family or a rename of a
shipped name convenes the full panel; a feature name inside an existing family convenes the
Chair + the relevant subset (see the sizing guide in `roster.md`).

**How the room is run** — two modes (see [`references/process.md`](references/process.md)):
- **Delegated (preferred when the host supports subagents):** spawn one subagent per active
  role so perspectives are genuinely independent. Each round is synthesized by a dedicated
  **Sonnet moderator** — this is a process function, not a domain vote. The **Chief Product
  Architect runs on the top-tier model and stays executive**: it sets the brief, ratifies each
  round's consensus, breaks ties, and owns the final naming decision. Per decide-high/
  execute-low, moderation (structured, repetitive synthesis) is delegated; the decision is not.
- **In-context (always available):** the executing agent role-plays the full panel as a
  structured, **attributed** multi-round transcript, with the moderator voice and the Chair's
  executive ratification kept visibly distinct even though both run on the same agent.

## Non-negotiable rules

1. **The Chief Product Architect always presides and stays executive.** They set the brief and
   the definition of "good", ratify an explicit consensus every round, break ties, and own the
   final naming decision + ADR-ready record. The Chair is never replaced by the moderator.
2. **Ground in the source of truth FIRST.** Before proposing any name, load the existing
   product/service catalog, the brand system / verbal-identity guide, and any prior naming
   ADRs. Never contradict a locked name or family silently — a proposal that overturns one
   must call it out as a **supersession** with reasons. (See "Grounding" below.)
2b. **ADR-conflict gate (HARD RULE — same as architecture-council).** Phase 0 sweeps the
   **entire** ADR index of the owning repo(s) and the organization's source-of-truth repo
   (every file, by listing — not a guessed subset). Accepted ADRs constrain the decision space; **founder-fixed rulings are
   immutable inputs, never options**. An option conflicting with an accepted ADR may only be
   presented as a labeled **SUPERSESSION PROPOSAL** — conflicting ADR named, violated clause
   quoted — and cannot be ranked #1, ratified, or recorded as accepted **without the
   founder's explicit, specific assent**. The deliverable includes an **ADR compliance
   table** (ADR · clause · complies/CONFLICTS→pending assent); an unresolved conflict makes
   the decision void.
3. **Research PRIMARY sources; never decide from training memory.** For every load-bearing
   naming claim, consult official cloud-provider naming/branding documentation (AWS, GCP,
   Azure), product-taxonomy and information-architecture practice, and naming/verbal-identity
   practice. Training memory has a cutoff and may be stale or wrong. State what was verified
   vs assumed, and cite the sources used.
4. **Protect the business identity.** A name is not a label; it is brand equity that must
   scale to the family it may spawn. Treat "obvious" candidate names with the same suspicion
   an architecture council treats an "obvious" technical fix — check it against the catalog,
   the brand, and how it will read in five products' time.
5. **Red-team every candidate name.** An assigned devil's-advocate attacks each finalist for
   internal catalog collisions, external trademark/domain risk (flagged to Legal, not decided
   by the council), mispronunciation, unintended meanings, translation risk, and "mini-brand"
   sprawl — the AWS anti-pattern where one family name means four unrelated things.
6. **Run exactly 2 focused consensus rounds; Sonnet moderates; the top model stays
   executive.** Round 1 diverges wide (generate broad, stress-test, cut); Round 2
   converges (refine the survivors, screen finalists, decide). Two rounds keep feedback
   cycles fast and cheap — do not pad to more rounds, and do not fake convergence. Only a
   genuinely foundational, ecosystem-wide *family* decision may add a third round, and
   only if the Chair records why the 2-round output was insufficient.
7. **Brief first — understand the business, not just the slot.** Before generating,
   the Chair captures and records a short brief: what the product/service *does*, its
   audience, its **market positioning and value proposition**, the business it serves,
   and the founder's qualitative desires for the name (e.g. "striking but not trivial",
   "warm", "technical"). These become explicit **naming variables** every candidate is
   judged against — a name that ignores the positioning is off-brief, not just off-brand.
8. **Follow market naming best practice AND think outside the box.** Ground in how
   disciplined naming programs actually work (rule 3), then generate **meaningful,
   distinctive** names — including invented coinages, morphological blends and evocative
   metaphors — not trivial dictionary words (which are both unoriginal and, in crowded
   categories, already taken). Aim for names that are ownable and resonant; "obvious" and
   "generic" are failure modes, not safe choices.
9. **On rejection, generate — do not defend (anti-bias rule).** When the founder rejects
   a proposal, the council must NOT re-litigate, anchor on, or re-pitch the rejected
   option. Treat the rejection as a **new, binding constraint** and genuinely work to
   produce *fresh, valid* alternatives that fit the founder's expressed scenario and
   desire **better** — that effort is what separates a real agency from a defensive one.
   The red-team (rule 5) explicitly checks each new round for anchoring bias on prior rejects.
10. **Deliver decision-grade output.** A naming decision (name + family + rationale +
   alternatives), the catalog row(s) to add/update, an ADR-ready record, and consistency
   checks against the existing catalog and brand. Format in
   [`references/output.md`](references/output.md).
11. **Do not decide the irreversible for the human.** Renaming an already-shipped,
   customer-facing product is expensive to reverse. Present findings, trade-offs and a
   recommendation — and **wait** for the founder before anything costly or hard to undo.

## Grounding: the source of truth (read before proposing)

At the start (Phase 0), establish the binding context:

- **Existing catalog present?** Find wherever the ecosystem's product/service names live
  today (a catalog doc, a brand-system repo, prior `docs/decisions/` ADRs). Load it and treat
  existing names/families as **locked** unless the council explicitly supersedes one.
- **Brand system / verbal-identity guide present?** Load it — voice, tone, naming
  do's-and-don'ts already decided for the ecosystem. Never design a name against an imagined
  brand; design against the one that exists.
- **A naming standard already exists?** Prior council outputs (ADRs) are the standard; honor
  them or explicitly supersede them.
- **No records yet →** capture the constraints **with the human**: what family this belongs
  to, the target audience, non-goals, and what "good" means for this name.
- **The brief (rule 7) — always captured, records or not.** State in 3–5 lines: what the
  product/service *does*, who it is for, its **market positioning and value proposition**,
  the business it serves, and the founder's qualitative desire for the name (e.g. "striking
  but not trivial", "warm", "definitive"). These are binding naming variables — candidates
  that ignore the positioning are off-brief.

Record the **grounding facts** (locked names/families + verified findings) + the **brief**
the council must honor; every candidate name is checked against them.

## Relentless Method (MANDATORY — read references/relentless-method.md)

Every session runs under the Relentless Method: grit before barriers (two
alternatives before any "can't"), negative-claims quarantine (test, don't
inherit), metrics interrogation (numbers carry their environment), iterative
research until the result, pre-registered decision rules, an out-of-the-box
round, veto alchemy, proactive validation on cheap executors, no lazy WATCH,
and silent no-op hunting. A synthesis that violates these is not ready for
the ratifier.

## Flow (summary)

[`references/process.md`](references/process.md) has the full protocol. In short:
**Phase 0** ground + **brief** (catalog + brand + prior naming ADRs + the business/
positioning brief per rule 7, or capture constraints with the founder) → **Phase 1**
primary-source research (AWS/GCP/Azure naming discipline + IA/taxonomy practice +
naming/verbal-identity practice) → **Phase 2** two consensus rounds — **Round 1 diverge**
(generate wide + meaningful/non-trivial, stress-test against the eight lenses, cut),
**Round 2 converge** (refine survivors, screen finalists, decide) → **Phase 3** converge
to a naming decision, catalog entries, an ADR-ready record, and consistency checks.

**Every session runs exactly 2 rounds** (diverge, then converge). Each round is synthesized
by a **Sonnet moderator**; the **Chief Product Architect (top-tier model) stays executive**
throughout — briefing, sizing the room, ratifying consensus, and owning the final call.
Only a foundational/ecosystem-wide *family* decision may add a third round, with the Chair
recording why; never pad rounds, never fake convergence. **On a re-run after a founder
rejection, do not re-pitch the rejected name** — per rule 9, brief on why it was rejected
and generate genuinely fresh alternatives that fit better.

Deliver the decision to the invoking agent (or the founder). This skill decides **what the
name is and why**; the invoking agent applies it to the catalog, and an accepted decision
should be recorded as an ADR in the owning repo.
