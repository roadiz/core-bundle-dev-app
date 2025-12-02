# Roadiz OpenID Bundle vs Symfony Native OIDC

## Overview

This document compares Roadiz's custom OpenID Connect implementation with Symfony's native OIDC authentication support introduced in Symfony 6.3 and enhanced in 7.3.

## Symfony Native OIDC Support

### What Symfony Provides (6.3+)

Symfony introduced native OIDC support through the `access_token` authenticator system:

- **OidcTokenHandler**: Validates OIDC JWT tokens (ID tokens or access tokens)
- **OidcUserInfoTokenHandler**: Fetches user information from the OIDC UserInfo endpoint
- Built-in JWT validation with automatic JWKS discovery
- Support for token introspection (OAuth2 RFC 7662)

### Configuration Example

```yaml
# config/packages/security.yaml
security:
    firewalls:
        api:
            stateless: true
            access_token:
                token_handler:
                    oidc_user_info: https://your-oidc-server.com/realms/demo/protocol/openid-connect/userinfo
```

Or with JWT validation:

```yaml
security:
    firewalls:
        api:
            stateless: true
            access_token:
                token_handler:
                    oidc:
                        claim: sub
                        audience: your-client-id
                        issuers:
                            - https://your-oidc-server.com/realms/demo
```

### Use Cases

Symfony's native OIDC is designed for:

1. **Stateless API authentication** with Bearer tokens
2. **Resource server** validation of access tokens
3. **Service-to-service** authentication
4. **Mobile and Single Page Application (SPA)** applications that obtain tokens independently

## Roadiz OpenID Bundle

### What Roadiz Provides

The `roadiz/openid` package implements a complete OpenID Connect authentication flow:

- **Authorization Code Flow**: Full OAuth2/OIDC authorization code flow with PKCE support
- **Discovery Service**: Automatic configuration from `.well-known/openid-configuration`
- **Session-based Authentication**: Traditional web application authentication with sessions
- **UI Integration**: Seamless integration with Rozier backoffice login page
- **Hybrid User Support**: Both local database users and virtual OIDC-only users
- **Role Mapping**: Maps OIDC claims to Symfony roles

### Current Implementation

```yaml
# config/packages/roadiz_rozier.yaml
roadiz_rozier:
    open_id:
        discovery_url: '%env(string:OPEN_ID_DISCOVERY_URL)%'
        hosted_domain: '%env(string:OPEN_ID_HOSTED_DOMAIN)%'
        oauth_client_id: '%env(string:OPEN_ID_CLIENT_ID)%'
        oauth_client_secret: '%env(string:OPEN_ID_CLIENT_SECRET)%'
        requires_local_user: false
        granted_roles:
            - ROLE_USER
            - ROLE_BACKEND_USER
```

### Use Cases

Roadiz OpenID is designed for:

1. **Web application SSO** for Rozier backoffice
2. **User-facing login flows** with redirect-based authentication
3. **Enterprise SSO** with providers like Authentik, Keycloak, Azure AD
4. **Session-based** authenticated users
5. **Mixed authentication** (local users + OIDC users)

## Key Differences

| Feature | Symfony Native OIDC | Roadiz OpenID Bundle |
|---------|-------------------|---------------------|
| **Authentication Flow** | Bearer Token (stateless) | Authorization Code Flow (stateful) |
| **Use Case** | API / Resource Server | Web Application SSO |
| **State Management** | Stateless | Session-based |
| **User Flow** | Token already obtained | Full login redirect flow |
| **UI Integration** | None (API-focused) | Rozier login button |
| **Discovery** | Manual or auto | Automatic with caching |
| **Local Users** | Not applicable | Optional requirement |
| **Role Mapping** | Basic | Advanced with strategies |
| **CSRF Protection** | Not needed | Built-in with state parameter |
| **Redirect Handling** | Not applicable | Full callback handling |

## Why Both Are Relevant

### Roadiz OpenID Bundle Should Be Kept Because:

1. **Different Use Case**: Symfony's native OIDC is for stateless API authentication, while Roadiz needs stateful web SSO
2. **Authorization Code Flow**: Symfony doesn't provide the OAuth2 authorization code flow (redirect to provider, callback handling, token exchange)
3. **UI Integration**: The bundle integrates with Rozier's login page and provides redirect links
4. **Callback Handling**: Manages the OAuth2 callback endpoint, state verification, and session establishment
5. **Hybrid Authentication**: Supports mixing OIDC authentication with local database users in the same firewall

### Potential Refactoring Opportunities

While the full bundle should be maintained, some components could leverage Symfony's native features:

1. **JWT Validation**: Use Symfony's JWT validation infrastructure instead of manual lcobucci/jwt implementation
2. **JWKS Caching**: Leverage Symfony's OIDC discovery caching mechanisms
3. **Token Introspection**: For API scenarios, add support for Symfony's token introspection
4. **UserInfo Endpoint**: Consider using `OidcUserInfoTokenHandler` for user info fetching

## Recommended Architecture

### For Rozier Backoffice (Web UI)
**Use Roadiz OpenID Bundle** - Provides the complete authorization code flow needed for web-based SSO.

```yaml
security:
    firewalls:
        main:
            custom_authenticator:
                - RZ\Roadiz\RozierBundle\Security\RozierAuthenticator
                - roadiz_rozier.open_id.authenticator  # Roadiz OpenID
```

### For API Endpoints
**Could Use Symfony Native OIDC** - For stateless API authentication with Bearer tokens.

```yaml
security:
    firewalls:
        api:
            stateless: true
            access_token:
                token_handler:
                    oidc_user_info: https://your-provider.com/userinfo
```

## Migration Path (Future)

If Symfony adds authorization code flow support in the future:

1. **Phase 1**: Refactor JWT validation to use Symfony's infrastructure
2. **Phase 2**: Replace discovery mechanism with Symfony's native discovery
3. **Phase 3**: Evaluate if Symfony provides sufficient authorization code flow support
4. **Phase 4**: Only if Symfony provides full flow, migrate or deprecate custom implementation

## Conclusion

**The Roadiz OpenID bundle remains necessary and relevant** because it solves a different problem than Symfony's native OIDC support. Symfony focuses on stateless API token validation, while Roadiz provides a complete web application SSO flow with UI integration.

The bundle could be enhanced by leveraging some of Symfony's native OIDC infrastructure for JWT validation and discovery, but a complete replacement is not appropriate at this time.

## References

- [Symfony Access Token Authentication](https://symfony.com/doc/current/security/access_token.html)
- [Symfony 6.3 OpenID Connect Token Handler](https://symfony.com/blog/new-in-symfony-6-3-openid-connect-token-handler)
- [Symfony 7.3 Security Improvements](https://symfony.com/blog/new-in-symfony-7-3-security-improvements)
- [OAuth 2.0 Authorization Code Flow](https://oauth.net/2/grant-types/authorization-code/)
- [OpenID Connect Core Specification](https://openid.net/specs/openid-connect-core-1_0.html)
