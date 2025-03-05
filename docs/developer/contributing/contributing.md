---
title: Contributing
---

# Contributing

If you want to contribute to Roadiz project by reporting issues or hacking code, let us thank you! You are awesome!

## Reporting issues

When you encounter an issue with Roadiz we would love to hear about it.
Because thanks to you, we can make the most awesome and stable CMS!

If you submit a bug report please include all information available to you, here are some things you can do :

- Try to simplify the things you are doing until getting a minimal set of actions reproducing the problem.
- Do not forget to join a screenshot or a trace of your error.

## Coding style

The code you contributed to the project should respect the guidelines defined in PHP *PSR2* standard.
If you install the requirements for devs by the command `composer update --dev`, you can use *phpcs* to check your code.

You can copy and paste the following command-lines to check and fix it easily :

```shell
php vendor/bin/php-cs-fixer fix --ansi -vvv
```

Please take those rules into account, we aim to have a clean codebase.
A coherent code-style will contribute to Roadiz stability.
Your code will be checked when we will be considering your pull requests.

## Static analysis

Then we use `phpstan` as a static code analyzer to check bugs and misuses before they occur :

```shell
php -d "memory_limit=-1" vendor/bin/phpstan analyse -c phpstan.neon
```
