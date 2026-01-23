# OpenID Bundle - Future Enhancement Opportunities

This document outlines potential enhancements to the Roadiz OpenID bundle that could leverage Symfony's native OIDC infrastructure while maintaining the authorization code flow functionality.

## Current Status

The Roadiz OpenID bundle is **actively maintained and necessary** for web application SSO. It implements the OAuth2 Authorization Code Flow which is not provided by Symfony's native OIDC support.

## Potential Enhancements

### 1. JWT Validation with Symfony Infrastructure (Medium Priority)

**Current Implementation:**
- Uses `lcobucci/jwt` directly for JWT parsing and validation
- Manual JWKS fetching and PEM conversion with `codercat/jwk-to-pem`

**Current Approach:**
```php
// In OpenIdAuthenticator::authenticate()
$configuration = $this->jwtConfigurationFactory->create();
$jwt = $configuration->parser()->parse($jsonResponse['id_token']);
$configuration->validator()->assert($jwt, ...$constraints);
```

**Potential Enhancement:**
```php
// Could leverage Symfony's Web Token components if available
// However, lcobucci/jwt is well-maintained and works well
// This is low priority unless clear benefits emerge
```

**Benefits:**
- Potential performance improvements
- Better integration with Symfony's security system
- Reduced maintenance burden

**Risks:**
- Breaking changes in token validation logic
- Migration effort may not justify benefits

### 2. Discovery Caching (Low Priority)

**Current Implementation:**
- Custom caching with PSR-6 CacheItemPoolInterface
- Manual cache key management

**Potential Enhancement:**
Could align with Symfony's OIDC discovery caching if they expose reusable components.

**Benefits:**
- Standardized caching approach
- Potential performance improvements

**Note:** Current implementation works well, this is very low priority.

### 3. Hybrid API Token Support (Future Feature)

**Current Limitation:**
The bundle focuses on web SSO and doesn't provide Bearer token validation for APIs.

**Potential Enhancement:**
Add an optional API authenticator that uses Symfony's native `OidcTokenHandler` for stateless API endpoints while keeping the Authorization Code Flow for web UI.

**Example Configuration:**
```yaml
# config/packages/security.yaml
security:
    firewalls:
        # Web UI - uses Authorization Code Flow
        main:
            custom_authenticator:
                - roadiz_rozier.open_id.authenticator
        
        # API - uses Bearer tokens (new)
        api:
            stateless: true
            access_token:
                token_handler:
                    oidc_user_info: '%env(OPEN_ID_DISCOVERY_URL)%/userinfo'
```

**Benefits:**
- Unified OIDC configuration
- Support for both web and API authentication
- Leverage Symfony's stateless token validation

**Implementation Notes:**
- Keep web SSO separate from API token validation
- Share OIDC configuration (discovery URL, client ID) between both
- Different user providers may be needed

### 4. User Info Endpoint Integration (Low Priority)

**Current Implementation:**
User information comes from ID token claims.

**Potential Enhancement:**
Optionally fetch additional user info from the UserInfo endpoint using `OidcUserInfoTokenHandler` patterns.

**Benefits:**
- Access to additional user claims not in ID token
- Better compatibility with providers that return minimal ID tokens

**Note:** Most providers include sufficient claims in the ID token, making this optional.

## What NOT to Migrate

### Authorization Code Flow ❌
**Do NOT migrate** the authorization code flow to Symfony native OIDC because:
- Symfony doesn't provide this flow
- It requires stateful session management
- It needs UI integration with login buttons and redirects
- The current implementation is mature and works well

### Session-Based Authentication ❌
**Do NOT migrate** to stateless authentication because:
- Rozier backoffice requires sessions
- CSRF protection needs session state
- Remember me functionality needs sessions
- User experience depends on traditional web app sessions

### Custom User Providers ❌
**Do NOT remove** `OpenIdAccountProvider` and virtual user support because:
- Enterprise customers need to authenticate without local users
- Role mapping strategies are essential
- The provider chain with local users is a key feature

## Decision Matrix

When considering migrating a component, ask:

| Question | Migrate if YES | Keep Custom if YES |
|----------|---------------|-------------------|
| Does Symfony provide this feature? | ✓ | |
| Is it core to authorization code flow? | | ✓ |
| Would it simplify the codebase significantly? | ✓ | |
| Is the current implementation problematic? | ✓ | |
| Would it break existing functionality? | | ✓ |
| Is it actively used by customers? | | ✓ |

## Recommendations

### Short Term (Current)
- ✅ **Document** the differences between Roadiz OpenID and Symfony native OIDC
- ✅ **Maintain** the current implementation as-is
- ⚠️ **Monitor** Symfony's OIDC development for authorization code flow support

### Medium Term (6-12 months)
- 🔍 **Evaluate** JWT validation migration if Symfony provides clear benefits
- 🔍 **Consider** hybrid API token support for unified authentication

### Long Term (12+ months)
- 🔍 **Reassess** if Symfony adds authorization code flow support
- 🔍 **Plan** migration path only if Symfony provides equivalent functionality

## Conclusion

The Roadiz OpenID bundle should remain independent because it solves a fundamentally different problem than Symfony's native OIDC support. Any future enhancements should be carefully evaluated to ensure they provide clear benefits without compromising the current functionality.

**Key Principle:** Only adopt Symfony native components when they provide equivalent or superior functionality for the specific use case (web application SSO with authorization code flow).
