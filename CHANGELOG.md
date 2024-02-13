# Changelog

All notable changes to Roadiz will be documented in this file.

## [unreleased]

### ⚠ Breaking changes

- Command constructor signatures changed
- Regenerate your api platform resource YAML files, or rename `getByPath` operation to `%entity%_get_by_path`
- `getResultItems` method will always return `array<SolrSearchResultItem>` no matter item type or highlighting.

### Features

-  [**breaking**]Refactored command signatures - ([173a4fb]($REPO/commit/173a4fbcec66c92125330ac3bb609f82c60d34f8))
-  [**breaking**]Create different operation names for each Entity with WebResponse. - ([21fdc5c]($REPO/commit/21fdc5cad7de5b6bce3d0ededd7fdab253485e37))
-  [**breaking**]Always use SolrSearchResultItem to wrap a single object from search engine results. - ([00ebe1d]($REPO/commit/00ebe1df1d6db05d8a5fb54fa47d2b58f9db8230))

## [2.2.3](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.2...v2.2.3) - 2024-02-09

### Bug Fixes

- Fixed stateless with some listeners - ([b04faa2]($REPO/commit/b04faa2a99167233328a5fdf6393cb2ae0041e2c))
- Fixed Setting value with \DateTimeInterface - ([fa9acb5]($REPO/commit/fa9acb574d68b88350247d77a2fdb0fbd9a30c9b))

### Features

- Added more social network urls to head - ([b4f6863]($REPO/commit/b4f68631c46442655208c922bf6d63e7c135e52c))
- Better overridability for WebResponses - ([1dff926]($REPO/commit/1dff926798e5df990725aeb78e4243d528f6b658))
- Allow WebResponse object instantiation overriding - ([8690594]($REPO/commit/8690594d2cd4119ee575126f710f0c9ae12a26a5))
- WebResponseDataTransformer must always transform PersistableInterface - ([de1226a]($REPO/commit/de1226a45ac7d7e9bac7d4643810bae5f5f46deb))

## [2.2.2](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.1...v2.2.2) - 2024-02-07

### Bug Fixes

- Removed explicit symfony-cmf/routing dependency - ([6daf32f]($REPO/commit/6daf32f8ae25ab371ca7eba94945e41894959103))
- Prevent redirections to resolve a not-published resource. - ([665c031]($REPO/commit/665c0316a40f487428c8277e5fd6cdd6b6dbba08))

### Features

- **(CoreBundle)** Refactored node routing - ([eea6399]($REPO/commit/eea6399eef1dfef2ef242d1237fd4a539e97172f))
- **(EntityGenerator)** Added ApiProperty documentation for generated entities non-virtual fields - ([d6e4462]($REPO/commit/d6e44626bd92ebaeeb275130138151efead59105))
- Do not serialize tag slug manually - ([42747f1]($REPO/commit/42747f1a957a13e1e555bc9e8ba53f03cdc752c5))
- Added ApiProperty documentation for base entities fields - ([fa4a0be]($REPO/commit/fa4a0be43309ebedcd5816d1f2650d9d07f2452f))
- Added new `DataListTextType` to render HTML input with their datalist. - ([5316137]($REPO/commit/5316137b85a1fd493fa657dfcbadd5be76690005))
- Added new `cron` testing commands to test if your cron jobs are executing - ([b486bec]($REPO/commit/b486bec23bfeef277e05c2f07d618dcfff7b5bc2))
- Added new `cron` testing commands to test if your cron jobs are executing - ([efdbaa5]($REPO/commit/efdbaa59ba4930dc239e1aeaacf189828ef0cfd5))

## [2.2.1](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.0...v2.2.1) - 2023-12-13

### Bug Fixes

- **(Api)** Added `AttributeValueQueryExtension` and `NodesTagsQueryExtension` to restrict tags and attributes linked to any published node. - ([cd2a017]($REPO/commit/cd2a017e840ec5fed0efd5c0825bfcb3d09f6275))

## [2.2.0](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.51...v2.2.0) - 2023-12-12

### ⚠ Breaking changes

- Requires PHP 8.1 minimum
- Controller::get and Controller::has methods have been removed

### Features

- **(DtsGenerator)** Added `AllRoadizNodesSources` Typescript type - ([c249f37]($REPO/commit/c249f375a9bf5a0e9d67bede61451dec40978cef))
-  [**breaking**]Require PHP 8.1 minimum - ([2fc0299]($REPO/commit/2fc02997ac386c7de98b31bc38767271a9f77668))
-  [**breaking**]Upgraded to Symfony 6.4 LTS - ([0e37266]($REPO/commit/0e37266d7ebb9f6b6a72d9e81714496a99fba8db))
- Improved `classicLikeComparison` for Node and NodesSources to search in attribute-values translations - ([d10dbbc]($REPO/commit/d10dbbcc9cc0ac399a0e45b7a9200b7256feedf0))

## [2.1.51](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.50...v2.1.51) - 2023-11-20

### Bug Fixes

- Fixed missing entityManager flush to remove custom-form answer. - ([e9aa0ba]($REPO/commit/e9aa0ba826244024426a0484a4e19a51008cbda2))

### Features

- Upgrade to ORM 2.17 and removed WITH joins on EAGER fetched relations - ([7aadd02]($REPO/commit/7aadd028af6b37a8119f32b87283d10d1db1986d))

## [2.1.50](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.49...v2.1.50) - 2023-11-16

### Bug Fixes

- Deprecated: Using ${var} in strings is deprecated, use {$var} instead - ([fae9914]($REPO/commit/fae991419558849ac31a5e97b1a0d0d6ac74e859))
- Prevent doctrine/orm to upgrade to 2.17.0: Associations with fetch-mode=EAGER may not be using WITH conditions - ([9ef566c]($REPO/commit/9ef566cad69d256af5fbd37f20fbde9463e18a6f))

### Features

- Use php82 - ([e10fdd5]($REPO/commit/e10fdd5632cb4327fcf598bfbdb32b272f9a4675))

## [2.1.49](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.48...v2.1.49) - 2023-10-20

### Bug Fixes

- Remaining merge conflict trace - ([518f940]($REPO/commit/518f94099116a10dc9a0992809579ffafc0ebaf6))

### Features

- **(CustomForm)** Made custom-form answer email notification async. **Do not forget to define `framework.router.default_uri`.** - ([5e783c2]($REPO/commit/5e783c21734a0c4b85f443ca844ddadc6d662049))

## [2.1.48](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.47...v2.1.48) - 2023-10-20

### Bug Fixes

- **(CustomForm)** Catch mailer transport exception when submitting custom-form answers - ([52487e3]($REPO/commit/52487e3b1ef626a771f382d9eb5b25d75ea2d483))

