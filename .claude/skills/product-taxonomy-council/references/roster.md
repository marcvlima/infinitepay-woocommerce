# The council roster — who sits in the room

The **Chief Product Architect** chairs every session. The Chair sizes the room to the
decision: a **new top-level family or a rename of a shipped name** convenes the full standing
panel; a **focused** decision (a feature name inside an existing family) convenes the Chair +
the subset whose disciplines the decision actually touches.

Each role is a genuine, senior perspective — in **delegated mode** it is one subagent; in
**in-context mode** it is an attributed voice in the transcript. A role speaks only from its
discipline and is expected to disagree when its discipline demands it.

## Chair (always present)

- **Chief Product Architect (Chair, executive).** Owns the brief and the definition of "good".
  Ratifies an explicit consensus each round, breaks ties, assigns the red-team, keeps the
  running log, and writes the final naming decision + ADR-ready record. Holds the
  whole-catalog view and the reversibility judgment. Runs on the **top-tier model** and stays
  the decision authority regardless of who moderates the rounds.

## Moderator (delegated mode)

- **Moderator (Sonnet).** A process function, not a domain vote. Reads each round's
  independent role outputs, resolves surface-level conflicts, and drafts the round's
  consensus for the Chair to ratify. This is the decide-high/execute-low split applied to the
  council's own operation: round-by-round synthesis is high-volume, structured work fit for a
  cheaper capable model; the decision itself is not, and stays with the Chair. In in-context
  mode, the single executing agent still separates the moderator's synthesis from the Chair's
  ratification as two distinct, attributed lines in the transcript.

## Core standing panel

- **Product Taxonomy / Information Architect.** Owns the catalog's structure: hierarchy,
  categorization, parent/child family relationships, and labeling for findability. Grounds in
  information-architecture practice — avoid hierarchies deeper than 3–4 levels, favor labels
  with strong "information scent" (a newcomer can guess what the thing is), and validate
  categorization against how users actually group things rather than how the org chart does.
- **Naming & Verbal-Identity Lead.** Owns candidate-name generation and linguistic screening:
  pronounceability, memorability, spelling, and fit with the brand's voice and tone. Runs the
  naming funnel — generate wide, filter hard, narrow to a shortlist — before the council
  stress-tests survivors. Flags legal/trademark and translation risk for the appropriate
  specialist rather than ruling on it directly.
- **Brand Architect.** Owns which naming-architecture model applies to this decision —
  branded house, house of brands, endorsed, hybrid, or alphanumeric/tiered — and guards
  against **"mini-brand" sprawl**: a family name that means several unrelated things (the
  documented AWS anti-pattern, e.g. a service family name reused across unrelated products).
  Ensures the candidate fits the ecosystem's chosen architecture rather than inventing a new
  one per decision.
- **Platform / DevRel Strategist.** Owns how the name reads across every developer-facing
  surface: docs, API/CLI identifiers, SDK namespaces, error messages. Grounds in how cloud
  providers keep a marketing name and its technical identifier coherent — AWS's
  foundational-service ("Amazon X") vs. tool/utility ("AWS X") prefix discipline, Google's
  product-name style rules (title case, no abbreviation of the trademarked name, no
  "verbing"), and Azure's resource-abbreviation catalog. Ensures the chosen name has a clean,
  unambiguous technical identifier, not just a good marketing sound.
- **Product Manager.** Owns the product/business context: what the offering actually does,
  its target user or buyer, its roadmap trajectory, and whether the name will need to extend
  to variants or tiers later (the SKU-proliferation risk a good family name should absorb).
  Grounds the naming decision in what the product is, not just what sounds good.
- **Catalog / Governance Steward.** Owns the living catalog as the system of record: checks
  every candidate for collisions with existing names/families, enforces the naming standard
  prospectively, and drafts the catalog row(s) and the ADR-ready record. The steward is the
  council's institutional memory — the one role whose job continues after the session ends,
  keeping the catalog and this council's own prior ADRs in sync.

## Optional specialists (convened as the decision requires)

- **Legal / Trademark Screener** — availability of the name (trademark, domain, social
  handles) in the relevant markets; the **only** role authorized to clear or block a name on
  legal grounds. Any unresolved legal risk on the recommended name is surfaced, not decided,
  by the council.
- **Localization / Global-Market Reviewer** — pronunciation, spelling and unintended meaning
  across the ecosystem's target languages/markets.
- **Customer / Support-Experience Rep** — how customers and support will actually say and
  type the name; disambiguation from existing product names in a support/search context.

## Sizing guide

| Decision shape | Room |
|---|---|
| Feature name inside an existing family | Chair + Naming/Verbal-Identity + Platform/DevRel + Catalog/Governance |
| New top-level product/service (new family) | **Full standing panel** + Legal/Trademark |
| Rename or consolidation of a shipped name | **Full standing panel** + Catalog/Governance (lead) + Legal/Trademark |
| Sub-brand or variant under an existing family | Chair + Brand Architect + Naming/Verbal-Identity + Product Manager + Catalog/Governance |
| Taxonomy / category restructuring (no single name) | Chair + Product Taxonomy/IA (lead) + Catalog/Governance + Brand Architect |

The Chair may always add a role. Under-staffing a naming decision is a failure mode — a name
shipped without a taxonomy check or a brand-architecture check is how catalogs rot into
"mini-brand" sprawl; so is convening every optional specialist for a single feature name two
teams already agree on.
