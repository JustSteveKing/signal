# Contributing to Signal

Contributions are welcome. This document covers how to get set up and what to expect from the review process.

---

## Setup

```bash
git clone https://github.com/juststeveking/signal.git
cd signal
composer install
```

---

## Running the Test Suite

```bash
composer test
```

To run a single test file:

```bash
./vendor/bin/phpunit tests/Attributes/ServiceTest.php
```

To run a single test method:

```bash
./vendor/bin/phpunit --filter it_stores_description tests/Attributes/ServiceTest.php
```

---

## Code Style

Signal uses Laravel Pint for formatting. Run the linter before submitting:

```bash
composer pint
```

To check without applying changes:

```bash
composer lint
```

---

## Static Analysis

```bash
composer static
```

All code must pass PHPStan at its configured level before merging.

---

## Adding a New Attribute

1. Create the attribute class in `src/Attributes/` — use an existing one as a template.
2. Register it in `ResolvesClassMetadata::CLASS_TYPES` (for class-level attributes) or add handling in `MarkdownOutput::renderMethod()` / `JsonOutput::serializeMethod()` (for method-level attributes).
3. Add a test in `tests/Attributes/`.
4. Add a fixture in `tests/Fixtures/` if needed for integration tests.
5. Update `CHANGELOG.md`.

---

## Pull Requests

- Keep PRs focused — one feature or fix per PR.
- New attributes must include attribute unit tests and at least one integration test in `ReflectorTest` or the output tests.
- PHPDoc blocks are not required; attribute constructors are self-documenting.
- Update `CHANGELOG.md` under `[Unreleased]`.

---

## Reporting Issues

Open an issue on GitHub describing the expected and actual behaviour, with a minimal reproducible example if possible.
