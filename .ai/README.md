# AI Knowledge Center

This folder is the project knowledge center for future Codex work.

## Startup order

Read these files first at the beginning of a task:

1. `.ai/README.md`
2. `.ai/context/skills.md`
3. `.ai/context/architecture.md`
4. `.ai/context/functionality.md`
5. `.ai/context/database-schema.md`
6. `.ai/tasks/active/current-task.md`
7. `.ai/tasks/inbox.md`

Then read only the domain notes and source files relevant to the task.

## Folder structure

### `context/`

Stable project reference documents:

- `skills.md`
- `architecture.md`
- `functionality.md`
- `database-schema.md`

### `domains/`

Focused notes for a feature area or integration.

Current domain notes:

- `public-api/backend-spec.md`
- `public-api/booking-date-contracts.md`
- `public-api/electron-day-summary-agent-guide.md`
- `public-api/electron-booking-agent-guide.md`
- `public-api/public-app-token-status.md`
- `public-api/resources/Summary-Bill.jpeg`

### `tasks/`

Operational task tracking for ongoing work.

- `active/current-task.md`
  - the task currently being worked on
- `inbox.md`
  - quick place for the user to drop new tasks
- `backlog/`
  - optional structured task notes that are not active yet
- `README.md`
  - task workflow rules

### `config/`

Project-support files for Codex setup and tooling.

- `mcp.json`

## Layout notes

- Keep top-level `.ai/` small and predictable.
- Put stable project knowledge in `context/`.
- Put feature-specific notes and assets together under `domains/`.
- Put active planning and task tracking in `tasks/`.
- Keep a single MCP config at `.ai/config/mcp.json`.

## Maintenance rules

- When a task changes architecture, functionality, or schema meaningfully, update the relevant `context/` file as part of the task.
- When a task introduces a new domain with enough complexity to deserve its own reference notes, create a new folder under `domains/`.
- Prefer renaming and consolidating notes instead of creating duplicate documents.
