## [v2.1.64](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.63...v2.1.64) (2024-06-17)


### Bug Fixes

* **Attributes:** Fixed AttributeValueRepository findByAttributable method preventing fetch attribute values without translations. Fixed AttributeChoiceType still requesting entityManager. ([5f59b69](https://github.com/roadiz/core-bundle-dev-app/commit/5f59b69885efa388fc16baba6c2c487abb38a561))

## [v2.1.63](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.62...v2.1.63) (2024-06-14)


### Bug Fixes

* Pass FormInterface to `bulkAction` to update bulk item with a form field data. ([b7684fd](https://github.com/roadiz/core-bundle-dev-app/commit/b7684fdad9e374c039ed80aad0ca7086eca3125a))

## [v2.1.62](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.61...v2.1.62) (2024-06-13)


### Features

* Added customizable `getDefaultRouteParameters` in AbstractAdminController ([985a02e](https://github.com/roadiz/core-bundle-dev-app/commit/985a02e0ae89c4a52d89803bf1ced6049804014a))

## [v2.1.61](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.60...v2.1.61) (2024-04-19)


### Bug Fixes

* **Documents:** Updated Dailymotion oembed discovery and iframe source generation. ([656b497](https://github.com/roadiz/core-bundle-dev-app/commit/656b497894e235f960a6f1bcbfa072a5d5563989))

## [v2.1.60](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.59...v2.1.60) (2024-03-08)


### Bug Fixes

* Error during back-porting from 2.2 ([a533030](https://github.com/roadiz/core-bundle-dev-app/commit/a533030b705a08ff30b211f9949bca8392c9b6bc))

## [v2.1.59](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.58...v2.1.59) (2024-03-08)


### Bug Fixes

* **Documents:** Do not try to render private Document URLs even for thumbnails ([2ddfb89](https://github.com/roadiz/core-bundle-dev-app/commit/2ddfb89beae68b2c455f1eeea5069f389d9d342d))

## [v2.1.58](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.57...v2.1.58) (2024-03-07)


### Bug Fixes

* Fixed roadiz version constraint to `2.1.*` ([7ded7e9](https://github.com/roadiz/core-bundle-dev-app/commit/7ded7e9c03480d4276a7815786a92e5c4bb7e3cf))
* **Trees:** Fixed tag and folder trees when they display first created translation instead of user chosen translation ([d39ffed](https://github.com/roadiz/core-bundle-dev-app/commit/d39ffed47fe61b99a88fbe65135023ee1a0f90cb))

## [v2.1.57](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.56...v2.1.57) (2024-02-27)


### Bug Fixes

* Lighten Tag and NodesSources serialization with `tag_documents` and `tag_color` serialization groups ([24eb88d](https://github.com/roadiz/core-bundle-dev-app/commit/24eb88d953d735298a71253096d03ed14d2dbd7c))

## [v2.1.56](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.55...v2.1.56) (2024-02-25)


### Bug Fixes

* **Search:** Missing `hl.q` Solr param when requesting highlight with complex queries ([42d85ae](https://github.com/roadiz/core-bundle-dev-app/commit/42d85aea8b1b1a58aaffccf87f8c222846780342))

## [v2.1.55](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.54...v2.1.55) (2024-02-21)


### Bug Fixes

* Fixed fetching non-ISO locale from database and querying result cache ([1383959](https://github.com/roadiz/core-bundle-dev-app/commit/1383959be20286ea29822fbf329f9c32d57fb1b3))
* Fixed maintenance mode exception when using API endpoints ([1f1b49d](https://github.com/roadiz/core-bundle-dev-app/commit/1f1b49d1d37f52aad982066bbab9a6e80d4ab9ca))

## [v2.1.54](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.53...v2.1.54) (2024-02-09)


### Bug Fixes

* Fix Setting value with \DateTimeInterface ([56c8606](https://github.com/roadiz/core-bundle-dev-app/commit/56c8606a7ba6e382cd88b2bea110a6234dbad79c))

## [v2.1.53](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.52...v2.1.53) (2024-02-07)


### Bug Fixes

* Fix doctrine/orm constraint <2.17 ([35c4689](https://github.com/roadiz/core-bundle-dev-app/commit/35c46894dd637bcf583c05b6284444310359849a))

## [v2.1.52](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.51...v2.1.52) (2024-02-07)


### Bug Fixes

* Prevent redirections to resolve a not-published resource. ([d7a5801](https://github.com/roadiz/core-bundle-dev-app/commit/d7a580142e745a8d9bd5c77f8a8e7a8f77c686c6))

## [v2.1.51](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.50...v2.1.51) (2023-11-20)


### Bug Fixes

* Fixed missing entityManager flush to remove custom-form answer. ([e9aa0ba](https://github.com/roadiz/core-bundle-dev-app/commit/e9aa0ba826244024426a0484a4e19a51008cbda2))

## [v2.1.50](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.49...v2.1.50) (2023-11-16)


### Bug Fixes

* Prevent doctrine/orm to upgrade to 2.17.0: Associations with fetch-mode=EAGER may not be using WITH conditions ([9ef566c](https://github.com/roadiz/core-bundle-dev-app/commit/9ef566cad69d256af5fbd37f20fbde9463e18a6f))

## [v2.1.49](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.48...v2.1.49) (2023-10-20)


### Bug Fixes

* Remaining merge conflict trace ([518f940](https://github.com/roadiz/core-bundle-dev-app/commit/518f94099116a10dc9a0992809579ffafc0ebaf6))

## [v2.1.48](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.47...v2.1.48) (2023-10-20)


### Bug Fixes

* **CustomForm:** Catch mailer transport exception when submitting custom-form answers ([52487e3](https://github.com/roadiz/core-bundle-dev-app/commit/52487e3b1ef626a771f382d9eb5b25d75ea2d483))

## [v2.1.47](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.46...v2.1.47) (2023-10-09)


### Bug Fixes

* **Solr:** Fixed fuzzy/proximity integer ([7d72a02](https://github.com/roadiz/core-bundle-dev-app/commit/7d72a024dc61688c8c566d0d835c11240bb43e69))

## [v2.1.46](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.45...v2.1.46) (2023-09-28)


### Bug Fixes

* Use multibyte string methods ([a9a5b84](https://github.com/roadiz/core-bundle-dev-app/commit/a9a5b8420492a4589fd2d88b9bc4434967e09498))

## [v2.1.45](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.44...v2.1.45) (2023-09-13)


### Bug Fixes

* **EntityListManager:** Validate ordering field name before QueryBuilder rejects it ([76cc4b4](https://github.com/roadiz/core-bundle-dev-app/commit/76cc4b427ab409d850198ae5da4091dad00a4f26))

## [v2.1.44](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.43...v2.1.44) (2023-09-07)


### Bug Fixes

* **Tag:** Added missing API search filter on tags relations ([146ce90](https://github.com/roadiz/core-bundle-dev-app/commit/146ce9007ecb2464ee923e5b0c3d0c941439fb4d))

## [v2.1.43](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.42...v2.1.43) (2023-09-04)


### Bug Fixes

* **UserSecurityType:** Missing CallbackTransformer on user chroot form normalization ([0d9a1b2](https://github.com/roadiz/core-bundle-dev-app/commit/0d9a1b293a181b8952cfdd7e29c28ea157dccf06))

## [v2.1.42](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.41...v2.1.42) (2023-09-04)


### Bug Fixes

* **NodeNameChecker:** Limit generated unique nodeName to 250 chars, no matter suffix added to it ([bb1e271](https://github.com/roadiz/core-bundle-dev-app/commit/bb1e27178cb524f74152f063e8d0105ed7df17ef))

## [v2.1.41](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.40...v2.1.41) (2023-08-04)


### Bug Fixes

* Do not require explicitly doctrine/dbal since issue came from doctrine/bundle ([93321bb](https://github.com/roadiz/core-bundle-dev-app/commit/93321bb2e8ab83cb317aca9cce294f54489ca390))

## [v2.1.40](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.39...v2.1.40) (2023-08-04)


### Bug Fixes

* **NodesTags:** Added a single UUID field to prevent primary key overlap. ([68fff41](https://github.com/roadiz/core-bundle-dev-app/commit/68fff41c9219e7e4c4b4254ecdfc1c3d119ca914))

## [v2.1.39](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.38...v2.1.39) (2023-08-03)


### Bug Fixes

* **Doctrine:** Do not extend `Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository` anymore due to _LazyServiceEntityRepository_ since `doctrine/doctrine-bundle@2.8.1` ([ec1687c](https://github.com/roadiz/core-bundle-dev-app/commit/ec1687cc27436b08ff0ec5f73d1adb04fb23e424)) (https://github.com/doctrine/DoctrineBundle/issues/1693)

## [v2.1.38](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.37...v2.1.38) (2023-08-03)


### Bug Fixes

* **doctrine/dbal** >=3.4.0 broke queries on inheriting class using fields from child classes. Still using NodesSources when querying on child entities and their custom fields. ([e7d5dbe](https://github.com/roadiz/core-bundle-dev-app/commit/e7d5dbe809bc447affe2a9a811763670c90b2216))

## [v2.1.37](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.36...v2.1.37) (2023-08-03)


### Features

* New Doctrine migration
* **Security:** Added UserChecker to check users enabled, expired, locked or credentialExpired. Removed useless User' boolean expired and credentialsExpired fields. ([42d4d11](https://github.com/roadiz/core-bundle-dev-app/commit/42d4d1133916ea1101872665cd3c13d2ea18175f))
    - Make sure to register Roadiz `UserChecker` in your `security.yaml` file: https://symfony.com/doc/current/security/user_checkers.html#enabling-the-custom-user-checker

```yaml
# config/packages/security.yaml

# ...
security:
    firewalls:
        main:
            pattern: ^/
            user_checker: RZ\Roadiz\CoreBundle\Security\UserChecker
```

## [v2.1.36](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.35...v2.1.36) (2023-08-03)


### Features

* **OpenID:** Added new OpenID mode with `roadiz_rozier.open_id.requires_local_user` (default: true) which requires an existing Roadiz account before authenticating SSO users. ([639e1a5](https://github.com/roadiz/core-bundle-dev-app/commit/639e1a5a90ec6d50078087db5d839ef8997da7de))
    - Users authenticated against SSO with real user account will use their real roles and groups instead of open_id permissions
* Fixed `discovery_url` configuration when using a DotEnv placeholder.


## [v2.1.35](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.34...v2.1.35) (2023-08-01)


### Bug Fixes

* **WebResponseOutputDataTransformer:** Made `WebResponseOutputDataTransformer` overrideable in projects instead of reimplementing it. ([52fd409](https://github.com/roadiz/core-bundle-dev-app/commit/52fd4096343aa4b68d0907375a53eefc64dc3fbc))

## [v2.1.34](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.33...v2.1.34) (2023-07-31)


### Bug Fixes

* **AjaxNodeTreeController:** Fixed non-integer translationId ([e33762f](https://github.com/roadiz/core-bundle-dev-app/commit/e33762f4931c8537425e1cb3b7995a48f87834ba))

## [v2.1.33](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.32...v2.1.33) (2023-07-31)


### Bug Fixes

* **AjaxNodeTree:** Removed `translationId` path param from `nodesTreeAjax` route ([d5e6fe6](https://github.com/roadiz/core-bundle-dev-app/commit/d5e6fe6333b2dc5e9aff372965adc9defb58af13))

## [v2.1.32](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.31...v2.1.32) (2023-07-25)


### Bug Fixes

* **ApiResourceGenerator:** Do not wrap *boolean* value in quotes ([90630c1](https://github.com/roadiz/core-bundle-dev-app/commit/90630c1cb8ac26839ef8e6cccd8a0c35ff03c0e6))

## [v2.1.31](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.30...v2.1.31) (2023-07-25)


### Features

* Use `ramsey/uuid` for Webhook ID generation, doctrine/dbal removed AutoGenerated uuids ([55f80fc](https://github.com/roadiz/core-bundle-dev-app/commit/55f80fcd25e8d76f65b56e84be25440cdf8a4a68))


### Bug Fixes

* **CoreBundle:** Fixed doctrine/dbal version ([3087a38](https://github.com/roadiz/core-bundle-dev-app/commit/3087a389c3cba3e7f108623cf30c16e78e7b7fd2))

## [v2.1.30](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.29...v2.1.30) (2023-07-25)


### Bug Fixes

* **CoreBundle:** Fixed doctrine/dbal dependency to 2.x due to UUID removal. Missing constraint in core-bundle. ([9824f1f](https://github.com/roadiz/core-bundle-dev-app/commit/9824f1fd96483b573d960093bf9d012a89d9120a))

## [v2.1.29](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.28...v2.1.29) (2023-07-25)


### Bug Fixes

* Fixed _doctrine/dbal_ dependency to `2.x` due to UUID removal ([d426160](https://github.com/roadiz/core-bundle-dev-app/commit/d426160ccef31ca2ab1619fdc1654d78942b4875))

## [v2.1.28](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.27...v2.1.28) (2023-07-19)


### Bug Fixes

* **Search:** Fixed node advanced search using `__node__` prefix ([bcb7d5d](https://github.com/roadiz/core-bundle-dev-app/commit/bcb7d5d9ad6df9e4c580bf4382dd34824ff3e7ea))

## [v2.1.27](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.26...v2.1.27) (2023-07-12)


### Bug Fixes

* Missing `isTransactional` on last migration ([5bf8833](https://github.com/roadiz/core-bundle-dev-app/commit/5bf8833cc4c7b7bd4c49a443bbb43bf99055f2ef))

## [v2.1.26](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.25...v2.1.26) (2023-07-12)


### Bug Fixes

* Fixed Loggable behaviour, removed relationship between UserLogEntry and User for future entity-manager separation. ([96d180b](https://github.com/roadiz/core-bundle-dev-app/commit/96d180b415f8e6cc379293e0e07aca8bccf2cbd3))

Add missing Doctrine mapping:

```yaml
gedmo_loggable:
    type: attribute
    prefix: Gedmo\Loggable\Entity\MappedSuperclass
    dir: "%kernel.project_dir%/vendor/gedmo/doctrine-extensions/src/Loggable/Entity/MappedSuperclass"
    alias: GedmoLoggableMappedSuperclass
    is_bundle: false
```

## [v2.1.25](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.24...v2.1.25) (2023-07-12)


### Bug Fixes

* Do not display document thumbnail controls for audio, video ([57bbc85](https://github.com/roadiz/core-bundle-dev-app/commit/57bbc85cecda42fba08900e71faa2958b74f8d81))

## [v2.1.24](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.23...v2.1.24) (2023-07-06)


### Bug Fixes

* **Search engine:** Do not add quotes if multi-word exact query, Solr Helper already does it ([b0aa80a](https://github.com/roadiz/core-bundle-dev-app/commit/b0aa80a139c576b5729cb8d049f9bc3e38d64f70))

## [v2.1.23](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.22...v2.1.23) (2023-07-02)


### Bug Fixes

* Use `FilterValidationException` instead of `InvalidArgumentException` to generate 400 and no 500 errors ([ca89b73](https://github.com/roadiz/core-bundle-dev-app/commit/ca89b733859a77460081f37a1d1265f7d128ae57))

## [v2.1.22](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.21...v2.1.22) (2023-06-30)


### Bug Fixes

* Do not handle node add attribute form when no attribute exists ([db77e62](https://github.com/roadiz/core-bundle-dev-app/commit/db77e62e778495b2f87eaed35022770064c5bd3c))

## [v2.1.21](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.20...v2.1.21) (2023-06-28)


### Bug Fixes

* **OpenID:** Do not register `roadiz_rozier.open_id.discovery` if `discovery_url` is not valid ([120b6a9](https://github.com/roadiz/core-bundle-dev-app/commit/120b6a999b6635d120ce5c7ee7225b61328692b1))

## [v2.1.20](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.19...v2.1.20) (2023-06-23)


### Features

* **Preview:** Added OpenAPI decorator to document `_preview` query param and JWT security ([449c9f9](https://github.com/roadiz/core-bundle-dev-app/commit/449c9f9fcdb2114e6c13804be696d558ac7efc49))


### Bug Fixes

* **Preview:** Check strict `_preview` param value (allowing `0`, `false` values to disable preview mode) ([70b60c9](https://github.com/roadiz/core-bundle-dev-app/commit/70b60c972ed13fa1819ef3611521e9a2f6fbf459))

## [v2.1.19](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.18...v2.1.19) (2023-06-23)


### Bug Fixes

* **OpenApi:** Fixed `getByPath` operation overlap with `get` by setting `id` request attribute and api_resource operation configuration ([54a378d](https://github.com/roadiz/core-bundle-dev-app/commit/54a378d151409ef6ec8fb7cfea6b9c74e5115d44))

## [v2.1.18](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.17...v2.1.18) (2023-06-23)


### Bug Fixes

* Fixed and refactored SQL search query building on Repository and Paginator levels ([b5d320b](https://github.com/roadiz/core-bundle-dev-app/commit/b5d320b49f28ff167e6a4482090fc743bd46c186))

## [v2.1.17](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.16...v2.1.17) (2023-06-20)


### Features

* Added Roadiz custom data-collector for web-profiler ([d0e01fa](https://github.com/roadiz/core-bundle-dev-app/commit/d0e01fa885b462a8dc46fb7ef04892c840452a7e))


### Bug Fixes

* **UI:** Changed user and menu panels size for LG breakpoint. Clear `cache.global_clearer` in CacheController and SchemaUpdater. ([c20463e](https://github.com/roadiz/core-bundle-dev-app/commit/c20463eb2e14b8c1977a6eabe12a9380a816f37b))
* `UpdateNodeTypeSchemaMessage` should be handled synced to avoid Doctrine exception on refresh ([84a611b](https://github.com/roadiz/core-bundle-dev-app/commit/84a611b17837c58d219c6f822c132b034b4420af))

## [v2.1.16](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.15...v2.1.16) (2023-06-01)


### Bug Fixes

* Do not prevent setting parent with not the same class ([3b5996d](https://github.com/roadiz/core-bundle-dev-app/commit/3b5996dc804510bed29b5f20ef542e933f101561))
* **LeafInterface:** Do not test exact class when setting LeafInterface parent to allow doctrine proxies. ([56ed76d](https://github.com/roadiz/core-bundle-dev-app/commit/56ed76d401fee87813e0cbb86ac92ec130a46752))

## [v2.1.15](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.14...v2.1.15) (2023-05-24)


### Bug Fixes

* Allow all `gedmo/doctrine-extensions` v3 ([0ed814b](https://github.com/roadiz/core-bundle-dev-app/commit/0ed814b1b347c6d4989acbedbb05636964687908))

## [v2.1.14](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.13...v2.1.14) (2023-05-19)


### Bug Fixes

* Fixed `Collection<int, classname>` type hinting and co-variance (part 2) ([2a53d81](https://github.com/roadiz/core-bundle-dev-app/commit/2a53d81f8218e3ca584e420568e0d0c9031ac681))

## [v2.1.13](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.12...v2.1.13) (2023-05-19)


### Bug Fixes

* Fixed `Collection<int, classname>` type hinting ([3a670bb](https://github.com/roadiz/core-bundle-dev-app/commit/3a670bb093afddb4591fdd36a432afaee3d015a4))
* Fixed CustomFormAnswer class renaming `getAnswers()` to `getAnswerFields()` ([d88a179](https://github.com/roadiz/core-bundle-dev-app/commit/d88a1795a14cbf1f58dd983199a7eed343aba6fe))

## [v2.1.12](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.11...v2.1.12) (2023-05-11)


### Bug Fixes

* Add `PreviewResolverInterface` into `NodeSourceWalkerContext` to alter TreeWalker definition against preview status ([463be2e](https://github.com/roadiz/core-bundle-dev-app/commit/463be2e43924d87f4a5b3a2ecda63ed0442b11c3))

## [v2.1.11](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.10...v2.1.11) (2023-05-05)


### Bug Fixes

* **Document:** Ignore thumbnail documents from Explorer ([d236f1a](https://github.com/roadiz/core-bundle-dev-app/commit/d236f1af68a9a99452024748ca3c5e250800414e))
* **Solr:** Added a new `wildcardQuery` to search and autocomplete at the same time ([37746af](https://github.com/roadiz/core-bundle-dev-app/commit/37746af717f7b504d761ef7fff377f7bfc36aaad))

## [v2.1.10](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.9...v2.1.10) (2023-05-05)

### Features

* **Document:** Added `DocumentPdfMessageHandler` to generate thumbnail for PDF documents. ([35241bc](https://github.com/roadiz/core-bundle-dev-app/commit/35241bcc88e17131a448f5a7f65388233a8e3d87))

## [v2.1.9](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.8...v2.1.9) (2023-04-11)


### Bug Fixes

* `JoinDataTransformer` must always transform to an array, even single objects ([e17b804](https://github.com/roadiz/core-bundle-dev-app/commit/e17b804baf73ca9d827b07322a0163a952b3e5c0))

## [v2.1.8](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.7...v2.1.8) (2023-04-06)


### Bug Fixes

* **PreviewBarSubscriber:** Test if Response content is string before searching `</body>` tag ([93d1897](https://github.com/roadiz/core-bundle-dev-app/commit/93d18970ba17c903c2a2feea9e787d166b5f6034))

## [v2.1.7](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.6...v2.1.7) (2023-04-04)


### Bug Fixes

* **Documents:** Prevent renaming document filename if new pathname is not available ([13982cc](https://github.com/roadiz/core-bundle-dev-app/commit/13982cce13b1876d6f55167a40abcb456cd1e64f))

## [v2.1.6](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.5...v2.1.6) (2023-03-23)


### Features

* **EntityGenerator:** Entity generator uses `DefaultValuesResolverInterface` to compute required _ENUM_ fields length according to their default values. ([20263b6](https://github.com/roadiz/core-bundle-dev-app/commit/20263b613d57f5e918f1b0f75de9835714259250))
* Migrate from monolithic docker image to nginx + app + worker + cron containers ([fa8b76a](https://github.com/roadiz/core-bundle-dev-app/commit/fa8b76a6216b93bdce93716d071131c138310921))


### Bug Fixes

* Fixed `AjaxNodesExplorerController` search params strict typing. ([fd38552](https://github.com/roadiz/core-bundle-dev-app/commit/fd385525f8f0ee4303cc77714c3d4207301f44a2))

## [v2.1.5](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.4...v2.1.5) (2023-03-17)


### Features

* Added `onlyVisible` optional param for TreeWalker definitions ([abf93a8](https://github.com/roadiz/core-bundle-dev-app/commit/abf93a8866629e095fd4302d05a46443158d8b81))

## [v2.1.4](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.3...v2.1.4) (2023-03-15)


### Bug Fixes

* **EntityGenerator:** Fixed attributes list generator when there is only one attribute to print ([b6139ba](https://github.com/roadiz/core-bundle-dev-app/commit/b6139ba0f339962b15d0247ab94d1aa856ec8123))

## [v2.1.3](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.2...v2.1.3) (2023-03-14)


### Bug Fixes

* Fixed SolrPaginator `getLastPage` using an index starting at 1 instead of 0 ([052f9be](https://github.com/roadiz/core-bundle-dev-app/commit/052f9be0b467bb5ea6031ef31763ec5005f63c5e))

## [v2.1.2](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.1...v2.1.2) (2023-03-14)


### Features

* Create overrideable `createSearchResultsFromResponse` method for any AbstractSearchHandler extending class ([b23b92f](https://github.com/roadiz/core-bundle-dev-app/commit/b23b92f9504a2130fac3455cd79d7f116fa7deba))


### Bug Fixes

* Fix SolrPaginator last-page when there are no results ([a044446](https://github.com/roadiz/core-bundle-dev-app/commit/a04444698d7b4a96a538419f840928f190054573))

## [v2.1.1](https://github.com/roadiz/core-bundle-dev-app/compare/v2.1.0...v2.1.1) (2023-03-13)


### ⚠ BREAKING CHANGES

* All Solr documents MUST have a idempotent ID field to ease up Solr indexing (no more need to delete previous index entry before indexing an entity).

### Features

* Added `NodesSourcesHeadFactoryInterface` for better WebResponse and CommonContent responses maintainability. ([ed05a24](https://github.com/roadiz/core-bundle-dev-app/commit/ed05a24947da4caa5533b37190c480b0b5358bd5))
* Rename `getCompositeIdentifier` to `getIdempotentIdentifier` and provided a fallback. ([e8c895a](https://github.com/roadiz/core-bundle-dev-app/commit/e8c895a056dee0f2668ed8d081a2021a45490174))
* Updated Solr indexing tags fields for multivalued strings and use composite ID for easy overriding ([50a04af](https://github.com/roadiz/core-bundle-dev-app/commit/50a04afc913eb1a7b67cd550fc39305598c4db19))

## v2.1.0 (2023-03-06)

### ⚠ BREAKING CHANGES

* Migrate to *API Platform* 2.7
* Merging all Roadiz packages into a single monorepo
    - `roadiz/compat-bundle`
    - `roadiz/core-bundle`
    - `roadiz/doc-generator`
    - `roadiz/documents`
    - `roadiz/dts-generator`
    - `roadiz/entity-generator`
    - `roadiz/font-bundle`
    - `roadiz/jwt`
    - `roadiz/markdown`
    - `roadiz/models`
    - `roadiz/openid`
    - `roadiz/random`
    - `roadiz/rozier`
    - `roadiz/rozier-bundle`
    - `roadiz/user-bundle`
* **Links header**: translation names are encoded in base64 because headers are ASCII only, you must decode it in frontend
* **Documents**: All documents read and write operation MUST go through `Flysystem` to support external storages.
* **Documents**: All classes are now in `RZ\Roadiz\Documents` namespace root
* **Core**: Node's tags relationship is now positionable, this will rename _API Platform_ filter

### Features (Core)

* Added callable $onValid on ContactFormManager handle method to allow additional script on form validation ([83ee2b1](https://github.com/roadiz/core-bundle/commit/83ee2b19a1433c16bcb581fd798ff4ec9acec484))
* Added copyrightValid boolean filter on Document resource ([e82eef6](https://github.com/roadiz/core-bundle/commit/e82eef687556c6d4d82508b52aa98b29f269f33d))
* Added Document publicUrl for non-processable documents ([229c36a](https://github.com/roadiz/core-bundle/commit/229c36a8cac2a8d0d14b507a1ed542979a9b0bb9))
* Added document_thumbnails serialization group to generated api resource files ([5f6b59f](https://github.com/roadiz/core-bundle/commit/5f6b59f911b42ecd4c910bf40935ffc7ce1d9293))
* Added new `document_private` serialization group ([0a0b088](https://github.com/roadiz/core-bundle/commit/0a0b08858763ed94b22aeabdace7b8eade9f5661))
* Added new setting for support email address ([ea07bb4](https://github.com/roadiz/core-bundle/commit/ea07bb47b7b638fe2845f4ea5783f56caf6bb628))
* Added Node nodesTags getter and setter ([293e428](https://github.com/roadiz/core-bundle/commit/293e42858ab6c1c3527073189f5d59306ca95941))
* Added node-type resolver to find node-type children types for menus ([13a32cb](https://github.com/roadiz/core-bundle/commit/13a32cb2f82713c549fbc16b074ab0339f82ebef))
* Added NodesSources noIndex boolean field ([bb7c087](https://github.com/roadiz/core-bundle/commit/bb7c087e4e4f6ad000d6ed461e5e993ef34691a1))
* Added noIndex to NodesSourcesHead ([c54e091](https://github.com/roadiz/core-bundle/commit/c54e091d8825896af41a0eb2ff38aa95edd76910))
* Added not filter on Tag's parent ([9a5118e](https://github.com/roadiz/core-bundle/commit/9a5118e099e212ccfce49153370e19feb0ea6ff2))
* Added OpenApi decorator for default JSON login operation ([228f248](https://github.com/roadiz/core-bundle/commit/228f248503b7ddbd4aa818b7e9b2f3f3d29c298f))
* Added Setting events for CRUD operations ([41fa26c](https://github.com/roadiz/core-bundle/commit/41fa26c512788212ddb2d62319dfbd56ff898884))
* Added TreeWalkerGenerator service ([ad7e4b4](https://github.com/roadiz/core-bundle/commit/ad7e4b416ff3f0d5a6e23b1319604d7c294ca016))
* **Attributes:** Migrating to PHP 8 attributes ([f1a5d00](https://github.com/roadiz/core-bundle/commit/f1a5d006a159c6ed2020210c6f0ea41929e37369))
* **Attributes:** Migration to attributes ([610d816](https://github.com/roadiz/core-bundle/commit/610d81624b978011356698f4a55378ddee83e594))
* **Attributes:** Migration to attributes ([5f2a999](https://github.com/roadiz/core-bundle/commit/5f2a999d03647c0579cd708cd5404a31101a7f59))
* **Attributes:** Migration to attributes ([412f7f7](https://github.com/roadiz/core-bundle/commit/412f7f7e73d1ccfdca1b0c29e68325b3f52192b8))
* Big roadiz/documents namespace refactoring ([fbaae81](https://github.com/roadiz/core-bundle/commit/fbaae81f6c612904e810fa63633c47bef613c951))
* Changed AbstractDocumentFactory constructor signature ([b4515d0](https://github.com/roadiz/core-bundle/commit/b4515d08c848838b4b60eda4b56e1e0378988cde))
* Customize SwaggerUI with Roadiz header ([c2e4eca](https://github.com/roadiz/core-bundle/commit/c2e4eca1236f0f2c12c3e6f93133f1847e97e044))
* Do not upload Document twice if the same checksum exists ([5d1f61e](https://github.com/roadiz/core-bundle/commit/5d1f61ebc04d58b23dbc7d1995f78fa78eac8091))
* DocumentRepository implements DocumentRepositoryInterface ([6905272](https://github.com/roadiz/core-bundle/commit/6905272481f59ae1c41bbb7e9536e77799cc48bf))
* Extracted font domain logic to a new Symfony Bundle ([d337151](https://github.com/roadiz/core-bundle/commit/d33715122a79751fb3668c712772b0d6b36e62e0))
* Gather Video/Audio message handler to avoid streaming media file multiple times ([86a4fa6](https://github.com/roadiz/core-bundle/commit/86a4fa6a6fb6ff8d30044f26ee18431a8e62f545))
* Generate and remove api resource on NodeType creation/deletion ([bbc7496](https://github.com/roadiz/core-bundle/commit/bbc7496ed9a14a3fcb647c7b5562c4f2be7dcd66))
* Made node's tags relation positionable ([2038f20](https://github.com/roadiz/core-bundle/commit/2038f20121b692a2ff9f1561c4b56b3bb96af17e))
* Migrate from Packages to **Flysystem** for all documents read/write operations ([f6beaa3](https://github.com/roadiz/core-bundle/commit/f6beaa33010af11956fa7eb05dabb81251a5ea0c))
* Migrate NodeType schema updates to Async Messenging and CLI processes ([89ac1b8](https://github.com/roadiz/core-bundle/commit/89ac1b8a481e890a3c2d100decb54266e5877fdf))
* Migrating to PHP 8 native annotations ([cab7fee](https://github.com/roadiz/core-bundle/commit/cab7fee3348036c7c74da41448e1c76f50adc5a3))
* Moved api resource creation from command to a dedicated service ([53f1d06](https://github.com/roadiz/core-bundle/commit/53f1d06ba4439e00f94c56e8b59f23ae1a0978f0))
* Moved Document related console command to roadiz/documents lib ([c529ee4](https://github.com/roadiz/core-bundle/commit/c529ee48a7619ff4db149a3120ef852cff80ad39))
* New Admin role: `ROLE_ACCESS_DOCUMENTS_CREATION_DATE` ([9f45aaa](https://github.com/roadiz/core-bundle/commit/9f45aaaa92c70266d4ddae5d8770fb362377ee50))
* **Normalization:** Disabled DataTransformers and use normalizers ([9996c81](https://github.com/roadiz/core-bundle/commit/9996c81fb0d6db7cbffbc9dcdf8531ba258a58eb))
* **Normalization:** Migrate AttributeValue Dto to Normalizer ([45b38e6](https://github.com/roadiz/core-bundle/commit/45b38e612c4e73cbc92aaa663ba805ed48242069))
* **Normalization:** Migrate Document DataTransformer to DocumentNormalizer ([87123b8](https://github.com/roadiz/core-bundle/commit/87123b85f11d2a69c843608d64a45da44ea976e0))
* **Normalization:** Migrate Folder data-transformer to FolderNormalizer ([5cae626](https://github.com/roadiz/core-bundle/commit/5cae626f6aedcd4bd92acd72e8a02474f4f5d3c0))
* **Normalization:** Migrate Tag data-transformer to normalization ([98fa13f](https://github.com/roadiz/core-bundle/commit/98fa13fcc8c1b85dee01a66bbd778b0963ac6a2d))
* **Normalization:** Migrate Translation data-transformation to normalizer ([59b00a4](https://github.com/roadiz/core-bundle/commit/59b00a4710ed2aed60c9f5b750bbc546e2266904))
* **Normalization:** Refactored User serialization groups ([e90de99](https://github.com/roadiz/core-bundle/commit/e90de99b1822aea8f27a8eaad5c2c92afa8d30a6))
* **Normalization:** Set normalizer decorator priorities ([99dcb88](https://github.com/roadiz/core-bundle/commit/99dcb8877beeedd31ba7645558e599cf22372c62))
* **Normalization:** Swtich Tag DataTransformer to TagNormalizer ([d603572](https://github.com/roadiz/core-bundle/commit/d60357206a82dc594eeb1b7ec1a1a005d4997a39))
* **Normalization:** WebResponseDataTransformerInterface does not extend DataTransformerInterface anymore ([dbf895f](https://github.com/roadiz/core-bundle/commit/dbf895f4cd19af1652f636485938f65ecb55043b))
* Normalize Document embedUrl ([0a7d876](https://github.com/roadiz/core-bundle/commit/0a7d8760f783317f426e045fa07d19ad741211f3))
* Only serialize visible Document folders with `document_folders` group. ([687c534](https://github.com/roadiz/core-bundle/commit/687c5342e6a6bda70b6af055ec0621b2901d4c8f))
* Removed settings : use_typed_node_names (replaced in bundle configuration), use_cdn (useless) ([ee07194](https://github.com/roadiz/core-bundle/commit/ee07194b9b38957d03342c57a6408695ededcddf))
* Requires InterventionRequestBundle ~3 or develop ([a490979](https://github.com/roadiz/core-bundle/commit/a490979de3d0255b33de0000d9ebf20beca6a8ec))
* **Serialization:** Tidy up User serialization groups (user, user_role, user_personal, user_identifier) ([d164a89](https://github.com/roadiz/core-bundle/commit/d164a89e32212309d53dd957aba2bea6cdb71ae0))
* **Settings:** Removed useless settings and moved required settings into Configuration and DotEnv variables ([9dabe7f](https://github.com/roadiz/core-bundle/commit/9dabe7fcc9b342f4ba709e5897937ca89d9fed8c))
* Tell messenger workers to stop when entity files have been changed ([e4564c5](https://github.com/roadiz/core-bundle/commit/e4564c5c8fc720dd1092a89303561f842ec9fc41))
* Throws UnrecoverableMessageHandlingException when handler entities are not available anymore ([fa2b2fd](https://github.com/roadiz/core-bundle/commit/fa2b2fdaadf1d13c847693529d8e81b431286b69))
* Upgrade rezozero/tree-walker to >1.3.0 ([6958e16](https://github.com/roadiz/core-bundle/commit/6958e162fe73fed8b6f967068d6b26ed7fbbbdeb))
* Upgraded to API platform 2.7 and PHP 8.0 minimum ([5f7cfe8](https://github.com/roadiz/core-bundle/commit/5f7cfe855339a7f9924425b9f7338fb0f6f902b0))
* Use a MediaFinderCompiler pass to register roadiz_core.medias.supported_platforms parameter ([3d10260](https://github.com/roadiz/core-bundle/commit/3d10260e18a3bffc569e8709471e99480e6f869a))
* Use DocumentInterface and DocumentArchiver whenever possible ([80cd280](https://github.com/roadiz/core-bundle/commit/80cd2800780c33efac9e30f2bb5d977ae738643a))
* Use interfaces instead of entity class in commands ([fed8c24](https://github.com/roadiz/core-bundle/commit/fed8c243fd6b0fc62d8066d39de10fb7a9a80dc5))
* **User:** Added new user public name to allow displaying non personal names or identifier ([3de281a](https://github.com/roadiz/core-bundle/commit/3de281a24c1aed1ca31014de247035ecad1d8bf1))
* Validate JSON form types ([c78ef21](https://github.com/roadiz/core-bundle/commit/c78ef21ea87ab6e1c0248a48cfbca4649259fecc))
* Variabilize Documents lib source dir ([c1439d7](https://github.com/roadiz/core-bundle/commit/c1439d763a4f565dd13d45712f1bd6354874f290))


### Bug Fixes (Core)

* **AbstractDoctrineExplorerProvider:** Sort entities the same way IDs were given ([caef1d6](https://github.com/roadiz/core-bundle/commit/caef1d6136da68ba7d90bc8fc54495b22fc7f5ee))
* Add documentTranslation into document during persisting loop to allow fetching before flushing ([33274de](https://github.com/roadiz/core-bundle/commit/33274deab3458b505eb04de1284b567eb81e9fad))
* Added isTransactional: false on doctrine migrations ([c132e78](https://github.com/roadiz/core-bundle/commit/c132e783ed37a56b61829eea017c3789a036615a))
* Cast all Node workflow places to string for forward compatibility ([0bc314a](https://github.com/roadiz/core-bundle/commit/0bc314a16f1866cdf7637df9e503c92e69c45530))
* CustomFormController must fetch translation from request, not config ([be33ef6](https://github.com/roadiz/core-bundle/commit/be33ef64200cabde3b557684d8c7bd8230ad3b12))
* Deprecation fixes ([3280044](https://github.com/roadiz/core-bundle/commit/3280044061e4c9cf8fe06506231f979c47b89adc))
* Entities __toString method should only return their ID, phpstan cleansing ([26bfa9a](https://github.com/roadiz/core-bundle/commit/26bfa9aa0bfdccc298b91abc0f240186148a24dd))
* ExplorerProviderItemTransformer::reverseTransform should return mixed ([d1b0f65](https://github.com/roadiz/core-bundle/commit/d1b0f6568a3e61c1cf8454ce907e323a011bcd76))
* Fixed src/Console/MailerTestCommand ([2c6d89c](https://github.com/roadiz/core-bundle/commit/2c6d89cfe5309532406a0e6f89f595138f67fbfe))
* Force folder documents join columns names ([e0fb620](https://github.com/roadiz/core-bundle/commit/e0fb62068cff15bcfaa507cbadb0fa8688b9f16f))
* JSON types cannot be indexed ([610cf0e](https://github.com/roadiz/core-bundle/commit/610cf0e258b4dec387fd69c162fb757a5d094061))
* Limit NodesSources discriminator column length to 30 chars for index performance ([84e055a](https://github.com/roadiz/core-bundle/commit/84e055aa101703913e60aeac24992973a53b0ea4))
* Link Headers must be ASCII only, so we encode in base64 ([d923008](https://github.com/roadiz/core-bundle/commit/d923008ed7977e85d949b13920f73d538b886e3d))
* NodesSourcesHead nodeSource can be null ([4bfdbf5](https://github.com/roadiz/core-bundle/commit/4bfdbf5ed94b923544eada123718fb2730295576))
* Override AttributeValue::getPosition method to add serialization groups ([0881085](https://github.com/roadiz/core-bundle/commit/0881085926b4ead0910c0611e333a2ce3598cf86))
* Single NodeTypeField data provider transformer ([deafe10](https://github.com/roadiz/core-bundle/commit/deafe10d9a78c22761e374b1bc7966785f5d0b3d))
* Try to update existing Document translation before creating a new one ([6034e06](https://github.com/roadiz/core-bundle/commit/6034e069cc6b706cf2491520fff76ac89be18355))

### Features (Models)

* Added static return type ([ab134f7](https://github.com/roadiz/models/commit/ab134f7bb920df77a0f1d7aae55df6c8f3ddaf88))
* Added validation assertion on AbstractEntities ([ccaac31](https://github.com/roadiz/models/commit/ccaac312e68daa000f19062a02b6802f202aeda2))
* Added validation assertion on AbstractEntities (2) ([4039c08](https://github.com/roadiz/models/commit/4039c0889d7ec24c67d79daf2508105c7257719c))
* GeoTag and MultiGeoTag fields use json type ([161136b](https://github.com/roadiz/models/commit/161136b8c2e23ba9f286a98aeb0695ae7e65b074))
* Moved PHP annotations to PHP attributes ([0f8b888](https://github.com/roadiz/models/commit/0f8b8880474583019a64a9e3fc6070ac94817031))
* **Serialization:** Added new AbstractHuman::publicName and user_personal group ([1a14e0b](https://github.com/roadiz/models/commit/1a14e0be7968180ad5cb5e4650aefb95dbd1c2f9))


### Bug Fixes (Models)

* Do not add static getParent return type, it breaks Doctrine proxy behaviour ([4f36943](https://github.com/roadiz/models/commit/4f36943e5554cae460f25db3d4f493954b24195b))
* Fixed AbstractField fields visibility ([1ae0f57](https://github.com/roadiz/models/commit/1ae0f5740582c766ff7056aa8df084c1d403a963))
* LeafInterface and LeafTrait type hintings ([9ed7c3e](https://github.com/roadiz/models/commit/9ed7c3ee02718539120f0782d494a729c1db792d))
* ORM\\Table attribute ([ae60f1f](https://github.com/roadiz/models/commit/ae60f1fe8c32c33d73715a640412f38f68680531))

### Features (Documents)

* Added contract interface for DocumentRepository ([4202b85](https://github.com/roadiz/documents/commit/4202b8553f8caf99bad85d50cedead2b66882155))
* Added createForUrl method in EmbedFinderFactory ([ebe6907](https://github.com/roadiz/documents/commit/ebe69078fdec15de9dfb7587b4dcfb9a6468d0ff))
* Added DocumentInterface getMountPath to get one path with storage type information ([5662b05](https://github.com/roadiz/documents/commit/5662b059b24603ab01dc2c9cd0b8f1661224edb6))
* Added EmbedFinderInterface getShortType to get a simple string for icons or identification purpose ([544b886](https://github.com/roadiz/documents/commit/544b8864bf5284eddc00fff0913c3fd052fc5524))
* Added static return type ([6e0113b](https://github.com/roadiz/documents/commit/6e0113bd11b868e15b6d3048d020b498bedbdedc))
* Added Symfony console commands ([677522e](https://github.com/roadiz/documents/commit/677522ecae218d8a98ea374bd741684e2ede0de5))
* Allow doctrine/orm 2.* and phpstan refactoring ([d6ac420](https://github.com/roadiz/documents/commit/d6ac420d954e51044b57f6b52914bdf220656ed0))
* Allow DocumentFactory to recreate existing document with duplicated file ([fc33d5b](https://github.com/roadiz/documents/commit/fc33d5ba73e15e4a15303b41c83b09dd12529f5b))
* **Attributes:** Migrate from PHP annotations to PHP attributes ([dc1657e](https://github.com/roadiz/documents/commit/dc1657ef07b48e3ef282172f8d1e0fa62320a5c6))
* Better phpstan hinting ([121ef39](https://github.com/roadiz/documents/commit/121ef39b693c571f5e699c104b7d0ea2f2bee4da))
* Better phpstan hinting ([3c5b67d](https://github.com/roadiz/documents/commit/3c5b67d69937f472f47685ea8b61ac743d203e7a))
* Do not upload Document twice if the same checksum exists ([6db1a50](https://github.com/roadiz/documents/commit/6db1a50b04e91768238b73c13718d44eb789c5e5))
* Fixed atoum tests ([e8b46e6](https://github.com/roadiz/documents/commit/e8b46e65c4d63beefd19ccfc9918952cdf8da078))
* New EmbedDocumentAlreadyExistsException exception ([8ec97b6](https://github.com/roadiz/documents/commit/8ec97b6ff3db33d49826dea1f088989d85d94322))
* Refactored all sources namespaces ([cc4246a](https://github.com/roadiz/documents/commit/cc4246aa78babcc7dd9818c862fcecef48911d23))
* Refactored Document lifecycle with Flysystem operator ([b803146](https://github.com/roadiz/documents/commit/b803146d58384479da3bdd1eedfcd10ce115eb5e))
* Refactoring SvgSizeResolver ([6783405](https://github.com/roadiz/documents/commit/6783405bcc5844abcb820d9e72251697053be3e9))
* Removed dependency to jms/serializer ([417f6ed](https://github.com/roadiz/documents/commit/417f6edbd3851cd17f42b1ac8b493d6430d19863))
* Removed deprecated code ([fad32c6](https://github.com/roadiz/documents/commit/fad32c6aca7baefe5f29b497fe67d71099a11cfd))
* Removed Packages usage in favor of FlysystemOperator ([f98de49](https://github.com/roadiz/documents/commit/f98de49cede087412ba1fcfb7e4f0146a828bba4))
* Rewrote EmbedFinderFactory using chain of responsability pattern ([d5b5668](https://github.com/roadiz/documents/commit/d5b566886b2dd47086f9b5d93f3ba829e7aaeb20))
* Use BinaryFileResponse to avoid serving ZIP file from memory ([8273e2b](https://github.com/roadiz/documents/commit/8273e2ba67db9787d7d607b45217086c930b6712))

### Bug Fixes (Documents)

* Allow overriding AbstractEmbedFinder getIframe method to render other html tag ([5212f2d](https://github.com/roadiz/documents/commit/5212f2dfc883c9e6e36f85337bf2466e73c139ca))
* Do not generate URL for a private document ([63f2771](https://github.com/roadiz/documents/commit/63f277174a1599036adfcdc7ef1d04d2e52527d1))
* Wrong callable URL for supportEmbedUrl ([6dc87f1](https://github.com/roadiz/documents/commit/6dc87f14f6b3614c8e103015fadad02f3724fec1))

### Features (Rozier)

* Added NodesSources noIndex boolean form field and translations ([aa466b4](https://github.com/roadiz/rozier/commit/aa466b462b1156fdff0b55a9e594018f43a26eb9))
* Added templates titles ([a4aa851](https://github.com/roadiz/rozier/commit/a4aa851dc4e192a9c56669e0d122610066572815))
* Big roadiz/documents namespace refactoring ([455f1b3](https://github.com/roadiz/rozier/commit/455f1b34cf4e6ff739c9efbd01d93ac5fb505f96))
* Dispatch events on Settings edition ([5abf8a2](https://github.com/roadiz/rozier/commit/5abf8a29f55c64aece32dc44d52e9e469e4ed4e4))
* Increase max length fro seo title to 80 chars ([f89bb0a](https://github.com/roadiz/rozier/commit/f89bb0a95f17da1a83cee67fb217fa5488a58418))
* Migrate schema update from routing to async message bus ([2d96ddf](https://github.com/roadiz/rozier/commit/2d96ddfc7ed3bd106aad0bc6e9c58728e330f559))
* Removed Font domain logic ([0c274d8](https://github.com/roadiz/rozier/commit/0c274d84945d854ac15d36f5008acb1265f38c3b))
* Removed Packages usage in favor of FlysystemOperator ([77ba080](https://github.com/roadiz/rozier/commit/77ba08091072900979e3530fcc16b4eaa579f471))
* Retry loading mainNodeTree during DB schema updates ([f8b53b3](https://github.com/roadiz/rozier/commit/f8b53b394b5e87383d918295f50bf34d8dd43789))
* **Settings:** Removed useless settings and moved required settings into Configuration and DotEnv variables ([cb5433e](https://github.com/roadiz/rozier/commit/cb5433e214c6e3ff40d50c7001ce8c9b46adbf6c))
* Use EmbedFinder shortType to display embed icons, overflow hidden on SVG thumbnails ([feefa99](https://github.com/roadiz/rozier/commit/feefa990b8f53e8a2c6d64e6d10ac2613724c6ed))
* Use GeoJson data structure for Geo and MultiGeo types ([133d1ab](https://github.com/roadiz/rozier/commit/133d1ab6132b9f0cf52b306ee40fb41b05fdf36e))
* Use valid GeoJSON FeatureCollection for multi-geo coordinates field ([c81e6df](https://github.com/roadiz/rozier/commit/c81e6df4e88cf41a3e93639f7e69bf660cd34228))
* **User:** Added User publicName form ([a4df5c0](https://github.com/roadiz/rozier/commit/a4df5c004e070d5852d5c3d4b465dad8e6111dfd))
* **Validation:** Removed redundant form field validators ([635683a](https://github.com/roadiz/rozier/commit/635683a32d4b91e64cb3b2c5ed58d7be3789a779))
* **WarningModal:** Separate warning modal for disconnected and health-check. Remove modal if service is up again ([9b5250b](https://github.com/roadiz/rozier/commit/9b5250be132c01bf6f2ab10bb4c03e0f102afca1))
* **WarningModal:** Translation messages ([6f8e9f5](https://github.com/roadiz/rozier/commit/6f8e9f5c09ae9f45ba8d741b06a8016f195eeb22))


### Bug Fixes (Rozier)

* Allow creating Leaflet marker with coordinates to zero ([76d1927](https://github.com/roadiz/rozier/commit/76d19275ae98358145a91cb76b0a3ba7b32b1923))
* Delete exported binaryfiles after sent ([66fa3ef](https://github.com/roadiz/rozier/commit/66fa3ef3fba9cc2efdd27d1c4e21dd16ff36aacd))
* Do not generate preview thumbnail for private documents ([9aa7f85](https://github.com/roadiz/rozier/commit/9aa7f8513b93d39d439a55811a6778b54c1d4b31))
* Do not publish any message on setting grouped edition forms, it may lead to flushing invalid form ([92faccf](https://github.com/roadiz/rozier/commit/92faccfa13bebc7bddba1bab8e317aa244081c3d))
* NodeSourceProviderType must be always multiple for multi or single data provider. ([37f411b](https://github.com/roadiz/rozier/commit/37f411b6cf245f8f946eaaf05c8852546a7a0fce))
* Use form value instead of data in Form Widget Drawer ([1d7ccce](https://github.com/roadiz/rozier/commit/1d7ccce4d5523ab4bb7b5dd29c4c205405079f24))
* Wrong form type ([2acc139](https://github.com/roadiz/rozier/commit/2acc1399413b56387807cdae6f86bda17894140b))

## 2.0.44 (2023-02-09)


### Bug Fixes

* Add documentTranslation into document during persisting loop to allow fetching before flushing ([cc94cbe](https://github.com/roadiz/core-bundle/commit/cc94cbe0adf9d959cdcc42bcd216faf6bbfc66c9))

## 2.0.43 (2023-01-26)


### Bug Fixes

* Enforce custom-form validation for API endpoints ([055201d](https://github.com/roadiz/core-bundle/commit/055201d8697c154f0f3bd631d78711020b9491bb))
* Removed static return type for php74 compatibility ([ba0473a](https://github.com/roadiz/core-bundle/commit/ba0473a251e9b91c94044844891e06e8a6d0d3c9))

## 2.0.42 (2023-01-16)

### Bug Fixes

* Format CustomFormAttribute value to Date or DateTime string ([86df706](https://github.com/roadiz/core-bundle/commit/86df7063f429fd334022d93b6471fbfefdd1636c))

## 2.0.41 (2023-01-11)

### Bug Fixes

* Fixed recursive findEmailData to find "email" key in a complex form data ([44a9a89](https://github.com/roadiz/core-bundle/commit/44a9a899a31c5a727eb36d3940bc78ded89a4254))

## 2.0.40 (2022-11-22)

### Features

* Added tags and folders position field to serialization ([4a92a02](https://github.com/roadiz/core-bundle/commit/4a92a0209a92313482d57131a303e582a38fb716))

## 2.0.39 (2022-11-09)

### Bug Fixes

* Missing allowRequestSearching condition on EntityListManager ([6e0f606](https://github.com/roadiz/core-bundle/commit/6e0f606803ab36a79e8f9384bfc970bdd53a7c1b))

## 2.0.38 (2022-11-09)

### Bug Fixes

* Make EntityListManager request sorting and searching optional for security purposes ([5103cf6](https://github.com/roadiz/core-bundle/commit/5103cf6d5bbc1e64b7a7685339cdf04ac37ffd4f))

## 2.0.37 (2022-10-03)

### Bug Fixes

* **Normalization:** Set higher priority for `NodesSourcesPathNormalizer` ([05498d2](https://github.com/roadiz/core-bundle/commit/05498d24bada43db5f0eb3d512a19a0606cb5001))

## 2.0.36 (2022-09-30)

### Features

* **NodeType:** Automatically store JSON serialized node-type in project and update `./src/Resources/config.yml` file ([2e1dc8a](https://github.com/roadiz/core-bundle/commit/2e1dc8a99d502719aabc6d16f7667d7bc909425d))
* **NodeType:** Remove JSON node-type field and update `./src/Resources/config.yml` after Node-type deletion ([32a325f](https://github.com/roadiz/core-bundle/commit/32a325f29ded18b0c0ea4a5ff0349743efb69974))
* **Translation:** Let user choose source and destination translations ([8426fa0](https://github.com/roadiz/core-bundle/commit/8426fa0ab88d2061acc636c7c636a18403cc6b07))

## 2.0.35 (2022-09-28)

### Features

* Upgrade to `roadiz/documents` 2.0.6 minimum
* Dispatch async document messages only for DocumentFileUpdatedEvent, dispatch DocumentCreatedEvent on custom-form post ([e008da6](https://github.com/roadiz/core-bundle/commit/e008da6643b417a69e411d1d62891fdeefae49df))

## 2.0.34 (2022-09-21)

### Bug Fixes

* **Constraints:** Missing `CLASS_CONSTRAINT` for NodeTypeField class constraint ([08246c7](https://github.com/roadiz/core-bundle/commit/08246c7801ed0d7831ecace9ed49e00f2c7d82b2))

## 2.0.33 (2022-09-20)

**Reverting**: https://github.com/api-platform/core/issues/4988

### Bug Fixes

* **api-platform:** Revert to api-platform/core 2.6 ([1a23f28](https://github.com/roadiz/core-bundle/commit/1a23f282a123016260320358d6d93f86f80467d1))

## 2.0.32 (2022-09-20)

### Features

* **Solr:** Differentiate tags_txt for visible Folders/Tags and all_tags_txt for filtering Solr queries. ([9d0c7e5](https://github.com/roadiz/core-bundle/commit/9d0c7e59b0eeb6e60b921560028265e130e1e51d))

## 2.0.31 (2022-09-19)

### Bug Fixes

* **Translation:** NodesSourcesPathResolver must not use unavailable translations unless preview mode is active ([459580b](https://github.com/roadiz/core-bundle/commit/459580b7d1d2eb448473d30718901e3ad31a3a93))

## 2.0.30 (2022-09-19)

Bug fixes due to *api-platform/core* upgrade to 2.7.0

### Bug Fixes

* **api-platform:** Fixed ArchiveExtension `applyToCollection` method which was not restricted to archive operations ([6bfa530](https://github.com/roadiz/core-bundle/commit/6bfa530c1a2859f02f2b03593fe7f636be78ea3a))
* **api-platform:** Increased `ArchiveExtension` service tag priority to be called before `PaginationExtension` ([3a7158a](https://github.com/roadiz/core-bundle/commit/3a7158ad6d47e052c008a93ae4a2e47f6246493e))
* **api-platform:** Address nullable RequestStack in `AbstractFilter` constructor ([de9b901](https://github.com/roadiz/core-bundle/commit/de9b90161e75d9933c2edab34e817b1815d762fa))
* **api-platform:** Fixed `AbstractFilter` deprecation using `AbstractContextAwareFilter` ([a85db5d](https://github.com/roadiz/core-bundle/commit/a85db5de112a12ab2b9211770d86dbf09f9ada70))

## 2.0.26 (2022-09-16)

### Features

* Migrated constraints from Symfony forms to global entity validation ([11741a3](https://github.com/roadiz/core-bundle/commit/11741a384fba8d630fc744034e506b1bf15c8d17))

## 2.0.25 (2022-09-15)

### Features

* Added Flex manifest and updated config files ([8ace107](https://github.com/roadiz/core-bundle/commit/8ace107e2a0448f13dec1af06f6c94ab6756706c))
* Added PathResolverInterface::resolvePath `$allowNonReachableNodes` arg to restrict path resolution to reachable node-types ([d78754d](https://github.com/roadiz/core-bundle/commit/d78754d8708e4584e9f8dd26b2d8ec391c3e7afd))
* Added `public` and `themes` dir in flex manifest ([305800d](https://github.com/roadiz/core-bundle/commit/305800dda9004505d622cc7413622c4a71cbf07b))

### Bug Fixes

* Missing default configuration value for `healthCheckToken` ([28668c4](https://github.com/roadiz/core-bundle/commit/28668c43591d3b1ef7f9b3472f8f1be074c69543))

## 2.0.24 (2022-09-07)

### Features

* Added `DocumentVideoThumbnailMessageHandler` to wrap `ffmpeg` process and extract videos first frame as thumbnail ([4b7d096](https://github.com/roadiz/core-bundle/commit/4b7d0969a772717c077cf9b915388dbf98776254))
* `ImageManager` is registered as a service to use app-wise configured driver ([cfa0b84](https://github.com/roadiz/core-bundle/commit/cfa0b845dda1fc9a101916e502ac201761797d68))
* Moved all document processes from event-subscribers to async messenger, read AV media size and duration ([251b9b5](https://github.com/roadiz/core-bundle/commit/251b9b5dc514a4177765200822544ef1d5a06d68))

### Bug Fixes

* Revert registering ImageManager as service since rezozero/intervention-request-bundle does it ([064c865](https://github.com/roadiz/core-bundle/commit/064c865678cd03d69985b6346436f834b56cd5d5))

## 2.0.23 (2022-09-06)

### Bug Fixes

* Force int progress start ([24247d2](https://github.com/roadiz/core-bundle/commit/24247d2bc99058f02a7e1b5f19ddc24ae55f7a07))
* Upgraded rezozero Liform to handle properly FileType multiple option ([cd1b147](https://github.com/roadiz/core-bundle/commit/cd1b147b7308c3a94dee0f9a78840907001438e8))

## 2.0.22 (2022-09-06)

### Bug Fixes

* Folder names and Tags names must be quoted in Solr filter query parameters ([d68d9b5](https://github.com/roadiz/core-bundle/commit/d68d9b51f1e507c3c57ec8c09ca1ca3f5fdd4264))

## 2.0.21 (2022-09-06)

### Bug Fixes

* Always index all documents folder names to Solr, not only visible ones (i.e. to restrict documents search with an invisible folder) ([c76fffc](https://github.com/roadiz/core-bundle/commit/c76fffcaa61e5cc60d19b271c3d638aebb3c166f))

## 2.0.20 (2022-09-01)

### Features

* Added Folder `locked` and `color` fields, improved table indexes ([b8f344d](https://github.com/roadiz/core-bundle/commit/b8f344db0fcadbed3532127812467a5f295f061a))
* Improved AbstractExplorerItem and AbstractExplorerItem ([66386d6](https://github.com/roadiz/core-bundle/commit/66386d6c5a63828577f2ddf24ad58296b8b379de))

## 2.0.19 (2022-08-29)

### Bug Fixes

* Updated *rezozero/tree-walker* in order to extend `AbstractCycleAwareWalker` and prevent cyclic children collection ([eb80381](https://github.com/roadiz/core-bundle/commit/eb80381738f7fa90cf1aa466827522982d4a2036))

## 2.0.18 (2022-08-05)

### Bug Fixes

* Missing validator translation message ([33648a3](https://github.com/roadiz/core-bundle/commit/33648a3b1b36010459d39dba73929a018700dece))
* **Security:** Use QueryItemExtension and QueryCollectionExtension to filter out non-published nodes-sources and raw documents ([f7c4688](https://github.com/roadiz/core-bundle/commit/f7c4688eee09034c7317de7c3fd01be7845e4f1d))

## 2.0.17 (2022-08-02)

### Bug Fixes

* **SearchEngine:** Use `Solarium\Core\Client\Client` instead of `Solarium\Client` because it's not compatible with Preload (defined constant at runtime) ([320df16](https://github.com/roadiz/core-bundle/commit/320df160182464f2aa35a82813f1676ce428d59c))

## 2.0.16 (2022-08-01)

### Bug Fixes

* **Document:** Fixed context groups undefined key ([8bbdc31](https://github.com/roadiz/core-bundle/commit/8bbdc313b29ecebf2ef594aec03cb30d7b487ea9))

## 2.0.15 (2022-08-01)

### Bug Fixes

* **Document:** Fixed document DTO thumbnail when document is Embed (it's an image too because an image has been downloaded from platform) ([0d7fef4](https://github.com/roadiz/core-bundle/commit/0d7fef4ed44fc2f2867eaf5ea54efb189bda404a))

## 2.0.14 (2022-08-01)

### Bug Fixes

* **ArchiveFilter:** Prevent normalizing not-string values ([b1fe49e](https://github.com/roadiz/core-bundle/commit/b1fe49ea909ec59170c5c5cf13a03465ceab901a))

## 2.0.13 (2022-07-29)

### Bug Fixes

* Remove useless eager join on document downscaledDocuments on DocumentRepository ([d821586](https://github.com/roadiz/core-bundle/commit/d82158616c6e8259c9264715e458bf1e2f0ccdb7))

## 2.0.12 (2022-07-29)

### Bug Fixes

* **Serializer:** Ignore can only be added on methods beginning with "get", "is", "has" or "set" ([78b52aa](https://github.com/roadiz/core-bundle/commit/78b52aa794413b73f67b08efad787300f6ebf07a))

## 2.0.11 (2022-07-29)

### Features

* Added `altSources` to Document DTO and optimize document downscaled relationship ([82a5fd6](https://github.com/roadiz/core-bundle/commit/82a5fd6cd0e37f15bff81655d34f63f9b2897fb3))

## 2.0.10 (2022-07-29)

### Bug Fixes

* DocumentFinder now extends AbstractDocumentFinder ([670516a](https://github.com/roadiz/core-bundle/commit/670516a9fbbdb7d312c356acc7f5626059f2150d))

## 2.0.9 (2022-07-25)

### Bug Fixes

* **SearchEngine:** Do no trigger error on Solr messages if Solr is not available ([785c559](https://github.com/roadiz/core-bundle/commit/785c5593db7a0fa4a3b11e3d277a035ff63d2361))

## 2.0.8 (2022-07-21)

### Bug Fixes

* Do not index empty arrays since [solariumphp/solarium 6.2.5](https://github.com/solariumphp/solarium/issues/1023) breaks empty array indexing ([c9da177](https://github.com/roadiz/core-bundle/commit/c9da177fd9af28e273048373f45c846ec8ca75d7))

## 2.0.7 (2022-07-20)

### Features

* Added new `NodeTranslator` service and remove dead code on User entity ([7f211c5](https://github.com/roadiz/core-bundle/commit/7f211c5354dac0ec953138a514e5d4e82f06e41f))

## 2.0.6 (2022-07-20)

### Bug Fixes

* Attach documents to custom-form notification emails ([c213e87](https://github.com/roadiz/core-bundle/commit/c213e87f9095ac1e21bda17c08cf7d5f389dff7b))

## 2.0.5 (2022-07-13)

### Features

* Added `NotFilter` ([29a608d](https://github.com/roadiz/core-bundle/commit/29a608d76782a68ddfa2e25b7e4ab5e8081cd3e2))
* Purge custom-form answers **documents** as well when retention time is over. ([a00a619](https://github.com/roadiz/core-bundle/commit/a00a619b5458c443f5099e59dfa964518d49e88d))

## 2.0.4 (2022-07-11)

### ⚠ BREAKING CHANGES

* WebResponseInterface now requires `getItem(): ?PersistableInterface` method to be implemented.

### Bug Fixes

* Set context translation from a WebResponseInterface object ([fbde288](https://github.com/roadiz/core-bundle/commit/fbde288f157f6c2bd84aadb786a8f23ed73300c2))

## 2.0.3 (2022-07-06)

### Bug Fixes

* Mailer test command sender and origin emails ([ae26d01](https://github.com/roadiz/core-bundle/commit/ae26d014fcf62c878f3c9e08c260313b9d855752))

## 2.0.2 (2022-07-05)

### Features

Added true filtrable archives endpoint extension for any Doctrine entities ([597803d](https://github.com/roadiz/core-bundle/commit/597803d37cb324c3d7076f323a5821d497e9fbf5)).
You need to add a custom collection operation for each Entity you want to enable archives for:

```yaml
# config/api_resources/nodes_sources.yml
RZ\Roadiz\CoreBundle\Entity\NodesSources:
    iri: NodesSources
    shortName: NodesSources
    collectionOperations:
        # ...
        archives:
            method: 'GET'
            path: '/nodes_sources/archives'
            pagination_enabled: false
            pagination_client_enabled: false
            archive_enabled: true
            archive_publication_field_name: publishedAt
            normalization_context:
                groups:
                    - get
                    - archives
            openapi_context:
                summary: Get available NodesSources archives
                parameters: ~
                description: |
                    Get available NodesSources archives (years and months) based on their `publishedAt` field
```


## 2.0.1 (2022-07-05)

### Features

* Added `IntersectionFilter` to create intersection with tags and folders aware entities. ([25c1dc5](https://github.com/roadiz/core-bundle/commit/25c1dc54b46dfadc02ed17b8e9de892eed784d73))

## 2.0.0 (2022-07-01)

### ⚠ BREAKING CHANGES

* `LoginRequestTrait` using Controller must implement getUserViewer() method.
* You must now define getByPath itemOperation for each routable API resource.
* Solr handler must be used with SolrSearchResults (for results and count)
* Rename @Rozier to @RoadizRoadiz

### Features

* Added Realm, RealmNode types, events and async messenging logic to propagate realms relationships in node-tree. ([c53cbec](https://github.com/roadiz/core-bundle/commit/c53cbec87f03178ed7e9f9ea8969426ab332ed33))
* Accept Address object as receiver ([4f5f925](https://github.com/roadiz/core-bundle/commit/4f5f925cf50a9e66f3be4db0d0e3f605465143c6))
* Add node' position to its DTO ([6dae0d6](https://github.com/roadiz/core-bundle/commit/6dae0d6f53c2bc431315bac294cef5ac1970193d))
* Added `--dry-run` option for documents:files:prune command ([8b61694](https://github.com/roadiz/core-bundle/commit/8b616942dd9967aa26a2e1844fc544edbfd09fcf))
* Added CircularReferenceHandler to expose only object ID ([8c9ddbd](https://github.com/roadiz/core-bundle/commit/8c9ddbd89210b9c88a1d9c7af3ff03d5fd8706d8))
* Added custom-form retention time ([22383e9](https://github.com/roadiz/core-bundle/commit/22383e91eb140c61dc019447536f2be2e90a0488))
* Added default NodesSources search and archives controller ([b8ff98b](https://github.com/roadiz/core-bundle/commit/b8ff98b4e0048bfec2ec178a0c6d7660ff5c6ccf))
* Added document CLI command to hash files and find duplicates ([d138a2a](https://github.com/roadiz/core-bundle/commit/d138a2ab805494de3e74a8977499603180c636d8))
* Added document Dto width, height and mimeType ([62958a3](https://github.com/roadiz/core-bundle/commit/62958a3091c9c90e15b5353088cbbdb8fa2ff229))
* Added DocumentRepository alterQueryBuilderWithCopyrightLimitations method ([637a0b7](https://github.com/roadiz/core-bundle/commit/637a0b7bbf37a0501dcb81649eee1c9943e89459))
* Added documents file_hash and file_hash_algorithm for duplicate detection. ([4549ada](https://github.com/roadiz/core-bundle/commit/4549ada40dba6d762bf85bf62cf401e265c2d176))
* Added generate:api-resources command ([25fd64c](https://github.com/roadiz/core-bundle/commit/25fd64c322c932b49c9ab1c5575993e338806760))
* Added HealthCheckController and appVersion config ([54bf276](https://github.com/roadiz/core-bundle/commit/54bf276bf3d710e4e3226744b20ce387958227f8))
* Added lexik_jwt_authentication ([bd5826d](https://github.com/roadiz/core-bundle/commit/bd5826d168b7373feb4eebb769fbf0b53d8a5575))
* Added missing Document DTO externalUrl ([cbce6f1](https://github.com/roadiz/core-bundle/commit/cbce6f19e35363438b57d8b67264d3cac5981512))
* Added new Archive filter on datetime fields ([0bae8d3](https://github.com/roadiz/core-bundle/commit/0bae8d3efe562fb81b6da051c11842aeb0c09165))
* Added new Document copyrightValidSince and Until fields to restrict document display. ([40a31c2](https://github.com/roadiz/core-bundle/commit/40a31c2e4ebc8c313ee6433c12c66403a436728e))
* Added new role ROLE_ACCESS_DOCUMENTS_LIMITATIONS ([bc564fd](https://github.com/roadiz/core-bundle/commit/bc564fd8d3e5c8ed853c76354a89ed44f359fdca))
* Added new role: ROLE_ACCESS_CUSTOMFORMS_RETENTION ([b3586c4](https://github.com/roadiz/core-bundle/commit/b3586c4c57fc5869dac226b9fb81e1e0b2cd24fb))
* Added new UserJoinedGroupEvent and UserLeavedGroupEvent events ([e12d6e4](https://github.com/roadiz/core-bundle/commit/e12d6e4be12e89fdab1bf31e64c77c7329d2a2bb))
* Added node-source archive operation logic (without filters) ([994d9bc](https://github.com/roadiz/core-bundle/commit/994d9bc14fe334168f5868dab2ba7d2ecf203bdd))
* Added OpenId authenticator ([5cf4383](https://github.com/roadiz/core-bundle/commit/5cf43836f9a8a95ed97aacf0f3a412169b34f52d))
* Added preview user provider and JwtExtensiont to generate secure jwt for frontend previewing ([76d81c0](https://github.com/roadiz/core-bundle/commit/76d81c0799a3df0c93c45556cc56adedae9bd1d7))
* Added Realm and RealmNode entities for defining security policies inside node tree ([99ad2a5](https://github.com/roadiz/core-bundle/commit/99ad2a53051ca9c96dab9d3e908b5c1ebf0491c8))
* Added Realm Security Voter, Normalizer and WebResponse logic ([f35083e](https://github.com/roadiz/core-bundle/commit/f35083ed2269718be149ec477d0a3178b2ae8a13))
* Added RealmResolverInterface to get Nodes realms and check security ([6fe7a00](https://github.com/roadiz/core-bundle/commit/6fe7a00d21991d70fdbdcc5934c8662cfafed181))
* Added RememberMeBadge ([1cf563c](https://github.com/roadiz/core-bundle/commit/1cf563c22d95f484e7721880141043907c5f5894))
* Added Solr document search `copyrightValid` criteria shortcut ([66f4215](https://github.com/roadiz/core-bundle/commit/66f4215497c45ad319f8bcbbfdd1ccd74ec7c560))
* Added translation in serializer context from _locale ([db0b45b](https://github.com/roadiz/core-bundle/commit/db0b45bfc611947b92d650dc34e8be72fad23ba1))
* Added Translation name to Link header to build locale switch menus ([9785438](https://github.com/roadiz/core-bundle/commit/978543882cb96b3e89063703485d0ff913e9cfa2))
* Added validation constraints and groups directly on User entity ([82e01f3](https://github.com/roadiz/core-bundle/commit/82e01f3e8fca7e3078afbe7a44f4d940b0a8079e))
* Added validators translation messages ([a61b9d8](https://github.com/roadiz/core-bundle/commit/a61b9d80c149540f6567f23bd1471a361eff514c))
* Configured `rezozero/crypto` to use encoded settings ([32f59d6](https://github.com/roadiz/core-bundle/commit/32f59d65064785560d4d9c01857c8e3d9285b3b8))
* ContactFormManager flatten form data with real form labels and prevent g-recaptcha-response to be sent ([53e7c9d](https://github.com/roadiz/core-bundle/commit/53e7c9df23741c0eab71f66811159801149dad65))
* Deprecated LoginAttemptManager in favor of built-in Symfony login throttling ([2d4a10e](https://github.com/roadiz/core-bundle/commit/2d4a10ec97969364ba5e829c650e3321a59d3607))
* Do not index not visible tags and folder into Solr ([d8fc516](https://github.com/roadiz/core-bundle/commit/d8fc5167371941ed44530186abcd49a904d773e1))
* Do not search for a locale if first token exceed 11 chars ([9f614d8](https://github.com/roadiz/core-bundle/commit/9f614d8b1c1f39a78d9dc6095eb4284161f7c979))
* **Document:** Added document-translation external URL and missing DB indexes ([c346f7b](https://github.com/roadiz/core-bundle/commit/c346f7bdddeb670b30997d3884ef7ec1ff987efb))
* **documents:** Added mediaDuration to Document DTO ([41673d6](https://github.com/roadiz/core-bundle/commit/41673d63ecc50dcf5ac76e6d823a8d224421326e))
* Filter and Sort Translations by availability, default and locale ([47605f8](https://github.com/roadiz/core-bundle/commit/47605f8cd4900cc4d50de2ccc6294abb2899510b))
* find email from any contact form compound ([3d55930](https://github.com/roadiz/core-bundle/commit/3d559300dc57acf6b20244f947b4c12fa1d386d3))
* Force API Platform to look for real resource configuration and serialization context ([9ee9f42](https://github.com/roadiz/core-bundle/commit/9ee9f42a098340344a73746585887a011a0b561a))
* FormErrorSerializerInterface and RateLimiters ([31c6ca8](https://github.com/roadiz/core-bundle/commit/31c6ca84c6576abb5240d7db8170cd7d53b2c869))
* Index documents copyright limitations dates ([f6bbf0b](https://github.com/roadiz/core-bundle/commit/f6bbf0b50181bab746d6b80dad9c33d4ea6bfebc))
* Jwt authentication supports LoginAttemptManager ([eee4c08](https://github.com/roadiz/core-bundle/commit/eee4c083d0c0223e02bfbc2e4f4ff4a0c6f0fdc8))
* LoginRequestTrait requires UserViewer ([544cdcc](https://github.com/roadiz/core-bundle/commit/544cdcc5c26a364a5b21d2ef99492a6d9feb0691))
* Made alterQueryBuilderWithAuthorizationChecker method public to use it along with Repository ([c567cc5](https://github.com/roadiz/core-bundle/commit/c567cc5cbf96bc86fc6a724e833fc24edff434c2))
* Made AutoChildrenNodeSourceWalker overridable and cacheable ([226ae1a](https://github.com/roadiz/core-bundle/commit/226ae1a10ac30474e24d8e9bef7f082703b016f3))
* Made WebResponse as an API Resource ([b778647](https://github.com/roadiz/core-bundle/commit/b778647dd6561d74e730746bf478650178936673))
* migrate custom-forms preview to Recaptcha v3 script ([1b1bfb1](https://github.com/roadiz/core-bundle/commit/1b1bfb15198f8ceea6954e2e99b5344417bd6d94))
* Moved all OpenID logic to RoadizRozierBundle as it only supports authentication to backoffice. ([0171cbb](https://github.com/roadiz/core-bundle/commit/0171cbb5492ea52c9292c41d92a5895b071db95c))
* Moved Security/Authentication/OpenIdAuthenticator to roadiz/openid package ([4f4e391](https://github.com/roadiz/core-bundle/commit/4f4e3919cc549fb824036010ec1414a865c0488d))
* New DocumentArchiver util to create ZIP archive from documents ([be8a35f](https://github.com/roadiz/core-bundle/commit/be8a35f86f264932ee32964036a81cd56815ab8c))
* NodesSourcesPathResolver can resolve home faster, and resolve default translation based on Accept-Language HTTP header ([00db41a](https://github.com/roadiz/core-bundle/commit/00db41a3e252f1464dbd99cf417da8628273e31b))
* Nullable discovery openId service ([ff555c3](https://github.com/roadiz/core-bundle/commit/ff555c33734d5b564dc248c105cb277dbde2dfbe))
* Only requires symfony/security-core ([f094ca2](https://github.com/roadiz/core-bundle/commit/f094ca260b0ccf6dc0c17920a697aa09131f04a3))
* Optimize NodesSourcesLinkHeaderEventSubscriber with repository method ([a4e5e37](https://github.com/roadiz/core-bundle/commit/a4e5e37fe389d55a6c695f9d880d85d181452c3c))
* postUrl for custom-form dto transformer ([7b3f00f](https://github.com/roadiz/core-bundle/commit/7b3f00fb7ad2d26f4091c87619cf5d0120d04ad2))
* **redirections:** Use recursive path normalization to normalize redirection to redirection ([4b4b03f](https://github.com/roadiz/core-bundle/commit/4b4b03fd7cec11b3462a63ca026d1710af635bd1))
* Refactored document search handler and removed deprecated ([4cfb5df](https://github.com/roadiz/core-bundle/commit/4cfb5dfe58daf0cdcec08629d532fba844ea93c1))
* refactored Document translation indexing using DocumentTranslationIndexingEvent, and make it document indexing overridable ([2f4126c](https://github.com/roadiz/core-bundle/commit/2f4126c02420426d0d2ebe49292c3f9c9d0214b0))
* Removed hide_roadiz_version parameter from DB to remove useless DB query on assets requests ([b7ad3a7](https://github.com/roadiz/core-bundle/commit/b7ad3a71190abc85ea24498fb92e9a5f69ffd707))
* Rename @Rozier to @RoadizRoadiz ([a5ebc4a](https://github.com/roadiz/core-bundle/commit/a5ebc4a7d1fb532165edca1dd36dbd65461c59da))
* Search existing realm-node root or inheritance when editing a node (moving) ([c718059](https://github.com/roadiz/core-bundle/commit/c71805978c1406ff3fa55e4fc2b22f49b80fa1bd))
* Serialize tag parent in DTO ([c48fb11](https://github.com/roadiz/core-bundle/commit/c48fb11681c44b8cc0a7126c58ce1a065b30a728))
* set real _api_resource_class for GetWebResponseByPathController ([f9c0804](https://github.com/roadiz/core-bundle/commit/f9c080447501baf988f027aa719abd77c4676724))
* Simplify UserLocaleSubscriber ([c79bf80](https://github.com/roadiz/core-bundle/commit/c79bf80d5537a1d10d2cd8e25d9616bc67eb25ee))
* Support exception in Log messages context ([3159a6c](https://github.com/roadiz/core-bundle/commit/3159a6c40aca76ccbb86bacf599157c961958559))
* Support non-available locales if previewing ([72cdf19](https://github.com/roadiz/core-bundle/commit/72cdf19563741fa5ed97f44f2d41a9ade9737f4b))
* Support non-available locales if previewing ([ca1ac63](https://github.com/roadiz/core-bundle/commit/ca1ac631206dcb38cc7ee32f066a45c553c97839))
* UniqueNodeGenerator: If parent has only one translation, use parent translation instead of default one for creating children ([d463d02](https://github.com/roadiz/core-bundle/commit/d463d024a171e245ed06f7b1d2b201dae1bf623e))
* Use a factory to create NodeNamePolicyInterface with settings; ([a5a9b9d](https://github.com/roadiz/core-bundle/commit/a5a9b9d90a49935f2a2f5647c9d75ac5f261707e))
* UserProvider support searching user by username or email ([4e6ad3c](https://github.com/roadiz/core-bundle/commit/4e6ad3c74fa56c4a823fc9c01f222d42bf8ee4dd))


### Bug Fixes

* Accept nullable DocumentOutput relativePath and mimeType ([910bc8f](https://github.com/roadiz/core-bundle/commit/910bc8fb10861eb38eadc3488349894efd56dc05))
* Added Assert annotations on User entity for API platform validation ([7685f8e](https://github.com/roadiz/core-bundle/commit/7685f8e0496a16dc0bd989285afb7b2c2a4b110c))
* Added email default sender name with Site name ([d9842ac](https://github.com/roadiz/core-bundle/commit/d9842ac654fc601690c898435822709a1a258414))
* Added getByPath itemOperation into generate command ([220c291](https://github.com/roadiz/core-bundle/commit/220c291de268902071e82e5a8b26c07f5cc75c1e))
* allow null string in AbstractSolarium::cleanTextContent ([aa418fc](https://github.com/roadiz/core-bundle/commit/aa418fccacc4cb7995badd070a6efb176428210d))
* Cache pools to clear ([0cd36c0](https://github.com/roadiz/core-bundle/commit/0cd36c073f8ef35f89da887c1250db6441464344))
* Casting attribute value to string when number input ([5263140](https://github.com/roadiz/core-bundle/commit/5263140cf9c08f79af32ae6e34f47adc84084df9))
* Change Discovery definition argument ([bed0e1b](https://github.com/roadiz/core-bundle/commit/bed0e1b3db9471203097eb79abf1243649c44692))
* Changed LocaleSubscriber priority ([de920ed](https://github.com/roadiz/core-bundle/commit/de920ed3c7aa6b93b2c931885152171b2ce17f52))
* clear existing cache pool in SchemaUpdater ([62a01af](https://github.com/roadiz/core-bundle/commit/62a01af1162ec4a91b9822d323172a1da44ad528))
* Configuration tree type ([de47a3c](https://github.com/roadiz/core-bundle/commit/de47a3cf4a5b8e698ae24a4883cc33e46feebc39))
* Context getAttribute comparison ([016df90](https://github.com/roadiz/core-bundle/commit/016df902f7710db0f04115639be744a415112298))
* do not set api resource GetWebResponseByPathController, it breaks serialization context ([49ccf2f](https://github.com/roadiz/core-bundle/commit/49ccf2f9f3720360b7e21955cd799b27a59afc71))
* Doctrine batch iterating for Solr indexing ([255699a](https://github.com/roadiz/core-bundle/commit/255699ab0dfb6068d39b58fc178d7b08c7eb28b8))
* Fix emptySolr query syntax ([717458f](https://github.com/roadiz/core-bundle/commit/717458f0538752736cf7fa4aa05fa80a0055e0a1))
* Fix ExplorerItemProviderType using asMultiple instead of special multiple option. ([8fdc3da](https://github.com/roadiz/core-bundle/commit/8fdc3da5019d0cb0cb6b81ee04d4e7f91ec8e513))
* Ignore getDefaultSeo ([e2f1a57](https://github.com/roadiz/core-bundle/commit/e2f1a578d046fa5afe3f81fafe8d46f62c3dbce8))
* Improved Recaptcha fields Contraint and naming ([4121345](https://github.com/roadiz/core-bundle/commit/4121345ca37626b5f091d2894068b4fb4e913d63))
* InversedBy relation, shorter log cleanup duration ([67c3ef0](https://github.com/roadiz/core-bundle/commit/67c3ef024e2305b51c15c63adad75a10bd8f06ee))
* Missing Liform transformers ([0e8cb2b](https://github.com/roadiz/core-bundle/commit/0e8cb2b33bba4d7799cc1bb8b728c4880ec50e6e))
* Missing RedirectionPathResolver to resolve redirections ([eaa99c7](https://github.com/roadiz/core-bundle/commit/eaa99c72a7838a4c59034b82c6be7d13f631fd60))
* missing trans on form error message ([68e38a6](https://github.com/roadiz/core-bundle/commit/68e38a6583c87774538e06dc88492188ea76e773))
* New SolariumDocumentTranslation constructor signature ([7f1f8b5](https://github.com/roadiz/core-bundle/commit/7f1f8b591a9bed2ccce55c5f6159392daab30a7d))
* NodesSourcesDto title must accept nullable string ([bf3dec2](https://github.com/roadiz/core-bundle/commit/bf3dec2487ef7f9e70eba445a85d123a0df38d85))
* non-existent cache.doctrine.orm.default.result cache pool ([e5bd921](https://github.com/roadiz/core-bundle/commit/e5bd9217f92dff7473552fbbd95146e16d0535a8))
* Nullable and strict typing AttributeValueTranslation ([4e4bb0a](https://github.com/roadiz/core-bundle/commit/4e4bb0a29b7c8146cc821698515d57b344829bd5))
* nullable custom-form email ([4fbeb58](https://github.com/roadiz/core-bundle/commit/4fbeb5844776e05d133dcd1bf0680401fee73056))
* Nullable roadiz_core.solr.client service reference ([2592084](https://github.com/roadiz/core-bundle/commit/2592084fc4cf79c8ff9942ceea55face1d8999ed))
* Only provide Link header for available translations or previewing user ([3255f58](https://github.com/roadiz/core-bundle/commit/3255f5896f582dd0a496de579fead2522c6e6633))
* OpenIdJwtConfigurationFactory configuration ([800b97b](https://github.com/roadiz/core-bundle/commit/800b97b291b22ff8fcc8763a709131420f451626))
* Prevent hashing non-existing document file ([5e92858](https://github.com/roadiz/core-bundle/commit/5e92858bb1fcdec60cd2ffeb0b1e18f2eaa97185))
* Remove NodeType repository class as well during deletion ([9c4c49e](https://github.com/roadiz/core-bundle/commit/9c4c49e1c5da2a056a9b7ac42819c94f2db3758a))
* Removed method dependency on UrlGeneratorInterface ([cbe9675](https://github.com/roadiz/core-bundle/commit/cbe9675ac20b59df2e0fd2595064a542e45468ef))
* removed optional cache-pools from SchemaUpdater ([ff2de22](https://github.com/roadiz/core-bundle/commit/ff2de227976e81f7abb5453f9e7ab8a607804303))
* Removed request option from Recaptcha constraint and using Form classes ([60a04d4](https://github.com/roadiz/core-bundle/commit/60a04d4e6582233cf08fac0dfd5f4c52b5f4c8eb))
* Rewritten node transtyper ([c1309dd](https://github.com/roadiz/core-bundle/commit/c1309ddbbdffbbf7b4613e33f41d5db1a35b1435))
* Roadiz LocaleSubscriber must be aware of _locale in query parameters too. ([65fe081](https://github.com/roadiz/core-bundle/commit/65fe0811cbefb2e0730ba6a4f7541c53ddd0b4bd))
* SolrDeleteMessage handler ([fefe729](https://github.com/roadiz/core-bundle/commit/fefe729a73684a816d9feffc0eac79c26c52af98))
* support ld+json exception ([fdce0d1](https://github.com/roadiz/core-bundle/commit/fdce0d102b82026ec9051723ea2168f720613cac))
* transactional email style for button ([c588994](https://github.com/roadiz/core-bundle/commit/c5889946d20b1fd583c7f7543fc65c8237f1429d))
* TranslationSubscriber dispatch CachePurgeRequestEvent ([4099304](https://github.com/roadiz/core-bundle/commit/409930477f91798b787d167ac0fd9b610d1373be))
* Uniformize custom-form error JSON response with other form-error responses ([a817fa4](https://github.com/roadiz/core-bundle/commit/a817fa4eeaf1eb7899fe27ad25e17566621bf508))
* unnecessary nullable ObjectManager ([218bbd1](https://github.com/roadiz/core-bundle/commit/218bbd15a4e236c20141eabb776aa84028e2d6b2))
* Update tag and document timestamps when translations were edited ([49774d8](https://github.com/roadiz/core-bundle/commit/49774d8deaa85d46103f8cfccc1f6fcb66a28c23))
* Use empty form name for custom-form ([202ea2a](https://github.com/roadiz/core-bundle/commit/202ea2a63c695ebc7a5d9a0432dc57852af09ce2))
* Use JSON status prop for status code, not message ([2a4f498](https://github.com/roadiz/core-bundle/commit/2a4f498aab74313b07f53ffbdc4ecb0db2adcd4b))
* Use Paginator to avoid pagination issues during Solr node indexing ([f43cae9](https://github.com/roadiz/core-bundle/commit/f43cae9d3ffab43a5a3fa70d1f3bdc29f8b18315))
* Use PriorityTaggedServiceTrait trait to respect tagged service priority ([4e31229](https://github.com/roadiz/core-bundle/commit/4e31229b8c84285fdc445050aafd2e7c7d6306c6))
* Use `rezozero/liform` fork ([863893c](https://github.com/roadiz/core-bundle/commit/863893c8c816289fcee7547d82f35eff8cd4fbeb))
* Use Security to create Preview user when isGranted ([133c6f8](https://github.com/roadiz/core-bundle/commit/133c6f889edb0f8d1ad9216bb54bfb6cb7560512))
* Use single_text widget for Date and DateTime customForm fields ([5bfa1d3](https://github.com/roadiz/core-bundle/commit/5bfa1d3bbc7a04e629fc2c63252f11a68d313dcc))
* User implements getUserIdentifier ([0355b1c](https://github.com/roadiz/core-bundle/commit/0355b1cf44911408d2467582dc64552d2d6ac3b4))

