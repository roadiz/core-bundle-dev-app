---
layout: home
features:
    - icon: 👤
      title: User Documentation (French)
      link: '/user/intro'
    - icon: 🛠️
      title: Developer Documentation
      link: '/developer/first-steps/requirements'
    - icon: 💡
      title: Extensions Documentation
      link: '/extensions/events'
hero:
    name: Welcome to Roadiz documentation
---

## A modern CMS

_Roadiz_ is a polymorphic and headless CMS based on a node system that can handle many types of services.  
It is built on **Symfony** framework, **Doctrine ORM**, **API Platform**, and **Composer** to ensure maximum performance and security.

_Roadiz_'s node system allows you to create your data schema and organize your content exactly how you want. It is designed to remove technical constraints when building tailor-made website architectures and layouts.

Imagine you need to display your graphic design portfolio and also sell some t-shirts. With _Roadiz_, you can create custom content forms from scratch and choose the exact fields you need: images and texts for your projects, images, texts, prices, and even geolocation for your products. That’s why it’s called **polymorphic**.

## Philosophy

When exploring _Roadiz_'s back-office interface, you'll notice there is no Rich Text Editor (also called *WYSIWYG* editor). We chose to promote **Markdown** syntax to focus on content hierarchy and quality rather than content styling. Our goal is to preserve and respect the work of web designers and graphic designers.

_Roadiz_ is built by web designers, for web designers. It allows you to quickly create website prototypes using **Twig** templates or develop complex headless websites powered by **API Platform**.

_Roadiz_ is designed to be a great tool for both designers and developers to build strong web experiences together. But we also thought about editors! The Roadiz back-office theme, *Rozier*, offers a great writing and administration experience for all back-end users.

## Features grid

### Structured and flexible content management

The Roadiz content system is its functional core, adapting to a wide variety of needs, from the simplest to the most complex.

| Feature                              | Description |
|--------------------------------------|---|
| **Tree-like node system**            | All content is organized within a logical and visual tree structure, which simplifies the management of a site's architecture (pages, sections, articles, etc.). |
| **Native page builder**              | Everything in Roadiz is a node. A page can itself contain content units with their own publication workflow, allowing you to create any type of block (galleries, text, etc.). |
| **Customizable content types**       | Create any type of content structure (e.g., articles, products) by defining specific fields. This approach removes the limitation of a standard page template. |
| **Native multi-language management** | The platform includes native management of translations for all content, with an interface allowing for easy navigation between languages for seamless editing. |
| **Versioning and changes history**   | A versioning system with every save ensures the persistence of modifications. It's possible to view the change history and restore a previous version at any time. |
| **Markdown-oriented content editor** | A modern editor based on Markdown syntax enables efficient management of the semantic hierarchy of texts (headings, lists) in line with the Headless philosophy. |
| **Taxonomy (tags)**                  | A keyword-based taxonomy system allows for organizing and creating cross-relationships between content, ideal for blogs, portfolios, or catalogs. |
| **AI Assisted translation**          | Native compatibility with the _DeepL API_ allows you to enable translation assistance for all Markdown fields if you have an API key. |

### Centralized and advanced media library

The media library centralizes the management of digital assets to optimize their reuse across the entire platform.

| Feature                                              | Description |
|------------------------------------------------------|---|
| **Support for multiple formats**                     | The system supports the import of a wide range of files: images, videos, documents (PDF, Word), audio files, and vectors (SVG). |
| **On-the-fly image manipulation & external storage** | Roadiz can dynamically resample, crop, and optimize images. It supports external storage (like _Amazon S3_) for increased scalability. |
| **Folder-based management**                          | Structuring the media library with folders and subfolders enables efficient organization and retrieval of assets. |
| **Metadata management**                              | Write an alternative name, description, and copyright for all documents in each translation. Time-based usage limits can also be set to respect copyright. |
| **Automated image processing**                       | Automatically process images and videos upon import (resizing, average color calculation, first-frame extraction, etc.). |
| **Deduplication**                                    | Based on the file's cryptographic hash, Roadiz detects and prevents duplicate media from being uploaded. |
| **Hotspot (point of interest)**                      | Define the main area of an image to ensure relevant automatic cropping, which can be set contextually for different pages. |
| **External video integration**                       | Easily integrate videos from platforms like _YouTube_ or _Vimeo_ using their URL via the _oEmbed_ standard to fetch metadata and thumbnails. |

