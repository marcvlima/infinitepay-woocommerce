# Research protocol & output format

## Competitive & trend research (the frontier check)

The **Design Researcher** grounds the panel in the current frontier so options are
cutting-edge, not generic. For every decision that benefits from market grounding (almost
all positioning, landing, and "make it modern" asks):

1. **Search the internet** for: direct competitors, aspirational leaders in the category,
   and the current best-in-class for the *exact* surface/decision (e.g. "modern AI
   platform landing pages 2026", "scroll-driven storytelling patterns", "developer-tool
   pricing pages"). Use real, current sources — never design from memory alone.
2. **Extract patterns:** what is now table-stakes, what differentiates, what looks dated,
   what the leaders are doing with layout, type, color, motion and narrative.
3. **Benchmark honestly:** where does the brand's current direction sit vs the frontier?
   What would put it *ahead* — a defensible, on-brand distinctive move (not a trend for
   its own sake)?
4. **Feed the panel** a concise benchmark (5–10 references with the takeaway from each),
   cited, so every proposal can be argued against real examples.

Always analyze the **existing brand and design elements** alongside the research — the goal
is the most modern outcome **that is still unmistakably this brand**, never a generic
trend clone.

## Output format (Phase 3 deliverable)

Deliver a structured result the invoking agent (or user) can act on immediately:

```
## Decision: <the exact ask>

### Grounding
- Brand constraints honored: <key rules/tokens/prohibitions>
- Design-system constraints honored: <components/effects allowed>
- Research benchmark: <5–10 references, one-line takeaway each, cited>

### Rounds log (auditable)
R1 … R10(+): one line each — the consensus reached that round (divergence → convergence)

### Options (1–3, max 5)
For each option:
- **Name / concept** — the one-sentence idea
- **Why it wins** — rationale tied to positioning + audience
- **Brand & design-system compliance** — how it honors (or deliberately, justifiably bends) them
- **Competitive positioning** — how it stands vs the benchmarked frontier
- **Experience & motion** — layout logic, scroll choreography, hover/micro-interactions
- **Copy (verbatim)** — every user-visible string proposed for this option, including empty
  states, CTAs, banners, ecosystem/affiliation lines. Must satisfy copy laws
  (`roster.md`): **subject before affiliation** — never bare "Part of …" without naming
  the product ("This product is …" / `<ProductName> is …`).
- **Copywriter sign-off** — Senior Copywriter: **pass** | **fail** (with rewrite if fail)
- **Risks / trade-offs**
- **What it would take to ship** — feasibility within the design system

### President's recommendation
- Ranked pick (1..N) with the reasoning; the single option to run if only one is chosen.
- Confirm Senior Copywriter sign-off on the winning option's strings.
- Any open question to confirm with the user before execution.
```

Keep options **genuinely differentiated** (not three variations of one idea) and each one
**excellent** — the panel's job is a short menu of great choices, not a long list of
mediocre ones. Execution of the chosen option flows through the design-system skill's
element/new-element protocol; the agency decides the direction, the design system enforces
the build.
