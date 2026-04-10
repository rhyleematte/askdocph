# Page Logic & Engineering Analysis

This document details the layout, engineering structure, individual `div` elements, buttons, and their underlying functions for the primary pages of the AskDocPH platform.

## 1. User Dashboard (`resources/views/userdashboard.blade.php`)

**Purpose:** The central feed and command center for regular users and doctors.

### Architecture & DOM Structure
- `<main class="dash">`: The primary shell covering the viewport.
  - `<div class="dash-body">`: A flex container split into the left sidebar and the main content area.
    - `<aside class="dash-left">`: Sticky sidebar for navigation and global actions.
      - `<div class="panel nav-panel">`: Holds core routing buttons.
      - `[partials.daily_affirmation_panel]`: A reusable component displaying dynamic quotes.
      - `<div class="panel mini-panel danger">`: The Crisis Support widget.
      - `[partials.doctor_status_panel]`: Shown only to verified doctors.
    - `<section class="dash-main">`: The scrollable primary feed area.
      - `<div class="panel composer" id="composerPanel">`: The multi-modal post creation box.
      - `<div id="dashFeed">`: An empty container populated natively via JavaScript fetching `dashboard.feed`.

### Key Buttons & Functions
- **Crisis Support (`#getHelpBtn`)**: Triggers immediate crisis intervention flow.
- **Composer Text Array (`#dashPostText`)**: Listens to inputs to expand automatically and bind hashtags/moods.
- **Mood Toggle Button (`#moodToggleBtn`)**: Triggers an interactive slide-down `div.mood-bar` where users select from 8 distinct emojis representing psychological states. Binds to `data-mood`.
- **Hashtag Toggle (`#hashtagToggleBtn`)**: Reveals `#hashtagRow` to input comma-separated classifications.
- **Attach Photo (`#mediaUpload`)**: Hidden file input triggered by `label.chip-btn`. Initiates a FileReader to display thumbnails in `#mediaPreviewArea`.
- **Post Submit (`#dashShareBtn`)**: Serializes form-data (including mood, links, and base64/binary media files) and hits `profile.posts.store` via AJAX.

### Engineering & Logic
The dashboard heavily relies on an injected `window.DASH_ROUTES` mapping to bridge backend Laravel Routes with the `assets/js/messenger.js` or dashboard-specific client logic. This allows asynchronous Like/Save/Comment actions without page reloads.

---

## 2. User Profile (`resources/views/profile/show.blade.php`)

**Purpose:** A comprehensive representation of a user's identity, history, and social network footprint. Adaptable based on whether viewing `isOwn` or a public visitor.

### Architecture & DOM Structure
- `<div class="prof-shell">`: Container for the entire profile hierarchy.
  - `<main class="prof-main">`: Central column.
    - `<div class="panel prof-card">`: Holds the Cover Image, Avatar, Biography, and Global Stats.
      - `<div class="prof-cover">`: Displays cover art; injects edit overlays natively if `isOwn`.
      - `<div class="prof-card-info">`: Contains Username, Name, Stats (Post Count, Groups, Resources).
    - `<nav class="prof-tabs" id="profTabs">`: Segment switcher (Posts, Groups, Resources, Network, Saved, Application).
    - **Tab Contents (`div.tab-content`)**: 
      - `#tab-posts`: Lists user's specific posts. If `isOwn`, shows a mini-composer.
      - `#tab-groups`: Contains a dropdown filter (Joined vs Created) and dynamic grid of groups (`prof-grid-groups`).
      - `#tab-resources`: Similar filtering and grid rendering but for educational modules.
      - `#tab-network`: Splices Followers vs Following using `display:none` toggles (`#networkTabs`). 
      - `#tab-application`: (For doctors) Loads the partial `_application` logic.

### Key Buttons & Functions
- **Edit Profile (`#editProfileBtn`)**: Removes the hidden class from `.modal-backdrop#editModal`. The modal uses forms mapping to `profile.update.info`.
- **Avatar Upload (`#photoUpload`)**: Hidden input to push `FormData` containing the image directly to `profile.update.photo`.
- **Follow Button (`#followBtn`)**: Toggles the follow status async by calling `PROFILE_USER_ID + '/follow'`. Evaluates state dynamically (`isFollowing`).
- **Tab Buttons (`.tab-btn`)**: Client-side JS event loops that suppress visual `<div class="tab-content">` arrays via `.hidden` CSS classes, exposing only the targeted `data-tab`.

### Engineering & Logic
Profile pages require sophisticated contextual logic (`$isOwn`, `$isVerifiedDoctor`, `$profileUser`). Variables are bound into the window namespace (`window.PROFILE_USER_ID`, `window.IS_OWN_PROFILE`) to allow separate vanilla JavaScript controllers to orchestrate interactions locally instead of depending heavily on Vue/React. Blade template partials (`@include('profile._post')`) promote DRY integration of complex looped elements.

---

## 3. Support Groups (`resources/views/groups/index.blade.php` & `show.blade.php`)

**Purpose:** Micro-communities within AskDocPH allowing segmented, topical conversations.
- **Div mappings**: Group cards use `.prof-group-card`.
- **Logic**: Public vs Private visibility dictates what's rendered in DOM.
- **Functions**: `#joinGroupBtn` hitting `groups.join` endpoint, which inserts a record mapping User ID to Group ID.

## 4. Doctor Applications (`resources/views/doctor/apply.blade.php`)

**Purpose:** Registration funnel for medical professionals.
- **Logic Structure**: A wizard-style interface moving from Personal details -> Qualifications -> Clinic Info -> Biometrics.
- **Engineering**: Employs rigorous CSRF protection and form validation. It handles heavy multipart payloads for document uploading securely to the server's private storage bucket. Has corresponding `_application_form.blade.php` partial.
