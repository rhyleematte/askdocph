# Freeform Project Planning: AskDocPH

## Executive Summary
AskDocPH is emerging as a comprehensive health and mental well-being social network bridging direct tele-support and community interaction. The core scaffolding (Routing, Unified Dashboards, Authentication, Doctor Validation, Messenger) is robust. The upcoming phases must focus on scalability, micro-interactions, robust media handling, and deepening AI intervention tools.

## Phase 1: Stabilization & Refinement 
*Current priority: ensuring the existing flow is bug-free and smooth.*

1. **Asset & Storage Optimization**
   - **Action:** Transition local profile/cover image uploads from `public/storage` to a structured cloud bucket (e.g., AWS S3 or Supabase Storage). Implement image compression on upload.
   - **Reason:** Current payload can become expensive to serve locally as user base grows.
2. **UI Real-Time Reactivity**
   - **Action:** Replace polling or isolated AJAX loops with comprehensive websockets (Laravel Reverb or Pusher) mapping to Dashboard Feeds, Notifications, and Messenger.
   - **Reason:** Ensuring "Like" or "Comment" counts augment seamlessly without refresh flashes.
3. **Doctor Verification Pipeline**
   - **Action:** Build out the Admin Panel Application Approval pipeline to handle massive queues, adding automated license validation layers with third-party APIs if possible.

## Phase 2: Engagement & Social Gamification 
*Mid-term priority: retaining user attention and fostering positive habits.*

1. **Daily Affirmation Scaling**
   - **Action:** Allow users to curate and customize their own daily affirmations. Send localized push notifications via Service Workers indicating new affirmations.
2. **Resource Gamification**
   - **Action:** For Workbooks and Audio modules listed in the `resources` section, implement a percentage-based progress tracking bar tied to `resource_user` pivot tables. Emblems or achievements for completion.
3. **Crisis & Trigger Warnings**
   - **Action:** Implement automated text-parsing on post creation. If words like "suicide, hurt" are typed, intervene dynamically via the composer to offer the `Get Help Now` module before submission.

## Phase 3: Telemedicine & AI Enhancements
*Long-term priority: monetization and direct high-value clinical interventions.*

1. **Secure Telehealth Video SDK**
   - **Action:** Integrate Agora or Twilio into the Messenger suite for HIPAA-compliant encrypted video/voice calls triggered directly from `help_requests` handshakes.
2. **AI Triage Assistant**
   - **Action:** Expand `partials/ai_chat_modal` to actively pre-screen patients based on a structured symptom checklist before connecting them with human Doctors. Send the AI-generated context hash securely to the designated doctor.
3. **Monetization & Appointment Scheduling**
   - **Action:** Allow Doctors to set schedules and attach Stripe/Paymongo payment tiers to premium, closed-group support sessions or one-on-one therapy durations.
