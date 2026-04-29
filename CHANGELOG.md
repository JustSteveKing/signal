# Changelog

All notable changes to Signal will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

- 24 PHP attributes covering class types, class metadata, and method documentation
- `#[Module]`, `#[Service]`, `#[Repository]`, `#[Action]`, `#[Controller]` — core class type attributes
- `#[Event]`, `#[Listener]`, `#[Middleware]`, `#[Job]`, `#[Command]`, `#[Query]`, `#[Aggregate]`, `#[ValueObject]` — domain/infrastructure class type attributes
- `#[DependsOn]` — repeatable class-level dependency declaration
- `#[ListensTo]` — repeatable class-level event listener declaration
- `#[Deprecated]` — class and method deprecation with optional version and reason
- `#[Internal]` — marks class or method as internal to the package/module
- `#[Route]` — HTTP verb + path binding for controller methods
- `#[Authorize]` — repeatable authorization ability declaration on methods
- `#[Validates]` — repeatable validation rule documentation on methods
- `#[Cached]` — cache TTL, key, and description for methods
- `#[Emits]` — repeatable domain event emission declaration on methods
- `#[Throws]` — repeatable exception declaration on methods
- `#[SideEffect]` — repeatable observable side effect declaration on methods
- Markdown output with grouped sections, tables, and blockquote notices
- JSON output with full structured representation of all annotations
- CLI command `signal generate` driven by `signal.json` configuration
- Support for `exclude` paths in configuration to skip attribute definitions and vendor code
