# Creating First Plugin

1. Clone `nativephp-plugin-template`.
2. Run `php configure.php`.
3. Review `composer.json`, `nativephp.json`, PHP namespaces, and native bridge targets.
4. Replace the template bridge function body with platform code.
5. Run tests and static analysis.

Keep the package type as `nativephp-plugin`.

The public bridge name should stay stable after release because NativePHP apps call it through the manifest name.

Use `--no-interaction` when driving the template from a scaffolder:

```bash
php configure.php --no-interaction --vendor=acme --package=mobile-battery --plugin=Battery --namespace="Acme\\MobileBattery" --description="NativePHP Mobile battery plugin." --android-package=mobilebattery
```
