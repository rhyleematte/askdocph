<?php $__env->startSection('title', 'About AskDocPH | Mental Health Support Platform'); ?>

<?php $__env->startPush('styles'); ?>
<style>
  /* ── About Page ───────────────────────────────────────── */
  .about-page {
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #0f172a;
  }

  /* Hero */
  .about-hero {
    background: linear-gradient(135deg, #0c8f98 0%, #0a6b72 60%, #064e54 100%);
    color: white;
    padding: 80px 40px 90px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .about-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
  }
  .about-hero .hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    backdrop-filter: blur(8px);
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
    letter-spacing: 0.5px;
  }
  .about-hero h1 {
    font-size: clamp(34px, 5vw, 56px);
    font-weight: 800;
    line-height: 1.15;
    margin-bottom: 18px;
    letter-spacing: -0.5px;
  }
  .about-hero h1 span {
    opacity: 0.75;
  }
  .about-hero p.lead {
    font-size: 18px;
    opacity: 0.88;
    max-width: 600px;
    margin: 0 auto 36px;
    line-height: 1.65;
  }
  .hero-actions {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
  }
  .btn-hero-primary {
    background: white;
    color: #0c8f98;
    font-weight: 700;
    padding: 14px 30px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
  }
  .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
  .btn-hero-ghost {
    border: 2px solid rgba(255,255,255,0.6);
    color: white;
    font-weight: 600;
    padding: 12px 28px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 15px;
    transition: background 0.2s, border-color 0.2s;
  }
  .btn-hero-ghost:hover { background: rgba(255,255,255,0.15); border-color: white; }

  /* Stats Strip */
  .stats-strip {
    background: white;
    border-bottom: 1px solid #eef2f7;
    padding: 28px 40px;
    display: flex;
    justify-content: center;
    gap: 60px;
    flex-wrap: wrap;
  }
  .stat-item { text-align: center; }
  .stat-item .num {
    font-size: 28px;
    font-weight: 800;
    color: #0c8f98;
    line-height: 1;
  }
  .stat-item .lbl {
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
  }

  /* Section Wrapper */
  .section {
    max-width: 1080px;
    margin: 0 auto;
    padding: 70px 32px;
  }
  .section-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e8fafb;
    color: #0a7f87;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 5px 13px;
    border-radius: 50px;
    margin-bottom: 14px;
  }
  .section-title {
    font-size: clamp(24px, 3.5vw, 36px);
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 14px;
    line-height: 1.2;
  }
  .section-sub {
    color: #64748b;
    font-size: 16px;
    line-height: 1.7;
    max-width: 560px;
  }

  /* Mission */
  .mission-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    margin-top: 48px;
    align-items: center;
  }
  .mission-text p {
    color: #334155;
    font-size: 15.5px;
    line-height: 1.75;
    margin-bottom: 16px;
  }
  .mission-highlight {
    background: linear-gradient(135deg, #0c8f98, #0a6b72);
    border-radius: 18px;
    padding: 36px;
    color: white;
  }
  .mission-highlight blockquote {
    font-size: 20px;
    font-weight: 600;
    line-height: 1.55;
    font-style: italic;
    opacity: 0.95;
    margin: 0 0 16px;
  }
  .mission-highlight cite {
    font-size: 13px;
    opacity: 0.75;
    font-style: normal;
  }

  /* Features Grid */
  .features-bg { background: #f6f9fc; }
  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 22px;
    margin-top: 48px;
  }
  .feature-card {
    background: white;
    border-radius: 16px;
    padding: 28px 24px;
    border: 1px solid #eef2f7;
    transition: transform 0.25s, box-shadow 0.25s;
  }
  .feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(12,143,152,0.1);
  }
  .feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: linear-gradient(135deg, #e8fafb, #c9f0f3);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
  }
  .feature-icon i { width: 24px; height: 24px; color: #0c8f98; }
  .feature-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; color: #0f172a; }
  .feature-card p { font-size: 14px; color: #64748b; line-height: 1.65; }

  /* How it Works */
  .steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 24px;
    margin-top: 48px;
  }
  .step-card {
    text-align: center;
    padding: 32px 20px;
    border-radius: 18px;
    background: white;
    border: 1px solid #eef2f7;
    position: relative;
  }
  .step-num {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0c8f98, #0a6b72);
    color: white;
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
  }
  .step-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; }
  .step-card p { font-size: 14px; color: #64748b; line-height: 1.6; }

  /* Values */
  .values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 48px;
  }
  .value-card {
    border-left: 4px solid #0c8f98;
    border-radius: 4px 12px 12px 4px;
    background: #f6f9fc;
    padding: 22px 20px;
    transition: background 0.2s;
  }
  .value-card:hover { background: #e8fafb; }
  .value-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 6px; color: #0f172a; }
  .value-card p { font-size: 13.5px; color: #64748b; line-height: 1.6; }

  /* CTA */
  .cta-section {
    background: linear-gradient(135deg, #0c8f98 0%, #064e54 100%);
    padding: 70px 40px;
    text-align: center;
    color: white;
  }
  .cta-section h2 { font-size: clamp(26px, 4vw, 40px); font-weight: 800; margin-bottom: 14px; }
  .cta-section p { font-size: 17px; opacity: 0.85; margin-bottom: 36px; }
  .cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
  .btn-cta-primary {
    background: white;
    color: #0c8f98;
    font-weight: 700;
    padding: 15px 34px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 15px;
    transition: transform 0.2s, box-shadow 0.2s;
    box-shadow: 0 4px 14px rgba(0,0,0,0.2);
  }
  .btn-cta-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 22px rgba(0,0,0,0.25); }
  .btn-cta-ghost {
    border: 2px solid rgba(255,255,255,0.6);
    color: white;
    font-weight: 600;
    padding: 13px 30px;
    border-radius: 12px;
    text-decoration: none;
    font-size: 15px;
    transition: background 0.2s;
  }
  .btn-cta-ghost:hover { background: rgba(255,255,255,0.15); border-color: white; }

  /* Footer-like note */
  .about-footer-note {
    background: white;
    border-top: 1px solid #eef2f7;
    text-align: center;
    padding: 24px 40px;
    font-size: 13px;
    color: #94a3b8;
  }
  .about-footer-note a { color: #0c8f98; text-decoration: none; font-weight: 600; }
  .about-footer-note a:hover { text-decoration: underline; }

  @media (max-width: 720px) {
    .mission-grid { grid-template-columns: 1fr; }
    .stats-strip { gap: 30px; }
    .about-hero { padding: 60px 24px 70px; }
    .section { padding: 50px 22px; }
  }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="about-page">

  
  <section class="about-hero">
    <div class="hero-badge">
      <i data-lucide="heart-pulse" style="width:14px;height:14px;"></i>
      Philippines' Mental Health Platform
    </div>
    <h1>Mental Wellness, <span>Made Accessible</span><br>for Every Filipino</h1>
    <p class="lead">
      AskDocPH bridges the gap between those who need mental health support and the licensed professionals who can provide it — all in one compassionate, secure platform.
    </p>
    <div class="hero-actions">
      <a href="<?php echo e(route('signup')); ?>" class="btn-hero-primary">Get Started Free</a>
      <a href="<?php echo e(route('login')); ?>" class="btn-hero-ghost">Sign In</a>
    </div>
  </section>

  
  <div class="stats-strip">
    <div class="stat-item">
      <div class="num">500+</div>
      <div class="lbl">Verified Doctors</div>
    </div>
    <div class="stat-item">
      <div class="num">10,000+</div>
      <div class="lbl">Users Helped</div>
    </div>
    <div class="stat-item">
      <div class="num">24/7</div>
      <div class="lbl">AI-Powered Support</div>
    </div>
    <div class="stat-item">
      <div class="num">100%</div>
      <div class="lbl">Confidential & Secure</div>
    </div>
  </div>

  
  <div class="section">
    <div class="section-tag"><i data-lucide="target" style="width:12px;height:12px;"></i> Our Mission</div>
    <h2 class="section-title">Breaking Barriers to<br>Mental Health Care</h2>
    <p class="section-sub">We believe that mental health support should never be out of reach — regardless of location, stigma, or circumstance.</p>

    <div class="mission-grid">
      <div class="mission-text">
        <p>
          Mental health challenges affect millions of Filipinos, yet access to qualified professionals remains limited due to cost, geography, and social stigma. AskDocPH was built to change that.
        </p>
        <p>
          Our platform connects users with <strong>licensed psychiatrists, psychologists, and mental health counselors</strong> in a private, judgment-free environment. Whether you're dealing with anxiety, depression, or simply need someone to talk to, we're here.
        </p>
        <p>
          We also leverage <strong>AI-powered guidance</strong> to help you navigate your mental wellness journey — offering immediate support and intelligently matching you with the right professional for your needs.
        </p>
      </div>
      <div class="mission-highlight">
        <blockquote>
          "Mental health is not a destination, but a process. It's about how you drive, not where you're going."
        </blockquote>
        <cite>— Noam Spirn &nbsp;|&nbsp; Guiding Principle of AskDocPH</cite>
      </div>
    </div>
  </div>

  
  <div class="features-bg">
    <div class="section">
      <div class="section-tag"><i data-lucide="sparkles" style="width:12px;height:12px;"></i> Platform Features</div>
      <h2 class="section-title">Everything You Need in<br>One Place</h2>
      <p class="section-sub">AskDocPH offers a comprehensive suite of tools designed to support every stage of your mental wellness journey.</p>

      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon"><i data-lucide="brain"></i></div>
          <h3>AI Mental Health Assistant</h3>
          <p>Get instant, compassionate support from our AI assistant — available any time of day, for any concern.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i data-lucide="stethoscope"></i></div>
          <h3>Connect with Doctors</h3>
          <p>Browse and connect with verified psychiatrists and mental health professionals across the Philippines.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i data-lucide="message-circle"></i></div>
          <h3>Private Messaging</h3>
          <p>Communicate securely with your chosen professional through our end-to-end encrypted messenger.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i data-lucide="calendar-check"></i></div>
          <h3>Appointment Scheduling</h3>
          <p>Book and manage appointments seamlessly — with calendar sync and automatic reminders built in.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i data-lucide="users"></i></div>
          <h3>Support Communities</h3>
          <p>Join moderated peer support groups where you can share experiences and find solidarity safely.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon"><i data-lucide="book-open"></i></div>
          <h3>Mental Health Resources</h3>
          <p>Access a curated library of articles, guides, and evidence-based tools for self-care and growth.</p>
        </div>
      </div>
    </div>
  </div>

  
  <div class="section">
    <div class="section-tag"><i data-lucide="map" style="width:12px;height:12px;"></i> How It Works</div>
    <h2 class="section-title">Your Journey in<br>Four Simple Steps</h2>
    <p class="section-sub">Getting started with AskDocPH takes just a few minutes. Here's how it works:</p>

    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <h3>Create Your Account</h3>
        <p>Sign up for free in under a minute. Your data is protected and your identity stays private.</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <h3>Talk to the AI</h3>
        <p>Start a conversation with our AI assistant. It helps you identify your needs and guides you to the right support.</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <h3>Connect with a Professional</h3>
        <p>Get matched with a verified doctor or counselor best suited to your situation — and message them directly.</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <h3>Begin Your Healing</h3>
        <p>Book appointments, join communities, and use curated resources to continue your mental wellness journey.</p>
      </div>
    </div>
  </div>

  
  <div class="features-bg">
    <div class="section">
      <div class="section-tag"><i data-lucide="heart" style="width:12px;height:12px;"></i> Our Values</div>
      <h2 class="section-title">What We Stand For</h2>
      <p class="section-sub">Our platform is built on principles that put people first — always.</p>

      <div class="values-grid">
        <div class="value-card">
          <h3>🛡️ Privacy First</h3>
          <p>Everything on AskDocPH is confidential. We never share your personal health information with third parties.</p>
        </div>
        <div class="value-card">
          <h3>✅ Clinical Integrity</h3>
          <p>All professionals on our platform are verified through a rigorous credentials review process.</p>
        </div>
        <div class="value-card">
          <h3>🌍 Accessibility</h3>
          <p>Mental health support for every Filipino — regardless of income, location, or background.</p>
        </div>
        <div class="value-card">
          <h3>💬 Compassion</h3>
          <p>We foster a judgment-free, empathetic environment where every person feels seen and heard.</p>
        </div>
        <div class="value-card">
          <h3>🔬 Evidence-Based</h3>
          <p>Our resources, tools, and recommendations are grounded in current clinical research and best practices.</p>
        </div>
        <div class="value-card">
          <h3>🤝 Community</h3>
          <p>Healing happens together. We build spaces for peer support and meaningful human connection.</p>
        </div>
      </div>
    </div>
  </div>

  
  <section class="cta-section">
    <h2>Ready to Take the First Step?</h2>
    <p>Join thousands of Filipinos already on their journey to better mental health.</p>
    <div class="cta-actions">
      <a href="<?php echo e(route('signup')); ?>" class="btn-cta-primary">Create Free Account</a>
      <a href="<?php echo e(route('login')); ?>" class="btn-cta-ghost">I Already Have an Account</a>
    </div>
  </section>

  
  <div class="about-footer-note">
    AskDocPH is not a crisis service. If you are in immediate danger, please call the
    <strong>National Crisis Hotline: <a href="tel:1553">1553</a></strong> or go to your nearest emergency room. &nbsp;|&nbsp;
    <a href="<?php echo e(route('login')); ?>">Login</a> &nbsp;·&nbsp; <a href="<?php echo e(route('signup')); ?>">Sign Up</a> &nbsp;·&nbsp; <a href="<?php echo e(route('doctor.apply')); ?>">Apply as Doctor</a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/about.blade.php ENDPATH**/ ?>