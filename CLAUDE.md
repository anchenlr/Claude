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
    has picks** (`filter` on the `AddContactToAutomation` module). **Guarded
    against direct/bot hits on the raw webhook URL**: module 2 (the first
    Airtable search, right after the trigger) carries a `filter` requiring
    `{{1.email}}` to match a real email pattern. Since a Make filter blocks
    everything downstream in that linear branch, a blank/malformed email now
    dead-ends the execution immediately — no Airtable write, no AC contact
    created, no error logged — instead of reaching `upsertContact2024` (module
    23) and throwing `[422] Invalid attribute. This value should not be
    blank.`. The quiz UI itself already blocks blank emails via its own
    screen-1 validation, so this only matters for someone/something posting
    straight to the webhook URL (which is visible in the page's JS as
    `CONFIG.QUIZ_SUBMIT_URL`) — added Nov 6 after two such hits showed up
    back-to-back in the Make error log.
  - `PBS Attendee Hub - Get Data` (scenario id **6758702**) — webhook the hub
    page polls, returns attendee plan + full schedule as JSON.
  - `PBS Attendee Hub - Resend Link` (scenario id **6758760**) — "email my
    link" resend flow. Finds the AC contact via `upsertContact2024` (email
    only, no name fields — passing empty strings there would blank out a
    real attendee's name), not a `ListAllContacts` search — see the gotcha
    below for why. Gated to only trigger automation 73 when `Plan Status =
    Ready`.
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
- **ActiveCampaign**: automation **62** = "Send Attendee Hub" — plain, single
  email action, sends `attendee-plan-ready-email.html`, triggered by the
  Quiz Submit scenario (both the new-registration and existing/unsubscribed
  branches). Automation **73** = the resend variant — a straight duplicate
  of 62 with its one email swapped for `attendee-plan-resend-email.html`,
  triggered by the Resend Link scenario. Automation **60** = "Send Speaker
  Hub". Custom field id `3` = Attendee Hub Link merge field
  (`%ATTENDEE_HUB_LINK%`), written by Make right before every automation
  trigger in both flows — this is always fresh by the time either
  automation fires, so there's no race condition to worry about.
  **Don't reintroduce a single automation + If/Else custom field split for
  this** — we tried that (custom field id 4, "Plan Email Reason") and it
  added a fragile Make→AC round-trip for no benefit, since Make already
  knows which flow is running and can just point straight at the right
  automation. Field id 4 still exists in AC but is unused/vestigial now.

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
- **Schedule page link is `https://pagebuildersummit.com/schedule/`** — the
  plain URL, not `/schedule-2026/` (an earlier, now-wrong variant that had
  crept into several emails and the kickoff deck — fixed, but don't
  reintroduce it).
- **"Coming soon" vs. "not applicable" — don't conflate them.** "Coming
  soon" (the hub's `data-resource` pattern) is only for things that are
  genuinely uniform-and-not-released-yet for everyone (swipe copy, graphics,
  the summit schedule link). A field that's empty because it just doesn't
  apply to that particular speaker/attendee (e.g. a live guest link for
  someone not doing a live session) should hide entirely, not show a
  "pending"/"coming soon" message implying it'll show up later.

## Deploy workflow

Three different cases — pick the right one:
1. **Content pasted into an existing WordPress HTML module** (e.g. the
   schedule-page calendar button): just hand over the file via `SendUserFile`
   or point to GitHub — no server upload needed, Anchen pastes it in herself.
2. **Standalone page that IS the live page** (e.g. `attendee-hub-template.html`
   → `/attendee-hub/`, served as `index.html`): needs a real deploy —
   `scp -O <file> r4d71765a594b8042@ssh.pressable.com:htdocs/<folder>/index.html`.
   **This session's cloud environment has no `scp`/SSH client installed and no
   credentials for the Pressable server** — Claude cannot actually run this
   command here, confirmation or not. Always hand Anchen the terminal script
   (curl the raw file from GitHub + the scp command) to run on her own
   machine. Don't imply a deploy will happen without checking this first.
3. **Files Anchen just wants to inspect/download**: `SendUserFile` directly.

## Known open items

