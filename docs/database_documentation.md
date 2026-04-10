# AskDocPH Database Documentation

## System Flowchart & Entity Relationships

The architecture centers around the core `users` table, which serves two primary roles (`user` vs `doctor`). Almost all auxiliary features attach to the User ID.

```mermaid
erDiagram
    USERS {
        bigint id PK
        string username
        string email
        string password
        enum role "user/doctor"
        enum doctor_status
        string profile_photo
        string cover_photo
    }

    DOCTOR_APPLICATIONS {
        bigint id PK
        bigint user_id FK
        string prc_number
        string specialization
        enum status
    }

    POSTS {
        bigint id PK
        bigint user_id FK
        enum post_type "text/media/mixed"
        text text_content
        bigint group_id FK
        bigint resource_id FK
        datetime created_at
    }

    POST_LIKES {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
    }

    POST_SAVES {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
    }

    POST_COMMENTS {
        bigint id PK
        bigint user_id FK
        bigint post_id FK
        text content
    }

    GROUPS {
        bigint id PK
        bigint creator_id FK
        string name
        text description
        enum visibility
    }

    GROUP_MEMBERS {
        bigint id PK
        bigint user_id FK
        bigint group_id FK
        enum role "admin/member"
    }

    RESOURCES {
        bigint id PK
        bigint user_id FK
        string title
        string type
        string duration_meta
        text description
    }

    HELP_REQUESTS {
        bigint id PK
        bigint user_id FK
        bigint doctor_id FK
        enum status "pending/accepted"
    }

    CONVERSATIONS {
        bigint id PK
        boolean is_group
    }

    MESSAGES {
        bigint id PK
        bigint conversation_id FK
        bigint sender_id FK
        text body
    }

    CONVERSATION_PARTICIPANTS {
        bigint id PK
        bigint conversation_id FK
        bigint user_id FK
    }

    USERS ||--o{ DOCTOR_APPLICATIONS : submits
    USERS ||--o{ POSTS : creates
    POSTS ||--o{ POST_LIKES : receives
    USERS ||--o{ POST_LIKES : performs
    POSTS ||--o{ POST_SAVES : receives
    USERS ||--o{ POST_SAVES : performs
    POSTS ||--o{ POST_COMMENTS : receives
    
    USERS ||--o{ GROUPS : "creates (creator_id)"
    USERS }|--|{ GROUP_MEMBERS : joins
    GROUPS ||--o{ GROUP_MEMBERS : contains
    GROUPS ||--o{ POSTS : "contains (group_id)"

    USERS ||--o{ RESOURCES : creates
    
    USERS ||--o{ HELP_REQUESTS : "requests (user_id)"
    USERS ||--o{ HELP_REQUESTS : "receives (doctor_id)"

    USERS }|--|{ CONVERSATION_PARTICIPANTS : participates
    CONVERSATIONS ||--|{ CONVERSATION_PARTICIPANTS : "has participants"
    CONVERSATIONS ||--o{ MESSAGES : contains
    USERS ||--o{ MESSAGES : "sends (sender_id)"
```

## Abstract Data Flow

1. **Authentication Layer**: Handled natively utilizing Laravel routing with `login`, `signup`, `signup-ajax`. State persists via `rememberToken`.
2. **Post Lifecycle**: A User pushes data -> Controllers determine context (`group_id` or `resource_id` mapping if applicable) -> Stored in `posts`.
3. **Social Actions**: Likes (`post_likes`), Comments (`post_comments`), Saves (`post_saves`), and Shares are isolated tables to maintain normalization, pivoting on `user_id` and `post_id`.
4. **Messenger Real-time Sync**: `conversations` -> `conversation_participants` mapping creates logical rooms. Real-time updates occur when `messages` maps to said `conversation_id`.
5. **Medical Routing**: `help_requests` serves as the intermediary handshake protocol connecting a basic user to a certified doctor. Once the `status` moves to `accepted`, logic spins up a unique `conversation` between the two entities.
