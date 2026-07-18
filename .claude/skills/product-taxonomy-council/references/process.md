# The session protocol — phases and rounds

The council works in disciplined phases. The core is a series of **consensus rounds** that
move from wide divergence to a single naming decision. The Chief Product Architect chairs
throughout, stays the decision authority, and keeps a running log.

## Two execution modes

Pick based on what the host supports; **state which you used**.

- **Delegated (preferred when subagents are available).** Spawn **one subagent per active
  role**, each given: the brief, the grounding (catalog + brand + prior naming ADRs), the
  research, and its discipline's mandate. Each round, roles respond **independently**. A
  dedicated **Sonnet moderator** subagent reads the round's independent outputs and drafts the
  round's consensus; the **Chair** (the Chief Product Architect, running on the top-tier
  model) reads the moderator's draft, resolves anything the moderator could not, and
  **ratifies** the round. This yields genuinely independent perspectives, keeps the
  high-volume synthesis work on a cheaper model, and keeps the actual decision on the
  top-tier model. Dispatch role subagents **in parallel** each round with a **shared brief +
  shared verified facts** so they cannot diverge on the facts.
- **In-context (always available).** The executing agent role-plays the whole panel as a
  structured, **attributed** transcript ("Chief Product Architect:", "Naming & Verbal-Identity
  Lead:", …), with a distinct **"Moderator:"** line synthesizing each round before a distinct
  **"Chief Product Architect (ratifies):"** line closes it — the split stays visible even
  though one agent executes both.

## Phase 0 — Convene & ground

1. **Read AND write the brief.** State exactly what is being named (or restructured), the
   candidates on the table (if any), the constraints, and what "good" means for this decision.
   Then capture the **business brief** (3–5 lines, binding): what the product/service *does*,
   its audience, its **market positioning and value proposition**, the business it serves, and
   the founder's qualitative desire for the name (e.g. "striking but not trivial"). These are
   naming variables every candidate is scored against; a name that ignores the positioning is
   off-brief. **On a re-run after a rejection:** also record *what was rejected and why*, and
   treat it as a binding constraint (do not re-propose it or anchor on it — see Phase 2).
2. **Chair sizes the room** (see `roster.md`): new family/rename → full panel; focused →
   Chair + the disciplines the decision touches.
3. **Ground in the source of truth.** Load the existing product/service catalog, the brand
   system / verbal-identity guide, and any prior naming ADRs. Treat existing names/families as
   **locked** unless the council explicitly supersedes one (with reasons).
4. **No records yet →** capture the constraints **with the human**: what family this belongs
   to, the target audience, non-goals, and the definition of "good" for this name.
5. **Record the grounding facts** — the locked names/families + the verified current-state
   facts the council must honor. Every candidate name is checked against these.

## Phase 1 — Primary-source research

Before generating candidates, establish the **naming discipline** the decision should follow.
The relevant specialists each consult **primary sources** for their load-bearing claims —
**never training memory**:

- **Platform/DevRel + Brand Architect:** official cloud-provider naming and branding
  documentation — how AWS, GCP and Azure govern their own service names and families (e.g.
  AWS's trademark/usage guidelines and its observed foundational-vs-utility prefix pattern,
  Google's API/resource naming conventions and product-name style guide, Azure's Cloud
  Adoption Framework naming conventions and abbreviation catalog).
- **Product Taxonomy/IA:** product-taxonomy and information-architecture practice —
  categorization, hierarchy depth, labeling for findability (e.g. published IA/taxonomy
  practice such as Nielsen Norman Group's taxonomy and IA research).
- **Naming & Verbal-Identity Lead + Brand Architect:** naming and verbal-identity practice —
  naming-architecture models (branded house, house of brands, endorsed, hybrid,
  alphanumeric/tiered) and the naming-funnel methodology (generate wide, screen for
  linguistics/trademark/translation, narrow to a shortlist) documented by established naming
  and brand-architecture practice.

Share a concise **fact sheet** with the panel: each key fact tagged **verified** (with the
source) or **assumed** (flagged as a risk to resolve). Assumptions that decide the outcome —
especially trademark/domain availability — must be converted to verified facts, or explicitly
handed to Legal, before Phase 3.

## Phase 2 — The rounds

Each round has the same shape:

1. **Propose / react** — each active role contributes a candidate name or a critique from its
   discipline (independent in delegated mode). **Generate meaningfully, outside the box:**
   favor distinctive, resonant names — invented coinages, morphological blends, evocative
   metaphors tied to the brief's positioning — over trivial dictionary words (which are
   unoriginal and, in crowded categories, already taken). **On a re-run after a rejection,
   the rejected name and its near-variants are off the table** — roles must produce fresh
   directions that fit the founder's stated desire *better*, never re-pitch or defend the
   reject (anti-anchoring; the red-team enforces this).
2. **Stress-test against the eight naming lenses** — every surviving candidate is tested
   against:
   - **Distinctiveness** — collides with nothing in the existing catalog or the visible market.
   - **Clarity / information scent** — a newcomer can guess roughly what it is.
   - **Verbal-identity fit** — tone, pronounceability, spelling, translation risk.
   - **Taxonomy / family fit** — sits correctly in the hierarchy; no orphan, no rogue family.
   - **Brand-architecture fit** — matches the chosen naming-architecture model; no
     "mini-brand" sprawl.
   - **Developer / technical-identifier coherence** — the marketing name maps cleanly to a
     clean API/CLI/doc identifier.
   - **Reversibility** — cost to rename later (docs, URLs, customer memory, filed trademarks).
   - **Governance buildability** — the Catalog/Governance Steward can turn it into a clean
     catalog row + ADR with no further decisions.
   Weak candidates are cut with a recorded reason.
3. **Moderator synthesizes; Chair ratifies** — the moderator (Sonnet) drafts the round's
   agreed position (what's in, out, and what to explore next); the Chair (top-tier model)
   resolves anything unresolved, breaks ties, and ratifies. An assigned **devil's-advocate /
   red-team** attacks any forced or premature consensus.
4. **Refine** — surviving candidates are sharpened for the next round.

The two rounds **narrow deliberately**: **Round 1 diverges wide** (many candidate names,
generated meaningfully per the brief; stress-test against the eight lenses; cut weak ones
with recorded reasons). **Round 2 converges** (refine the survivors, run the finalist
collision/trademark screen, pick, and specify to implementation depth — exact catalog row,
technical identifiers, migration notes if superseding a name). **Every session runs exactly
2 rounds** — fast, cheap feedback cycles are the point; do not pad rounds and do not fake
convergence. Only a foundational/ecosystem-wide *family* decision may add a third round,
and only with the Chair recording why the 2-round output was insufficient.

Keep a short **running log**: one line per round with the consensus reached, so the narrowing
is auditable.

## Phase 3 — Converge & deliver

Distill to **one naming decision** (occasionally a small ranked shortlist, if the founder must
choose between two live options). The decision is specified to the depth the decision needs:
the name, the family it lives in, the rationale, the eight-lens assessment, and the
alternatives seriously considered (with why each was rejected). The Chair gives the **final
call** with the reasoning, and — for a weighty/irreversible fork (renaming a shipped,
customer-facing product; establishing a brand-new top-level family) — hands it to the
**founder** rather than executing.

Produce, in the format of [`output.md`](output.md):
- the naming decision with honest trade-offs against the alternatives;
- the **catalog row(s)** to add/update;
- an **ADR-ready decision** (Context · Decision · Why · Consequences · Status) for the pick;
- **consistency checks** against the existing catalog and the brand system.

Hand the deliverable to the invoking agent or the founder. This skill decides **what the name
is and why**; the invoking agent applies it to the catalog, and an accepted decision is
recorded as an ADR in the owning repo.
