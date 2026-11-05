# Changelog

## [v2.0.1] - 2026-11-03

### Improved

* Publish `/favicon.ico`, `/favicon.svg` and `/favicon.png` to the public root for Firefox Mobile, Google Search and other clients that request well-known root paths
* Emit `type` attributes on favicon `<link>` tags
* Prefer PNG icons ≥ 48px (especially 96px) before ICO/SVG and apple-touch-icons
* Add `48` to the default `favicon_sizes`
* Skip empty root favicon files when rendering tags
* Fix recommended source image size calculation (`max()` on size values instead of array keys)

### Docs

* Document Google Search / Firefox Mobile requirements and the need for a PNG (SVG alone is not enough for Google)


## [v1.3.1] - 2024-07-09

### Fixed

* fix: string interpolation in PHP 8.2+

## [v1.3.0] - 2024-05-15

### Added

* Add support for Statamic 5.x

## [v1.2.2] - 2023-06-26

### Added

* Add dutch (nl) translations (by @j-jalving)

## [v1.2.1] - 2023-05-22

### Fixed

* Fix .ico and .svg paths
* Empty folder before generating new favicons

## [v1.2.0] - 2023-05-17

### Added

* Add support for Statamic 4.x

## [v1.1.0] - 2023-03-10

### Added

* Add multi-site support (thanks @quintenbuis)

## [v1.0.2] - 2023-01-13

### Fixed

* Fix issue #1 where theme_color isn't correctly applied to tag view

## [v1.0.1] - 2022-04-25

### Added

* Every favicon file now has a version checksum, so new favicons are loaded instantly instead of the cached version
* SVG favicon is now served from a copied version instead of the original asset location

### Fixed

* Refactored asset container code

## [v1.0.0] - 2021-04-13
