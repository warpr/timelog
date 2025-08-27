# Timelog Codebase Guidelines

## Code Style Guidelines
- PHP: Declare strict types (`declare(strict_types=1)`)
- Namespace: Use `timelog\` namespace for all library code
- Naming: Use snake_case for everything - classes, methods, variables, files
- Classes: Use lowercase snake_case class names (e.g., `project`, not `Project`)
- Files: Match file names to class names (e.g., `project.php` contains `class project`)
- Indentation: 4 spaces
- Formatting: Prettier is used for auto-formatting
- Error handling: Use exceptions with descriptive messages
- Comments: Include copyright header in all files
- License: Include SPDX identifier in headers
- Testing: Test files should be named with `_test.php` suffix
- Assertions: Use `Assert::assertEquals()` style in tests
