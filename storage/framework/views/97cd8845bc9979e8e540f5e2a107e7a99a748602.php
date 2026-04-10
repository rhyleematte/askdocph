

<?php $__env->startSection('title', 'Sign Up | AskDocPH'); ?>

<?php $__env->startSection('content'); ?>
<main class="wrap">
  <section class="left">
    <div class="card">
      <h1>Create Account</h1>
      <p class="subtitle">Start your mental wellness journey</p>

      
      <div id="signupMsg" class="alert" style="display:none;"></div>

      <form id="signupForm" class="form" novalidate>
        <?php echo csrf_field(); ?>
      <input type="hidden" id="signupAjaxUrl" value="<?php echo e(route('signup.ajax')); ?>">

        
        <label>First Name</label>
        <div class="input-group">
          <i data-lucide="user"></i>
          <input type="text" name="fname" placeholder="First name" required />
        </div>

        
        <label>Middle Name (optional)</label>
        <div class="input-group">
          <i data-lucide="user"></i>
          <input type="text" name="mname" placeholder="Middle name" />
        </div>

        
        <label>Last Name</label>
        <div class="input-group">
          <i data-lucide="user"></i>
          <input type="text" name="lname" placeholder="Last name" required />
        </div>

        
        <label>Gender</label>
        <div class="input-group">
          <i data-lucide="venus-and-mars"></i>
          <select name="gender" required>
            <option value="">Select gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="prefer_not_say">Prefer not to say</option>
          </select>
        </div>

        
        <label>Birthday</label>
        <div class="input-group">
          <i data-lucide="calendar"></i>
          <input type="date" name="bday" required />
        </div>

        
        <label>Username</label>
        <div class="input-group">
          <i data-lucide="at-sign"></i>
          <input type="text" name="username" placeholder="Choose a username" required />
        </div>

        
        <label>Email Address</label>
        <div class="input-group">
          <i data-lucide="mail"></i>
          <input type="email" name="email" placeholder="you@example.com" required />
        </div>

        
        <label>Password</label>
        <div class="input-group">
          <i data-lucide="lock"></i>
          <input
            type="password"
            name="password"
            id="reg_password"
            placeholder="Create a password"
            required
          />
          <button type="button" class="toggle" id="toggleRegPass">
            <i data-lucide="eye"></i>
          </button>
        </div>

        
        <label>Confirm Password</label>
        <div class="input-group">
          <i data-lucide="shield-check"></i>
          <input
            type="password"
            name="password_confirmation"
            id="reg_password_confirm"
            placeholder="Confirm your password"
            required
          />
          <button type="button" class="toggle" id="toggleRegConfirm">
            <i data-lucide="eye"></i>
          </button>
        </div>

        <button type="submit" class="btn primary">Create Account</button>

        <p class="switch">
          Already have an account?
          <a href="<?php echo e(route('login')); ?>">Sign In</a>
        </p>
      </form>
    </div>
  </section>

  <section class="right" style="display: flex; flex-direction: column;">
    <div class="right-content">
      <h2>Your mental health journey<br>starts here</h2>

      <div class="feature">
        <div class="badge"><i data-lucide="users"></i></div>
        <div>
          <h3>Connect with Professionals</h3>
          <p>Access licensed psychiatrists and mental health experts.</p>
        </div>
      </div>

      <div class="feature">
        <div class="badge"><i data-lucide="heart-handshake"></i></div>
        <div>
          <h3>Join Our Community</h3>
          <p>Share experiences and find support.</p>
        </div>
      </div>

      <div class="feature">
        <div class="badge"><i data-lucide="shield-check"></i></div>
        <div>
          <h3>Safe & Confidential</h3>
          <p>Your privacy and security are our priority.</p>
        </div>
      </div>
    </div>

    <div style="margin-top: auto; text-align: center; padding-top: 40px;">
        <a href="<?php echo e(route('doctor.apply')); ?>" style="color: rgba(255, 255, 255, 0.8); text-decoration: underline; font-size: 14px; transition: color 0.3s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255, 255, 255, 0.8)'">Apply for medical staff privileges</a>
    </div>
  </section>
</main>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/auth/signup.blade.php ENDPATH**/ ?>