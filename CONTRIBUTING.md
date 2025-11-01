# Contributing to ProVAI

Welcome to project! We're excited to have you on the team. To ensure our project remains high-quality, maintainable, and easy to work on, we adhere to a simple set of development guidelines.

## Guiding Philosophy


## Development Workflow


### Merge Policy

The following rules are the formal rules for merging code into the `main` branch.

**1. All Work Must Be on a Feature Branch**

- No commit will ever be pushed directly to the `main` branch.
- For every new issue, create a descriptively named branch from `main`.
- **Branch Naming Convention:** `[type]/[short-description]`

type:feature - A new feature or user-facing functionality.
type:bug - A bug fix that corrects incorrect behavior.
type:chore - Maintenance, refactoring, or DevOps tasks that aren't features or bugs.
type:research - A time-boxed investigation or technical spike (e.g., evaluating a new library).
type:testing - A task focused exclusively on adding or improving tests.
type:documentation - A task related to writing or updating documentation.

  - **Examples:**
    - `feat/new-publications api`
 
**2. All Merges Must Go Through a Pull Request**

- The only way code gets into `main` is by merging a Pull Request.
- The PR serves three critical functions:
  1.  **It triggers the CI pipeline.**
  2.  **It provides a formal opportunity for self-review.**
  3.  **It links the code to the project plan (the GitHub Issue).**

**3. The CI Pipeline is the Gatekeeper**

- A Pull Request is **not ready to merge** until the CI pipeline (GitHub Actions running) has completed and all checks are green (✅).

- If CI fails, you are responsible for fixing it on your branch before merging.

**4. Merge on Green**

- Once the CI pipeline has passed, you are authorized to merge your own Pull Request.
- Always use the **"Squash and Merge"** option to maintain a clean, readable history on the `main` branch.

### Code Quality and Tooling

We use `tox` as the single source of truth for all quality checks. Run `tox` locally to verify your changes before pushing.

### Architecture

This project follows the principles of **Screaming Architecture** and **Onion Architecture**. Please refer to the [Architecture Overview](docs/ARCHITECTURE.md) for a detailed explanation.