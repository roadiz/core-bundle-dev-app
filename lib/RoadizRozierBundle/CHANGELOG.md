## 2.0.8 (2022-11-09)

### Bug Fixes

* Ignore Rozier vendor folder which may contain php files ([26cf7b5](https://github.com/roadiz/rozier-bundle/commit/26cf7b5018829e5fabc449476133597dcbc69747))

## 2.0.6 (2022-09-30)

### Features

* Rename ROLE_PREVIOUS_ADMIN to IS_IMPERSONATOR ([811b794](https://github.com/roadiz/rozier-bundle/commit/811b7943cec1784d665f92d7174d22d2c9474c79))
* **Translations:** Let user choose source and destination translations ([1581cd9](https://github.com/roadiz/rozier-bundle/commit/1581cd97efc922b88380c63b5a94b4dc948a22d7))
* **Translations:** Moved controller and form-type from Rozier to RozierBundle ([9bbf6a7](https://github.com/roadiz/rozier-bundle/commit/9bbf6a76fe919890926131e2c01652acc476cf78))
* **Translations:** Update help messages ([2c30a29](https://github.com/roadiz/rozier-bundle/commit/2c30a29300982fbbad714921d44cfae4a6fffbf9))

### Bug Fixes

* Bad merge commit ([1b697a8](https://github.com/roadiz/rozier-bundle/commit/1b697a8122bcd29cfa2418337d0b53b403cb5335))

## 2.0.5 (2022-09-16)

### Bug Fixes

* Duplicated translation key ([63872ce](https://github.com/roadiz/rozier-bundle/commit/63872ce1629e532b29bf045fc8681c71fc807932))

## 2.0.4 (2022-09-16)

### Bug Fixes

* Remove dead-code and moved most of the constraints out of Forms in favor of Entity annotations (CoreBundle) ([9787606](https://github.com/roadiz/rozier-bundle/commit/978760680d34ca267206e90efeb1fd9117ebfc04))

## 2.0.3 (2022-09-01)

### Bug Fixes

* Missing help translation for folder form type ([9983db6](https://github.com/roadiz/rozier-bundle/commit/9983db65a6a9fc8867509a342e7383efe7b8c9e3))

## 2.0.2 (2022-07-29)

### Features

* Allow editing SEO info on non-reachable nodes ([9df4675](https://github.com/roadiz/rozier-bundle/commit/9df4675febbd61405a3264c6c77768d3302bdf4f))
* **Documents:** Add all video, audio and picture sources in document preview page ([e1c51e3](https://github.com/roadiz/rozier-bundle/commit/e1c51e34735e12b2c86ca966926fcae788bb87bb))

## 2.0.1 (2022-07-20)

### Features

* Override Rozier TranslateController to use new NodeTranslator service ([1a722c5](https://github.com/roadiz/rozier-bundle/commit/1a722c508db577845afef16f7136febd8c1685a7))

## 2.0.0 (2022-07-01)

### ⚠ BREAKING CHANGES

* `LoginRequestTrait` using Controller must implement getUserViewer() method.
* Rename `@Rozier` to `@RoadizRoadiz`

### Features

* Added new CustomForm usage admin section to see which nodes use custom-form ([669de0b](https://github.com/roadiz/rozier-bundle/commit/669de0b8ce962c80809c232f341d760e6b11e857))
* Added new document limitations edit page ([8494361](https://github.com/roadiz/rozier-bundle/commit/849436120e4375ee94ec9407d607617abe2cd53d))
* Added Realm and RealmNode admin templates and controllers ([e432bab](https://github.com/roadiz/rozier-bundle/commit/e432babe7bdb52d7e6d588e51b12b98459ad2a7e))
* Added Realm behaviour column ([dbb03d4](https://github.com/roadiz/rozier-bundle/commit/dbb03d49f13ded11bbb079153ea4d602e63ca86d))
* Added user list lastLogin ([83f0d85](https://github.com/roadiz/rozier-bundle/commit/83f0d854a502412dcf3dbe0025fd821cd2d725fd))
* Added users translations messages ([13b63a4](https://github.com/roadiz/rozier-bundle/commit/13b63a43ec0148e6e6c10f2a3d39c9037a63baef))
* Document duplicates and unused section are now in main-menu ([df2be36](https://github.com/roadiz/rozier-bundle/commit/df2be36e1642c706ab18306ec9005b0bc5c377ed))
* EN, FR translations messages for custom-forms ([c8ddc2f](https://github.com/roadiz/rozier-bundle/commit/c8ddc2f1af52612951e9e8f9e1cd98fd66b08941))
* Moved all OpenID logic to RoadizRozierBundle as it only supports authentication to backoffice. ([bfaa380](https://github.com/roadiz/rozier-bundle/commit/bfaa3804b5d10285200fc09542cd850f0563877e))
* Moved SessionListFilters to RozierBundle for type compatibility issues with Aliases. ([5ca9296](https://github.com/roadiz/rozier-bundle/commit/5ca9296d2bbc20850c153c5e7b6975e2f5f2efc7))
* Nullable discovery openId service ([e9bf89f](https://github.com/roadiz/rozier-bundle/commit/e9bf89f7a27279e21a62c44cbcd6943a45446649))
* Nullable Profiler ([ab77abe](https://github.com/roadiz/rozier-bundle/commit/ab77abe0935dbbcd69090e130a6ff7487c5988e2))
* Override /bulk-download route controller to use new DocumentArchiver ([79171ed](https://github.com/roadiz/rozier-bundle/commit/79171ed665162ef2cd96f09e2d93845fb31d3ec0))
* Override DocumentTranslation form for external url ([6eece86](https://github.com/roadiz/rozier-bundle/commit/6eece8666606c94cf7b92c4a3c5070a2c8e55781))
* Override rozier CustomFormType to implement retentionTime form ([86584fd](https://github.com/roadiz/rozier-bundle/commit/86584fd0e1de436323afc6f531fe0aab4d243079))
* Realm EN translations ([6a08c9c](https://github.com/roadiz/rozier-bundle/commit/6a08c9c190bc146050b258520551df5be08515a7))
* Realm FR translations ([97e7b35](https://github.com/roadiz/rozier-bundle/commit/97e7b3502fc371c74b4111d8e5469e40090341ba))
* Refactored PingController to avoid profiler ([7ed7da2](https://github.com/roadiz/rozier-bundle/commit/7ed7da2aa21bee6015d6ad53f3a1cd76501fe214))
* Rename @Rozier to @RoadizRoadiz to share the same Twig namespace ([23a31cb](https://github.com/roadiz/rozier-bundle/commit/23a31cb38265b5d37ff4139f4a46b2cd846764c5))
* Rewrote LoginRequestController ([ff8e0e2](https://github.com/roadiz/rozier-bundle/commit/ff8e0e2c7d334868842d6404a82cd8489f227da4))


### Bug Fixes

* Dependencies constraints ([e2fa667](https://github.com/roadiz/rozier-bundle/commit/e2fa6678c76f44eda7934dc1c68ff2eb52300469))
* Fixed login error translation ([f5cb8fd](https://github.com/roadiz/rozier-bundle/commit/f5cb8fdc547aa43602a84e47bcc54ad06247f3c6))
* Login placeholder and label translation ([4ea29a9](https://github.com/roadiz/rozier-bundle/commit/4ea29a916ef81ec035b1cb42b768892e62c54db5))
* Missing folders/users/settings explorer services ([99c32cf](https://github.com/roadiz/rozier-bundle/commit/99c32cf92fd5a2f5146270cbd41f4c5002f6964e))
* Never connected users ([62d0c1a](https://github.com/roadiz/rozier-bundle/commit/62d0c1a819fd067360c35d407f4ee7bb0db9f5f4))
* provide themeService in login page ([3c7ea8e](https://github.com/roadiz/rozier-bundle/commit/3c7ea8ea5955e52dc2cc682b075520460aa3288b))
* Redirect to home page if access denied ([d65ce71](https://github.com/roadiz/rozier-bundle/commit/d65ce71700925fb741782700d951fb2d280d26c6))
* Removed dead node export JSON pages ([14d969e](https://github.com/roadiz/rozier-bundle/commit/14d969e0261c1581d7e41b7cc7db1623671b106c))

