# Changelog

All notable changes to the **H5P Page Controller** (`local_h5pchapter`) plugin will be documented in this file.

## [1.1.1] - 2026-08-10

### Added
- Added helper test cases and observer tests to verify course module deletion.
- Added `.gitignore` file.

### Changed
- Updated terminology from "Chapter" to "Page" across the plugin language strings and README to match Moodle's native H5P integration.
- Updated database images and PHP versions in CI configuration.

### Fixed
- Cleaned up unused event callbacks.

## [1.1.0] - 2026-07-31

### Added
- **Backup and Restore**: Added backup and restore plugin classes.

## [1.0.0] - 2026-07-29

### Added
- Initial release of the plugin.
- Added custom settings to the H5P activity settings form for Interactive Books.
- Added `Target Chapter` setting to automatically unlock specific chapters.
- Added `Block navigation` setting to hide side menu and navigation arrows.
