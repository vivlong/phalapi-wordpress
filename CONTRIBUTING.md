# Contributing to PhalApi WordPress

Thank you for considering contributing to this project!

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Install dependencies: `composer install`

## Development Workflow

### Code Style

This project follows PSR-12 coding standards. To check and fix code style:

```bash
# Check code style
composer cs:check

# Fix code style
composer cs:fix

# Or use PHPCS
composer sniff
composer sniff:fix
```

### Static Analysis

Run static analysis with PHPStan:

```bash
composer phpstan
```

### Testing

Run the test suite:

```bash
composer test

# With coverage report
composer test:coverage
```

## Pull Request Process

1. Create a feature branch from `master`
2. Make your changes
3. Add tests for new functionality
4. Ensure all tests pass
5. Run code style checks and static analysis
6. Submit a pull request

## Reporting Issues

When reporting issues, please include:

- PHP version
- PhalApi version
- WordPress version
- Steps to reproduce
- Expected behavior
- Actual behavior

## License

By contributing, you agree that your contributions will be licensed under the MulanPSL-2.0 License.
