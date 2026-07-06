# Creating First Plugin

1. Clone `nativephp-plugin-template`.
2. Replace `{{ vendor }}`, `{{ package }}`, `{{ plugin }}`, `{{ namespace }}`, and `{{ description }}`.
3. Rename files that contain `{{ plugin }}` in their filename.
4. Update `composer.json`.
5. Update `nativephp.json`.
6. Replace the template bridge function body with platform code.
7. Run tests and static analysis.

Keep the package type as `nativephp-plugin`.

The public bridge name should stay stable after release because NativePHP apps call it through the manifest name.