### Search Engine Optimization (SEO) tools

Roadiz provides a comprehensive set of tools designed to optimize the site's natural search ranking.

| Feature                                | Description |
|----------------------------------------|---|
| **Detailed metadata management**       | Granular control over `title`, `meta-description`, and `no-index` tags for each page, with visual indicators for recommended lengths. |
| **Customizable URLs (slugs)**          | Easily produce clear, readable, and search engine-optimized URLs, which can be customized for each translation. |
| **Redirect manager**                   | An integrated module allows for the creation and management of 301 and 302 redirects to preserve traffic integrity during a redesign. |
| **Automatic `sitemap.xml` generation** | A sitemap is automatically generated and kept up-to-date. For Headless sites, an API endpoint provides data for sitemap generation tools. |

### Administration and user experience

The administration interface has been designed to combine efficiency with user comfort for daily use.

| Feature                        | Description |
|--------------------------------|---|
| **Modern and clean interface** | A clear and modern interface that focuses on the essential features for content management. |
| **Responsive design**          | Its fully responsive design allows for administration from any type of device (desktop, tablet, smartphone). |
| **Customizable dashboard**     | Provides quick access to key information and functional shortcuts right after logging in. |
| **Preview functionality**      | Changes can be previewed in context before being published using a JWT token that allows the display of unpublished content. |
| **Custom-form builder**        | Create contact forms with standard field types. Responses are stored in the CMS and can be emailed, with GDPR-compliant data retention settings. |

### User management and security

The system allows for precise definition of access rights thanks to a robust permissions system.

| Feature                                 | Description |
|-----------------------------------------|---|
| **Role management**                     | Create custom roles and define granular access rights for each feature. |
| **User groups**                         | Organize users (e.g., Editor, Contributor) into groups to facilitate the assignment and maintenance of permissions on a large scale. |
| **Symfony-Based security**              | The platform relies on the robust and proven security system of the Symfony framework. |
| **Secure realm definition**             | Create secure Realms to configure specific authentication types (e.g., password, logged-in user) and apply them to parts of the node tree. |
| **Centralized authentication (OpenID)** | Native compatibility with OpenID Connect facilitates integration with centralized authentication systems (SSO) for administrators. |
| **Two-Factor authentication (2FA)**     | Enable 2FA for administrator accounts to add an extra layer of security for back-office access. |

### Extensibility and customization

Roadiz's design anticipates the future evolution needs of projects.

| Feature                              | Description |
|--------------------------------------|---|
| **Custom fields**                    | The ability to integrate specific fields (color, geolocation, inter-content relationships) offers considerable flexibility for data modeling. |
| **Full customization**               | As a Symfony Bundle, Roadiz allows you to create custom Doctrine entities, admin pages, and API endpoints alongside the core CMS features. |
| **Native REST API exposure**         | Deep integration with _API Platform_ automatically exposes all content types via a comprehensive REST API, essential for Headless architecture. |
| **Native extension for _Apache Solr_** | Natively use the _Apache Solr_ full-text search engine to index content and media, providing a powerful search engine accessible via the REST API. |

### Cloud-Native architecture and deployment

Roadiz is designed to integrate seamlessly with modern development methodologies and infrastructures.

| Feature                                     | Description |
|---------------------------------------------|---|
| **Modern development workflow**             | The project structure is designed for a workflow based on Git, CI/CD, and deployment via Docker containers. |
| **Configuration via environment variables** | The entire application configuration can be managed through environment variables, essential for containerized deployments. |
| **Externalized media storage**              | The ability to use external file storage (like _Amazon S3_) decouples media from the application instance, reinforcing the stateless approach. |
| **_Headless_ decoupling**                     | The architecture allows for the complete separation of the back-office from the front-end application(s), offering maximum flexibility. |