### Features

- **(Document)** Added document image crop alignment control - ([51e2cdb]($REPO/commit/51e2cdb7ea2144e91126222e242e1d1e6439ef02))
- **(Documents)** Added image crop alignment for processable documents. - ([970d4c6]($REPO/commit/970d4c61046ef01ffb9243a9ec1b3e25e09df1ff))
- **(NodeType)** Added `attributable` node-type field to display or not nodes' attributes - ([9206664]($REPO/commit/92066648f23ea46ea9198de18d0d26091edb36dc))

### Styling

- Refactoring CSS vars, difference between primary and success colors - ([be5a594]($REPO/commit/be5a5943a160f2ff95116580f77a63edf6503652))
- Moved and minified tree-add-btn - ([ab99bc6]($REPO/commit/ab99bc6875432b3e4d474a497fcabede2076bb1e))

## [2.1.47](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.46...v2.1.47) - 2023-10-09

### Bug Fixes

- **(Serializer)** Do not use `class-string` type-hint it breaks JSON serializer https://github.com/symfony/symfony/issues/47736 - ([9ffaf6e]($REPO/commit/9ffaf6e550ac07afd9e5c9c5dba73129e3c76c3b))
- **(Solr)** Fixed fuzzy/proximity integer - ([7d72a02]($REPO/commit/7d72a024dc61688c8c566d0d835c11240bb43e69))
- Append History state.headerData to url query-param is set - ([0a1e35c]($REPO/commit/0a1e35cb803c72faa532d1a255338382bfc9730c))

### Features

- **(Documents)** Added inline download route to preview private PDF from backoffice. - ([0b607d2]($REPO/commit/0b607d27ca955ee961c82d6edeb02969180b80a3))
- Configure API firewall as database-less JWT by default to ensure PreviewUser are not reloaded - ([51305e1]($REPO/commit/51305e13b779162af68a576b16711acfbf5866b8))

## [2.1.46](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.45...v2.1.46) - 2023-09-28

### ⚠ Breaking changes

- Solr search with hightlightings will output `highlighting` object with different labelled keys (`title_txt_en`, `collection_txt_en`) according to search `locale`.
- Make sure your Solr managed-schema has a dynamic field named `*_ps` with type `location` multiValued:

```xml
<dynamicField name="*_ps" type="location" indexed="true" stored="true" multiValued="true"/>
```

### Bug Fixes

- **(EntityRepository)** Support `node.` order-by keys - ([be7c441]($REPO/commit/be7c4413a4f292b792da4fa14d7f4d22e0dd9b24))
- **(Node)** Enforce UniqueEntity validation is used on all nodes and on UrlAlias when checking `nodeName` - ([5d7e8ae]($REPO/commit/5d7e8aebfb91e92c0df944dbb95369ce3421095e))
- **(Rozier)** Call `off()` on undefined element - ([dd4324d]($REPO/commit/dd4324df32e8648a7aa7967d66bf8190163be4cf))
- **(RozierBundle)** Add a second path to make RoadizRozier templates extendable with @!RoadizRozier namespace - ([9fcb20b]($REPO/commit/9fcb20ba9cd209184f55fb0fc3e9901ef483f245))
- **(User)** Removed deprecated User salt column - ([1294167]($REPO/commit/1294167edff39e7bedbbcbe4133f752d9b93db2b))
- Support reverseProxyCache host with or without protocol - ([9a170d5]($REPO/commit/9a170d51e9ec7593bc45b71aaff4b3f5c922a2f6))
- Use multi-byte string methods - ([a9a5b84]($REPO/commit/a9a5b8420492a4589fd2d88b9bc4434967e09498))

### Features

- **(AbstractAdminController)** Made AbstractAdminController getRepository method overridable - ([a46157d]($REPO/commit/a46157dd518958c46d89a15d98100680f497fc00))
- **(AbstractAdminWithBulkController)** Added generic bulk-actions behaviour using `AbstractAdminWithBulkController` and `@RoadizRozier/admin/bulk_actions.html.twig` template - ([1495058]($REPO/commit/14950586367e59606eafb057b1e2dfb22817833f))
- **(AbstractAdminWithBulkController)** Made remove behaviour overridable. - ([a306faf]($REPO/commit/a306fafab42408e2b3f25ef9ba36209807454596))
- **(AbstractAdminWithBulkController)** Made new bulkActions more easy to code, added User enable/disable bulk actions. - ([50651ea]($REPO/commit/50651ea27960398f3373d77b9d670677951e6b93))
- **(Node)** Added new `SEARCH` attribute for NodeVoter to allow non-editor to at least search nodes. - ([f713c0c]($REPO/commit/f713c0c36b94eab1ee3a900e005920d5e2ab96d7))
- **(Solr)** [**breaking**] Highlightings keep fields name to allow to display title with haighlighted parts, not only collection_txt - ([3ae0379]($REPO/commit/3ae0379100940b69da32798395f117d6e51536bc))
- **(Solr)** Index integer, decimal, datetime, string and geojson fields - ([a62c29c]($REPO/commit/a62c29c5157a58f3a09c9aa7c278e309d9531aff))
- **(Solr)** [**breaking**] Added multiple geojson point indexing - ([13041a2]($REPO/commit/13041a2c775f3a6baf9659d328d574c21b8b4c6b))
- **(Solr)** Added `TreeWalkerIndexingEventSubscriber` to index node children using a TreeWalker, improved `AttributeValueIndexingSubscriber` - ([fea4ea0]($REPO/commit/fea4ea02d4d9e7dc38ad8912a585f256c0e72bbc))
- Added redirections bulk delete action - ([6652e67]($REPO/commit/6652e67467c10af7de8f0894cb4d726577a71bd9))
- Added Custom forms bulk delete actions - ([25dd271]($REPO/commit/25dd2712abfd10bbe22835a06545b4471ceee204))

### Refactor

- Replace all $.ajax and XHR request with window.fetch - ([0c6579e]($REPO/commit/0c6579e0a02e62467e4a12ed65429e9412c68a89))
- Send JSON form response after a fetch POST request - ([75bb3d3]($REPO/commit/75bb3d3b0558c0f2af284e203c60c9e4f6d23df2))

### Styling

- user-image and uk-badge-mini - ([ba8236d]($REPO/commit/ba8236d17691cc8f1a258a6593336e75ded9c50e))
- Wrong colspan count - ([0efc2d2]($REPO/commit/0efc2d238300dbdd20dc1f013ecf53fccec75499))
- main-container height, and user-panel action button size - ([4f5f714]($REPO/commit/4f5f7145908b97c9c3c1d59106aacd6707b2d460))