- **Base migration (deferred until after the summit, Nov 6+)**: the
  `Attendees` and `Hub Settings` tables live inside the Speaker Tracker base
  (`appopyUiFU6NMIdy8`) — they should eventually move to their own base, with
  every Make scenario's `base` parameter and the Airtable formula fields
  repointed. Explicitly deferred by Anchen due to summit timing risk — don't
  start this without her asking again.
- Automations 62 and 73 both need to be **active** in ActiveCampaign for the
  plan/resend emails to send at all — a manual AC-side toggle, not something
  Make/code fixes.
- **`Hub - Show to Attendees` checkbox logic (deferred to next year's build)**:
  Anchen considers the current design flawed — one manually-ticked checkbox
  per speaker gates whether they appear in the attendee hub/quiz pool, the
  public schedule, and anything else built from "active sessions." It's easy
  to miss ticking it for a real session (happened with Melissa Love — fixed
  by ticking it, but caught by chance, not by any safeguard). Worth
  rethinking the mechanism next time, not this year — don't start that
  without her asking.

## Pre-existing bug fixed: wrong cross-branch reference in Quiz Submit

`PBS Attendee Quiz - Submit`'s "New registration" branch had a stale bug in
module 32's onerror retry (module id 960): its prompt referenced `{{36.text}}`
(the *other* branch's TextAggregator output) instead of `{{31.text}}` (its
own). Harmless as long as the retry path never fired — but Make's validator
flagged it as "references inaccessible module" once something triggered a
full validation pass, which auto-deactivated the whole scenario. Fixed by
pointing module 960 at `{{31.text}}`. If this scenario gets auto-deactivated
again with a vague `BlueprintValidationError`/"1 problem(s) found" and no
detail from the API, open it in Make's UI directly — it shows a red warning
icon on the exact broken module, which the API doesn't surface.

## Fixed bug: `activecampaign:ListAllContacts` (search by email) is unreliable — don't use it to fetch a contact ID

`PBS Attendee Hub - Resend Link` used to find the AC contact via
`ListAllContacts` (search by email) + an aggregator, then used that
aggregated `.id` for the contact-update and automation-add steps. This
consistently returned a bundle with EVERY field blank (not just id —
email, firstName, everything), confirmed via a debug test across three
different test emails, including a plain one (`anchenlr@gmail.com`, no
`+` alias — an earlier theory blaming `+`-aliasing was wrong, ruled out
once a plain email showed the same failure). Adding filter params
(`tagId`, matching Quiz Submit's working calls) did not fix it either.

**Why Quiz Submit never hit this**: it also calls `ListAllContacts` twice,
but only ever uses the result via `length(X.array)` for branch-routing
(new vs. existing registrant) — it never reads the `.id` field, so the
same underlying brokenness was invisible there. Its real contact ID comes
from a separate, reliable call: `activecampaign:upsertContact2024`.

**Fix applied**: Resend Link now gets the contact ID from
`upsertContact2024` (email only) instead of `ListAllContacts`, the same
pattern Quiz Submit already relies on. If you ever need a contact's AC id
by email again in a new scenario, use `upsertContact2024`, not
`ListAllContacts` — don't reintroduce the broken pattern. Pass only
`email` to `upsertContact2024` when you don't have a real name to supply
— passing empty strings for `firstName`/`lastName` would blank out an
existing attendee's name.

## Make formula gotchas (learned the hard way)

- `or()` / `and()` are not valid Make function calls — use infix (`a or b`).
- `empty()` is not a valid function — use `ifempty(x; "") = ""`.
- A Make `filter` blocks ALL downstream modules in that linear branch, not
  just the next one — if you need "try this, but don't block anything else,"
  use `onerror: Ignore` instead of a filter, or put the filter on the very
  last module in the branch.

## Session Time Slot gotcha

When grouping Speaker Info sessions by calendar day (e.g. for a schedule
overview), a session landing exactly at `T00:00:00 UTC` is the CLOSING
session of the *previous* day's lineup (one hour after that day's 23:00
slot), not the opener of the next day — each day's real content block runs
roughly 18:00 UTC through 00:00 UTC. Group by that pattern, not by naive
UTC calendar-date splitting, or sessions land on the wrong day.

## Git / attribution

Every commit ends with:
```
Co-Authored-By: Claude Sonnet 5 <noreply@anthropic.com>
Claude-Session: <session link>
```
(the exact session link changes per conversation — check the current
system reminder for it, don't reuse an old one).
