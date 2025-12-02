# openid
Roadiz sub-package for handling OpenID Connect authentication

[![Unit tests, static analysis and code style](https://github.com/roadiz/openid/actions/workflows/run-test.yml/badge.svg?branch=develop)](https://github.com/roadiz/openid/actions/workflows/run-test.yml)

## Purpose

This package implements the **OAuth2 Authorization Code Flow** for OpenID Connect, providing web application Single Sign-On (SSO) for the Roadiz CMS backoffice. This is different from Symfony's native OIDC support (introduced in 6.3+), which focuses on stateless API authentication with Bearer tokens.

## Why Not Use Symfony's Native OIDC?

Symfony's native `OidcTokenHandler` and `OidcUserInfoTokenHandler` are designed for:
- **Stateless API authentication** with access tokens
- **Resource servers** validating tokens from external identity providers
- **Bearer token** authentication (the token is already obtained by the client)

This package provides:
- **Authorization Code Flow** with redirect-based authentication
- **Session-based** authentication for web applications
- **UI integration** with Rozier backoffice login
- **OAuth2 callback handling** (state verification, token exchange)
- **Hybrid user support** (local users + virtual OIDC users)
- **Role mapping strategies** from OIDC claims

For a detailed comparison, see the [OpenID vs Native Symfony OIDC documentation](../../docs/developer/security/openid-native-comparison.md).

## Features

- OpenID Connect Discovery (.well-known/openid-configuration)
- OAuth2 Authorization Code Flow with PKCE support
- JWT token validation with JWK Set verification
- Session-based authentication
- Optional local user requirement
- Configurable role mapping from JWT claims
- CSRF protection with state parameter
- Automatic token refresh
- Support for multiple identity providers (Google, Authentik, Keycloak, Azure AD, etc.)

## Contributing

Report [issues](https://github.com/roadiz/core-bundle-dev-app/issues) and send [Pull Requests](https://github.com/roadiz/core-bundle-dev-app/pulls) in the [main Roadiz repository](https://github.com/roadiz/core-bundle-dev-app)