## [2.1.45](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.44...v2.1.45) - 2023-09-13

### Bug Fixes

- **(EntityListManager)** Validate ordering field name before QueryBuilder rejects it - ([76cc4b4]($REPO/commit/76cc4b427ab409d850198ae5da4091dad00a4f26))
- **(LeafletGeotagField)** Do not display a Marker if field is empty - ([9b6b27d]($REPO/commit/9b6b27dfd3ca27e9d64be5ff2bb5711376f00352))
- Do not display children-nodes-quick-creation if no linked types - ([a826ec9]($REPO/commit/a826ec9a3007979bca2b336d1e8938abd0c03eb8))

### Features

- Removed jQuery from LeafletGeotagField.js MultiLeafletGeotagField.js - ([6de56cf]($REPO/commit/6de56cfafef2a2e8224729b4d5766e124f6d9482))

### Styling

- Vanilla JS cleansing - ([37bf74b]($REPO/commit/37bf74b65f2c01e19519b7317779e7e7905ed7d9))
- Fixed drawer nav using flexbox - ([10f772b]($REPO/commit/10f772bd19930a148866e290d003a1fe9767ba35))
- Better table style for horizontal ones - ([8c6246a]($REPO/commit/8c6246a4e51b8129a5603f534db1d4dac5b6cd59))

## [2.1.44](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.43...v2.1.44) - 2023-09-07

### ⚠ Breaking changes

- `validateNodeAccessForRole` is deprecated, use `denyAccessUnlessGranted` or `isGranted` with one of `NodeVoter` attribute and a Node instance

### Bug Fixes

- **(Tag)** Added missing API search filter on tags relations - ([146ce90]($REPO/commit/146ce9007ecb2464ee923e5b0c3d0c941439fb4d))

### Features

- **(Attributes)** Added relation between Realms and Attributes and AttributeValue to secure attribute access - ([6f5f477]($REPO/commit/6f5f477f94eaa53f4e88ec63e5844c0ce9bad2fa))
- **(Attributes)** Secure API collection and item attribute_values requests with realms - ([fd68f51]($REPO/commit/fd68f5152f76e1ca69824db593501cb5b9c661f6))
- **(Chroot)** Display node chroot on mainNodeTree instead of its children - ([b9e2f7a]($REPO/commit/b9e2f7ad83c36e81b70e2cb31cecaea596ad21f3))
- **(NodeVoter)** [**breaking**] Added a NodeVoter and deprecated `validateNodeAccessForRole` method - ([f6de0ee]($REPO/commit/f6de0ee867153d00f4d401b0bdcaf1f5c534ffa3))

### Styling

- Removed Roadiz font and drop old browser postcss support - ([18186fa]($REPO/commit/18186faacca8c64adb913c9cbb6764e3b9bb4f39))
- Breadcrumb separator style - ([dbe4ec3]($REPO/commit/dbe4ec3d491d67cc3dd7ecc2a106749fd3779aef))

## [2.1.43](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.42...v2.1.43) - 2023-09-04

### Bug Fixes

- **(UserSecurityType)** Missing CallbackTransformer on user chroot form normalization - ([0d9a1b2]($REPO/commit/0d9a1b293a181b8952cfdd7e29c28ea157dccf06))

## [2.1.42](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.41...v2.1.42) - 2023-09-04

### Bug Fixes

- **(NodeNameChecker)** Limit generated unique nodeName to 250 chars, no matter suffix added to it - ([bb1e271]($REPO/commit/bb1e27178cb524f74152f063e8d0105ed7df17ef))
- Fixed non-named inherited indexes - ([a43439c]($REPO/commit/a43439c910e379afca8577a3f64b0188657ea3df))
- Renamed NodeTypeField and CustomFormField defaultValues field label - ([957024b]($REPO/commit/957024bd28517df295a38551ba760161181a2ceb))
- Duplicated translation keys - ([d108eec]($REPO/commit/d108eeca37f2ee3eaf382f11e205d5d24032777a))

### Features

- **(Attributes)** Added indexes on attributes values position, fallback value on default translation during Normalization - ([453708f]($REPO/commit/453708f1e63e6d8488154dc6deb4f9be9c91060a))
- Added NumericFilter on node.position, added new `node_listing` serialization group - ([a73b5b2]($REPO/commit/a73b5b20cf632dc990668916bcdc515d802079c9))
- Added NodesSources getListingSortOptions method - ([26ff61f]($REPO/commit/26ff61f60c8d608d02588e4ea48835786e613714))
- Use RangeFilter on Node' position - ([1423bf2]($REPO/commit/1423bf245db02de23e6963bb1a8b101c7e948988))
- Displays parent node listing sort options if parent is a stack (hiding children) - ([48d06ce]($REPO/commit/48d06ce9beb62e6d448fb02a7e1fb5d225a4ba9a))

## [2.1.41](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.40...v2.1.41) - 2023-08-04

### Bug Fixes

- Do not require explicitly doctrine/dbal since issue came from doctrine/bundle - ([93321bb]($REPO/commit/93321bb2e8ab83cb317aca9cce294f54489ca390))

## [2.1.40](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.39...v2.1.40) - 2023-08-04

### Bug Fixes

- **(NodesTags)** Added a single UUID field to prevent primary key overlap. - ([68fff41]($REPO/commit/68fff41c9219e7e4c4b4254ecdfc1c3d119ca914))

### Features

- **(TreeWalker)** Added new tag `roadiz_core.tree_walker_definition_factory` to inject TreeWalker definitions into TreeWalkerGenerator and any BlocksAwareWebResponseOutputDataTransformerTrait - ([86e5ac6]($REPO/commit/86e5ac6cc48662e54e69c7a1b90c4294580478f3))

## [2.1.39](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.38...v2.1.39) - 2023-08-03

### Bug Fixes

- **(Doctrine)** Do not extend `Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository` anymore due to LazyServiceEntityRepository starting `doctrine/doctrine-bundle@2.8.1` - ([ec1687c]($REPO/commit/ec1687cc27436b08ff0ec5f73d1adb04fb23e424))

## [2.1.38](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.37...v2.1.38) - 2023-08-03

### Bug Fixes

- doctrine/dbal >=3.4.0 broke queries on inheriting class using fields from child classes. Still using NodesSources when querying on child entities and their custom fields. - ([e7d5dbe]($REPO/commit/e7d5dbe809bc447affe2a9a811763670c90b2216))

## [2.1.37](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.36...v2.1.37) - 2023-08-03

### Features

