# Changelog

All notable changes to Roadiz will be documented in this file.

## [2.3.41](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.40...v2.3.41) - 2025-10-23

### Bug Fixes

- **(tags)** add default translation to tag retrieval ([#256](https://github.com/roadiz/core-bundle-dev-app/issues/256)) - ([5d901c8](https://github.com/roadiz/core-bundle-dev-app/commit/5d901c8b68fbd9d562b9958fd3b4a0553756f697))

## [2.3.40](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.39...v2.3.40) - 2025-09-10

### Bug Fixes

- Update enshrined/svg-sanitize dependency to version ^0.22 - ([739aa7f](https://github.com/roadiz/core-bundle-dev-app/commit/739aa7f1c1497f5da6d493cf4bfd55bc835ac39e))
- Handle sanitization failure in DocumentSvgMessageHandler - ([8ef2d66](https://github.com/roadiz/core-bundle-dev-app/commit/8ef2d6646e46d1b709f4fd1624fb53019d36883c))
- Exclude SVG documents from DocumentSizeMessageHandler - ([4218999](https://github.com/roadiz/core-bundle-dev-app/commit/4218999e894db0f9c2a7e7f978cd153e32e857ab))

## [2.3.39](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.38...v2.3.39) - 2025-09-10

### Bug Fixes

- Add `hl.bs.type` highlighting configuration ([#179](https://github.com/roadiz/core-bundle-dev-app/issues/179)) - ([ad8b038](https://github.com/roadiz/core-bundle-dev-app/commit/ad8b0384705a9e16e7abbdb3f2aa6703d9d9895f))

## [2.3.38](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.37...v2.3.38) - 2025-06-30

### Bug Fixes

- **(Document)** Update getAlternativeText method to return null for decorative images - ([f7f2a04](https://github.com/roadiz/core-bundle-dev-app/commit/f7f2a0454d229a9d7d0be3fe5c2a404542c9059a))

### Testing

- **(Document)** Add alternative text handling for images in rendering - ([bf36a45](https://github.com/roadiz/core-bundle-dev-app/commit/bf36a459442120ea991d9059b4e71f6d5da6fdcf))

## [2.3.37](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.36...v2.3.37) - 2025-06-11

### Bug Fixes

- **(CustomForm)** Fixes translation key - ([211fb94](https://github.com/roadiz/core-bundle-dev-app/commit/211fb94156a7628fd9750999d48d19e47c92db4b))

## [2.3.36](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.35...v2.3.36) - 2025-06-11

### Bug Fixes

- **(CustomForm)** Use Batch processing to iterate on large custom-form answer list - ([f47af78](https://github.com/roadiz/core-bundle-dev-app/commit/f47af785c1068ca448d4c2d91526239e290e5a6c))
- **(CustomForm)** Unused use statement - ([3b1e12c](https://github.com/roadiz/core-bundle-dev-app/commit/3b1e12c9617f6aee2c1a84d16376ec1ac1d1825b))
- **(CustomForm)** Display custom-form answer count instead of field count - ([ed1d13a](https://github.com/roadiz/core-bundle-dev-app/commit/ed1d13a085709adc49bbc21834e1293b13265c9c))

## [2.3.35](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.34...v2.3.35) - 2025-04-16

### Features

- Added new NodesTagsFilter to enhance tag filtering and security against nodes - ([9bf1e56](https://github.com/roadiz/core-bundle-dev-app/commit/9bf1e56d876a257f06a0f911805a4c4408f23e60))

## [2.3.34](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.33...v2.3.34) - 2025-02-25

### Bug Fixes

- Fix CustomForm nullable setters - ([e8b4f95](https://github.com/roadiz/core-bundle-dev-app/commit/e8b4f95739f75affb656c6b2cd8251f9f3a297fc))

## [2.3.33](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.32...v2.3.33) - 2025-02-17

### Bug Fixes

- **(RozierBundle)** Added configurable csv_encoder_options - ([5332874](https://github.com/roadiz/core-bundle-dev-app/commit/53328746c7d776c1b8aac539c21e58a53d9d6b15))

## [2.3.32](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.31...v2.3.32) - 2025-02-10

### Bug Fixes

- Migrate all Node-type related TreeWalker definition from NodeSourceApi to QueryBuilder and NodeTypes bag - ([9bba248](https://github.com/roadiz/core-bundle-dev-app/commit/9bba248043075a0f6d8ace60e99edd6c8f8b85e1))

## [2.3.31](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.30...v2.3.31) - 2024-10-26

### Bug Fixes

- **(DtsGenerator)** Fixed `.d.ts` file header generation with latest @roadiz/types package - ([49aca37](https://github.com/roadiz/core-bundle-dev-app/commit/49aca375f0e51ec81bfa4a044c8924740067218a))

## [2.3.30](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.29...v2.3.30) - 2024-10-25

### Bug Fixes

- Updated MySQL reserved words list - ([277fd69](https://github.com/roadiz/core-bundle-dev-app/commit/277fd6979e648f526c9c7391faba6bc8cd37e7ff))

## [2.3.29](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.28...v2.3.29) - 2024-10-10

### Bug Fixes

- Allow duplicated documents when embedding audio embed covers, fix #19 - ([e61e2ee](https://github.com/roadiz/core-bundle-dev-app/commit/e61e2ee7f6caf12275a86519085e50d41917003b))
- Do not throw exception on bad page and itemPerPage argument, just use defaults fix #20 - ([e051822](https://github.com/roadiz/core-bundle-dev-app/commit/e05182222564e2a4dd59048cbac5d7771fc99d82))

## [2.3.28](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.27...v2.3.28) - 2024-09-24

### Bug Fixes

- **(Documents)** Do not throw `UnableToMoveFile` when document `filename` changes because we update the whole file - ([da5386e](https://github.com/roadiz/core-bundle-dev-app/commit/da5386e0cab580ec1a414f91779c1a1fbcc14160))

## [2.3.27](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.26...v2.3.27) - 2024-09-13

### Bug Fixes

- Fixed usage view with fieldName - ([c0a3e07](https://github.com/roadiz/core-bundle-dev-app/commit/c0a3e0738c1aec1487bc5e171d446332c1a7be5f))

## [2.3.26](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.25...v2.3.26) - 2024-08-27

### Bug Fixes

- Missing `processable` property for backoffice Explorer thumbnails - ([72e2195](https://github.com/roadiz/core-bundle-dev-app/commit/72e21955c7024a085555edfadeffb5abe00f97ae))

## [2.3.25](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.24...v2.3.25) - 2024-08-26

### Bug Fixes

- Set translation and locale to be used in Request context from Roadiz ResourceInfo - ([a444bc9](https://github.com/roadiz/core-bundle-dev-app/commit/a444bc98026c55d628b4465238c832da0eb473fb))

## [2.3.24](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.23...v2.3.24) - 2024-08-26

### Bug Fixes

- Do not use array reference for ConfigurableExplorerItem.php anymore - ([6a23ba0](https://github.com/roadiz/core-bundle-dev-app/commit/6a23ba032d174e58545f85bf81f2cdaf1760b154))

## [2.3.23](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.22...v2.3.23) - 2024-08-02

### Bug Fixes

- Fixed StringHandler camelCase method with lc_first - ([47c7798](https://github.com/roadiz/core-bundle-dev-app/commit/47c7798dc33e6453dd8028eaebb41f62fe0e528b))

## [2.3.22](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.21...v2.3.22) - 2024-08-01

### Bug Fixes

- Do not require a `themes` folder at project root - ([02b2e1c](https://github.com/roadiz/core-bundle-dev-app/commit/02b2e1cf242c8b8f63fb9caaa6d8eca0220ec53b))

## [2.3.21](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.20...v2.3.21) - 2024-07-29

### Bug Fixes

- **(Style)** Fixed explorer drawer item style to allow long titles and breadcrumbs - ([c621b6a](https://github.com/roadiz/core-bundle-dev-app/commit/c621b6ae459724f548867b5a9be8d85f4f472e91))
- Added autocomplete on login template - ([7369d9c](https://github.com/roadiz/core-bundle-dev-app/commit/7369d9cb4f56fd7779d19d8377e48323f74aeace))

## [2.3.20](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.19...v2.3.20) - 2024-07-13

### Bug Fixes

- Do not crash Doctrine metadata if NodesSources class does not exist. - ([93d84df](https://github.com/roadiz/core-bundle-dev-app/commit/93d84df1019c5fbb04ee3622e4cfe5085ed4ef41))

## [2.3.19](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.18...v2.3.19) - 2024-07-12

### Bug Fixes

- Fixed RequestDataCollector - ([4aa997a](https://github.com/roadiz/core-bundle-dev-app/commit/4aa997ac2605e0c1283100473d050923cdeba3c0))
- Fixed CacheController twig template - ([1d9d203](https://github.com/roadiz/core-bundle-dev-app/commit/1d9d20356329442b244eaa12020bbd629ed6aba9))
- Use constructor promotion for all final classes when possible - ([0513183](https://github.com/roadiz/core-bundle-dev-app/commit/0513183efb1146fc5a248257632240a745721de4))
- Fixed simple.html.twig Twig template with rz-accent-color for login and custom-form - ([2fa84b6](https://github.com/roadiz/core-bundle-dev-app/commit/2fa84b662046cefdc1c000c5ec002824221651ca))
- Get rid of `cssAction` route and use CSS vars for node, tag, attribute, custom-form, folder accent colors - ([06548e2](https://github.com/roadiz/core-bundle-dev-app/commit/06548e2f129a22c96a81b248f725a98e62fe54af))
- Use NodeSourceXlsxSerializer in Export and Search node controllers - ([00b96e4](https://github.com/roadiz/core-bundle-dev-app/commit/00b96e437fb03842a5f9caee2d542e6b6a7d8b13))
- Fixed unit tests namespaces, moved to tests folder and use `autoload-dev` composer directive - ([f8092dc](https://github.com/roadiz/core-bundle-dev-app/commit/f8092dce03b81a3d523d4a44b56a6e5796b08d0e))

### Features

- Export custom-form answers into CSV file instead of Xlsx - ([5da1a44](https://github.com/roadiz/core-bundle-dev-app/commit/5da1a448bc62ebe5373d0a2b8b2341fd801dca34))
- Added deprecation notices about `RZ\Roadiz\CoreBundle\Xlsx` services - ([a9af918](https://github.com/roadiz/core-bundle-dev-app/commit/a9af9187b7196c528250f19febf73877d22db4da))

## [2.3.18](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.17...v2.3.18) - 2024-07-05

### Bug Fixes

- **(Document)** Remove Documents used by CustomFormAnswer from unused repository method - ([f5289b8](https://github.com/roadiz/core-bundle-dev-app/commit/f5289b884266c1366d936f3c5f9690ed16dcf5b1))
- **(EmailManager)** Removed EmailManager and ContactFormManager direct usage. Use their *Factory service to avoid sharing service data between async requests. - ([80e25e5](https://github.com/roadiz/core-bundle-dev-app/commit/80e25e52319213bde65d2ca5021bbcf083870e6a))

## [2.3.17](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.16...v2.3.17) - 2024-07-05

### Bug Fixes

- **(Serialization)** Always falls back on default translation if no translation for Tag, Folder and Document entities - ([0f0ceed](https://github.com/roadiz/core-bundle-dev-app/commit/0f0ceeda64b6deb57d4dfebfcf45826736507211))

## [2.3.16](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.15...v2.3.16) - 2024-07-02

### Bug Fixes

- Missing `RealmRepository::findByNodeWithSerializationGroup` method - ([aac6e4e](https://github.com/roadiz/core-bundle-dev-app/commit/aac6e4e3eda25967908a0a9d2789c799acf82b0a))
- Missing `NodesTagsRepository` - ([b80548d](https://github.com/roadiz/core-bundle-dev-app/commit/b80548de67835ea4fb05374f3670972940cad754))
- `StringHandler::variablize` method with non-alphanum characters - ([6db4486](https://github.com/roadiz/core-bundle-dev-app/commit/6db4486e7414f3cb2335b56455debbaa86175930))

### Features

- Added required column in custom-form field listing view - ([f184d4e](https://github.com/roadiz/core-bundle-dev-app/commit/f184d4e1ca294e3c5cbd12a0998474979430c736))
- Transform CustomFormField `groupName` to DataList to improve user experience - ([676a016](https://github.com/roadiz/core-bundle-dev-app/commit/676a0161ad593e2efbfdcd4f0a15db8086b28425))
- Added CustomFormField `autocomplete` field to provide HTML autocomplete hint - ([6b8c3e7](https://github.com/roadiz/core-bundle-dev-app/commit/6b8c3e722e0c4385587f3fcd2566d6e04f9e68c1))
- Upgrade rezozero/tree-walker package - ([d90c04a](https://github.com/roadiz/core-bundle-dev-app/commit/d90c04ae736629a0d6618022b866fa625da7c958))

## [2.3.15](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.14...v2.3.15) - 2024-06-26

### Bug Fixes

- Still execute migration Version20240318204224 when there are no node_type_fields: i.e at Roadiz first install - ([6fda4a9](https://github.com/roadiz/core-bundle-dev-app/commit/6fda4a9b55361d9d49e847c79cc1876f5f3cce0a))

## [2.3.14](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.13...v2.3.14) - 2024-06-21

### Bug Fixes

- Fixed `null` newParent parameter for Ajax Node/Tag/Folder controllers - ([81d3570](https://github.com/roadiz/core-bundle-dev-app/commit/81d3570abc5c5b4dae139b71da03b6b6094c66da))

## [2.3.13](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.12...v2.3.13) - 2024-06-19

### Bug Fixes

- Removed duplicated Twig templates - ([a6461a1](https://github.com/roadiz/core-bundle-dev-app/commit/a6461a1952b238e9c3554a045a7226ac51f81c07))

## [2.3.12](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.11...v2.3.12) - 2024-06-17

### Bug Fixes

- **(Nodes)** Fixed node offspring resolution cache with new `NodeOffspringResolverInterface` service - ([cf62690](https://github.com/roadiz/core-bundle-dev-app/commit/cf62690c623bdba20812afb557538720b53b2abb))

## [2.3.11](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.10...v2.3.11) - 2024-06-17

### Bug Fixes

- **(Attributes)** Fixed AttributeValueRepository findByAttributable method preventing fetch attribute values without translations. Fixed AttributeChoiceType still requesting entityManager. - ([1ce795e](https://github.com/roadiz/core-bundle-dev-app/commit/1ce795ec77ac85af9aeee2d8d43520b21d214791))

## [2.3.10](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.9...v2.3.10) - 2024-06-14

### Bug Fixes

- Pass FormInterface to `bulkAction` to update bulk item with a form field data. - ([8d46507](https://github.com/roadiz/core-bundle-dev-app/commit/8d4650767dcaa183c135c4043ffd1cfea4dc64d6))

## [2.3.9](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.8...v2.3.9) - 2024-06-13

### Features

- Added security layer on NodesSources form type for each node-type-field (NodeTypeFieldVoter) - ([29b0332](https://github.com/roadiz/core-bundle-dev-app/commit/29b03327001d3b27db38928b5c7eee758bf1a9a6))
- Added customizable `getDefaultRouteParameters` in AbstractAdminController - ([899b397](https://github.com/roadiz/core-bundle-dev-app/commit/899b39788c25d9b735dc1d049684b4303d8b42a1))

## [2.3.8](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.7...v2.3.8) - 2024-06-07

### Performance

- Do not execute `RealmSerializationGroupNormalizer` if there are no realms setup with a serialization_group. - ([32da06d](https://github.com/roadiz/core-bundle-dev-app/commit/32da06dc98312371d230fd70f7c464b5b931d4f6))

## [2.3.7](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.6...v2.3.7) - 2024-06-06

### Features

- **(CoreBundle)** Made preview required RoleName configurable - ([1f94711](https://github.com/roadiz/core-bundle-dev-app/commit/1f947114846778923527effa243538e7b49316d1))

## [2.3.6](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.5...v2.3.6) - 2024-06-04

### Features

- **(NodeType)** Added `sortingAttributesByWeight` boolean option for NodeType - ([becf83e](https://github.com/roadiz/core-bundle-dev-app/commit/becf83e34f2242545f78b8496465f8a2a30777f3))

## [2.3.5](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.4...v2.3.5) - 2024-06-03

### Bug Fixes

- Undefined JS var - ([cb67bec](https://github.com/roadiz/core-bundle-dev-app/commit/cb67bec1803b28a2ab356d4a4ad9697f32f672fd))

## [2.3.4](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.3...v2.3.4) - 2024-06-03

### Bug Fixes

- **(UI)** Fixed drag and drop custom-form fields, node-type fields and attribute-values by setting new position against previous or next element id - ([1c929fc](https://github.com/roadiz/core-bundle-dev-app/commit/1c929fcdf5c636a77a7ec3730895c0790e1a9c09))

### Features

- **(Attributes)** Added Attribute `weight` field to sort filtered lists. - ([cbcc6e6](https://github.com/roadiz/core-bundle-dev-app/commit/cbcc6e682f0783b9da02454f411fe06f0417f0cb))

## [2.3.3](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.2...v2.3.3) - 2024-05-31

### Bug Fixes

- Display attribute code in list with usage count - ([4f88a0a](https://github.com/roadiz/core-bundle-dev-app/commit/4f88a0a1a854be03db31d254849ddb9596acd631))

## [2.3.2](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.1...v2.3.2) - 2024-05-27

### Bug Fixes

- **(OpenId)** Drop Guzzle client for Symfony Http client and silent open-id misconfiguration errors on Discovery - ([4fe292f](https://github.com/roadiz/core-bundle-dev-app/commit/4fe292f66932d98b22057df6b341afa4e2735fab))
- **(User)** Fixed user-list image when no picture - ([399bd91](https://github.com/roadiz/core-bundle-dev-app/commit/399bd9140341d67b8779317082af9cd268728466))
- Throw 400 when requesting explorer entities with bad node-type-field id - ([883bc71](https://github.com/roadiz/core-bundle-dev-app/commit/883bc715ec0013c8652d53effd1105c5d9b4e517))

## [2.3.1](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.0...v2.3.1) - 2024-05-22

### Bug Fixes

- **(Node)** Always set node TTL against its node-type - ([a772d06](https://github.com/roadiz/core-bundle-dev-app/commit/a772d065602cf7411ae7a090707e3809f7c6e9f4))

## [2.3.0](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.15...v2.3.0) - 2024-05-15

### ⚠ BREAKING CHANGES

* **Solr:** Removed `$proximity` argument from `search` and `searchWithHighlight` SearchHandlerInterface methods
* Make sure you don't have fields with name longer than 50 characters before migrating. Migration can be skipped if so.
* Removed NodeTypeField `id` join column from NodesCustomForms, NodesSourcesDocuments and NodesToNodes relation tables to use `field_name` string column for loose relation. **Make sure to backup your database before performing this migration**.
* `node_type_name` JSON property is no-longer required in node-type JSON export files.
* Node constructor does not accept any argument anymore.
* Switched to ApiPlatform 3.2, make sure to upgrade `bundles.php` file and `api_platform.yaml` configuration
* **Nodes:** NodesSources `metaKeyword` and Node `priority` fields will be dropped.
* **Settings:** Setting encryption and crypto keys have been dropped, migrate all your secrets to symfony:secrets to get only one secure vault.

Remove any crypto configuration from `config/packages/roadiz_core.yml`:

```yaml
    security:
        private_key_name: default
```
* `getResultItems` method will always return `array<SolrSearchResultItem>` no matter item type or highlighting.
* Regenerate your api platform resource YAML files, or rename `getByPath` operation to `%entity%_get_by_path`
* Command constructor signatures changed
* Controller::get and Controller::has methods have been removed

### Features

* Added ApiProperty documentation for base entities fields ([fa4a0be](https://github.com/roadiz/core-bundle-dev-app/commit/fa4a0be43309ebedcd5816d1f2650d9d07f2452f))
* Added more social network urls to head ([b4f6863](https://github.com/roadiz/core-bundle-dev-app/commit/b4f68631c46442655208c922bf6d63e7c135e52c))
* Added new `cron` testing commands to test if your cron jobs are executing ([efdbaa5](https://github.com/roadiz/core-bundle-dev-app/commit/efdbaa59ba4930dc239e1aeaacf189828ef0cfd5))
* Added new `cron` testing commands to test if your cron jobs are executing ([b486bec](https://github.com/roadiz/core-bundle-dev-app/commit/b486bec23bfeef277e05c2f07d618dcfff7b5bc2))
* Added new `DataListTextType` to render HTML input with their datalist. ([5316137](https://github.com/roadiz/core-bundle-dev-app/commit/5316137b85a1fd493fa657dfcbadd5be76690005))
* **Admin:** Create an update event for each bulk entity ([7ca5792](https://github.com/roadiz/core-bundle-dev-app/commit/7ca57920922893e58bd37031a81494faaa33488e))
* Allow WebResponse object instantiation overriding ([8690594](https://github.com/roadiz/core-bundle-dev-app/commit/8690594d2cd4119ee575126f710f0c9ae12a26a5))
* Always use SolrSearchResultItem to wrap a single object from search engine results. ([00ebe1d](https://github.com/roadiz/core-bundle-dev-app/commit/00ebe1df1d6db05d8a5fb54fa47d2b58f9db8230))
* Better overridability for WebResponses ([1dff926](https://github.com/roadiz/core-bundle-dev-app/commit/1dff926798e5df990725aeb78e4243d528f6b658))
* **CoreBundle:** Refactored node routing ([eea6399](https://github.com/roadiz/core-bundle-dev-app/commit/eea6399eef1dfef2ef242d1237fd4a539e97172f))
* Create different operation names for each Entity with WebResponse. ([21fdc5c](https://github.com/roadiz/core-bundle-dev-app/commit/21fdc5cad7de5b6bce3d0ededd7fdab253485e37))
* **CustomForms:** Made NodesCustomForms.php relation columns not nullable for index performance ([dba0237](https://github.com/roadiz/core-bundle-dev-app/commit/dba02370ac5bd4fd8cf93d8d610ea7a51c4e1d7e))
* Do not serialize tag slug manually ([42747f1](https://github.com/roadiz/core-bundle-dev-app/commit/42747f1a957a13e1e555bc9e8ba53f03cdc752c5))
* **Documents:** Improved $mimeToIcon with additional mime-types and new `3d` category. ([898264f](https://github.com/roadiz/core-bundle-dev-app/commit/898264fa246334137b0d804ffef66e1cc3e230e4))
* **Documents:** Made NodesSourcesDocuments.php relation columns not nullable for index performance ([167b2dd](https://github.com/roadiz/core-bundle-dev-app/commit/167b2dd3934b90ba40b6940605f12b700cb341bc))
* **Documents:** Set SvgRenderers higher priority than ImageRenderer ([920f273](https://github.com/roadiz/core-bundle-dev-app/commit/920f273c1671f9887abbe19c084bfd8361f0987c))
* **EntityGenerator:** Added ApiProperty documentation for generated entities non-virtual fields ([d6e4462](https://github.com/roadiz/core-bundle-dev-app/commit/d6e44626bd92ebaeeb275130138151efead59105))
* Expose WebResponse optional `maxAge` and populate with Node's TTL when available ([1a020de](https://github.com/roadiz/core-bundle-dev-app/commit/1a020de46bde509543e806903c4cedc9c5ae7963))
* Fixed api-platform version to 3.2 ([6501910](https://github.com/roadiz/core-bundle-dev-app/commit/6501910e6613a80c59d37754f5734e8bb60f7243))
* Improved *locale* management by storing `_translation` into Request attributes during LocaleSubscriber ([9e70e04](https://github.com/roadiz/core-bundle-dev-app/commit/9e70e04f068c677c20aa0becb98ca144517be279))
* Made Gravatar optional with roadiz_core.useGravatar config option ([6e8c396](https://github.com/roadiz/core-bundle-dev-app/commit/6e8c3965ee83c118bc826271fec8e9a6975b8a70))
* **Nodes:** Made NodesToNodes.php relation columns not nullable for index performance ([bd9f5d6](https://github.com/roadiz/core-bundle-dev-app/commit/bd9f5d63e5ccb2cdfb600d77564fd19c2f24d113))
* **Nodes:** Removed useless NodesSources metaKeyword and Node priority fields. ([e3bed58](https://github.com/roadiz/core-bundle-dev-app/commit/e3bed5894589327507add5dcd24868ad3fad10b2))
* **NodesSources:** Respect user set TTL on nodes-sources during API requests ([f1c66c2](https://github.com/roadiz/core-bundle-dev-app/commit/f1c66c2628fa7cb4c7860a4b69d91a40348c1487))
* **OpenId:** Added `force_ssl_on_redirect_uri` open_id configuration to allow `http` redirectUri ([352bf79](https://github.com/roadiz/core-bundle-dev-app/commit/352bf796344e44b0ebfcd2f569697bf638396319))
* Override `_api_operation` attribute as well when using `GetWebResponseByPathController` ([89dd9a3](https://github.com/roadiz/core-bundle-dev-app/commit/89dd9a3cf8378749055ba7ddbeeae27cf838899f))
* Prevent creating same NodeTypeField name but with different doctrine type. ([c81773d](https://github.com/roadiz/core-bundle-dev-app/commit/c81773dd367eed945cdf9849a1950a1aca5e5d10))
* **Realms:** Made RealmNode.php realm_id column not nullable for index performance ([153d53c](https://github.com/roadiz/core-bundle-dev-app/commit/153d53cc2f8a1aefe3d0639d9b1b87f191fd976b))
* Reduce NodeTypeField name to maximum 50 characters long. ([8d7529a](https://github.com/roadiz/core-bundle-dev-app/commit/8d7529aad953f5b4f5689672df9fd6ae725bc89f))
* Refactored command signatures ([173a4fb](https://github.com/roadiz/core-bundle-dev-app/commit/173a4fbcec66c92125330ac3bb609f82c60d34f8))
* Removed NodeTypeField `id` join column from NodesCustomForms, NodesSourcesDocuments and NodesToNodes relation tables to use string column for loose relation. ([fb5a2d8](https://github.com/roadiz/core-bundle-dev-app/commit/fb5a2d812cd5544d9c34d00dc6e787f2dc5a0749))
* Removed NodeTypeFieldObjectConstructor.php for app:install to avoid conflicts between object construction and dropping all fields. `node_type_name` JSON property is no-longer required ([6a30b32](https://github.com/roadiz/core-bundle-dev-app/commit/6a30b32b7720cc761b57c34b5555bb50bdcf5f5d))
* Set Request attribute `data` as WebResponse instead of WebResponse item. Changed LinkHeader and AddHeaders event subscribers to check if `data` is WebResponseInterface ([a7da352](https://github.com/roadiz/core-bundle-dev-app/commit/a7da352e369d185c9531b0528c9835bf4010a82e))
* **Settings:** Removed encrypted Settings. Use symfony:secret to store secrets. ([ccec892](https://github.com/roadiz/core-bundle-dev-app/commit/ccec89210ba7e0a677f523b983ed168441d65d1d))
* **Solr:** Removed `$proximity` argument from `search` and `searchWithHighlight` SearchHandlerInterface methods ([e0e36b5](https://github.com/roadiz/core-bundle-dev-app/commit/e0e36b51bd1eadc239dc5f5adea0ee0d7858375f))
* Upgrade to ApiPlatform 3.2 ([4962d87](https://github.com/roadiz/core-bundle-dev-app/commit/4962d87b316a9fac6a0163eaabf23987a94c4bf0))
* Upgrade to doctrine/orm ~2.19.0 ([b14f5be](https://github.com/roadiz/core-bundle-dev-app/commit/b14f5bef13870c6c4c929b1fe914295e89e00751))
* Upgraded to Symfony 6.4 LTS ([0e37266](https://github.com/roadiz/core-bundle-dev-app/commit/0e37266d7ebb9f6b6a72d9e81714496a99fba8db))
* WebResponseDataTransformer must always transform PersistableInterface ([de1226a](https://github.com/roadiz/core-bundle-dev-app/commit/de1226a45ac7d7e9bac7d4643810bae5f5f46deb))
* **WebResponse:** Expose request attribute `_web_response_item_class` to store WebResponse item class name ([e483039](https://github.com/roadiz/core-bundle-dev-app/commit/e483039982cf655012c25b5d72c6fc94c21be0fb))
* **WebResponse:** Moved all web_response_by_path operations to WebResponse resource to avoid cheating with Api Platform resource resolution. ([a44745a](https://github.com/roadiz/core-bundle-dev-app/commit/a44745ae4f3598e6ca64133937c1de35a15bb7e8))


### Bug Fixes

* Allow OpenApi decoration with no `web_response_by_path` path ([c74dc6f](https://github.com/roadiz/core-bundle-dev-app/commit/c74dc6f2f65cbfe0ed9082b99af40e72a9c1684d))
* **Documents:** Requires api-platform/metadata for unit tests ([ae67dba](https://github.com/roadiz/core-bundle-dev-app/commit/ae67dba4325f43bae173dd250ad237b46767b60e))
* **Documents:** Updated Dailymotion oembed discovery and iframe source generation. ([2a06744](https://github.com/roadiz/core-bundle-dev-app/commit/2a067441e808cf16d872a9d9d3688cb3b7d0c23a))
* Fixed AjaxNodesExplorerController with SearchResultsInterface ([9231f42](https://github.com/roadiz/core-bundle-dev-app/commit/9231f42e35decc50aebdc7c4c1b3c9634b4edf64))
* Fixed Attributes import ([9023668](https://github.com/roadiz/core-bundle-dev-app/commit/90236689350a01e850332282791dec2382aef6b2))
* Fixed permission for nodesTranstypePage action menu button ([151878e](https://github.com/roadiz/core-bundle-dev-app/commit/151878e7cce5b58da2c9c9b6ce48b9d26e707f9d))
* Fixed stateless with some listeners ([b04faa2](https://github.com/roadiz/core-bundle-dev-app/commit/b04faa2a99167233328a5fdf6393cb2ae0041e2c))
* LoginController.php with random images ([032e68e](https://github.com/roadiz/core-bundle-dev-app/commit/032e68e7cd184b7a4be7c88397dad8ac41a15c70))
* Missing `orphanRemoval: true` on non-nullable OneToMany relationships ([019b353](https://github.com/roadiz/core-bundle-dev-app/commit/019b3534ba89effa1479673382e2150164f63973))
* Moved AbstractDateTimedPositioned.php to CoreBundle to avoid requiring ApiPlatform on Models ([359c026](https://github.com/roadiz/core-bundle-dev-app/commit/359c026527e1d8a61eaa2c27e3471e05270a6559))
* Need to comment all bundle config api_resource definitions ([cfd0c53](https://github.com/roadiz/core-bundle-dev-app/commit/cfd0c530297ae002410698b628b1e16dc806075a))
* Prevent some api endpoint routes to trigger session from LocaleSubscriber ([fa63c8c](https://github.com/roadiz/core-bundle-dev-app/commit/fa63c8cc0cde00bb328e42724a92223d79e6ec58))
* Removed explicit symfony-cmf/routing dependency ([6daf32f](https://github.com/roadiz/core-bundle-dev-app/commit/6daf32f8ae25ab371ca7eba94945e41894959103))
* Removed NodeTypeInterface from Node constructor, Removed ThemeRoutesLoader.php ([0816525](https://github.com/roadiz/core-bundle-dev-app/commit/08165257777756f954be303719659ec7070a586e))
* Rewrote all api_resources config files with `resources:` prefix ([40e5e9a](https://github.com/roadiz/core-bundle-dev-app/commit/40e5e9a4fe3b030858301e33c3a62924fcd2f4ea))
* **Security:** Fixed `NodeVoter` accepting `UserInterface` for OpenId accounts ([e9dc229](https://github.com/roadiz/core-bundle-dev-app/commit/e9dc229853a56ccae34730de10ed4935e72e0364))
* **Setting:** Always format DateTimeInterface to string in settings even if type is wrongly set ([3c9eda6](https://github.com/roadiz/core-bundle-dev-app/commit/3c9eda63c3bbf30e9feed9130d8701fb27397100))


### Performance Improvements

* Improved event subscriber registration and initialization ([febf372](https://github.com/roadiz/core-bundle-dev-app/commit/febf372018ec54726914ba07ebba480acf1dc661))
* Made all foreign key columns not nullable for index performance ([fd90805](https://github.com/roadiz/core-bundle-dev-app/commit/fd908058e2856c58cca0d1eba582bee020145a3c))
* Missing `node_parent_position` composite index ([9147b6b](https://github.com/roadiz/core-bundle-dev-app/commit/9147b6bb03a7176ee05257eed5fc6ca66c8c7e78))

## [2.2.15](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.14...v2.2.15) - 2024-04-19

### ⚠ Breaking changes

- Removed `$proximity` argument from `search` and `searchWithHighlight` SearchHandlerInterface methods

### Bug Fixes

- **(Documents)** Updated Dailymotion oembed discovery and iframe source generation. - ([2628f40](https://github.com/roadiz/core-bundle-dev-app/commit/2628f40dfe9e50139a16e759018d8ff356f11496))
- Allow OpenApi decoration with no `web_response_by_path` path - ([c74dc6f](https://github.com/roadiz/core-bundle-dev-app/commit/c74dc6f2f65cbfe0ed9082b99af40e72a9c1684d))
- Need to comment all bundle config api_resource definitions - ([cfd0c53](https://github.com/roadiz/core-bundle-dev-app/commit/cfd0c530297ae002410698b628b1e16dc806075a))
- Rewrote all api_resources config files with `resources:` prefix - ([40e5e9a](https://github.com/roadiz/core-bundle-dev-app/commit/40e5e9a4fe3b030858301e33c3a62924fcd2f4ea))

### Features

- **(Solr)** [**breaking**] Removed `$proximity` argument from `search` and `searchWithHighlight` SearchHandlerInterface methods - ([e0e36b5](https://github.com/roadiz/core-bundle-dev-app/commit/e0e36b51bd1eadc239dc5f5adea0ee0d7858375f))
- **(WebResponse)** Expose request attribute `_web_response_item_class` to store WebResponse item class name - ([e483039](https://github.com/roadiz/core-bundle-dev-app/commit/e483039982cf655012c25b5d72c6fc94c21be0fb))
- Set Request attribute `data` as WebResponse instead of WebResponse item. Changed LinkHeader and AddHeaders event subscribers to check if `data` is WebResponseInterface - ([a7da352](https://github.com/roadiz/core-bundle-dev-app/commit/a7da352e369d185c9531b0528c9835bf4010a82e))

## [2.2.14](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.13...v2.2.14) - 2024-03-20

### Bug Fixes

- Use `\DateTimeInterface` to check object types instead of `\DateTime` - ([9389865](https://github.com/roadiz/core-bundle-dev-app/commit/9389865ed9d2ef47e5c680b5bf519bd45111dea5))

## [2.2.13](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.12...v2.2.13) - 2024-03-08

### ⚠ Breaking changes

- Make sure you don't have fields with name longer than 50 characters before migrating. Migration can be skipped if so.
- Removed NodeTypeField `id` join column from NodesCustomForms, NodesSourcesDocuments and NodesToNodes relation tables to use `field_name` string column for loose relation. **Make sure to backup your database before performing this migration**.
- `node_type_name` JSON property is no-longer required in node-type JSON export files.
- Node constructor does not accept any argument anymore.

### Bug Fixes

- **(Documents)** Requires api-platform/metadata for unit tests - ([ae67dba](https://github.com/roadiz/core-bundle-dev-app/commit/ae67dba4325f43bae173dd250ad237b46767b60e))
- **(Setting)** Always format DateTimeInterface to string in settings even if type is wrongly set - ([3c9eda6](https://github.com/roadiz/core-bundle-dev-app/commit/3c9eda63c3bbf30e9feed9130d8701fb27397100))
- Error during back-porting from 2.3.x-dev - ([409eed2](https://github.com/roadiz/core-bundle-dev-app/commit/409eed2df003f3107ab5b73e2b9cbac56239c93a))
- Fixed AjaxNodesExplorerController with SearchResultsInterface - ([9231f42](https://github.com/roadiz/core-bundle-dev-app/commit/9231f42e35decc50aebdc7c4c1b3c9634b4edf64))
- Missing `orphanRemoval: true` on non-nullable OneToMany relationships - ([019b353](https://github.com/roadiz/core-bundle-dev-app/commit/019b3534ba89effa1479673382e2150164f63973))
-  [**breaking**]Removed NodeTypeInterface from Node constructor, Removed ThemeRoutesLoader.php - ([0816525](https://github.com/roadiz/core-bundle-dev-app/commit/08165257777756f954be303719659ec7070a586e))
- LoginController.php with random images - ([032e68e](https://github.com/roadiz/core-bundle-dev-app/commit/032e68e7cd184b7a4be7c88397dad8ac41a15c70))
- Moved AbstractDateTimedPositioned.php to CoreBundle to avoid requiring ApiPlatform on Models - ([359c026](https://github.com/roadiz/core-bundle-dev-app/commit/359c026527e1d8a61eaa2c27e3471e05270a6559))

### Documentation

- Temporary CHANGELOG.md - ([1df3c21](https://github.com/roadiz/core-bundle-dev-app/commit/1df3c21ec6b1ac8a414bf5c01aa28773059929eb))

### Features

- Expose WebResponse optional `maxAge` and populate with Node's TTL when available - ([1a020de](https://github.com/roadiz/core-bundle-dev-app/commit/1a020de46bde509543e806903c4cedc9c5ae7963))
-  [**breaking**]Reduce NodeTypeField name to maximum 50 characters long. - ([8d7529a](https://github.com/roadiz/core-bundle-dev-app/commit/8d7529aad953f5b4f5689672df9fd6ae725bc89f))
-  [**breaking**]Removed NodeTypeField `id` join column from NodesCustomForms, NodesSourcesDocuments and NodesToNodes relation tables to use string column for loose relation. - ([fb5a2d8](https://github.com/roadiz/core-bundle-dev-app/commit/fb5a2d812cd5544d9c34d00dc6e787f2dc5a0749))
-  [**breaking**]Removed NodeTypeFieldObjectConstructor.php for app:install to avoid conflicts between object construction and dropping all fields. `node_type_name` JSON property is no-longer required - ([6a30b32](https://github.com/roadiz/core-bundle-dev-app/commit/6a30b32b7720cc761b57c34b5555bb50bdcf5f5d))
- Upgrade to doctrine/orm ~2.19.0 - ([b14f5be](https://github.com/roadiz/core-bundle-dev-app/commit/b14f5bef13870c6c4c929b1fe914295e89e00751))

### Refactor

- Removed deprecated AppController.php and Handler methods. Removed FrontendController.php. - ([4c72de2](https://github.com/roadiz/core-bundle-dev-app/commit/4c72de294546bcf4666924697be406ec84d6f84a))
- Some constructor args refactoring, remove direct dependency on KernelInterface - ([de5356f](https://github.com/roadiz/core-bundle-dev-app/commit/de5356f702520492ef2fb455eda93826f307d7f3))

### Testing

- Fixed tests directory - ([ad895dc](https://github.com/roadiz/core-bundle-dev-app/commit/ad895dccf9c701b0ae689c28eb4c58ef2c3ab997))
- Align all phpstan.neon levels - ([261c583](https://github.com/roadiz/core-bundle-dev-app/commit/261c5839ff4150bee3307e470cc9d3ea16fcaf78))
- Remove atoum from github actions - ([dff59c9](https://github.com/roadiz/core-bundle-dev-app/commit/dff59c9df3da2eae6a5ca2cdb18d24a3e230ad41))
- Replace atoum with PHPUnit for unit tests - ([49f315f](https://github.com/roadiz/core-bundle-dev-app/commit/49f315f99b1a4c5110105f11eae1327af264b9f1))

## [2.2.12](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.11...v2.2.12) - 2024-03-08

### Bug Fixes

- **(Documents)** Do not try to render private Document URLs even for thumbnails - ([57ee606](https://github.com/roadiz/core-bundle-dev-app/commit/57ee606e35b8e2cd2b5327cd47593a4925ff0b90))

### Refactor

- Removed deprecated Packages.php - ([d8f7511](https://github.com/roadiz/core-bundle-dev-app/commit/d8f75116ee6740e21b4bffc531585a3457632f7d))

## [2.2.11](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.10...v2.2.11) - 2024-03-07

### Bug Fixes

- **(Trees)** Fixed tag and folder trees when they display first created translation instead of user chosen translation - ([87878c2](https://github.com/roadiz/core-bundle-dev-app/commit/87878c28e56e8812041e6c506d2db96e2c5c05c7))

### Refactor

- Use constructor readonly initializers on Ajax controllers - ([ce01609](https://github.com/roadiz/core-bundle-dev-app/commit/ce016097734698486d84fefa209c075db0c02c3b))

## [2.2.10](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.9...v2.2.10) - 2024-03-07

### Bug Fixes

- **(NodeTypes)** Do not update DB schema when importing a NodeType JSON file. Fixes `app:install` command which updated JSON files and schema instead of just importing Types. - ([80f7a86](https://github.com/roadiz/core-bundle-dev-app/commit/80f7a86d20994e2fce09cf6e2f9cf57a6a6dad3f))

## [2.2.9](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.8...v2.2.9) - 2024-03-07

### Bug Fixes

- **(AttributeValue)** Always join Node to filter out attribute values linked to not-published nodes. - ([cdd4158](https://github.com/roadiz/core-bundle-dev-app/commit/cdd41580b85f7781a4a9c4d0a7060b9e26bb0d6d))

## [2.2.8](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.7...v2.2.8) - 2024-03-07

### ⚠ Breaking changes

- Switched to ApiPlatform 3.2, make sure to upgrade `bundles.php` file and `api_platform.yaml` configuration

### Bug Fixes

- **(Realms)** Fixed realm cache-key user awareness - ([ce71706](https://github.com/roadiz/core-bundle-dev-app/commit/ce7170684520e05fbd6f1b47c3b847ba138f12f5))

### Features

- **(Admin)** Create an update event for each bulk entity - ([7ca5792](https://github.com/roadiz/core-bundle-dev-app/commit/7ca57920922893e58bd37031a81494faaa33488e))
- **(CustomForms)** Made NodesCustomForms.php relation columns not nullable for index performance - ([dba0237](https://github.com/roadiz/core-bundle-dev-app/commit/dba02370ac5bd4fd8cf93d8d610ea7a51c4e1d7e))
- **(Documents)** Made NodesSourcesDocuments.php relation columns not nullable for index performance - ([167b2dd](https://github.com/roadiz/core-bundle-dev-app/commit/167b2dd3934b90ba40b6940605f12b700cb341bc))
- **(Nodes)** Made NodesToNodes.php relation columns not nullable for index performance - ([bd9f5d6](https://github.com/roadiz/core-bundle-dev-app/commit/bd9f5d63e5ccb2cdfb600d77564fd19c2f24d113))
- **(Realms)** Made RealmNode.php realm_id column not nullable for index performance - ([153d53c](https://github.com/roadiz/core-bundle-dev-app/commit/153d53cc2f8a1aefe3d0639d9b1b87f191fd976b))
-  [**breaking**]Upgrade to ApiPlatform 3.2 - ([4962d87](https://github.com/roadiz/core-bundle-dev-app/commit/4962d87b316a9fac6a0163eaabf23987a94c4bf0))

### Performance

- Missing `node_parent_position` composite index - ([9147b6b](https://github.com/roadiz/core-bundle-dev-app/commit/9147b6bb03a7176ee05257eed5fc6ca66c8c7e78))
- Made all foreign key columns not nullable for index performance - ([fd90805](https://github.com/roadiz/core-bundle-dev-app/commit/fd908058e2856c58cca0d1eba582bee020145a3c))

## [2.2.7](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.6...v2.2.7) - 2024-02-27

### Bug Fixes

- Lighten Tag and NodesSources serialization with `tag_documents` and `tag_color` serialization groups - ([6890501](https://github.com/roadiz/core-bundle-dev-app/commit/68905012e4200983a2a054d5ac71f4e0f05949cf))

## [2.2.6](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.5...v2.2.6) - 2024-02-25

### Bug Fixes

- **(Search)** Missing `hl.q` Solr param when requesting highlight with complex queries - ([ae7fb60](https://github.com/roadiz/core-bundle-dev-app/commit/ae7fb60a11445a1ba0a2e39b51b8b713f2a9d919))

### Styling

- Fixed roadiz icon position when no custom image set - ([cfaf751](https://github.com/roadiz/core-bundle-dev-app/commit/cfaf751a390d9bf0dd49fac3646738274766d661))
- Upgraded Backoffice user-panel and admin-menu sub-nav items - ([744aec2](https://github.com/roadiz/core-bundle-dev-app/commit/744aec23b2990b89bfee60f0751a39c844a5bcbc))

## [2.2.5](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.4...v2.2.5) - 2024-02-23

### Bug Fixes

- Prevent some api endpoint routes to trigger session from LocaleSubscriber - ([fa63c8c](https://github.com/roadiz/core-bundle-dev-app/commit/fa63c8cc0cde00bb328e42724a92223d79e6ec58))

### Features

- Prevent creating same NodeTypeField name but with different doctrine type. - ([d4a5c58](https://github.com/roadiz/core-bundle-dev-app/commit/d4a5c583d8208fd289126f5338b0dda7f861a90f))

## [2.2.4](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.3...v2.2.4) - 2024-02-21

### ⚠ Breaking changes

- NodesSources `metaKeyword` and Node `priority` fields will be dropped.
- Setting encryption and crypto keys have been dropped, migrate all your secrets to symfony:secrets to get only one secure vault.

Remove any crypto configuration from `config/packages/roadiz_core.yml`:

```yaml
    security:
        private_key_name: default
```
- `getResultItems` method will always return `array<SolrSearchResultItem>` no matter item type or highlighting.
- Regenerate your api platform resource YAML files, or rename `getByPath` operation to `%entity%_get_by_path`
- Command constructor signatures changed

### Bug Fixes

- Fixed fetching non-ISO locale from database and querying result cache - ([6a1ec32](https://github.com/roadiz/core-bundle-dev-app/commit/6a1ec3292c20fa11e9f026535630711fc6e6ac8e))
- Fixed maintenance mode exception when using API endpoints - ([02f0457](https://github.com/roadiz/core-bundle-dev-app/commit/02f045761a57f047f34c0d6e8a3e5209487be69c))

### Documentation

- Changed CHANGELOG generator to git-cliff - ([1de377b](https://github.com/roadiz/core-bundle-dev-app/commit/1de377bb3822a023ecd02db87dbd214a229cc297))

### Features

- **(Nodes)** [**breaking**] Removed useless NodesSources metaKeyword and Node priority fields. - ([e3bed58](https://github.com/roadiz/core-bundle-dev-app/commit/e3bed5894589327507add5dcd24868ad3fad10b2))
- **(NodesSources)** Respect user set TTL on nodes-sources during API requests - ([f1c66c2](https://github.com/roadiz/core-bundle-dev-app/commit/f1c66c2628fa7cb4c7860a4b69d91a40348c1487))
- **(Settings)** [**breaking**] Removed encrypted Settings. Use symfony:secret to store secrets. - ([ccec892](https://github.com/roadiz/core-bundle-dev-app/commit/ccec89210ba7e0a677f523b983ed168441d65d1d))
- Improved *locale* management by storing `_translation` into Request attributes during LocaleSubscriber - ([9e70e04](https://github.com/roadiz/core-bundle-dev-app/commit/9e70e04f068c677c20aa0becb98ca144517be279))
- Override `_api_operation` attribute as well when using `GetWebResponseByPathController` - ([89dd9a3](https://github.com/roadiz/core-bundle-dev-app/commit/89dd9a3cf8378749055ba7ddbeeae27cf838899f))
-  [**breaking**]Always use SolrSearchResultItem to wrap a single object from search engine results. - ([00ebe1d](https://github.com/roadiz/core-bundle-dev-app/commit/00ebe1df1d6db05d8a5fb54fa47d2b58f9db8230))
-  [**breaking**]Create different operation names for each Entity with WebResponse. - ([21fdc5c](https://github.com/roadiz/core-bundle-dev-app/commit/21fdc5cad7de5b6bce3d0ededd7fdab253485e37))
-  [**breaking**]Refactored command signatures - ([173a4fb](https://github.com/roadiz/core-bundle-dev-app/commit/173a4fbcec66c92125330ac3bb609f82c60d34f8))

### Performance

- Improved event subscriber registration and initialization - ([febf372](https://github.com/roadiz/core-bundle-dev-app/commit/febf372018ec54726914ba07ebba480acf1dc661))

## [2.2.3](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.2...v2.2.3) - 2024-02-09

### Bug Fixes

- Fixed Setting value with \DateTimeInterface - ([fa9acb5](https://github.com/roadiz/core-bundle-dev-app/commit/fa9acb574d68b88350247d77a2fdb0fbd9a30c9b))
- Fixed stateless with some listeners - ([b04faa2](https://github.com/roadiz/core-bundle-dev-app/commit/b04faa2a99167233328a5fdf6393cb2ae0041e2c))

### Features

- WebResponseDataTransformer must always transform PersistableInterface - ([de1226a](https://github.com/roadiz/core-bundle-dev-app/commit/de1226a45ac7d7e9bac7d4643810bae5f5f46deb))
- Allow WebResponse object instantiation overriding - ([8690594](https://github.com/roadiz/core-bundle-dev-app/commit/8690594d2cd4119ee575126f710f0c9ae12a26a5))
- Better overridability for WebResponses - ([1dff926](https://github.com/roadiz/core-bundle-dev-app/commit/1dff926798e5df990725aeb78e4243d528f6b658))
- Added more social network urls to head - ([b4f6863](https://github.com/roadiz/core-bundle-dev-app/commit/b4f68631c46442655208c922bf6d63e7c135e52c))

## [2.2.2](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.1...v2.2.2) - 2024-02-07

### Bug Fixes

- Prevent redirections to resolve a not-published resource. - ([665c031](https://github.com/roadiz/core-bundle-dev-app/commit/665c0316a40f487428c8277e5fd6cdd6b6dbba08))
- Removed explicit symfony-cmf/routing dependency - ([6daf32f](https://github.com/roadiz/core-bundle-dev-app/commit/6daf32f8ae25ab371ca7eba94945e41894959103))

### Features

- **(CoreBundle)** Refactored node routing - ([eea6399](https://github.com/roadiz/core-bundle-dev-app/commit/eea6399eef1dfef2ef242d1237fd4a539e97172f))
- **(EntityGenerator)** Added ApiProperty documentation for generated entities non-virtual fields - ([d6e4462](https://github.com/roadiz/core-bundle-dev-app/commit/d6e44626bd92ebaeeb275130138151efead59105))
- Added new `cron` testing commands to test if your cron jobs are executing - ([efdbaa5](https://github.com/roadiz/core-bundle-dev-app/commit/efdbaa59ba4930dc239e1aeaacf189828ef0cfd5))
- Added new `DataListTextType` to render HTML input with their datalist. - ([5316137](https://github.com/roadiz/core-bundle-dev-app/commit/5316137b85a1fd493fa657dfcbadd5be76690005))
- Added ApiProperty documentation for base entities fields - ([fa4a0be](https://github.com/roadiz/core-bundle-dev-app/commit/fa4a0be43309ebedcd5816d1f2650d9d07f2452f))
- Do not serialize tag slug manually - ([42747f1](https://github.com/roadiz/core-bundle-dev-app/commit/42747f1a957a13e1e555bc9e8ba53f03cdc752c5))

## [2.2.1](https://github.com/roadiz/core-bundle-dev-app/compare/v2.2.0...v2.2.1) - 2023-12-13

### Bug Fixes

- **(Api)** Added `AttributeValueQueryExtension` and `NodesTagsQueryExtension` to restrict tags and attributes linked to any published node. - ([cd2a017](https://github.com/roadiz/core-bundle-dev-app/commit/cd2a017e840ec5fed0efd5c0825bfcb3d09f6275))

## [2.2.0](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.51...v2.2.0) - 2023-12-12

### ⚠ Breaking changes

- Controller::get and Controller::has methods have been removed
- Requires PHP 8.1 minimum

### Features

- **(DtsGenerator)** Added `AllRoadizNodesSources` Typescript type - ([c249f37](https://github.com/roadiz/core-bundle-dev-app/commit/c249f375a9bf5a0e9d67bede61451dec40978cef))
- Improved `classicLikeComparison` for Node and NodesSources to search in attribute-values translations - ([d10dbbc](https://github.com/roadiz/core-bundle-dev-app/commit/d10dbbcc9cc0ac399a0e45b7a9200b7256feedf0))
-  [**breaking**]Upgraded to Symfony 6.4 LTS - ([0e37266](https://github.com/roadiz/core-bundle-dev-app/commit/0e37266d7ebb9f6b6a72d9e81714496a99fba8db))
-  [**breaking**]Require PHP 8.1 minimum - ([2fc0299](https://github.com/roadiz/core-bundle-dev-app/commit/2fc02997ac386c7de98b31bc38767271a9f77668))

## [2.1.51](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.50...v2.1.51) - 2023-11-20

### Bug Fixes

- Fixed missing entityManager flush to remove custom-form answer. - ([e9aa0ba](https://github.com/roadiz/core-bundle-dev-app/commit/e9aa0ba826244024426a0484a4e19a51008cbda2))

### Features

- Upgrade to ORM 2.17 and removed WITH joins on EAGER fetched relations - ([7aadd02](https://github.com/roadiz/core-bundle-dev-app/commit/7aadd028af6b37a8119f32b87283d10d1db1986d))

## [2.1.50](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.49...v2.1.50) - 2023-11-16

### Bug Fixes

- Prevent doctrine/orm to upgrade to 2.17.0: Associations with fetch-mode=EAGER may not be using WITH conditions - ([9ef566c](https://github.com/roadiz/core-bundle-dev-app/commit/9ef566cad69d256af5fbd37f20fbde9463e18a6f))
- Deprecated: Using ${var} in strings is deprecated, use {$var} instead - ([fae9914](https://github.com/roadiz/core-bundle-dev-app/commit/fae991419558849ac31a5e97b1a0d0d6ac74e859))

### Features

- Use php82 - ([e10fdd5](https://github.com/roadiz/core-bundle-dev-app/commit/e10fdd5632cb4327fcf598bfbdb32b272f9a4675))

## [2.1.49](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.48...v2.1.49) - 2023-10-20

### Bug Fixes

- Remaining merge conflict trace - ([518f940](https://github.com/roadiz/core-bundle-dev-app/commit/518f94099116a10dc9a0992809579ffafc0ebaf6))

### Features

- **(CustomForm)** Made custom-form answer email notification async. **Do not forget to define `framework.router.default_uri`.** - ([5e783c2](https://github.com/roadiz/core-bundle-dev-app/commit/5e783c21734a0c4b85f443ca844ddadc6d662049))

## [2.1.48](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.47...v2.1.48) - 2023-10-20

### Bug Fixes

- **(CustomForm)** Catch mailer transport exception when submitting custom-form answers - ([52487e3](https://github.com/roadiz/core-bundle-dev-app/commit/52487e3b1ef626a771f382d9eb5b25d75ea2d483))

### Features

- **(Document)** Added document image crop alignment control - ([51e2cdb](https://github.com/roadiz/core-bundle-dev-app/commit/51e2cdb7ea2144e91126222e242e1d1e6439ef02))
- **(Documents)** Added image crop alignment for processable documents. - ([970d4c6](https://github.com/roadiz/core-bundle-dev-app/commit/970d4c61046ef01ffb9243a9ec1b3e25e09df1ff))
- **(NodeType)** Added `attributable` node-type field to display or not nodes' attributes - ([9206664](https://github.com/roadiz/core-bundle-dev-app/commit/92066648f23ea46ea9198de18d0d26091edb36dc))

### Styling

- Moved and minified tree-add-btn - ([ab99bc6](https://github.com/roadiz/core-bundle-dev-app/commit/ab99bc6875432b3e4d474a497fcabede2076bb1e))
- Refactoring CSS vars, difference between primary and success colors - ([be5a594](https://github.com/roadiz/core-bundle-dev-app/commit/be5a5943a160f2ff95116580f77a63edf6503652))

## [2.1.47](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.46...v2.1.47) - 2023-10-09

### Bug Fixes

- **(Serializer)** Do not use `class-string` type-hint it breaks JSON serializer https://github.com/symfony/symfony/issues/47736 - ([9ffaf6e](https://github.com/roadiz/core-bundle-dev-app/commit/9ffaf6e550ac07afd9e5c9c5dba73129e3c76c3b))
- **(Solr)** Fixed fuzzy/proximity integer - ([7d72a02](https://github.com/roadiz/core-bundle-dev-app/commit/7d72a024dc61688c8c566d0d835c11240bb43e69))
- Append History state.headerData to url query-param is set - ([0a1e35c](https://github.com/roadiz/core-bundle-dev-app/commit/0a1e35cb803c72faa532d1a255338382bfc9730c))

### Features

- **(Documents)** Added inline download route to preview private PDF from backoffice. - ([0b607d2](https://github.com/roadiz/core-bundle-dev-app/commit/0b607d27ca955ee961c82d6edeb02969180b80a3))
- Configure API firewall as database-less JWT by default to ensure PreviewUser are not reloaded - ([51305e1](https://github.com/roadiz/core-bundle-dev-app/commit/51305e13b779162af68a576b16711acfbf5866b8))

## [2.1.46](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.45...v2.1.46) - 2023-09-28

### ⚠ Breaking changes

- Make sure your Solr managed-schema has a dynamic field named `*_ps` with type `location` multiValued:

```xml
<dynamicField name="*_ps" type="location" indexed="true" stored="true" multiValued="true"/>
```
- Solr search with hightlightings will output `highlighting` object with different labelled keys (`title_txt_en`, `collection_txt_en`) according to search `locale`.

### Bug Fixes

- **(EntityRepository)** Support `node.` order-by keys - ([be7c441](https://github.com/roadiz/core-bundle-dev-app/commit/be7c4413a4f292b792da4fa14d7f4d22e0dd9b24))
- **(Node)** Enforce UniqueEntity validation is used on all nodes and on UrlAlias when checking `nodeName` - ([5d7e8ae](https://github.com/roadiz/core-bundle-dev-app/commit/5d7e8aebfb91e92c0df944dbb95369ce3421095e))
- **(Rozier)** Call `off()` on undefined element - ([dd4324d](https://github.com/roadiz/core-bundle-dev-app/commit/dd4324df32e8648a7aa7967d66bf8190163be4cf))
- **(RozierBundle)** Add a second path to make RoadizRozier templates extendable with @!RoadizRozier namespace - ([9fcb20b](https://github.com/roadiz/core-bundle-dev-app/commit/9fcb20ba9cd209184f55fb0fc3e9901ef483f245))
- **(User)** Removed deprecated User salt column - ([1294167](https://github.com/roadiz/core-bundle-dev-app/commit/1294167edff39e7bedbbcbe4133f752d9b93db2b))
- Use multi-byte string methods - ([a9a5b84](https://github.com/roadiz/core-bundle-dev-app/commit/a9a5b8420492a4589fd2d88b9bc4434967e09498))
- Support reverseProxyCache host with or without protocol - ([9a170d5](https://github.com/roadiz/core-bundle-dev-app/commit/9a170d51e9ec7593bc45b71aaff4b3f5c922a2f6))

### Features

- **(AbstractAdminController)** Made AbstractAdminController getRepository method overridable - ([a46157d](https://github.com/roadiz/core-bundle-dev-app/commit/a46157dd518958c46d89a15d98100680f497fc00))
- **(AbstractAdminWithBulkController)** Made new bulkActions more easy to code, added User enable/disable bulk actions. - ([50651ea](https://github.com/roadiz/core-bundle-dev-app/commit/50651ea27960398f3373d77b9d670677951e6b93))
- **(AbstractAdminWithBulkController)** Made remove behaviour overridable. - ([a306faf](https://github.com/roadiz/core-bundle-dev-app/commit/a306fafab42408e2b3f25ef9ba36209807454596))
- **(AbstractAdminWithBulkController)** Added generic bulk-actions behaviour using `AbstractAdminWithBulkController` and `@RoadizRozier/admin/bulk_actions.html.twig` template - ([1495058](https://github.com/roadiz/core-bundle-dev-app/commit/14950586367e59606eafb057b1e2dfb22817833f))
- **(Node)** Added new `SEARCH` attribute for NodeVoter to allow non-editor to at least search nodes. - ([f713c0c](https://github.com/roadiz/core-bundle-dev-app/commit/f713c0c36b94eab1ee3a900e005920d5e2ab96d7))
- **(Solr)** Added `TreeWalkerIndexingEventSubscriber` to index node children using a TreeWalker, improved `AttributeValueIndexingSubscriber` - ([fea4ea0](https://github.com/roadiz/core-bundle-dev-app/commit/fea4ea02d4d9e7dc38ad8912a585f256c0e72bbc))
- **(Solr)** [**breaking**] Added multiple geojson point indexing - ([13041a2](https://github.com/roadiz/core-bundle-dev-app/commit/13041a2c775f3a6baf9659d328d574c21b8b4c6b))
- **(Solr)** Index integer, decimal, datetime, string and geojson fields - ([a62c29c](https://github.com/roadiz/core-bundle-dev-app/commit/a62c29c5157a58f3a09c9aa7c278e309d9531aff))
- **(Solr)** [**breaking**] Highlightings keep fields name to allow to display title with haighlighted parts, not only collection_txt - ([3ae0379](https://github.com/roadiz/core-bundle-dev-app/commit/3ae0379100940b69da32798395f117d6e51536bc))
- Added Custom forms bulk delete actions - ([25dd271](https://github.com/roadiz/core-bundle-dev-app/commit/25dd2712abfd10bbe22835a06545b4471ceee204))
- Added redirections bulk delete action - ([6652e67](https://github.com/roadiz/core-bundle-dev-app/commit/6652e67467c10af7de8f0894cb4d726577a71bd9))

### Refactor

- Send JSON form response after a fetch POST request - ([75bb3d3](https://github.com/roadiz/core-bundle-dev-app/commit/75bb3d3b0558c0f2af284e203c60c9e4f6d23df2))
- Replace all $.ajax and XHR request with window.fetch - ([0c6579e](https://github.com/roadiz/core-bundle-dev-app/commit/0c6579e0a02e62467e4a12ed65429e9412c68a89))

### Styling

- main-container height, and user-panel action button size - ([4f5f714](https://github.com/roadiz/core-bundle-dev-app/commit/4f5f7145908b97c9c3c1d59106aacd6707b2d460))
- Wrong colspan count - ([0efc2d2](https://github.com/roadiz/core-bundle-dev-app/commit/0efc2d238300dbdd20dc1f013ecf53fccec75499))
- user-image and uk-badge-mini - ([ba8236d](https://github.com/roadiz/core-bundle-dev-app/commit/ba8236d17691cc8f1a258a6593336e75ded9c50e))

## [2.1.45](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.44...v2.1.45) - 2023-09-13

### Bug Fixes

- **(EntityListManager)** Validate ordering field name before QueryBuilder rejects it - ([76cc4b4](https://github.com/roadiz/core-bundle-dev-app/commit/76cc4b427ab409d850198ae5da4091dad00a4f26))
- **(LeafletGeotagField)** Do not display a Marker if field is empty - ([9b6b27d](https://github.com/roadiz/core-bundle-dev-app/commit/9b6b27dfd3ca27e9d64be5ff2bb5711376f00352))
- Do not display children-nodes-quick-creation if no linked types - ([a826ec9](https://github.com/roadiz/core-bundle-dev-app/commit/a826ec9a3007979bca2b336d1e8938abd0c03eb8))

### Features

- Removed jQuery from LeafletGeotagField.js MultiLeafletGeotagField.js - ([6de56cf](https://github.com/roadiz/core-bundle-dev-app/commit/6de56cfafef2a2e8224729b4d5766e124f6d9482))

### Styling

- Better table style for horizontal ones - ([8c6246a](https://github.com/roadiz/core-bundle-dev-app/commit/8c6246a4e51b8129a5603f534db1d4dac5b6cd59))
- Fixed drawer nav using flexbox - ([10f772b](https://github.com/roadiz/core-bundle-dev-app/commit/10f772bd19930a148866e290d003a1fe9767ba35))
- Vanilla JS cleansing - ([37bf74b](https://github.com/roadiz/core-bundle-dev-app/commit/37bf74b65f2c01e19519b7317779e7e7905ed7d9))

## [2.1.44](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.43...v2.1.44) - 2023-09-07

### ⚠ Breaking changes

- `validateNodeAccessForRole` is deprecated, use `denyAccessUnlessGranted` or `isGranted` with one of `NodeVoter` attribute and a Node instance

### Bug Fixes

- **(Tag)** Added missing API search filter on tags relations - ([146ce90](https://github.com/roadiz/core-bundle-dev-app/commit/146ce9007ecb2464ee923e5b0c3d0c941439fb4d))

### Features

- **(Attributes)** Secure API collection and item attribute_values requests with realms - ([fd68f51](https://github.com/roadiz/core-bundle-dev-app/commit/fd68f5152f76e1ca69824db593501cb5b9c661f6))
- **(Attributes)** Added relation between Realms and Attributes and AttributeValue to secure attribute access - ([6f5f477](https://github.com/roadiz/core-bundle-dev-app/commit/6f5f477f94eaa53f4e88ec63e5844c0ce9bad2fa))
- **(Chroot)** Display node chroot on mainNodeTree instead of its children - ([b9e2f7a](https://github.com/roadiz/core-bundle-dev-app/commit/b9e2f7ad83c36e81b70e2cb31cecaea596ad21f3))
- **(NodeVoter)** [**breaking**] Added a NodeVoter and deprecated `validateNodeAccessForRole` method - ([f6de0ee](https://github.com/roadiz/core-bundle-dev-app/commit/f6de0ee867153d00f4d401b0bdcaf1f5c534ffa3))

### Styling

- Breadcrumb separator style - ([dbe4ec3](https://github.com/roadiz/core-bundle-dev-app/commit/dbe4ec3d491d67cc3dd7ecc2a106749fd3779aef))
- Removed Roadiz font and drop old browser postcss support - ([18186fa](https://github.com/roadiz/core-bundle-dev-app/commit/18186faacca8c64adb913c9cbb6764e3b9bb4f39))

## [2.1.43](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.42...v2.1.43) - 2023-09-04

### Bug Fixes

- **(UserSecurityType)** Missing CallbackTransformer on user chroot form normalization - ([0d9a1b2](https://github.com/roadiz/core-bundle-dev-app/commit/0d9a1b293a181b8952cfdd7e29c28ea157dccf06))

## [2.1.42](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.41...v2.1.42) - 2023-09-04

### Bug Fixes

- **(NodeNameChecker)** Limit generated unique nodeName to 250 chars, no matter suffix added to it - ([bb1e271](https://github.com/roadiz/core-bundle-dev-app/commit/bb1e27178cb524f74152f063e8d0105ed7df17ef))
- Duplicated translation keys - ([d108eec](https://github.com/roadiz/core-bundle-dev-app/commit/d108eeca37f2ee3eaf382f11e205d5d24032777a))
- Renamed NodeTypeField and CustomFormField defaultValues field label - ([957024b](https://github.com/roadiz/core-bundle-dev-app/commit/957024bd28517df295a38551ba760161181a2ceb))
- Fixed non-named inherited indexes - ([a43439c](https://github.com/roadiz/core-bundle-dev-app/commit/a43439c910e379afca8577a3f64b0188657ea3df))

### Features

- **(Attributes)** Added indexes on attributes values position, fallback value on default translation during Normalization - ([453708f](https://github.com/roadiz/core-bundle-dev-app/commit/453708f1e63e6d8488154dc6deb4f9be9c91060a))
- Displays parent node listing sort options if parent is a stack (hiding children) - ([48d06ce](https://github.com/roadiz/core-bundle-dev-app/commit/48d06ce9beb62e6d448fb02a7e1fb5d225a4ba9a))
- Use RangeFilter on Node' position - ([1423bf2](https://github.com/roadiz/core-bundle-dev-app/commit/1423bf245db02de23e6963bb1a8b101c7e948988))
- Added NodesSources getListingSortOptions method - ([26ff61f](https://github.com/roadiz/core-bundle-dev-app/commit/26ff61f60c8d608d02588e4ea48835786e613714))
- Added NumericFilter on node.position, added new `node_listing` serialization group - ([a73b5b2](https://github.com/roadiz/core-bundle-dev-app/commit/a73b5b20cf632dc990668916bcdc515d802079c9))

## [2.1.41](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.40...v2.1.41) - 2023-08-04

### Bug Fixes

- Do not require explicitly doctrine/dbal since issue came from doctrine/bundle - ([93321bb](https://github.com/roadiz/core-bundle-dev-app/commit/93321bb2e8ab83cb317aca9cce294f54489ca390))

## [2.1.40](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.39...v2.1.40) - 2023-08-04

### Bug Fixes

- **(NodesTags)** Added a single UUID field to prevent primary key overlap. - ([68fff41](https://github.com/roadiz/core-bundle-dev-app/commit/68fff41c9219e7e4c4b4254ecdfc1c3d119ca914))

### Features

- **(TreeWalker)** Added new tag `roadiz_core.tree_walker_definition_factory` to inject TreeWalker definitions into TreeWalkerGenerator and any BlocksAwareWebResponseOutputDataTransformerTrait - ([86e5ac6](https://github.com/roadiz/core-bundle-dev-app/commit/86e5ac6cc48662e54e69c7a1b90c4294580478f3))

## [2.1.39](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.38...v2.1.39) - 2023-08-03

### Bug Fixes

- **(Doctrine)** Do not extend `Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository` anymore due to LazyServiceEntityRepository starting `doctrine/doctrine-bundle@2.8.1` - ([ec1687c](https://github.com/roadiz/core-bundle-dev-app/commit/ec1687cc27436b08ff0ec5f73d1adb04fb23e424))

## [2.1.38](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.37...v2.1.38) - 2023-08-03

### Bug Fixes

- doctrine/dbal >=3.4.0 broke queries on inheriting class using fields from child classes. Still using NodesSources when querying on child entities and their custom fields. - ([e7d5dbe](https://github.com/roadiz/core-bundle-dev-app/commit/e7d5dbe809bc447affe2a9a811763670c90b2216))

## [2.1.37](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.36...v2.1.37) - 2023-08-03

### Features

- **(Security)** Added UserChecker to check users enabled, expired, locked or credentialExpired. Removed useless User' boolean expired and credentialsExpired fields. - ([42d4d11](https://github.com/roadiz/core-bundle-dev-app/commit/42d4d1133916ea1101872665cd3c13d2ea18175f))

## [2.1.36](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.35...v2.1.36) - 2023-08-03

### Bug Fixes

- Gathered Mysql and Postgresql database initialization in the same Migration to avoid always skipping RZ\Roadiz\Migrations\Version20201225181256 - ([af73bf4](https://github.com/roadiz/core-bundle-dev-app/commit/af73bf499c010fbc12882debcde550dcace9d7ae))

### Features

- **(OpenID)** Added new OpenID mode with `roadiz_rozier.open_id.requires_local_user` (default: true) which requires an existing Roadiz account before authenticating SSO users. - ([639e1a5](https://github.com/roadiz/core-bundle-dev-app/commit/639e1a5a90ec6d50078087db5d839ef8997da7de))

## [2.1.35](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.34...v2.1.35) - 2023-08-01

### Bug Fixes

- **(WebResponseOutputDataTransformer)** Made `WebResponseOutputDataTransformer` overrideable in projects instead of reimplementing it. - ([52fd409](https://github.com/roadiz/core-bundle-dev-app/commit/52fd4096343aa4b68d0907375a53eefc64dc3fbc))

## [2.1.34](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.33...v2.1.34) - 2023-07-31

### Bug Fixes

- **(AjaxNodeTreeController)** Fixed non-integer translationId - ([e33762f](https://github.com/roadiz/core-bundle-dev-app/commit/e33762f4931c8537425e1cb3b7995a48f87834ba))

## [2.1.33](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.32...v2.1.33) - 2023-07-31

### ⚠ Breaking changes

- `bin/console themes:migrate` command do not execute Doctrine migrations, generate NS entities and schema update. **You must version your NS\*\*\*\*.php files and migrations** to sync your app in different environments.

### Bug Fixes

- **(AjaxNodeTree)** Removed `translationId` path param from `nodesTreeAjax` route - ([d5e6fe6](https://github.com/roadiz/core-bundle-dev-app/commit/d5e6fe6333b2dc5e9aff372965adc9defb58af13))

### Features

- **(NodeType)** [**breaking**] Roadiz now generates a new Doctrine Migration for each updates on node-types and node-types fields. - ([3e1b8bb](https://github.com/roadiz/core-bundle-dev-app/commit/3e1b8bb2971be50e263f909cdc3b42a4c50f3e6d))
- **(NodeType)** NodeTypeImporter now removes extra fields from database when not present on .json files - ([c59919e](https://github.com/roadiz/core-bundle-dev-app/commit/c59919ea3183a197f895c63182d0057033960837))
- Added more OpenApi context to generated api resources - ([29c8fa3](https://github.com/roadiz/core-bundle-dev-app/commit/29c8fa310bae8b35c850435a947282335628bad5))

## [2.1.32](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.31...v2.1.32) - 2023-07-25

### Bug Fixes

- **(ApiResourceGenerator)** Do not wrap *boolean* value in quotes - ([90630c1](https://github.com/roadiz/core-bundle-dev-app/commit/90630c1cb8ac26839ef8e6cccd8a0c35ff03c0e6))

## [2.1.31](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.30...v2.1.31) - 2023-07-25

### Bug Fixes

- **(CoreBundle)** Fixed doctrine/dbal version - ([3087a38](https://github.com/roadiz/core-bundle-dev-app/commit/3087a389c3cba3e7f108623cf30c16e78e7b7fd2))

### Features

- Use `ramsey/uuid` for Webhook ID generation, doctrine/dbal removed AutoGenerated uuids - ([55f80fc](https://github.com/roadiz/core-bundle-dev-app/commit/55f80fcd25e8d76f65b56e84be25440cdf8a4a68))

## [2.1.30](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.29...v2.1.30) - 2023-07-25

### Bug Fixes

- **(CoreBundle)** Fixed doctrine/dbal dependency to 2.x due to UUID removal. Missing constraint in core-bundle. - ([9824f1f](https://github.com/roadiz/core-bundle-dev-app/commit/9824f1fd96483b573d960093bf9d012a89d9120a))

### Features

- Allowed `doctrine/dbal` 3.x - ([ba47367](https://github.com/roadiz/core-bundle-dev-app/commit/ba4736750a3f364a0b08558e5672a162f5cc8927))

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

- Fixed doctrine/dbal dependency to 2.x due to UUID removal - ([d426160](https://github.com/roadiz/core-bundle-dev-app/commit/d426160ccef31ca2ab1619fdc1654d78942b4875))

### Features

- **(ApiPlatform)** [**breaking**] Disabled `metadata_backward_compatibility_layer` and migrated all API resources, filters, extensions to new `Operation` syntax - ([f0317aa](https://github.com/roadiz/core-bundle-dev-app/commit/f0317aab253ad75011534c857e8bf9c7a70635cc))
- **(UserBundle)** Moved `/users/me` operation to `/me` to avoid conflict on User Get operation and IRI generation - ([883146e](https://github.com/roadiz/core-bundle-dev-app/commit/883146e7839d19a9a072e361b9b7baf22ae77381))
- Migrated api-platform deprecated http_cache configuration - ([4177d05](https://github.com/roadiz/core-bundle-dev-app/commit/4177d05ae2b83c996b441bd126657e3a89a4bd1e))
- Use `ramsey/uuid` for Webhook ID generation, doctrine/dbal removed AutoGenerated uuids - ([3b409d3](https://github.com/roadiz/core-bundle-dev-app/commit/3b409d39aa699d5ea22fd2b62a587548aa5e693c))
- Upgrade to rezozero/liform ^0.19 - ([d4862d6](https://github.com/roadiz/core-bundle-dev-app/commit/d4862d6cac923f52d0b43cced1cbe0da48880909))

## [2.1.28](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.27...v2.1.28) - 2023-07-19

### ⚠ Breaking changes

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
- RZ\Roadiz\CoreBundle\Entity\LoginAttempt has been removed with its manager and utils

### Bug Fixes

- **(Search)** Fixed node advanced search using `__node__` prefix - ([bcb7d5d](https://github.com/roadiz/core-bundle-dev-app/commit/bcb7d5d9ad6df9e4c580bf4382dd34824ff3e7ea))

### Features

-  [**breaking**]Moved `Log` entity to a different namespace to handle with a different entity-manager and avoid flushing Log in the same transaction with other entities - ([6d2583c](https://github.com/roadiz/core-bundle-dev-app/commit/6d2583c8b6b420d743e940a50d9c1811c13392b8))
- Removed dead code `ExceptionViewer` and redondant Logger levels constants - ([f5287f6](https://github.com/roadiz/core-bundle-dev-app/commit/f5287f65e7b08a5334de6f7f8ee1ac56b814b0e2))
-  [**breaking**]Removed deprecated LoginAttempt entity, using default Symfony login throtling - ([f7e2a39](https://github.com/roadiz/core-bundle-dev-app/commit/f7e2a39bb06bf45adf9708b031889a88804e5191))

## [2.1.27](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.26...v2.1.27) - 2023-07-12

### Bug Fixes

- Missing `isTransactional` on last migration - ([5bf8833](https://github.com/roadiz/core-bundle-dev-app/commit/5bf8833cc4c7b7bd4c49a443bbb43bf99055f2ef))

## [2.1.26](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.25...v2.1.26) - 2023-07-12

### Bug Fixes

- Fixed Loggable behaviour, removed relationship between UserLogEntry and User for future entity-manager separation. - ([96d180b](https://github.com/roadiz/core-bundle-dev-app/commit/96d180b415f8e6cc379293e0e07aca8bccf2cbd3))

## [2.1.25](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.24...v2.1.25) - 2023-07-12

### Bug Fixes

- Do not display document thumbnail controls for audio, video - ([57bbc85](https://github.com/roadiz/core-bundle-dev-app/commit/57bbc85cecda42fba08900e71faa2958b74f8d81))
- Do not generate log URL when no entityId is set in log - ([997ee20](https://github.com/roadiz/core-bundle-dev-app/commit/997ee2093b1754967fc9552443f28197655c0308))

## [2.1.24](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.23...v2.1.24) - 2023-07-06

### Bug Fixes

- **(Search engine)** Do not add quotes if multi word exact query, Solr Helper already does it - ([b0aa80a](https://github.com/roadiz/core-bundle-dev-app/commit/b0aa80a139c576b5729cb8d049f9bc3e38d64f70))

## [2.1.23](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.22...v2.1.23) - 2023-07-02

### Bug Fixes

- Use FilterValidationException instead of InvalidArgumentException to generate 400 and no 500 errors - ([ca89b73](https://github.com/roadiz/core-bundle-dev-app/commit/ca89b733859a77460081f37a1d1265f7d128ae57))

## [2.1.22](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.21...v2.1.22) - 2023-06-30

### ⚠ Breaking changes

- Log entity requires a Migration that may be destructive on `log` table.

### Bug Fixes

- Do not handle node add attribute form when no attribute exists - ([db77e62](https://github.com/roadiz/core-bundle-dev-app/commit/db77e62e778495b2f87eaed35022770064c5bd3c))

### Features

- **(Log)** Made Log entity autonomous without any relationship, only loose references - ([e0dd99c](https://github.com/roadiz/core-bundle-dev-app/commit/e0dd99cffa05918ebde1c8744cfe582eaef64520))
- **(Log)** Added Twig Log extension to generate link to entities edit pages - ([471a571](https://github.com/roadiz/core-bundle-dev-app/commit/471a571144ad7a3725b0cfd1137c2968cfeccffc))
- **(Log)** [**breaking**] Added entityClass and entityId to remove hard relationship with NodesSources - ([7300cc3](https://github.com/roadiz/core-bundle-dev-app/commit/7300cc3a1d7359a996b3d3925e21a046e2437057))

## [2.1.21](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.20...v2.1.21) - 2023-06-28

### ⚠ Breaking changes

- Roadiz/models namespace root is now ./src. Change your Doctrine entities path

### Bug Fixes

- **(OpenID)** Do not register `roadiz_rozier.open_id.discovery` if `discovery_url` is not valid - ([120b6a9](https://github.com/roadiz/core-bundle-dev-app/commit/120b6a999b6635d120ce5c7ee7225b61328692b1))
- CoreBundle must not reference other bundles classes. - ([701cbf3](https://github.com/roadiz/core-bundle-dev-app/commit/701cbf3c0a7e5218032ccb3d05a837149dfbf31f))
- Fixed monorepo phpcs config standard - ([b3a1ac0](https://github.com/roadiz/core-bundle-dev-app/commit/b3a1ac0067ff61e76bf6125cfd3974b1c622cf74))
- Reached phpstan level 7 validation - ([d5c0bdc](https://github.com/roadiz/core-bundle-dev-app/commit/d5c0bdc9572d9cd95691f7b0782d705312abd2c9))

### Features

- **(ListManager)** Refactored and factorized request query-params handling - ([a6188c6](https://github.com/roadiz/core-bundle-dev-app/commit/a6188c6bea8fab24e1b5788ed69f10633a840275))
- **(TwoFactorBundle)** Added console command to disable 2FA and override users:list command - ([81cd472](https://github.com/roadiz/core-bundle-dev-app/commit/81cd4720dea1c545741147ecca7e5e80723796db))
- Added Example PageController and more phpstan fixes - ([db8cec8](https://github.com/roadiz/core-bundle-dev-app/commit/db8cec819f09718d4774cb6b93decabb5184a5dc))
-  [**breaking**]Moved Models namespace to src - ([fe0960f](https://github.com/roadiz/core-bundle-dev-app/commit/fe0960fe3a076a9618e4ea3ae67624f37bb33cb9))

## [2.1.20](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.19...v2.1.20) - 2023-06-23

### Bug Fixes

- **(Preview)** Check strict `_preview` param value (allowing `0`, `false` values to disable preview mode) - ([70b60c9](https://github.com/roadiz/core-bundle-dev-app/commit/70b60c972ed13fa1819ef3611521e9a2f6fbf459))

### Features

- **(Preview)** Added OpenAPI decorator to document `_preview` query param and JWT security - ([449c9f9](https://github.com/roadiz/core-bundle-dev-app/commit/449c9f9fcdb2114e6c13804be696d558ac7efc49))

## [2.1.19](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.18...v2.1.19) - 2023-06-23

### Bug Fixes

- **(OpenApi)** Fixed `getByPath` operation overlap with `get` by setting `id` request attribute and api_resource operation configuration - ([54a378d](https://github.com/roadiz/core-bundle-dev-app/commit/54a378d151409ef6ec8fb7cfea6b9c74e5115d44))

## [2.1.18](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.17...v2.1.18) - 2023-06-23

### Bug Fixes

- Fixed and refactored SQL search query building on Repository and Paginator levels - ([b5d320b](https://github.com/roadiz/core-bundle-dev-app/commit/b5d320b49f28ff167e6a4482090fc743bd46c186))

## [2.1.17](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.16...v2.1.17) - 2023-06-20

### Bug Fixes

- **(UI)** Changed user and menu panels size for LG breakpoint. Clear `cache.global_clearer` in CacheController and SchemaUpdater. - ([c20463e](https://github.com/roadiz/core-bundle-dev-app/commit/c20463eb2e14b8c1977a6eabe12a9380a816f37b))
- UpdateNodeTypeSchemaMessage should be handled synced to avoid Doctrine exception on refresh - ([84a611b](https://github.com/roadiz/core-bundle-dev-app/commit/84a611b17837c58d219c6f822c132b034b4420af))

### Features

- **(Redirections)** Added redirection look-up cache and CRUD events - ([a78b3d3](https://github.com/roadiz/core-bundle-dev-app/commit/a78b3d3e3a6353a5cb0c6e83df39d9fd2afb8cf6))
- **(Redirections)** Added cache adapter to look for redirection query - ([18da77c](https://github.com/roadiz/core-bundle-dev-app/commit/18da77cbb0736b6bc7424f938430f2bc3571ca00))
- Added Roadiz custom data-collector for web-profiler - ([d0e01fa](https://github.com/roadiz/core-bundle-dev-app/commit/d0e01fa885b462a8dc46fb7ef04892c840452a7e))
- Added Redirections usage count to better analyze your app routes - ([3a6a38b](https://github.com/roadiz/core-bundle-dev-app/commit/3a6a38bcaf90dbb2fe2fe36985121132018fa1ff))
- Added all Doctrine string fields explicit length and validation assert. - ([8c1d230](https://github.com/roadiz/core-bundle-dev-app/commit/8c1d23005ae79916aecdc03510e0403f123474b8))

### Refactor

- Removed dead classes - ([f9c1c8b](https://github.com/roadiz/core-bundle-dev-app/commit/f9c1c8bbfd20be1f4320085f0e05800fd2ad6963))
- Deprecated `Controller` methods to get services - ([d8d351b](https://github.com/roadiz/core-bundle-dev-app/commit/d8d351bd136a76311e4eb7f094d7262e631980e9))

## [2.1.16](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.15...v2.1.16) - 2023-06-01

### Bug Fixes

- **(LeafInterface)** Do not test exact class when setting LeafInterface parent to allow doctrine proxies. - ([56ed76d](https://github.com/roadiz/core-bundle-dev-app/commit/56ed76d401fee87813e0cbb86ac92ec130a46752))
- Do not prevent setting parent with not the same class - ([3b5996d](https://github.com/roadiz/core-bundle-dev-app/commit/3b5996dc804510bed29b5f20ef542e933f101561))

## [2.1.15](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.14...v2.1.15) - 2023-05-24

### Bug Fixes

- Allow all `gedmo/doctrine-extensions` v3 - ([0ed814b](https://github.com/roadiz/core-bundle-dev-app/commit/0ed814b1b347c6d4989acbedbb05636964687908))

## [2.1.14](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.13...v2.1.14) - 2023-05-19

### Bug Fixes

- Fixed Collection type hinting and co-variance - ([2a53d81](https://github.com/roadiz/core-bundle-dev-app/commit/2a53d81f8218e3ca584e420568e0d0c9031ac681))

## [2.1.13](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.12...v2.1.13) - 2023-05-19

### Bug Fixes

- Fixed `Collection<int, classname>` type hinting - ([3a670bb](https://github.com/roadiz/core-bundle-dev-app/commit/3a670bb093afddb4591fdd36a432afaee3d015a4))
- Fixed CustomFormAnswer class renaming `getAnswers()` to `getAnswerFields()` - ([d88a179](https://github.com/roadiz/core-bundle-dev-app/commit/d88a1795a14cbf1f58dd983199a7eed343aba6fe))

## [2.1.12](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.11...v2.1.12) - 2023-05-11

### Bug Fixes

- Add PreviewResolverInterface into NodeSourceWalkerContext to alter TreeWalker definition against preview status - ([463be2e](https://github.com/roadiz/core-bundle-dev-app/commit/463be2e43924d87f4a5b3a2ecda63ed0442b11c3))

## [2.1.11](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.10...v2.1.11) - 2023-05-05

### Bug Fixes

- **(Document)** Ignore thumbnail documents from Explorer - ([d236f1a](https://github.com/roadiz/core-bundle-dev-app/commit/d236f1af68a9a99452024748ca3c5e250800414e))
- **(Solr)** Added a new wildcardQuery to search and autocomplete at the same time - ([37746af](https://github.com/roadiz/core-bundle-dev-app/commit/37746af717f7b504d761ef7fff377f7bfc36aaad))

## [2.1.10](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.9...v2.1.10) - 2023-05-05

### Bug Fixes

- **(Rozier)** Fixed global backoffice grid when tree panel is hidden - ([4aa8a5e](https://github.com/roadiz/core-bundle-dev-app/commit/4aa8a5ec210e63f9b2835bb293000a0a11b53e76))

### Features

- **(Document)** Added DocumentPdfMessageHandler to generate thumbnail for PDF documents. - ([35241bc](https://github.com/roadiz/core-bundle-dev-app/commit/35241bcc88e17131a448f5a7f65388233a8e3d87))
- **(Rozier)** Added UserActionsMenuEvent to customize backoffice user panel menu from other bundles - ([ee5e2a6](https://github.com/roadiz/core-bundle-dev-app/commit/ee5e2a66a140ed6507dc51f88f00a50066028f80))
- **(SearchEngine)** Added `DocumentSearchQueryEvent` and `NodeSourceSearchQueryEvent` event to alter Solr select queries - ([083d2e5](https://github.com/roadiz/core-bundle-dev-app/commit/083d2e5294ff31734cd8ca78258275a8adfba6c7))
- **(TwoFactorBundle)** Added github actions - ([2bc480a](https://github.com/roadiz/core-bundle-dev-app/commit/2bc480a9d16546e681d56557cc69dd790f4c8991))
- **(TwoFactorBundle)** Added backup codes and backoffice templates - ([95db653](https://github.com/roadiz/core-bundle-dev-app/commit/95db653efaac1202268b458515967d12d179d841))
- **(TwoFactorBundle)** Added TwoFactor bundle to login to Roadiz with TOTP application - ([0953b00](https://github.com/roadiz/core-bundle-dev-app/commit/0953b0086452343d40adce79ebba65089a0090bd))
- **(UserViewer)** Removed setUser setter for single method usage - ([b7c0f75](https://github.com/roadiz/core-bundle-dev-app/commit/b7c0f75757410c6addd5e4ec7b8f8247cf339319))
- Added solarium webprofiler panel - ([2cea745](https://github.com/roadiz/core-bundle-dev-app/commit/2cea74513e4153bcdc2c8a36cc22adf4c11a77fe))

## [2.1.9](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.8...v2.1.9) - 2023-04-11

### Bug Fixes

- JoinDataTransformer must always transform to an array, even single objects - ([e17b804](https://github.com/roadiz/core-bundle-dev-app/commit/e17b804baf73ca9d827b07322a0163a952b3e5c0))

## [2.1.8](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.7...v2.1.8) - 2023-04-06

### Bug Fixes

- **(PreviewBarSubscriber)** Test if Response content is string before searching </body> tag - ([93d1897](https://github.com/roadiz/core-bundle-dev-app/commit/93d18970ba17c903c2a2feea9e787d166b5f6034))

## [2.1.7](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.6...v2.1.7) - 2023-04-04

### Bug Fixes

- **(Documents)** Prevent renaming document filename if new pathname is not available - ([13982cc](https://github.com/roadiz/core-bundle-dev-app/commit/13982cce13b1876d6f55167a40abcb456cd1e64f))
- **(EntityGenerator)** Nullable `$field->getDefaultValues()` - ([297f099](https://github.com/roadiz/core-bundle-dev-app/commit/297f099cc738272c94e88f58b060b39351e655ed))

## [2.1.6](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.5...v2.1.6) - 2023-03-23

### Bug Fixes

- Fixed AjaxNodesExplorerController search params strict typing. - ([fd38552](https://github.com/roadiz/core-bundle-dev-app/commit/fd385525f8f0ee4303cc77714c3d4207301f44a2))

### Features

- **(EntityGenerator)** Entity generator uses DefaultValuesResolverInterface to compute required ENUM fields length acording to their default values. - ([20263b6](https://github.com/roadiz/core-bundle-dev-app/commit/20263b613d57f5e918f1b0f75de9835714259250))

## [2.1.5](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.4...v2.1.5) - 2023-03-17

### Features

- Added onlyVisible optional param for TreeWalker definitions - ([abf93a8](https://github.com/roadiz/core-bundle-dev-app/commit/abf93a8866629e095fd4302d05a46443158d8b81))
- Migrate from monolithic docker image to nginx + app + worker + cron containers - ([fa8b76a](https://github.com/roadiz/core-bundle-dev-app/commit/fa8b76a6216b93bdce93716d071131c138310921))

## [2.1.4](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.3...v2.1.4) - 2023-03-15

### Bug Fixes

- **(EntityGenerator)** Fixed attributes list generator when there is only one attribute to print - ([b6139ba](https://github.com/roadiz/core-bundle-dev-app/commit/b6139ba0f339962b15d0247ab94d1aa856ec8123))

## [2.1.3](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.2...v2.1.3) - 2023-03-14

### Bug Fixes

- Fixed SolrPaginator `getLastPage` using an index starting at 1 instead of 0 - ([052f9be](https://github.com/roadiz/core-bundle-dev-app/commit/052f9be0b467bb5ea6031ef31763ec5005f63c5e))

## [2.1.2](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.1...v2.1.2) - 2023-03-14

### Bug Fixes

- Fix SolrPaginator last-page when there are no results - ([a044446](https://github.com/roadiz/core-bundle-dev-app/commit/a04444698d7b4a96a538419f840928f190054573))

### Features

- Create overrideable `createSearchResultsFromResponse` method for any AbstractSearchHandler extending class - ([b23b92f](https://github.com/roadiz/core-bundle-dev-app/commit/b23b92f9504a2130fac3455cd79d7f116fa7deba))

## [2.1.1](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.0...v2.1.1) - 2023-03-13

### ⚠ Breaking changes

- All Solr documents MUST have a idempotent ID field to ease up Solr indexing (no more need to delete previous index entry before indexing an entity).

### Features

-  [**breaking**]Rename getCompositeIdentifier to getIdempotentIdentifier and provided a fallback. - ([e8c895a](https://github.com/roadiz/core-bundle-dev-app/commit/e8c895a056dee0f2668ed8d081a2021a45490174))
- Added NodesSourcesHeadFactoryInterface for better WebResponse and CommonContent responses maintainability. - ([ed05a24](https://github.com/roadiz/core-bundle-dev-app/commit/ed05a24947da4caa5533b37190c480b0b5358bd5))
- Updated Solr indexing tags fields for multivalued strings and use composite ID for easy overriding - ([50a04af](https://github.com/roadiz/core-bundle-dev-app/commit/50a04afc913eb1a7b67cd550fc39305598c4db19))

## [2.1.0](https://github.com/roadiz/core-bundle-dev-app/compare/v2.3.1...v2.1.0) - 2023-03-06

### Bug Fixes

- **(api-platform)** Revert to api-platform/core 2.6 - ([0f3f429](https://github.com/roadiz/core-bundle-dev-app/commit/0f3f4291691dd1a40593045d51b3d5ea4f00927c))
- Missing openid package from rozier-bundle - ([2f63197](https://github.com/roadiz/core-bundle-dev-app/commit/2f63197e25ef3a4cc0efbfd67ad6cbf6d4606d8e))
- Fixed doctrine resolved entities - ([e2b038a](https://github.com/roadiz/core-bundle-dev-app/commit/e2b038aca52ead8b67cf7bab35212cdf602807e5))
- Fixed mappings App\GeneratedEntity\NSPage#usersProxy and App\Entity\PositionedPageUser#nodeSource are inconsistent with each other - ([0b8d980](https://github.com/roadiz/core-bundle-dev-app/commit/0b8d9805fa61d8559f08db1c18a9460c12836965))
- php image supervisor configuration - ([63977d2](https://github.com/roadiz/core-bundle-dev-app/commit/63977d224aaebea8c0a6fec057aca00365adb12f))

### CI/CD

- Fix newlines for multiline run commands - ([387a93c](https://github.com/roadiz/core-bundle-dev-app/commit/387a93c33bf5a5cba190a401fb1e35a5db78cda6))
- Use org secret ROADIZ_SPLIT_ACCESS_TOKEN - ([4816fb6](https://github.com/roadiz/core-bundle-dev-app/commit/4816fb630b1f0090a03ccec6cd353686a82a60ae))
- Force GITHUB_TOKEN (https://docs.github.com/en/actions/security-guides/automatic-token-authentication) - ([d0d25cd](https://github.com/roadiz/core-bundle-dev-app/commit/d0d25cd502d061155c6ff90419866c892fc12aa1))
- Invalid workflow - ([4dbc1f7](https://github.com/roadiz/core-bundle-dev-app/commit/4dbc1f7d20e63cf162bcf6fd4d400c6ca49e90b3))
- try to use auto GITHUB_TOKEN from github actions - ([e8c32fb](https://github.com/roadiz/core-bundle-dev-app/commit/e8c32fb61b2be97afc21a93cfcfa95eb7a892f18))
- Define default_branch for all packages - ([645a91b](https://github.com/roadiz/core-bundle-dev-app/commit/645a91bc8a30f0abf2bd71f06eb47cdc69bd9739))
- Do not rename GITHUB_TOKEN - ([aa93d6a](https://github.com/roadiz/core-bundle-dev-app/commit/aa93d6a789feeef45ad83c25c99f4db4b6f1d441))
- Changed username for github Actions - ([a558dbd](https://github.com/roadiz/core-bundle-dev-app/commit/a558dbdfc816d88331abec1e85156ac4ac3b0e60))

### Features

- **(Attributes)** Migration to attributes - ([21a28c8](https://github.com/roadiz/core-bundle-dev-app/commit/21a28c8ca44e374af799ff64f2d7046aa31c0c1a))
- **(Documents)** Moved private documents to a dedicated listing - ([6433842](https://github.com/roadiz/core-bundle-dev-app/commit/643384268add7d436f5f2813846a4f0f306a5ae7))
- Added sub-package code - ([9898e76](https://github.com/roadiz/core-bundle-dev-app/commit/9898e76a80327d6d0978551218f6e7bdfe47be2c))
- Moving all subpackages into monorepo, added Github Actions for splitting repositories - ([3e3f0f0](https://github.com/roadiz/core-bundle-dev-app/commit/3e3f0f090551286f59965f1469638552d702a217))
- Added all roadiz/* sub packages to lib/ folder in order to create monorepo - ([854c74d](https://github.com/roadiz/core-bundle-dev-app/commit/854c74d81632d46c37dcf4d4ba5ce0b33ab084ae))
- Dotenv for API name and description - ([0184f91](https://github.com/roadiz/core-bundle-dev-app/commit/0184f91b11751a8c7f1e3f739a1bd10e60c6c5fe))
- Added MenuLinkPathNormalizer - ([800c5c6](https://github.com/roadiz/core-bundle-dev-app/commit/800c5c6a8e91d05f3c15ec2bac54ca200244a61b))
- Added Menu and auto treeWalker generator - ([2bad3a9](https://github.com/roadiz/core-bundle-dev-app/commit/2bad3a95ea436a297910091a31816af3cec46717))
- Added flysystem font.storage - ([f639f4a](https://github.com/roadiz/core-bundle-dev-app/commit/f639f4aaea464ae74b4bf203f8fa06d101845b7b))
- Big roadiz/documents namespace refactoring - ([02dfbb5](https://github.com/roadiz/core-bundle-dev-app/commit/02dfbb547f8b5061ec09f606a4a529f69cc5a90c))
- Clone roadiz/documents lib directly in lib folder for better development process - ([85a9d93](https://github.com/roadiz/core-bundle-dev-app/commit/85a9d931a3e8deee9c455c293ff4f84c514c4b80))
- Added flysystem dependency for InterventionRequest - ([09769ca](https://github.com/roadiz/core-bundle-dev-app/commit/09769ca21fd32017164c3d3f4ed4c67798846b61))
- Added roadiz/models lib into single project structure - ([8697f7b](https://github.com/roadiz/core-bundle-dev-app/commit/8697f7b01f76f3e76f8125024c848165a6965073))
- Node types - ([6f4addc](https://github.com/roadiz/core-bundle-dev-app/commit/6f4addc0863b799f18529c60bc04db0f10753f4c))
- New RoadizFontBundle to extract domain logic from Core and make it optional - ([377384a](https://github.com/roadiz/core-bundle-dev-app/commit/377384a27b2493cf4b3444dbcdc848823c5cf1fe))
- Upgraded to API platform 2.7 and PHP 8.0 minimum - ([f56b655](https://github.com/roadiz/core-bundle-dev-app/commit/f56b65573fe7ead12812260f95c17a40d510b859))
- Added ffmpeg to docker image and populate DotEnv var - ([601b137](https://github.com/roadiz/core-bundle-dev-app/commit/601b137f87b98d785d7332c5d106d54e64762a9f))
- Added new user proxied reference field to Page node-type - ([f44edae](https://github.com/roadiz/core-bundle-dev-app/commit/f44edae8347b206c04a9121b8f0194a95a93d470))
- Use definitive archives endpoint configuration - ([a894b5a](https://github.com/roadiz/core-bundle-dev-app/commit/a894b5afa5c46c292396774f6c0c5138ebedec84))
- Moved open_id configuration from core to rozier bundle - ([7b3172c](https://github.com/roadiz/core-bundle-dev-app/commit/7b3172c29da5b45006b756ece44e941099e7e3a5))
- Added default built-in search - ([4c2d5dc](https://github.com/roadiz/core-bundle-dev-app/commit/4c2d5dcddb3274208ef58ae60e7900486f73d693))
- added nsarticle archive operation - ([a73f0bb](https://github.com/roadiz/core-bundle-dev-app/commit/a73f0bb0c0ec9cd6c3af7a28cb6d9e322661870b))
- Requires rollerworks/password-common-list - ([3b9b67c](https://github.com/roadiz/core-bundle-dev-app/commit/3b9b67c2abc1e9afd349d7c385eb3eb15467fcac))
- Use built-in symfony login throttling - ([75234a1](https://github.com/roadiz/core-bundle-dev-app/commit/75234a173dba0c2d3deb30f6660f6652f5140888))
- UserValidationRequest configuration - ([400466e](https://github.com/roadiz/core-bundle-dev-app/commit/400466ef0b7d5ebf66679bb9f5285c316902ed20))
- UserBundle configuration - ([09a670f](https://github.com/roadiz/core-bundle-dev-app/commit/09a670f7a785b082ca562780f15890a3197090b7))
- Added RoadizUserBundle configuration - ([37dbc5a](https://github.com/roadiz/core-bundle-dev-app/commit/37dbc5af2c46626b24dc4a9fb4a13f1039c67f14))
- Register RoadizUserBundle - ([5bba2bb](https://github.com/roadiz/core-bundle-dev-app/commit/5bba2bbc12f10f682b878486aeec66f6f989a71b))
- UserBundle config start - ([498fd5d](https://github.com/roadiz/core-bundle-dev-app/commit/498fd5d1a580a55ac39cd74bb715256b1cf57954))
- changed document menu - ([83e1ec1](https://github.com/roadiz/core-bundle-dev-app/commit/83e1ec1fb1959f129aa841bd885a274d86ad9b22))
- add healthcheck config and traefik - ([3676d2b](https://github.com/roadiz/core-bundle-dev-app/commit/3676d2b3db01548262a634f69a9ba54231755003))
- Added realms admin section - ([571181c](https://github.com/roadiz/core-bundle-dev-app/commit/571181c19e06e35659226e3368075de18fbc1b1a))
- Add php8.1 - ([3e4f9c7](https://github.com/roadiz/core-bundle-dev-app/commit/3e4f9c76d28bf2df23ec50eb2290459cae09e9e9))
- WebResponse as an API Resource - ([fd41c30](https://github.com/roadiz/core-bundle-dev-app/commit/fd41c3050b1277a516a2709462fe917675b088a1))
- Configure getByPath operation for each API platform resource - ([9f723de](https://github.com/roadiz/core-bundle-dev-app/commit/9f723de14948c2834a820b539422e9c0cf2ef023))
- Rate limiters for contact and custom form submissions - ([70a28f0](https://github.com/roadiz/core-bundle-dev-app/commit/70a28f0766879243729df5edf41a260644ae1eb9))
- OpenId authenticator config - ([14f36df](https://github.com/roadiz/core-bundle-dev-app/commit/14f36dfe6f9c4daefd7ac58f1eaa85d5f14a72a2))
- Added lexik_jwt_authentication - ([0cae5d0](https://github.com/roadiz/core-bundle-dev-app/commit/0cae5d044460ff40ae65091fd7a6f31ff10b9d2a))
- Env var HTTP_CACHE_SHARED_MAX_AGE - ([e543e05](https://github.com/roadiz/core-bundle-dev-app/commit/e543e0538f750ad6eef2de04fa8bdb71404fdd26))
- Better makefile - ([8f3376f](https://github.com/roadiz/core-bundle-dev-app/commit/8f3376f0fab33704eb45393cbaad617fe804321a))

### Refactor

- Updated dependencies - ([c201f15](https://github.com/roadiz/core-bundle-dev-app/commit/c201f15a2137c41c7342352ae3833c9f6fc75703))

<!-- generated by git-cliff -->
