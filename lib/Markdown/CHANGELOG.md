## 2.0.0 (2022-06-28)

### ⚠ BREAKING CHANGES

* Removed Parsedown lib support
* Removed Pimple

### Features

* Removed dead code and Pimple dependency ([4c45885](https://github.com/roadiz/markdown/commit/4c458852f7ebdf03a199c799ae438176385bdc02))
* Removed Parsedown lib support ([aad67bb](https://github.com/roadiz/markdown/commit/aad67bb94a0053a1b520b40a879d4b71457cb325))

### Bug Fixes

* Calling "convertToHtml()" on a League\CommonMark\MarkdownConverter class is deprecated, use "convert()" instead ([8b92632](https://github.com/roadiz/markdown/commit/8b9263292e41ddc303444583440a3702eb464f4c))
