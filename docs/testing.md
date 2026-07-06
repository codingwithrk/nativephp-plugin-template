# Testing

The template uses Pest with Orchestra Testbench.

```bash
composer test
```

The included tests cover:

- Manifest JSON loading.
- Manifest bridge target shape.
- Placeholder documentation.
- Laravel service container registration.
- Facade dispatch into the NativePHP bridge binding.

Static checks:

```bash
composer lint
composer analyse
composer rector:test
composer format
```
