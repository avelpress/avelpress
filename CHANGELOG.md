# Release Notes

## [2.0.0] - 2026-10-07

### Removed

- **BREAKING** Plugin updater: the classes `AvelPress\Update\PluginUpdater`,
  `AvelPress\Update\HttpUpdateProvider` and
  `AvelPress\Update\Contracts\UpdateProvider` are no longer part of the
  framework; they moved, unchanged and under the same namespace, to the
  `avelpress/updater` package, so plugins published on wordpress.org no longer
  ship them. Code that instantiates them directly (for example
  `new \AvelPress\Update\PluginUpdater(...)` in the main plugin file) stops
  with a "Class not found" fatal error unless the package is installed.
  To upgrade, run `composer require avelpress/updater` in every plugin that uses
  `updater` or those classes, together with the bump to `avelpress/avelpress`
  2.0; plugins published on wordpress.org must not require it

### Changed

- Plugin updater: a plugin that declares `updater` in `AvelPress::init()`
  without the package keeps running, without updates, and administrators see a
  notice saying why (only the `updater` config is covered; direct use of the
  classes still needs the package)

## [1.3.2] - 2026-09-11

### Fixed

- Plugin updater: a plugin no longer shows an update to the version it has just
  installed

## [1.3.1] - 2026-09-02

### Fixed

- Update provider: send the plugin slug on the unauthenticated GET, without which
  the endpoint had no way to know which plugin was being asked about

## [1.3.0] - 2026-09-02

### Added

- Plugin updater: declare `updater` in `AvelPress::init()` and the plugin appears
  on the WordPress update screen, with the update objects core expects, a cached
  lookup and support for an endpoint that requires a licence

## [1.0.3] - 2025-12-02

### Added

- Add 'position' function for WodPress Menu
- Add Options in JsonResource
- Add request method in FormRequest Validator

### Fixed

- Bugs in wordpress menu

## [1.0.0] - 2025-07-23

### Initial Version

- Welcome to AvelPress
