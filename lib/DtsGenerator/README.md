# dts-generator
Roadiz sub-package which generates Typescript interface declaration skeleton based on your schema.

[![Unit tests, static analysis and code style](https://github.com/roadiz/dts-generator/actions/workflows/run-test.yml/badge.svg?branch=develop)](https://github.com/roadiz/dts-generator/actions/workflows/run-test.yml)

### Usage

```php
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Typescript\Declaration\DeclarationGeneratorFactory;
use RZ\Roadiz\Typescript\Declaration\Generators\DeclarationGenerator;
use Symfony\Component\HttpFoundation\ParameterBag;

/** @var ParameterBag<NodeTypeInterface> $nodeTypesBag */
$nodeTypesBag = $serviceContainer->get('nodeTypesBag');

$declarationFactory = new DeclarationGeneratorFactory($nodeTypesBag);
$declaration = new DeclarationGenerator($declarationFactory);

echo $declaration->getContents();
```
