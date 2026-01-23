# OpenID Authentication Decision Summary

## Issue
The issue requested an investigation into migrating from Roadiz's custom OpenID bundle to Symfony's native OIDC authentication (introduced in Symfony 6.3+).

## Investigation Findings

### Symfony's Native OIDC (6.3+)
Symfony introduced native OpenID Connect support through:
- `OidcTokenHandler`: Validates OIDC JWT tokens
- `OidcUserInfoTokenHandler`: Fetches user info from OIDC UserInfo endpoint
- `access_token` authenticator: Built-in access token authentication

**Design Purpose**: Stateless API authentication with Bearer tokens
**Use Cases**: Resource servers, API authentication, mobile apps, SPAs

### Roadiz OpenID Bundle
The `roadiz/openid` package provides:
- Full OAuth2 Authorization Code Flow implementation
- Session-based web application authentication
- UI integration with Rozier backoffice login
- Discovery service with automatic configuration
- Hybrid user support (local + virtual OIDC users)
- Role mapping from JWT claims

**Design Purpose**: Web application Single Sign-On (SSO)
**Use Cases**: Rozier backoffice login, enterprise SSO, session-based authentication

## Decision: Maintain Roadiz OpenID Bundle

**The Roadiz OpenID bundle remains necessary** because:

1. **Different Authentication Flow**
   - Symfony: Token validation (assumes token already obtained)
   - Roadiz: Authorization code flow (redirect to provider, obtain token)

2. **Different State Management**
   - Symfony: Stateless (no sessions)
   - Roadiz: Stateful (session-based)

3. **Different Use Cases**
   - Symfony: API authentication
   - Roadiz: Web application SSO

4. **UI Integration**
   - Symfony: No UI components
   - Roadiz: Login button, callback handling, session management

5. **User Management**
   - Symfony: Basic token validation
   - Roadiz: Hybrid local/virtual users, role strategies

## No Migration Required

**No code changes are necessary.** The existing OpenID configuration continues to work as designed.

## Documentation Added

This PR adds comprehensive documentation:

1. **[openid-native-comparison.md](docs/developer/security/openid-native-comparison.md)** (177 lines)
   - Detailed comparison of Symfony native OIDC vs Roadiz OpenID
   - Use cases for each approach
   - Feature comparison table
   - Recommended architecture patterns

2. **[MIGRATION_GUIDE.md](lib/OpenId/MIGRATION_GUIDE.md)** (160 lines)
   - Potential future enhancements
   - Decision matrix for considering migrations
   - What should NOT be migrated
   - Short/medium/long term recommendations

3. **Updated [lib/OpenId/README.md](lib/OpenId/README.md)**
   - Clarified purpose of the bundle
   - Explained differences from Symfony native OIDC
   - Listed key features

4. **Updated [docs/developer/security/security.md](docs/developer/security/security.md)**
   - Added reference to comparison documentation

5. **Updated [UPGRADE.md](UPGRADE.md)**
   - Added section explaining the decision
   - Confirmed no migration required

6. **Updated [CHANGELOG.md](CHANGELOG.md)**
   - Documented the decision

## Future Considerations

While maintaining the bundle, potential future enhancements could include:
- Using Symfony's JWT validation infrastructure (if beneficial)
- Adding optional API token support using native OIDC for API endpoints
- Leveraging Symfony's OIDC discovery caching mechanisms

However, the core authorization code flow implementation should remain custom.

## Conclusion

**The Roadiz OpenID bundle is still relevant and necessary.** Symfony's native OIDC support solves a different problem (stateless API authentication) than what Roadiz requires (stateful web SSO).

The documentation now clearly explains:
- Why both solutions exist
- When to use each approach
- That no migration is required or recommended
- Potential future enhancements that could leverage Symfony's infrastructure

## Files Changed
- `CHANGELOG.md` - Added entry documenting the decision
- `UPGRADE.md` - Added clarification that no migration is needed
- `docs/developer/security/security.md` - Added reference to comparison doc
- `docs/developer/security/openid-native-comparison.md` - **NEW** - Comprehensive comparison
- `lib/OpenId/MIGRATION_GUIDE.md` - **NEW** - Future enhancement guide
- `lib/OpenId/README.md` - Updated with purpose and comparison

Total: 402 lines of new documentation added
