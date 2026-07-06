# NativePHP Mobile Plugin Template

`{{ vendor }}/{{ package }}` is a reusable, production-oriented starter kit for building NativePHP Mobile v3 plugins.

This repository is not a demo plugin and not a sample app. It is a template that can be cloned, renamed, and published as a real NativePHP plugin after replacing placeholders.

## Placeholders

Replace every placeholder before publishing:

| Placeholder | Replace with |
| --- | --- |
| `{{ vendor }}` | Composer vendor and GitHub owner, for example `acme`. |
| `{{ package }}` | Composer package name, for example `mobile-battery`. Use a platform-safe value in Kotlin package paths. |
| `{{ plugin }}` | Public plugin name, facade name, and bridge namespace, for example `Battery`. |
| `{{ namespace }}` | PHP namespace, for example `Acme\\MobileBattery`. |
| `{{ description }}` | Short package description. |

## How NativePHP Plugins Work

NativePHP Mobile plugins are Composer packages with `type: nativephp-plugin`.

The package ships:

- PHP classes in `src/` for Laravel-style service provider, facade, contracts, events, and support code.
- A `nativephp.json` manifest that tells NativePHP which native bridge functions exist.
- Android source in `resources/android`.
- iOS source in `resources/ios`.

## Bridge Architecture

The included bridge flow is:

```text
PHP facade
  -> {{ namespace }}\Plugin::example()
  -> NativePHP bridge function "{{ plugin }}.Example"
  -> Kotlin com.{{ vendor }}.{{ package }}.{{ plugin }}Functions.Example
  -> Android API or platform logic
  -> JSON response returned to PHP
```

```text
PHP facade
  -> {{ namespace }}\Plugin::example()
  -> NativePHP bridge function "{{ plugin }}.Example"
  -> Swift {{ plugin }}Functions.Example
  -> iOS API or platform logic
  -> dictionary response returned to PHP
```

## Folder Structure

```text
src/                 PHP package layer
resources/android/   Kotlin bridge implementation copied into Android builds
resources/ios/       Swift bridge implementation copied into iOS builds
android/             Android module starter and packaging notes
ios/                 iOS module starter and packaging notes
stubs/               Files for future scaffolding automation
tests/               Pest tests for PHP and manifest behavior
docs/                Maintainer and user documentation
.github/             Issue templates, workflows, release automation
```

## Installation

After replacing placeholders and publishing the package:

```bash
composer require {{ vendor }}/{{ package }}
```

Laravel auto-discovery registers `{{ namespace }}\Providers\{{ plugin }}ServiceProvider`.

## Creating Your First Plugin

1. Clone this repository.
2. Replace placeholders across the repository.
3. Rename PHP classes and files that include `{{ plugin }}`.
4. Update `nativephp.json` bridge function names and native targets.
5. Replace the template bridge implementation with the platform APIs your plugin needs.
6. Run the test and lint commands.

## Publishing

Publish to GitHub as `{{ vendor }}/{{ package }}`, then submit the package to Packagist.

The package type must remain:

```json
"type": "nativephp-plugin"
```

## Releasing

Use semantic versioning. Tags should use `vMAJOR.MINOR.PATCH`, for example `v1.0.0`.

Run before each release:

```bash
composer validate --strict
composer test
composer lint
```

## Future Automation

The `stubs/` directory is designed for a future companion scaffolder such as `nativephp-plugin-maker`.

Possible workflows:

```bash
composer create-project {{ vendor }}/nativephp-plugin-template my-plugin
```

```bash
nativephp-plugin new Battery
```
