# Release Notes

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
  on the WordPress update screen, with the transient and plugins_api objects core
  expects, a cached lookup and support for an endpoint that requires a licence

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
