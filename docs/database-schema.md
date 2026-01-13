# Database Schema - Art Gallery

This document defines the database schema for the Art Gallery application in DBML format.

## Overview

The database supports:
- Admin authentication (Laravel default users table)
- Artwork management with categories
- Image storage via Spatie Media Library

---

## DBML Schema

```dbml
// ===========================================
// LARAVEL DEFAULT TABLES
// ===========================================

Table users {
  id bigint [pk, increment]
  name varchar(255) [not null]
  email varchar(255) [unique, not null]
  email_verified_at timestamp [null]
  password varchar(255) [not null]
  remember_token varchar(100) [null]
  created_at timestamp [null]
  updated_at timestamp [null]

  Note: 'Laravel default users table - used for admin authentication'
}

Table password_reset_tokens {
  email varchar(255) [pk]
  token varchar(255) [not null]
  created_at timestamp [null]

  Note: 'Laravel default password reset tokens'
}

Table sessions {
  id varchar(255) [pk]
  user_id bigint [null]
  ip_address varchar(45) [null]
  user_agent text [null]
  payload longtext [not null]
  last_activity int [not null, note: 'Unix timestamp']

  indexes {
    user_id
    last_activity
  }

  Note: 'Laravel default sessions table for session driver'
}

Table cache {
  key varchar(255) [pk]
  value mediumtext [not null]
  expiration int [not null]

  Note: 'Laravel default cache table'
}

Table cache_locks {
  key varchar(255) [pk]
  owner varchar(255) [not null]
  expiration int [not null]

  Note: 'Laravel default cache locks table'
}

// ===========================================
// APPLICATION TABLES
// ===========================================

Table categories {
  id bigint [pk, increment]
  name varchar(255) [not null, unique]
  slug varchar(255) [not null, unique]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    slug
  }

  Note: 'Artwork categories (e.g., Oil Paintings, Digital, Sketches)'
}

Table artworks {
  id bigint [pk, increment]
  category_id bigint [not null, ref: > categories.id]
  title varchar(255) [not null]
  slug varchar(255) [not null, unique]
  artist_name varchar(255) [not null]
  description text [null]
  medium varchar(255) [not null, note: 'e.g., Oil on canvas, Digital art, Graphite on paper']
  is_published boolean [not null, default: true]
  published_at timestamp [null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    slug
    category_id
    is_published
    published_at
  }

  Note: 'Main artworks table - images handled via Spatie Media Library polymorphic relationship'
}

// ===========================================
// SPATIE MEDIA LIBRARY TABLE
// ===========================================

Table media {
  id bigint [pk, increment]
  model_type varchar(255) [not null]
  model_id bigint [not null]
  uuid varchar(36) [null, unique]
  collection_name varchar(255) [not null]
  name varchar(255) [not null]
  file_name varchar(255) [not null]
  mime_type varchar(255) [null]
  disk varchar(255) [not null]
  conversions_disk varchar(255) [null]
  size bigint [not null]
  manipulations json [not null]
  custom_properties json [not null]
  generated_conversions json [not null]
  responsive_images json [not null]
  order_column int [null]
  created_at timestamp [null]
  updated_at timestamp [null]

  indexes {
    (model_type, model_id) [name: 'media_model_type_model_id_index']
    order_column
  }

  Note: 'Spatie Media Library table - handles artwork images with automatic conversions'
}

// ===========================================
// RELATIONSHIPS
// ===========================================

Ref: artworks.category_id > categories.id [delete: restrict, update: cascade]
Ref: sessions.user_id > users.id [delete: cascade]
```

---

## Entity Relationship Diagram

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│   users     │       │  categories  │       │   media     │
├─────────────┤       ├──────────────┤       ├─────────────┤
│ id          │       │ id           │       │ id          │
│ name        │       │ name         │       │ model_type  │
│ email       │       │ slug         │       │ model_id    │◄──┐
│ password    │       │ created_at   │       │ collection  │   │
│ ...         │       │ updated_at   │       │ file_name   │   │
└─────────────┘       └──────┬───────┘       │ ...         │   │
                             │               └─────────────┘   │
                             │ 1:N                             │
                             ▼                                 │
                      ┌──────────────┐                         │
                      │   artworks   │                         │
                      ├──────────────┤                         │
                      │ id           │─────────────────────────┘
                      │ category_id  │        (polymorphic)
                      │ title        │
                      │ slug         │
                      │ artist_name  │
                      │ description  │
                      │ medium       │
                      │ is_published │
                      │ published_at │
                      │ created_at   │
                      │ updated_at   │
                      └──────────────┘
```

---

## Table Details

### categories
Stores artwork categorization options.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| name | varchar(255) | NOT NULL, UNIQUE | Display name (e.g., "Oil Paintings") |
| slug | varchar(255) | NOT NULL, UNIQUE | URL-friendly slug (e.g., "oil-paintings") |
| created_at | timestamp | NULLABLE | Laravel timestamp |
| updated_at | timestamp | NULLABLE | Laravel timestamp |

### artworks
Stores artwork metadata. Images are handled via polymorphic relationship with media table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| category_id | bigint | FK → categories.id, NOT NULL | Category reference |
| title | varchar(255) | NOT NULL | Artwork title |
| slug | varchar(255) | NOT NULL, UNIQUE | SEO-friendly URL slug |
| artist_name | varchar(255) | NOT NULL | Name of the artist |
| description | text | NULLABLE | Detailed description/story |
| medium | varchar(255) | NOT NULL | Art medium (e.g., "Oil on canvas") |
| is_published | boolean | NOT NULL, DEFAULT true | Visibility toggle |
| published_at | timestamp | NULLABLE | When artwork was published |
| created_at | timestamp | NULLABLE | Laravel timestamp |
| updated_at | timestamp | NULLABLE | Laravel timestamp |

### media (Spatie Media Library)
Handles all image uploads with automatic thumbnail generation. Uses polymorphic relationship.

| Column | Type | Description |
|--------|------|-------------|
| model_type | varchar(255) | Eloquent model class (e.g., "App\Models\Artwork") |
| model_id | bigint | ID of the related model |
| collection_name | varchar(255) | Media collection (e.g., "artwork-image") |
| conversions | json | Generated image conversions (thumbnail, medium, etc.) |

---

## Media Library Configuration

The following image conversions should be configured for artworks:

| Conversion | Dimensions | Purpose |
|------------|------------|---------|
| thumbnail | 400x400 | Gallery grid display |
| medium | 800x800 | Preview/hover states |
| original | preserved | Detail page high-res view |

---

## Indexes

Key indexes for query optimization:

1. **artworks.slug** - SEO-friendly URL lookups
2. **artworks.category_id** - Category filtering
3. **artworks.is_published** - Published artwork filtering
4. **artworks.published_at** - Ordering by publish date
5. **categories.slug** - Category URL lookups
6. **media.model_type + model_id** - Polymorphic relationship queries

---

## Foreign Key Constraints

| Table | Column | References | On Delete | On Update |
|-------|--------|------------|-----------|-----------|
| artworks | category_id | categories.id | RESTRICT | CASCADE |
| sessions | user_id | users.id | CASCADE | CASCADE |

**Note:** Categories cannot be deleted if artworks are assigned (RESTRICT). This enforces data integrity and requires admin to reassign or delete artworks first.

---

## Migration Order

1. Laravel default tables (users, password_reset_tokens, sessions, cache)
2. categories
3. artworks
4. media (via Spatie Media Library migration)
