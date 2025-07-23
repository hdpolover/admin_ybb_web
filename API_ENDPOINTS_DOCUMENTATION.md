# API Endpoints Documentation for Redis Optimization

This document details all API endpoints called during sign-in, sign-up, and landing page operations in the CodeIgniter 4 application. These endpoints are candidates for Redis caching to improve performance.

## Table of Contents
- [Authentication Endpoints](#authentication-endpoints)
- [Landing Page Endpoints](#landing-page-endpoints)
- [Dashboard & User Data Endpoints](#dashboard--user-data-endpoints)
- [Program & Participant Endpoints](#program--participant-endpoints)
- [Submission & Form Endpoints](#submission--form-endpoints)
- [Payment Endpoints](#payment-endpoints)
- [Document & Certificate Endpoints](#document--certificate-endpoints)
- [AJAX & Utility Endpoints](#ajax--utility-endpoints)
- [Redis Optimization Recommendations](#redis-optimization-recommendations)

---

## Authentication Endpoints

### Sign In Process
**Base URL Pattern:** `{API_BASE_URL}/auth/*`

| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/auth/sign-in` | POST | User authentication | ❌ | Contains credentials, don't cache |
| `/participants/user/{user_id}` | GET | Get user participants | ✅ High | Called after successful login |
| `/programs/category/{category_id}` | GET | Get programs by category | ✅ High | Called during login flow |

### Sign Up Process
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/auth/participant/sign-up` | POST | Register new user | ❌ | User registration, don't cache |
| `/ambassadors/check-query` | GET | Validate ambassador query | ✅ Medium | Validate referral codes |
| `/programs/slug/{program_slug}` | GET | Get program by slug | ✅ High | Program details for signup |
| `/ambassadors/decode-query` | GET | Decode ambassador query | ✅ Low | Fallback for query validation |

### Password Reset
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/auth/forgot-password` | POST | Send reset link | ❌ | Email sending, don't cache |
| `/auth/reset-password` | POST | Reset password | ❌ | Password change, don't cache |
| `/auth/verify-email` | GET | Verify email token | ❌ | One-time verification |

### Ambassador Authentication
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/auth/sign-in` | POST | Ambassador login (type=3) | ❌ | Authentication, don't cache |

---

## Landing Page Endpoints

### Home Page
**Base URL Pattern:** `{API_BASE_URL}/landing/*`

| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/landing/home` | GET | Home page data | ✅ High | Frequently accessed, cache 15-30 min |
| `/program-photos` | GET | Program photos fallback | ✅ Medium | Cache 1-2 hours |

### Programs Listing
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/landing/programs` | GET | Programs list page | ✅ High | Cache 30 min - 1 hour |

### Program Details
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/programs/slug/{slug}/details` | GET | Program details by slug | ✅ High | Cache 1-2 hours |

### Insights Page
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/landing/insights` | GET | Insights page data | ✅ Medium | Cache 2-4 hours |

### Partners & Sponsors
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/landing/partners-sponsors` | GET | Partners & sponsors data | ✅ Medium | Cache 4-8 hours |

### Announcements
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/landing/announcements` | GET | Announcements list | ✅ Medium | Cache 30 min - 1 hour |
| `/program_announcements/list` | GET | Program announcements | ✅ Medium | Cache 30 min |

---

## Dashboard & User Data Endpoints

### Dashboard Initialization
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/payments/participants/{participant_id}` | GET | User payment status | ✅ Low | Cache 5-10 min, frequent updates |

### Topbar Data
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/programs/category/{category_id}` | GET | Programs for topbar | ✅ High | Cache 30 min - 1 hour |
| `/program-categories/{category_id}` | GET | Program category details | ✅ High | Cache 2-4 hours |

---

## Program & Participant Endpoints

### Program Management
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/programs/{program_id}` | GET | Single program details | ✅ High | Cache 1-2 hours |
| `/programs/category/{category_id}` | GET | Programs by category | ✅ High | Cache 30 min - 1 hour |

### Participant Management
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/participants/user/{user_id}` | GET | User's participants | ✅ Medium | Cache 10-15 min |
| `/participants/{participant_id}` | GET | Single participant | ✅ Low | Cache 5-10 min |
| `/participants/users/{user_id}/create` | POST | Register for program | ❌ | Creates new data |
| `/participants/{participant_id}/details` | GET | Participant details | ✅ Low | Cache 5-10 min |

---

## Submission & Form Endpoints

### Form Updates
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/submission/personal/{participant_id}/update` | POST | Update personal info | ❌ | Data modification |
| `/submission/professional/{participant_id}/update` | POST | Update professional info | ❌ | Data modification |
| `/submission/entry/{participant_id}/update` | POST | Update entry info | ❌ | Data modification |
| `/submission/miscs/{participant_id}/update` | POST | Update misc info | ❌ | Data modification |
| `/submission/validateAmbassadorCode` | POST | Validate ambassador code | ✅ Low | Cache 5 min |
| `/submission/submit` | POST | Submit final form | ❌ | Final submission |

### Abstract Paper Management
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/abstracts/participant/{participant_id}/details` | GET | Abstract details | ✅ Low | Cache 5-10 min |
| `/abstracts/{abstract_id}/save-version` | POST | Save abstract version | ❌ | Data modification |
| `/abstracts/versions/compare` | GET | Compare versions | ✅ Low | Cache 2-5 min |

---

## Payment Endpoints

### Payment Data
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/program-payments/program/{program_id}` | GET | Program payment info | ✅ Medium | Cache 30 min |
| `/payments/participants/{participant_id}` | GET | Participant payments | ✅ Low | Cache 5-10 min |
| `/payment-methods/program/{program_id}` | GET | Payment methods | ✅ High | Cache 1-2 hours |
| `/payments/program-payment/{id}/participant/{participant_id}` | GET | Payment details | ✅ Low | Cache 5 min |
| `/program-payments/{id}` | GET | Program payment details | ✅ Medium | Cache 30 min |
| `/payments/{id}` | GET | Single payment | ✅ Low | Cache 5 min |

### Payment Processing
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/payments/create` | POST | Create payment | ❌ | Payment processing |
| `/payments/gateway` | POST | Gateway payment | ❌ | Payment processing |

---

## Document & Certificate Endpoints

### Documents
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/documents/program/{program_id}` | GET | Program documents | ✅ Medium | Cache 1-2 hours |
| `/documents/{document_id}` | GET | Single document | ✅ Medium | Cache 1-2 hours |

### Certificates
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/certificates/generate` | POST | Generate certificate | ❌ | Dynamic generation |
| `/documents/generate-loa/{program_id}/{participant_id}` | GET | Generate LOA | ❌ | Dynamic generation |

---

## AJAX & Utility Endpoints

### System Configuration
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/web-settings` | GET | Website settings | ✅ High | Cache 1-4 hours |

### Utility Endpoints
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/topbar/setProgram/{program_id}` | POST | Change current program | ❌ | Session management |
| `/topbar/{user_id}/create` | POST | Register for program | ❌ | Data creation |
| `/api/user/current` | GET | Current user info | ✅ Low | Cache 5 min |
| `/topbar/updateParticipantSession` | POST | Update session data | ❌ | Session management |
| `/popup-notification/getRecentRegistrations` | GET | Recent registrations | ✅ Low | Cache 1-2 min |

### Error Handling
| Endpoint | Method | Purpose | Cache Candidate | Notes |
|----------|--------|---------|-----------------|-------|
| `/ajax/timeout` | GET/POST | AJAX timeout handling | ❌ | Error handling |
| `/ajax/error/{code}` | GET/POST | AJAX error handling | ❌ | Error handling |

---

## Redis Optimization Recommendations

### High Priority (Cache 30 min - 4 hours)
1. **Web Settings** (`/web-settings`) - Core site configuration
2. **Programs by Category** (`/programs/category/{id}`) - Frequently accessed
3. **Landing Page Data** (`/landing/*`) - Public pages with high traffic
4. **Payment Methods** (`/payment-methods/program/{id}`) - Rarely changes
5. **Program Details** (`/programs/{id}`, `/programs/slug/{slug}`) - Static content

### Medium Priority (Cache 5-30 min)
1. **Participant Lists** (`/participants/user/{id}`) - User-specific data
2. **Program Payments** (`/program-payments/program/{id}`) - Semi-static
3. **Document Lists** (`/documents/program/{id}`) - Changes infrequently
4. **Program Categories** (`/program-categories/{id}`) - Stable data

### Low Priority (Cache 1-10 min)
1. **Payment Status** (`/payments/participants/{id}`) - May change frequently
2. **Abstract Details** (`/abstracts/participant/{id}/details`) - User edits
3. **Current User** (`/api/user/current`) - Session-dependent
4. **Recent Registrations** (`/popup-notification/getRecentRegistrations`) - Real-time data

### Cache Invalidation Triggers
1. **User Registration** - Clear user-specific caches
2. **Program Updates** - Clear program-related caches
3. **Payment Completion** - Clear payment status caches
4. **Form Submissions** - Clear participant data caches
5. **Admin Changes** - Clear relevant content caches

### Cache Keys Strategy
```
Format: {domain}:{endpoint}:{parameters}:{version}

Examples:
- web_settings:worldyouthfest.com:v1
- programs:category:123:v1
- participant:user:456:v1
- payments:participant:789:v1
- landing:home:worldyouthfest.com:v1
```

### Implementation Notes
1. Use Redis TTL for automatic expiration
2. Implement cache versioning for easy invalidation
3. Add cache warming for critical endpoints
4. Monitor cache hit/miss ratios
5. Consider user-specific vs global caching strategies
6. Implement graceful fallback when Redis is unavailable

### Performance Impact Estimation
- **High Priority endpoints**: 70-90% performance improvement
- **Medium Priority endpoints**: 40-70% performance improvement  
- **Low Priority endpoints**: 20-40% performance improvement

This documentation should provide a comprehensive foundation for implementing Redis caching to optimize your API performance.