- **(Security)** Added UserChecker to check users enabled, expired, locked or credentialExpired. Removed useless User' boolean expired and credentialsExpired fields. - ([42d4d11]($REPO/commit/42d4d1133916ea1101872665cd3c13d2ea18175f))

## [2.1.36](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.35...v2.1.36) - 2023-08-03

### Bug Fixes

- Gathered Mysql and Postgresql database initialization in the same Migration to avoid always skipping RZ\Roadiz\Migrations\Version20201225181256 - ([af73bf4]($REPO/commit/af73bf499c010fbc12882debcde550dcace9d7ae))

### Features

- **(OpenID)** Added new OpenID mode with `roadiz_rozier.open_id.requires_local_user` (default: true) which requires an existing Roadiz account before authenticating SSO users. - ([639e1a5]($REPO/commit/639e1a5a90ec6d50078087db5d839ef8997da7de))

## [2.1.35](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.34...v2.1.35) - 2023-08-01

### Bug Fixes

- **(WebResponseOutputDataTransformer)** Made `WebResponseOutputDataTransformer` overrideable in projects instead of reimplementing it. - ([52fd409]($REPO/commit/52fd4096343aa4b68d0907375a53eefc64dc3fbc))

## [2.1.34](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.33...v2.1.34) - 2023-07-31

### Bug Fixes

- **(AjaxNodeTreeController)** Fixed non-integer translationId - ([e33762f]($REPO/commit/e33762f4931c8537425e1cb3b7995a48f87834ba))

## [2.1.33](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.32...v2.1.33) - 2023-07-31

### ⚠ Breaking changes

- `bin/console themes:migrate` command do not execute Doctrine migrations, generate NS entities and schema update. **You must version your NS\*\*\*\*.php files and migrations** to sync your app in different environments.
- `bin/console themes:migrate` command do not execute Doctrine migrations, generate NS entities and schema update. **You must version your NS\*\*\*\*.php files and migrations** to sync your app in different environments.

- `bin/console themes:migrate` and `bin/console themes:install` are deprecated: use `bin/console app:install` to just import data in database or `bin/console app:migrate` to import data and generate Entities and Doctrine migrations

### Bug Fixes

- **(AjaxNodeTree)** Removed `translationId` path param from `nodesTreeAjax` route - ([d5e6fe6]($REPO/commit/d5e6fe6333b2dc5e9aff372965adc9defb58af13))

### Features

- **(NodeType)** [**breaking**] Roadiz now generates a new Doctrine Migration for each updates on node-types and node-types fields. - ([3e1b8bb]($REPO/commit/3e1b8bb2971be50e263f909cdc3b42a4c50f3e6d))
- **(NodeType)** [**breaking**] Roadiz now generates a new Doctrine Migration for each updates on node-types and node-types fields. - ([d2cd965]($REPO/commit/d2cd96566d0d8bc8c7addae41a019674fc1ca485))
- **(NodeType)** NodeTypeImporter now removes extra fields from database when not present on .json files - ([c59919e]($REPO/commit/c59919ea3183a197f895c63182d0057033960837))
- Added more OpenApi context to generated api resources - ([29c8fa3]($REPO/commit/29c8fa310bae8b35c850435a947282335628bad5))

## [2.1.32](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.31...v2.1.32) - 2023-07-25

### Bug Fixes

- **(ApiResourceGenerator)** Do not wrap *boolean* value in quotes - ([90630c1]($REPO/commit/90630c1cb8ac26839ef8e6cccd8a0c35ff03c0e6))

## [2.1.31](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.30...v2.1.31) - 2023-07-25

### Bug Fixes

- **(CoreBundle)** Fixed doctrine/dbal version - ([3087a38]($REPO/commit/3087a389c3cba3e7f108623cf30c16e78e7b7fd2))

### Features

- Use `ramsey/uuid` for Webhook ID generation, doctrine/dbal removed AutoGenerated uuids - ([55f80fc]($REPO/commit/55f80fcd25e8d76f65b56e84be25440cdf8a4a68))

## [2.1.30](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.29...v2.1.30) - 2023-07-25

### Bug Fixes

- **(CoreBundle)** Fixed doctrine/dbal dependency to 2.x due to UUID removal. Missing constraint in core-bundle. - ([9824f1f]($REPO/commit/9824f1fd96483b573d960093bf9d012a89d9120a))

### Features

- Allowed `doctrine/dbal` 3.x - ([ba47367]($REPO/commit/ba4736750a3f364a0b08558e5672a162f5cc8927))

## [2.1.29](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.28...v2.1.29) - 2023-07-25

### ⚠ Breaking changes

