# Project Overview

This project is the monorepository containing all source code for Roadiz CMS, a content management system built with PHP and Symfony. 

## Folder Structure

- `docs/`: Contains Roadiz documentation built with VitePress
- `src/`: Contains the sources for testing the CMS in this development repository.
- `lib/`: Contains all the Symfony bundles used by Roadiz CMS and shared libraries. Each bundle is split into its own repository during GitHub Actions workflow located at `.github/workflows/split.yaml`. These packages are published automatically on Packagist with `roadiz/` namespace.
  - `lib/RoadizCoreBundle`: Core bundle for Roadiz CMS logic, persistence, routing and API
  - `lib/RoadizRozierBundle`: Required bundle for back-office application (controllers, forms and templating)
  - `lib/RoadizSolrBundle`: _additional features bundle_ for connecting Roadiz to a Solr Search engine
  - `lib/RoadizTwoFactorBundle`: _additional features bundle_ to enable 2FA for back-office authentication
  - `lib/RoadizUserBundle`: _additional features bundle_ to enable public user management (public user creation and APIs)
  - `lib/RoadizFontBundle`: _additional features bundle_ to manage font files (deprecated)
  - `lib/Documents`: abstract package describing documents (media) management (interfaces, contracts, file uploaders and embed finders)
  - `lib/DocGenerator`: _secondary package_ to generate Markdown documentation from Roadiz node-types configuration
  - `lib/DtsGenerator`: _secondary package_ to generate TypeScript type declarations from Roadiz node-types configuration
  - `lib/EntityGenerator`: _required package_ to generate Doctrine PHP entities from Roadiz node-types configuration
  - `lib/Jwt`: _required package_ to handle JWT
  - `lib/Markdown`: _required package_ to support Markdown to HTML generation in Twig templates
  - `lib/Models`: _required package_ containing all interfaces, contracts, traits and abstract entities used in Roadiz CMS
  - `lib/OpenId`: _required package_ to support OpenID authentication in Roadiz back-office
  - `lib/Random`: _required package_ to provide random token and password generation for Roadiz back-office
  - `lib/Rozier`: Contains the frontend code for building the CMS administration interface. This package is not published on Packagist, and is meant to build static CSS, JS and image assets into `lib/RoadizRozierBundle/public/`.

## Libraries and Frameworks

- PHP 8.0+
- Symfony framework
- API Platform
- Doctrine ORM
- PHPUnit for testing
- PHPStan for static analysis
- PHP-CS-Fixer for code style
- Docker and Docker Compose
- Crowdin for interface localization: https://crowdin.com/project/roadiz-cms

### For Rozier package

- pnpm
- Vite
- Prettier
- ESLint
- HTML custom-elements (anonymous and built-in)
- Storybook for UI components

### For documentation

- VitePress: to generate user documentation, deployed on https://docs.roadiz.io/

## Coding Standards

- Follow PSR-12 coding standards for PHP
- Write clear and concise comments
- Use *git-cliff* to generate `./CHANGELOG.md` file for each released tag
- Document any breaking change or any migration procedure in `./UPGRADE.md` file

### Commit Instruction

- Use conventional commit format: type(scope): Description
- Use imperative mood: 'Add feature' not 'Added feature'
- Keep subject line under 50 characters
- Use types: feat, fix, docs, style, refactor, perf, test, chore, ci
- Include scope when relevant (e.g., api, ui, auth, front)
- Reference issue numbers with # prefix

### For Rozier package

- Lint frontend source code using `pnpm lint`
- Develop each component (custom-elements) in Storybook
- Build assets when merging new features on `develop` branch using `pnpm build`
