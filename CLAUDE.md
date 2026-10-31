# Page Builder Summit 9.0 (PBS) — Project Notes

This repo holds the HTML/email deliverables for Anchen's Page Builder Summit 9.0
(2–6 November 2026). The automation backend (Make.com, Airtable, ActiveCampaign)
is NOT in this repo — it's live, external, and this file is how a fresh session
gets up to speed on it without replaying old chat history.

## Live systems (all accessed via MCP tools, not files)

- **Make.com** (`mcp__Make__*`), team 2233864, org 7500910:
  - `PBS Attendee Quiz - Submit` (scenario id **6758758**) — webhook that handles
    quiz submission, upserts the Airtable Attendees record + AC contact, routes
    new vs. existing/unsubscribed registrants, generates the AI session plan
    (Claude via `anthropic-claude:createAMessage`, with a one-shot automatic
    retry built into the onerror chain), writes Plan Status (Ready/Failed), and
    triggers AC automation 62 — **gated so it only fires when the plan actually
    has picks** (`filter` on the `AddContactToAutomation` module).
  - `PBS Attendee Hub - Get Data` (scenario id **6758702**) — webhook the hub
    page polls, returns attendee plan + full schedule as JSON.
  - `PBS Attendee Hub - Resend Link` (scenario id **6758760**) — "email my
    link" resend flow. Also gated to only trigger automation 62 when
    `Plan Status = Ready`.
- **Airtable** base `appopyUiFU6NMIdy8` (shared with the Speaker Tracker —
  this is known to be messy; see "Known open items" below):
  - `Attendees` (`tbl7TdR1o4Ur4OpNW`) — one row per attendee/quiz-taker. Key
    fields: Token, Email, Plan Status (Pending/Ready/Failed), Plan JSON,
    Goal Line, Hub URL (formula, slug is `/attendee-hub/`).
  - `Speaker Info` (`tblHXhUUNsZsFwvri`) — sessions/speakers. Only rows with
    `Hub - Show to Attendees` ticked appear in the attendee hub/quiz.
    **Standing instruction: never write to this table's schedule/Time Slot
    data — reads only**, unless explicitly asked.
  - `Hub Settings` (`tblkMqDNe7VJiirvb`) — single-row-per-key settings table
    (Summit Schedule Link, promo/graphics links, etc.) that both the speaker
    hub and attendee pages read live. No code change needed to update these —
    just update the Airtable field.
- **ActiveCampaign**: automation **62** = "Send Attendee Hub" (the plan-ready/
  resend email). Automation **60** = "Send Speaker Hub". Custom field id
  `3` = Attendee Hub Link merge field (`%ATTENDEE_HUB_LINK%`).

## Brand palette & email conventions

- Colors: `--pink: #D85A95`, `--pink-dark: #CC4E89`, `--blue: #25AAE1`,
  `--navy: #312E61`, `--teal: #75B8A1`, `--off-white: #FBFCFE`.
- Fonts: Montserrat (headings) + Open Sans (body).
- HTML emails are table-based, email-safe markup with MSO conditional
  comments, inline styles, 600px max-width container — match the structure
  of existing files like `attendee-bingo-email.html` / `speaker-update-email-3.html`
  when building a new one.
- **Email sign-off is always just "Anchen"** — not "Anchen and Nathan". Check
  existing sent emails before asking.
- CTA links in attendee-facing emails go early (near the top), not buried
  below bullet lists.
- **No dark navy backgrounds** — use `--light-blue` (#E2F0FB) with navy
  text for a "light" section instead of a dark navy one. Pink and teal
  gradients are fine as dark/colored backgrounds; navy specifically isn't.

## Deploy workflow

Three different cases — pick the right one:
1. **Content pasted into an existing WordPress HTML module** (e.g. the
   schedule-page calendar button): just hand over the file via `SendUserFile`
   or point to GitHub — no server upload needed, Anchen pastes it in herself.
2. **Standalone page that IS the live page** (e.g. `attendee-hub-template.html`
   → `/attendee-hub/`, served as `index.html`): needs a real deploy —
   `scp -O <file> r4d71765a594b8042@ssh.pressable.com:htdocs/<folder>/index.html`.
   This is a production deploy and currently requires explicit user confirmation
   (auto-mode blocks it) — either get a yes and run it, or hand Anchen the
   terminal script (curl the raw GitHub file + the scp command) to run herself.
3. **Files Anchen just wants to inspect/download**: `SendUserFile` directly.

## Known open items

- **Base migration (deferred until after the summit, Nov 6+)**: the
  `Attendees` and `Hub Settings` tables live inside the Speaker Tracker base
  (`appopyUiFU6NMIdy8`) — they should eventually move to their own base, with
  every Make scenario's `base` parameter and the Airtable formula fields
  repointed. Explicitly deferred by Anchen due to summit timing risk — don't
  start this without her asking again.
- **"Plan Email Reason" custom field**: planned but not yet created in AC,
  for forking between the plan-ready (`attendee-plan-ready-email.html`) and
  resend (`attendee-plan-resend-email.html`) email variants inside automation
  62. Wire the field-write into Make once Anchen confirms it's created.
- Automation 62 needs to be **active** in ActiveCampaign for the plan email to
  send at all — this is a manual AC-side toggle, not something Make/code fixes.

## Make formula gotchas (learned the hard way)

- `or()` / `and()` are not valid Make function calls — use infix (`a or b`).
- `empty()` is not a valid function — use `ifempty(x; "") = ""`.
- A Make `filter` blocks ALL downstream modules in that linear branch, not
  just the next one — if you need "try this, but don't block anything else,"
  use `onerror: Ignore` instead of a filter, or put the filter on the very
  last module in the branch.

## Git / attribution

Every commit ends with:
```
Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
Claude-Session: <session link>
```
(the exact session link changes per conversation — check the current
system reminder for it, don't reuse an old one).
