# AI Agent Directives & Engineering Guide for Google Jules (Agentic Engineering)

This document establishes key principles and instructions for working with AI coding agents (specifically Google Jules) within this repository. Based on the concepts of **Agentic Engineering**, these directives aim to maximize code relevance, minimize iterative corrections, and ensure production-level quality.

---

## 1. The Spectrum of AI-Assisted Development

AI-assisted development sits on a spectrum based on project stakes:

- **Vibe Coding (Prototypes & Experimentation):**
  - Best for quick prototypes, exploratory scripts, or internal utilities where speed is paramount.
  - Workflow: High-level prompt -> Generate code -> Quick iterative tweaks until it works.
- **Agentic Engineering (Production Systems):**
  - Required for production-grade software.
  - Operates in a highly structured environment using precise specifications, architecture docs, test suites, CI/CD gates, security rules, and guardrails.

---

## 2. Context Engineering Over Prompt Engineering

High-quality code generation depends far more on repository context than on clever prompting. The agent must thoroughly analyze existing project context before implementing changes.

### Static vs. Dynamic Context
- **Static Context (Always Available):**
  - Architecture conventions, coding standards, and repository instructions (e.g., `AGENT.md`, `DESIGN.md`).
  - Core tech stack rules and allowed/forbidden libraries.
- **Dynamic Context (Loaded On-Demand):**
  - Specific API contracts, task specs, tool outputs, execution logs, and debugging traces.

### Golden Rule of Context
Follow this workflow: **Specify Intent -> Discover Repo Context -> Generate Solution -> Verify & Validate**, avoiding arbitrary architectural assumptions.

---

## 3. The Agent Harness (`Agent = Model + Harness`)

An AI coding agent is more than just an LLM. The model is wrapped inside a surrounding harness that enables reliable work:

- **Tools & Sandbox:** Leverage available tools (file reading, bash sessions, static analysis, test runners) to understand the workspace before modifying files.
- **Guardrails & Constraints:** Adhere strictly to repository security policies, access controls, and code conventions.
- **Observability:** Analyze execution logs and test failures systematically rather than guessing root causes.

---

## 4. Test-Driven & Specification-First Workflow

Because code implementation has become fast and cheap, human and agent effort should focus heavily up front on specification and verification.

### Mandatory Workflow
1. **Specification:** Clearly define objectives, interfaces, invariants, and edge cases.
2. **Tests & Evaluations:** Define unit/integration tests or validation criteria *before* or alongside implementation.
3. **Implementation:** Generate surgical, minimal code to satisfy specifications and pass tests.
4. **Verification:** Validate code correctness and assess overall system impact.

---

## 5. Shift Focus from Implementation to Verification

AI agents easily handle the first 70–80% of feature implementation. The core engineering value lies in verifying the remaining 20%:

- **Edge Case Coverage:** Handle unexpected inputs, error paths, and boundary conditions.
- **Architectural Alignment:** Ensure new code maintains system structure without adding technical debt or violating existing patterns.
- **Trajectory & Result Evaluation:**
  - *Output Evaluation:* Does the final diff meet all functional and security requirements?
  - *Trajectory Evaluation:* Did the agent take sensible, well-scoped steps and use tools appropriately along the way?

---

## 6. Operating Modes: Conductor vs. Orchestrator

Adapt how you direct Google Jules based on task complexity:

- **Conductor Mode (Complex/Architectural Tasks):**
  - Work closely with the agent inside the IDE/sandbox, inspecting and reviewing changes step-by-step.
- **Orchestrator Mode (Well-Defined Tasks, Refactorings, Test Generation):**
  - Define clear goals, constraints, and success criteria. Let the agent work autonomously and conduct a thorough review at the end.

---

## 7. Core Commitments for Google Jules

During every task, Google Jules must:
1. Consult repository instructions and codebase context before writing code.
2. Verify existing specifications and tests before making non-trivial modifications.
3. Produce clean, surgical, and minimal diffs.
4. Execute project tests and verification steps to confirm correctness.
5. Prioritize system maintainability, security, and architectural integrity.