- You must migrate your `config/api_resources/*.yml` files to use new [ApiPlatform interfaces and resource YML syntax](https://api-platform.com/docs/core/upgrade-guide/#summary-of-the-changes-between-26-and-2730)

- Remove and regenerate your NS entities with `bin/console generate:nsentities` to update namespaces
- Remove and regenerate your Resource configs with `bin/console generate:api-resources`
    - If you do not want to remove existing config, [you'll have to move `itemOperations` and `collectionOperations` to single `operations` node](https://api-platform.com/docs/core/upgrade-guide/#metadata-changes) and add `class` with `ApiPlatform\Metadata\Get` or `ApiPlatform\Metadata\GetCollection`
    - Rename `iri` to `types` and wrap single values into array
    - Rename `path` to `uriTemplate`
    - Rename `normalization_context` to `normalizationContext`
    - Rename `openapi_context` to `openapiContext`
    - Move `shortName` to each `operation`
    - Rename `attributes` to `extraProperties` (for `/archives` endpoints)
    - Add `uriTemplate` for your custom endpoints (for `/archives` endpoints)
    - Prefix all named operations with `api_` to  avoid conflict with non API routes
- All filters and extensions use new interfaces
- Removed all deprecated DataTransformer and Dto classes
- Once everything is migrated changed `metadata_backward_compatibility_layer: false` in `config/packages/api_platform.yaml`

### Bug Fixes

- Fixed doctrine/dbal dependency to 2.x due to UUID removal - ([d426160]($REPO/commit/d426160ccef31ca2ab1619fdc1654d78942b4875))

### Features

- **(ApiPlatform)** [**breaking**] Disabled `metadata_backward_compatibility_layer` and migrated all API resources, filters, extensions to new `Operation` syntax - ([f0317aa]($REPO/commit/f0317aab253ad75011534c857e8bf9c7a70635cc))
- **(UserBundle)** Moved `/users/me` operation to `/me` to avoid conflict on User Get operation and IRI generation - ([883146e]($REPO/commit/883146e7839d19a9a072e361b9b7baf22ae77381))
- Upgrade to rezozero/liform ^0.19 - ([d4862d6]($REPO/commit/d4862d6cac923f52d0b43cced1cbe0da48880909))
- Use `ramsey/uuid` for Webhook ID generation, doctrine/dbal removed AutoGenerated uuids - ([3b409d3]($REPO/commit/3b409d39aa699d5ea22fd2b62a587548aa5e693c))
- Migrated api-platform deprecated http_cache configuration - ([4177d05]($REPO/commit/4177d05ae2b83c996b441bd126657e3a89a4bd1e))

## [2.1.28](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.27...v2.1.28) - 2023-07-19

### ⚠ Breaking changes

- RZ\Roadiz\CoreBundle\Entity\LoginAttempt has been removed with its manager and utils
- Log namespace changed to `RZ\Roadiz\CoreBundle\Logger\Entity\Log`

Make sure you update `config/packages/doctrine.yaml` with:

```yaml
    orm:
        auto_generate_proxy_classes: true
        default_entity_manager: default
        entity_managers:
            # Put `logger` entity manager first to select it as default for Log entity
            logger:
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                mappings:
                    ## Just sharding EM to avoid having Logs in default EM
                    ## and flushing bad entities when storing log entries.
                    RoadizCoreLogger:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/vendor/roadiz/core-bundle/src/Logger/Entity'
                        prefix: 'RZ\Roadiz\CoreBundle\Logger\Entity'
                        alias: RoadizCoreLogger
            default:
                dql:
                    string_functions:
                        JSON_CONTAINS: Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains
                naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
                auto_mapping: true
                mappings:
                    ## Keep RoadizCoreLogger to avoid creating different migrations since we are using
                    ## the same database for both entity managers. Just sharding EM to avoid
                    ## having Logs in default EM and flushing bad entities when storing log entries.
                    RoadizCoreLogger:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/vendor/roadiz/core-bundle/src/Logger/Entity'
                        prefix: 'RZ\Roadiz\CoreBundle\Logger\Entity'
                        alias: RoadizCoreLogger
                    App:
                        is_bundle: false
                        type: attribute
                        dir: '%kernel.project_dir%/src/Entity'
                        prefix: 'App\Entity'
                        alias: App
	            # ...
```

### Bug Fixes

- **(Search)** Fixed node advanced search using `__node__` prefix - ([bcb7d5d]($REPO/commit/bcb7d5d9ad6df9e4c580bf4382dd34824ff3e7ea))

### Features

-  [**breaking**]Removed deprecated LoginAttempt entity, using default Symfony login throtling - ([f7e2a39]($REPO/commit/f7e2a39bb06bf45adf9708b031889a88804e5191))
- Removed dead code `ExceptionViewer` and redondant Logger levels constants - ([f5287f6]($REPO/commit/f5287f65e7b08a5334de6f7f8ee1ac56b814b0e2))
-  [**breaking**]Moved `Log` entity to a different namespace to handle with a different entity-manager and avoid flushing Log in the same transaction with other entities - ([6d2583c]($REPO/commit/6d2583c8b6b420d743e940a50d9c1811c13392b8))

## [2.1.27](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.26...v2.1.27) - 2023-07-12

### Bug Fixes

- Missing `isTransactional` on last migration - ([5bf8833]($REPO/commit/5bf8833cc4c7b7bd4c49a443bbb43bf99055f2ef))

## [2.1.26](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.25...v2.1.26) - 2023-07-12

### Bug Fixes

- Fixed Loggable behaviour, removed relationship between UserLogEntry and User for future entity-manager separation. - ([96d180b]($REPO/commit/96d180b415f8e6cc379293e0e07aca8bccf2cbd3))

## [2.1.25](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.24...v2.1.25) - 2023-07-12

### Bug Fixes

- Do not generate log URL when no entityId is set in log - ([997ee20]($REPO/commit/997ee2093b1754967fc9552443f28197655c0308))
- Do not display document thumbnail controls for audio, video - ([57bbc85]($REPO/commit/57bbc85cecda42fba08900e71faa2958b74f8d81))

## [2.1.24](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.23...v2.1.24) - 2023-07-06

### Bug Fixes

- **(Search engine)** Do not add quotes if multi word exact query, Solr Helper already does it - ([b0aa80a]($REPO/commit/b0aa80a139c576b5729cb8d049f9bc3e38d64f70))

## [2.1.23](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.22...v2.1.23) - 2023-07-02

### Bug Fixes

- Use FilterValidationException instead of InvalidArgumentException to generate 400 and no 500 errors - ([ca89b73]($REPO/commit/ca89b733859a77460081f37a1d1265f7d128ae57))

## [2.1.22](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.21...v2.1.22) - 2023-06-30

### ⚠ Breaking changes

- Log entity requires a Migration that may be destructive on `log` table.

### Bug Fixes

- Do not handle node add attribute form when no attribute exists - ([db77e62]($REPO/commit/db77e62e778495b2f87eaed35022770064c5bd3c))

### Features

- **(Log)** [**breaking**] Added entityClass and entityId to remove hard relationship with NodesSources - ([7300cc3]($REPO/commit/7300cc3a1d7359a996b3d3925e21a046e2437057))
- **(Log)** Added Twig Log extension to generate link to entities edit pages - ([471a571]($REPO/commit/471a571144ad7a3725b0cfd1137c2968cfeccffc))
- **(Log)** Made Log entity autonomous without any relationship, only loose references - ([e0dd99c]($REPO/commit/e0dd99cffa05918ebde1c8744cfe582eaef64520))

## [2.1.21](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.20...v2.1.21) - 2023-06-28

### ⚠ Breaking changes

- Roadiz/models namespace root is now ./src. Change your Doctrine entities path

### Bug Fixes

- **(OpenID)** Do not register `roadiz_rozier.open_id.discovery` if `discovery_url` is not valid - ([120b6a9]($REPO/commit/120b6a999b6635d120ce5c7ee7225b61328692b1))
- Reached phpstan level 7 validation - ([d5c0bdc]($REPO/commit/d5c0bdc9572d9cd95691f7b0782d705312abd2c9))
- Reached phpstan level 7 validation - ([89d9e8a]($REPO/commit/89d9e8ae132d305567e613838b2ce3174a902231))
- Fixed monorepo phpcs config standard - ([b3a1ac0]($REPO/commit/b3a1ac0067ff61e76bf6125cfd3974b1c622cf74))
- CoreBundle must not reference other bundles classes. - ([701cbf3]($REPO/commit/701cbf3c0a7e5218032ccb3d05a837149dfbf31f))

### Features

- **(ListManager)** Refactored and factorized request query-params handling - ([a6188c6]($REPO/commit/a6188c6bea8fab24e1b5788ed69f10633a840275))
- **(TwoFactorBundle)** Added console command to disable 2FA and override users:list command - ([81cd472]($REPO/commit/81cd4720dea1c545741147ecca7e5e80723796db))
-  [**breaking**]Moved Models namespace to src - ([fe0960f]($REPO/commit/fe0960fe3a076a9618e4ea3ae67624f37bb33cb9))
- Added Example PageController and more phpstan fixes - ([db8cec8]($REPO/commit/db8cec819f09718d4774cb6b93decabb5184a5dc))

## [2.1.20](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.19...v2.1.20) - 2023-06-23

### Bug Fixes

- **(Preview)** Check strict `_preview` param value (allowing `0`, `false` values to disable preview mode) - ([70b60c9]($REPO/commit/70b60c972ed13fa1819ef3611521e9a2f6fbf459))

### Features

- **(Preview)** Added OpenAPI decorator to document `_preview` query param and JWT security - ([449c9f9]($REPO/commit/449c9f9fcdb2114e6c13804be696d558ac7efc49))

## [2.1.19](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.18...v2.1.19) - 2023-06-23

### Bug Fixes

- **(OpenApi)** Fixed `getByPath` operation overlap with `get` by setting `id` request attribute and api_resource operation configuration - ([54a378d]($REPO/commit/54a378d151409ef6ec8fb7cfea6b9c74e5115d44))

## [2.1.18](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.17...v2.1.18) - 2023-06-23

### Bug Fixes

- Fixed and refactored SQL search query building on Repository and Paginator levels - ([b5d320b]($REPO/commit/b5d320b49f28ff167e6a4482090fc743bd46c186))

## [2.1.17](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.16...v2.1.17) - 2023-06-20

### Bug Fixes

- **(UI)** Changed user and menu panels size for LG breakpoint. Clear `cache.global_clearer` in CacheController and SchemaUpdater. - ([c20463e]($REPO/commit/c20463eb2e14b8c1977a6eabe12a9380a816f37b))
- UpdateNodeTypeSchemaMessage should be handled synced to avoid Doctrine exception on refresh - ([84a611b]($REPO/commit/84a611b17837c58d219c6f822c132b034b4420af))

### Features

- **(Redirections)** Added cache adapter to look for redirection query - ([18da77c]($REPO/commit/18da77cbb0736b6bc7424f938430f2bc3571ca00))
- **(Redirections)** Added redirection look-up cache and CRUD events - ([a78b3d3]($REPO/commit/a78b3d3e3a6353a5cb0c6e83df39d9fd2afb8cf6))
- Added all Doctrine string fields explicit length and validation assert. - ([8c1d230]($REPO/commit/8c1d23005ae79916aecdc03510e0403f123474b8))
- Added Redirections usage count to better analyze your app routes - ([3a6a38b]($REPO/commit/3a6a38bcaf90dbb2fe2fe36985121132018fa1ff))
- Added Roadiz custom data-collector for web-profiler - ([d0e01fa]($REPO/commit/d0e01fa885b462a8dc46fb7ef04892c840452a7e))

### Refactor

- Deprecated `Controller` methods to get services - ([d8d351b]($REPO/commit/d8d351bd136a76311e4eb7f094d7262e631980e9))
- Removed dead classes - ([f9c1c8b]($REPO/commit/f9c1c8bbfd20be1f4320085f0e05800fd2ad6963))

## [2.1.16](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.15...v2.1.16) - 2023-06-01

### Bug Fixes

- **(LeafInterface)** Do not test exact class when setting LeafInterface parent to allow doctrine proxies. - ([56ed76d]($REPO/commit/56ed76d401fee87813e0cbb86ac92ec130a46752))
- Do not prevent setting parent with not the same class - ([3b5996d]($REPO/commit/3b5996dc804510bed29b5f20ef542e933f101561))

## [2.1.15](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.14...v2.1.15) - 2023-05-24

### Bug Fixes

- Allow all `gedmo/doctrine-extensions` v3 - ([0ed814b]($REPO/commit/0ed814b1b347c6d4989acbedbb05636964687908))

## [2.1.14](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.13...v2.1.14) - 2023-05-19

### Bug Fixes

- Fixed Collection type hinting and co-variance - ([2a53d81]($REPO/commit/2a53d81f8218e3ca584e420568e0d0c9031ac681))

## [2.1.13](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.12...v2.1.13) - 2023-05-19

### Bug Fixes

- Fixed CustomFormAnswer class renaming `getAnswers()` to `getAnswerFields()` - ([d88a179]($REPO/commit/d88a1795a14cbf1f58dd983199a7eed343aba6fe))
- Fixed `Collection<int, classname>` type hinting - ([3a670bb]($REPO/commit/3a670bb093afddb4591fdd36a432afaee3d015a4))

## [2.1.12](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.11...v2.1.12) - 2023-05-11

### Bug Fixes

- Add PreviewResolverInterface into NodeSourceWalkerContext to alter TreeWalker definition against preview status - ([463be2e]($REPO/commit/463be2e43924d87f4a5b3a2ecda63ed0442b11c3))

## [2.1.11](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.10...v2.1.11) - 2023-05-05

### Bug Fixes

- **(Document)** Ignore thumbnail documents from Explorer - ([d236f1a]($REPO/commit/d236f1af68a9a99452024748ca3c5e250800414e))
- **(Solr)** Added a new wildcardQuery to search and autocomplete at the same time - ([37746af]($REPO/commit/37746af717f7b504d761ef7fff377f7bfc36aaad))

## [2.1.10](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.9...v2.1.10) - 2023-05-05

### Bug Fixes

- **(Rozier)** Fixed global backoffice grid when tree panel is hidden - ([4aa8a5e]($REPO/commit/4aa8a5ec210e63f9b2835bb293000a0a11b53e76))

### Features

- **(Document)** Added DocumentPdfMessageHandler to generate thumbnail for PDF documents. - ([35241bc]($REPO/commit/35241bcc88e17131a448f5a7f65388233a8e3d87))
- **(Rozier)** Added UserActionsMenuEvent to customize backoffice user panel menu from other bundles - ([ee5e2a6]($REPO/commit/ee5e2a66a140ed6507dc51f88f00a50066028f80))
- **(SearchEngine)** Added `DocumentSearchQueryEvent` and `NodeSourceSearchQueryEvent` event to alter Solr select queries - ([083d2e5]($REPO/commit/083d2e5294ff31734cd8ca78258275a8adfba6c7))
- **(TwoFactorBundle)** Added TwoFactor bundle to login to Roadiz with TOTP application - ([0953b00]($REPO/commit/0953b0086452343d40adce79ebba65089a0090bd))
- **(TwoFactorBundle)** Added backup codes and backoffice templates - ([95db653]($REPO/commit/95db653efaac1202268b458515967d12d179d841))
- **(TwoFactorBundle)** Added github actions - ([2bc480a]($REPO/commit/2bc480a9d16546e681d56557cc69dd790f4c8991))
- Added solarium webprofiler panel - ([2cea745]($REPO/commit/2cea74513e4153bcdc2c8a36cc22adf4c11a77fe))

## [2.1.9](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.8...v2.1.9) - 2023-04-11

### Bug Fixes

- JoinDataTransformer must always transform to an array, even single objects - ([e17b804]($REPO/commit/e17b804baf73ca9d827b07322a0163a952b3e5c0))

## [2.1.8](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.7...v2.1.8) - 2023-04-06

### Bug Fixes

- **(PreviewBarSubscriber)** Test if Response content is string before searching </body> tag - ([93d1897]($REPO/commit/93d18970ba17c903c2a2feea9e787d166b5f6034))

## [2.1.7](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.6...v2.1.7) - 2023-04-04

### Bug Fixes

- **(Documents)** Prevent renaming document filename if new pathname is not available - ([13982cc]($REPO/commit/13982cce13b1876d6f55167a40abcb456cd1e64f))
- **(EntityGenerator)** Nullable `$field->getDefaultValues()` - ([297f099]($REPO/commit/297f099cc738272c94e88f58b060b39351e655ed))

### Features

- **(UserViewer)** Removed setUser setter for single method usage - ([b7c0f75]($REPO/commit/b7c0f75757410c6addd5e4ec7b8f8247cf339319))

## [2.1.6](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.5...v2.1.6) - 2023-03-23

### Bug Fixes

- Fixed AjaxNodesExplorerController search params strict typing. - ([fd38552]($REPO/commit/fd385525f8f0ee4303cc77714c3d4207301f44a2))

### Features

- **(EntityGenerator)** Entity generator uses DefaultValuesResolverInterface to compute required ENUM fields length acording to their default values. - ([20263b6]($REPO/commit/20263b613d57f5e918f1b0f75de9835714259250))

## [2.1.5](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.4...v2.1.5) - 2023-03-17

### Features

- Migrate from monolithic docker image to nginx + app + worker + cron containers - ([fa8b76a]($REPO/commit/fa8b76a6216b93bdce93716d071131c138310921))
- Added onlyVisible optional param for TreeWalker definitions - ([abf93a8]($REPO/commit/abf93a8866629e095fd4302d05a46443158d8b81))

## [2.1.4](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.3...v2.1.4) - 2023-03-15

### Bug Fixes

- **(EntityGenerator)** Fixed attributes list generator when there is only one attribute to print - ([b6139ba]($REPO/commit/b6139ba0f339962b15d0247ab94d1aa856ec8123))

## [2.1.3](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.2...v2.1.3) - 2023-03-14

### Bug Fixes

- Fixed SolrPaginator `getLastPage` using an index starting at 1 instead of 0 - ([052f9be]($REPO/commit/052f9be0b467bb5ea6031ef31763ec5005f63c5e))

## [2.1.2](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.1...v2.1.2) - 2023-03-14

### Bug Fixes

- Fix SolrPaginator last-page when there are no results - ([a044446]($REPO/commit/a04444698d7b4a96a538419f840928f190054573))

### Features

- Create overrideable `createSearchResultsFromResponse` method for any AbstractSearchHandler extending class - ([b23b92f]($REPO/commit/b23b92f9504a2130fac3455cd79d7f116fa7deba))

## [2.1.1](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.0...v2.1.1) - 2023-03-13

### ⚠ Breaking changes

- All Solr documents MUST have a idempotent ID field to ease up Solr indexing (no more need to delete previous index entry before indexing an entity).

### Features

- Updated Solr indexing tags fields for multivalued strings and use composite ID for easy overriding - ([50a04af]($REPO/commit/50a04afc913eb1a7b67cd550fc39305598c4db19))
- Added NodesSourcesHeadFactoryInterface for better WebResponse and CommonContent responses maintainability. - ([ed05a24]($REPO/commit/ed05a24947da4caa5533b37190c480b0b5358bd5))
-  [**breaking**]Rename getCompositeIdentifier to getIdempotentIdentifier and provided a fallback. - ([e8c895a]($REPO/commit/e8c895a056dee0f2668ed8d081a2021a45490174))

## [2.1.0] - 2023-03-06

### Bug Fixes

- **(api-platform)** Revert to api-platform/core 2.6 - ([0f3f429]($REPO/commit/0f3f4291691dd1a40593045d51b3d5ea4f00927c))
- php image supervisor configuration - ([63977d2]($REPO/commit/63977d224aaebea8c0a6fec057aca00365adb12f))
- Fixed mappings App\GeneratedEntity\NSPage#usersProxy and App\Entity\PositionedPageUser#nodeSource are inconsistent with each other - ([0b8d980]($REPO/commit/0b8d9805fa61d8559f08db1c18a9460c12836965))
- Fixed doctrine resolved entities - ([e2b038a]($REPO/commit/e2b038aca52ead8b67cf7bab35212cdf602807e5))
- Missing openid package from rozier-bundle - ([2f63197]($REPO/commit/2f63197e25ef3a4cc0efbfd67ad6cbf6d4606d8e))

### CI/CD

- Changed username for github Actions - ([a558dbd]($REPO/commit/a558dbdfc816d88331abec1e85156ac4ac3b0e60))
- Do not rename GITHUB_TOKEN - ([aa93d6a]($REPO/commit/aa93d6a789feeef45ad83c25c99f4db4b6f1d441))
- Define default_branch for all packages - ([645a91b]($REPO/commit/645a91bc8a30f0abf2bd71f06eb47cdc69bd9739))
- try to use auto GITHUB_TOKEN from github actions - ([e8c32fb]($REPO/commit/e8c32fb61b2be97afc21a93cfcfa95eb7a892f18))
- Invalid workflow - ([4dbc1f7]($REPO/commit/4dbc1f7d20e63cf162bcf6fd4d400c6ca49e90b3))
- Force GITHUB_TOKEN (https://docs.github.com/en/actions/security-guides/automatic-token-authentication) - ([d0d25cd]($REPO/commit/d0d25cd502d061155c6ff90419866c892fc12aa1))
- Use org secret ROADIZ_SPLIT_ACCESS_TOKEN - ([4816fb6]($REPO/commit/4816fb630b1f0090a03ccec6cd353686a82a60ae))
- Fix newlines for multiline run commands - ([387a93c]($REPO/commit/387a93c33bf5a5cba190a401fb1e35a5db78cda6))

### Features

- **(Attributes)** Migration to attributes - ([21a28c8]($REPO/commit/21a28c8ca44e374af799ff64f2d7046aa31c0c1a))
- **(Documents)** Moved private documents to a dedicated listing - ([6433842]($REPO/commit/643384268add7d436f5f2813846a4f0f306a5ae7))
- Better makefile - ([8f3376f]($REPO/commit/8f3376f0fab33704eb45393cbaad617fe804321a))
- Env var HTTP_CACHE_SHARED_MAX_AGE - ([e543e05]($REPO/commit/e543e0538f750ad6eef2de04fa8bdb71404fdd26))
- Added lexik_jwt_authentication - ([0cae5d0]($REPO/commit/0cae5d044460ff40ae65091fd7a6f31ff10b9d2a))
- OpenId authenticator config - ([14f36df]($REPO/commit/14f36dfe6f9c4daefd7ac58f1eaa85d5f14a72a2))
- Rate limiters for contact and custom form submissions - ([70a28f0]($REPO/commit/70a28f0766879243729df5edf41a260644ae1eb9))
- Configure getByPath operation for each API platform resource - ([9f723de]($REPO/commit/9f723de14948c2834a820b539422e9c0cf2ef023))
- WebResponse as an API Resource - ([fd41c30]($REPO/commit/fd41c3050b1277a516a2709462fe917675b088a1))
- Add php8.1 - ([3e4f9c7]($REPO/commit/3e4f9c76d28bf2df23ec50eb2290459cae09e9e9))
- Added realms admin section - ([571181c]($REPO/commit/571181c19e06e35659226e3368075de18fbc1b1a))
- add healthcheck config and traefik - ([3676d2b]($REPO/commit/3676d2b3db01548262a634f69a9ba54231755003))
- changed document menu - ([83e1ec1]($REPO/commit/83e1ec1fb1959f129aa841bd885a274d86ad9b22))
- UserBundle config start - ([498fd5d]($REPO/commit/498fd5d1a580a55ac39cd74bb715256b1cf57954))
- Register RoadizUserBundle - ([5bba2bb]($REPO/commit/5bba2bbc12f10f682b878486aeec66f6f989a71b))
- Added RoadizUserBundle configuration - ([37dbc5a]($REPO/commit/37dbc5af2c46626b24dc4a9fb4a13f1039c67f14))
- UserBundle configuration - ([09a670f]($REPO/commit/09a670f7a785b082ca562780f15890a3197090b7))
- UserValidationRequest configuration - ([400466e]($REPO/commit/400466ef0b7d5ebf66679bb9f5285c316902ed20))
- Use built-in symfony login throttling - ([75234a1]($REPO/commit/75234a173dba0c2d3deb30f6660f6652f5140888))
- Requires rollerworks/password-common-list - ([3b9b67c]($REPO/commit/3b9b67c2abc1e9afd349d7c385eb3eb15467fcac))
- added nsarticle archive operation - ([a73f0bb]($REPO/commit/a73f0bb0c0ec9cd6c3af7a28cb6d9e322661870b))
- Added default built-in search - ([4c2d5dc]($REPO/commit/4c2d5dcddb3274208ef58ae60e7900486f73d693))
- Moved open_id configuration from core to rozier bundle - ([7b3172c]($REPO/commit/7b3172c29da5b45006b756ece44e941099e7e3a5))
- Use definitive archives endpoint configuration - ([a894b5a]($REPO/commit/a894b5afa5c46c292396774f6c0c5138ebedec84))
- Added new user proxied reference field to Page node-type - ([f44edae]($REPO/commit/f44edae8347b206c04a9121b8f0194a95a93d470))
- Added ffmpeg to docker image and populate DotEnv var - ([601b137]($REPO/commit/601b137f87b98d785d7332c5d106d54e64762a9f))
- Upgraded to API platform 2.7 and PHP 8.0 minimum - ([f56b655]($REPO/commit/f56b65573fe7ead12812260f95c17a40d510b859))
- New RoadizFontBundle to extract domain logic from Core and make it optional - ([377384a]($REPO/commit/377384a27b2493cf4b3444dbcdc848823c5cf1fe))
- Node types - ([6f4addc]($REPO/commit/6f4addc0863b799f18529c60bc04db0f10753f4c))
- Added roadiz/models lib into single project structure - ([8697f7b]($REPO/commit/8697f7b01f76f3e76f8125024c848165a6965073))
- Added flysystem dependency for InterventionRequest - ([09769ca]($REPO/commit/09769ca21fd32017164c3d3f4ed4c67798846b61))
- Clone roadiz/documents lib directly in lib folder for better development process - ([85a9d93]($REPO/commit/85a9d931a3e8deee9c455c293ff4f84c514c4b80))
- Big roadiz/documents namespace refactoring - ([02dfbb5]($REPO/commit/02dfbb547f8b5061ec09f606a4a529f69cc5a90c))
- Added flysystem font.storage - ([f639f4a]($REPO/commit/f639f4aaea464ae74b4bf203f8fa06d101845b7b))
- Added Menu and auto treeWalker generator - ([2bad3a9]($REPO/commit/2bad3a95ea436a297910091a31816af3cec46717))
- Added MenuLinkPathNormalizer - ([800c5c6]($REPO/commit/800c5c6a8e91d05f3c15ec2bac54ca200244a61b))
- Dotenv for API name and description - ([0184f91]($REPO/commit/0184f91b11751a8c7f1e3f739a1bd10e60c6c5fe))
- Added all roadiz/* sub packages to lib/ folder in order to create monorepo - ([854c74d]($REPO/commit/854c74d81632d46c37dcf4d4ba5ce0b33ab084ae))
- Moving all subpackages into monorepo, added Github Actions for splitting repositories - ([3e3f0f0]($REPO/commit/3e3f0f090551286f59965f1469638552d702a217))
- Added sub-package code - ([9898e76]($REPO/commit/9898e76a80327d6d0978551218f6e7bdfe47be2c))

### Refactor

- Updated dependencies - ([c201f15]($REPO/commit/c201f15a2137c41c7342352ae3833c9f6fc75703))

### WIP

- deps - ([7285a07]($REPO/commit/7285a077d3a30ac6ce2983c6cb33713cb8e4bef9))
- Markdown compiler pass, Solr endpoints, more commands - ([7d72796]($REPO/commit/7d7279605898c68c3474e270dde27afac7c487e0))
- disable apc cache - ([6c0b459]($REPO/commit/6c0b459d38b04a14083de3657357aee8db5cb9de))

<!-- generated by git-cliff -->
