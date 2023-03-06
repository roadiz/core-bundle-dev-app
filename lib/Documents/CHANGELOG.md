## 2.1 and newer

Since v2.1 all changelog entries are merged into [Roadiz monorepo](https://github.com/roadiz/core-bundle-dev-app/blob/main/CHANGELOG.md)

## 2.0.7 (2022-10-07)

### Bug Fixes

* Allow roadiz/models to dev-develop ([f826244](https://github.com/roadiz/documents/commit/f826244ab56492bb307ce9a1584801e1953cf06b))

## 2.0.6 (2022-09-28)

### Features

* **Events:** Deprecated all documents upload events which are dispatched before DB flush. ([7f929c9](https://github.com/roadiz/documents/commit/7f929c96d2d97353b191a0a26ab27c1a8cd14f74))

## 2.0.5 (2022-09-07)

### Bug Fixes

* Missing image file extensions in DownscaleImageManager ([7572026](https://github.com/roadiz/documents/commit/75720264bff67bbf6932d0cac04db46eadd91406))

## 2.0.4 (2022-09-07)

### ⚠ BREAKING CHANGES

* `DownscaleImageManager` constructor signature changed and requires a `ImageManager` object

### Bug Fixes

* Do not instanciate new ImageManager, just pass it as constructor arg ([11bffec](https://github.com/roadiz/documents/commit/11bffec3a19dc91f16a544265d4288fe3602cbcf))

## 2.0.3 (2022-09-07)

### Features

* Added `image/heic` and `image/heif` mime type to image, deprecated document event subscribers ([d58b08a](https://github.com/roadiz/documents/commit/d58b08a4f73d5f3986881863729c9a9b9321dfa5))

## 2.0.2 (2022-07-29)

### Features

* Added AbstractDocumentFinder to hold video, audio and picture document finding logic ([9f7d1bd](https://github.com/roadiz/documents/commit/9f7d1bdb68ea6c8e33ff6228652683d1673c58a2))

## 2.0.1 (2022-06-30)

## 2.0.0 (2022-06-29)

### Features

* Added FileHashInterface to documents to store their file hash for duplicates detection. ([18edef5](https://github.com/roadiz/documents/commit/18edef58ef0c1bdfea8cf78404e58c33169e1f1f))
* Support readId patterns for Spotify and Deezer embed platforms ([fd2c1ab](https://github.com/roadiz/documents/commit/fd2c1ab18d220322417973c1b71bfa28b304c1ed))
* Update document file hash when downscaled/upscaled ([039d3af](https://github.com/roadiz/documents/commit/039d3af9f8adbe0e9a5fe3d974d7f59776478595))
* Updated dependencies ([ca34c19](https://github.com/roadiz/documents/commit/ca34c1955528f41acdb2814e12f12aca14e66b18))
* Use CacheItemPoolInterface in AbstractDocumentUrlGenerator, phpcs ([b52054b](https://github.com/roadiz/documents/commit/b52054b50414f95d768fb8e2195853f8e3a958af))

### Bug Fixes

* Fix nullable setOriginal method arg ([173338c](https://github.com/roadiz/documents/commit/173338c0c6f7b4a1d3725998f5df393aae620c29))

