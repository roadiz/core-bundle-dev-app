## 2.0.3 (2022-07-20)

### Features

* Added clone override method to clone proxies relationships ([fcc99d0](https://github.com/roadiz/entity-generator/commit/fcc99d07d105683deeeab4baafa480bb2ceae36f))

## 2.0.2 (2022-07-20)

### Bug Fixes

* Missing `$this->objectManager->persist($proxyEntity)` after creating proxy objects ([9e3fc09](https://github.com/roadiz/entity-generator/commit/9e3fc09491dc837a8e9dd123ba1baaf73e7ee6bc))

## 2.0.1 (2022-06-28)

### Features

* Added examples for many-to-many/one and proxied ([3a3ad1c](https://github.com/roadiz/entity-generator/commit/3a3ad1c17741b7b50bdf1c968c4797cb0431401a))
* All multi-valued fields should be JSON by default ([59e05f7](https://github.com/roadiz/entity-generator/commit/59e05f7d79288fa418dc4aa35406c5fc39ae81a7))

### Bug Fixes

* Missing SymfonySerializer\Ignore ([6e9876f](https://github.com/roadiz/entity-generator/commit/6e9876f6fc165687f80f825ea6a4989255737c5d))
* Multiple fields must be array or null ([319d84b](https://github.com/roadiz/entity-generator/commit/319d84be32420ad175dcc69bff675cbf26d9f2c4))

