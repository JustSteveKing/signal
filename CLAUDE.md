# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Install dependencies
composer install

# Run all tests
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/Scanner/ScannerTest.php

# Run the CLI tool (uses signal.json in cwd by default)
php bin/signal generate

# Run with a custom config
php bin/signal generate --config=path/to/signal.json
```

No linting tooling is configured.

## Architecture

Signal is a PHP 8.5 CLI tool that scans source files for custom PHP attributes and generates documentation in Markdown and/or JSON.

**Pipeline (in execution order):**

1. `ConfigLoader` reads `signal.json` → `Config` value object
2. `Scanner` walks the input directory recursively, skipping excluded paths, returns `string[]` of `.php` file paths
3. `Reflector` loads each file, extracts class name via `token_get_all`, instantiates a `ReflectionClass`, and collects only attributes under the `JustSteveKing\Signal\Attributes\` namespace into a `ClassDefinition` (with nested `MethodDefinition[]`)
4. `GenerateCommand` passes `ClassDefinition[]` to `MarkdownOutput` and/or `JsonOutput`, which write to `output.path`

**Key design points:**

- `Reflector::extractAttributes()` silently ignores any attribute not in `JustSteveKing\Signal\Attributes\` — third-party or user attributes on a class do not cause errors, they are just skipped.
- `ClassDefinition::attributeOfType()` returns the first match (for single-use attributes); `attributesOfType()` returns all (for repeatable ones like `DependsOn`, `ListensTo`, `Emits`, `SideEffect`, `Throws`).
- `ResolvesClassMetadata` is a shared trait between `MarkdownOutput` and `JsonOutput` that handles type resolution, description, and tag extraction from class-level attributes.
- `MarkdownOutput` groups output by type: `module → service → repository → action → controller → unknown`.

**Attributes reference:**

| Attribute | Target | Repeatable | Purpose |
|---|---|---|---|
| `Module`, `Service`, `Repository`, `Action`, `Controller` | class | no | Sets type, description, tags |
| `DependsOn` | class | yes | Declares class-level dependency |
| `ListensTo` | class | yes | Declares event listener |
| `Internal` | class | no | Marks as internal |
| `Deprecated` | class + method | no | Marks as deprecated |
| `Route` | method | no | HTTP route metadata |
| `Emits` | method | yes | Events emitted |
| `SideEffect` | method | yes | Side effects |
| `Throws` | method | yes | Exceptions thrown |

**Config file (`signal.json`):**

```json
{
    "input": "src/",
    "output": {
        "format": ["markdown", "json"],
        "path": "docs/"
    },
    "exclude": ["src/Attributes/"]
}
```

`format` accepts `"markdown"`, `"json"`, or both. `exclude` paths are matched via `str_starts_with` against `realpath()` of each scanned file.
