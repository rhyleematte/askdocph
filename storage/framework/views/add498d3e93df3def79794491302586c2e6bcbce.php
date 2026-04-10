

<?php $__env->startSection('title', 'Admin Login - AskDocPH'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Scoped styles for admin login */
body {
    background: #0f172a; /* Pre-fill background */
}

.topbar {
    display: none !important; /* Hide standard topbar for focused admin login */
}

.admin-login-body {
    background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 20px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
}

.admin-auth-wrap {
    width: 100%;
    max-width: 440px;
    z-index: 10;
}

.admin-auth-card {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.05) inset;
    border-radius: 20px;
    padding: 3rem 2.5rem;
    color: white;
    animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes  scaleIn {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.admin-auth-card h1 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    text-align: center;
    background: linear-gradient(135deg, #38bdf8 0%, #6366f1 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.025em;
}

.admin-auth-card p.subtitle {
    color: #94a3b8;
    text-align: center;
    font-size: 1rem;
    margin-bottom: 2.5rem;
    font-weight: 400;
}

.admin-form-group {
    margin-bottom: 1.5rem;
    position: relative;
    transition: transform 0.3s ease;
}

.admin-form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #cbd5e1;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease;
}

.admin-form-group:focus-within label {
    color: #38bdf8;
}

.admin-form-control {
    width: 100%;
    padding: 0.875rem 1.25rem;
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-sizing: border-box;
}

.admin-form-control::placeholder {
    color: #475569;
}

.admin-form-control:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
    background: rgba(15, 23, 42, 0.9);
}

.admin-btn {
    width: 100%;
    padding: 0.875rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #38bdf8 0%, #4f46e5 100%);
    color: white;
    font-size: 1.05rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    position: relative;
    overflow: hidden;
}

.admin-btn::after {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 50%; height: 100%;
    background: linear-gradient(to right, transparent, rgba(255,255,255,0.2), transparent);
    transform: skewX(-20deg);
    transition: all 0.5s ease;
}

.admin-btn:hover::after {
    left: 150%;
}

.admin-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4), 0 4px 6px -2px rgba(79, 70, 229, 0.2);
}

.admin-btn:active {
    transform: translateY(0);
    box-shadow: 0 0 transparent;
}

.admin-alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

@keyframes  shake {
    10%, 90% { transform: translate3d(-1px, 0, 0); }
    20%, 80% { transform: translate3d(2px, 0, 0); }
    30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
    40%, 60% { transform: translate3d(4px, 0, 0); }
}

.admin-auth-footer {
    margin-top: 2rem;
    text-align: center;
    font-size: 0.9rem;
    color: #94a3b8;
}

.admin-auth-footer a {
    color: #38bdf8;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.admin-auth-footer a:hover {
    color: #60a5fa;
    text-shadow: 0 0 8px rgba(96, 165, 250, 0.4);
}

/* Background blob effects */
.blob-1, .blob-2 {
    position: absolute;
    filter: blur(80px);
    z-index: 0;
    opacity: 0.4;
    border-radius: 50%;
    animation: float 10s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
}

.blob-1 {
    width: 400px;
    height: 400px;
    background: #4f46e5;
    top: -100px;
    left: -100px;
}

.blob-2 {
    width: 300px;
    height: 300px;
    background: #0ea5e9;
    bottom: -50px;
    right: -50px;
    animation-delay: -5s;
}

@keyframes  float {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(30px, 30px) scale(1.1); }
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="admin-login-body">
  <!-- Decorative background elements -->
  <div class="blob-1"></div>
  <div class="blob-2"></div>

  <div class="admin-auth-wrap">
    <div class="admin-auth-card">
      <h1>Admin Portal</h1>
      <p class="subtitle">Secure System Access</p>

      <?php if($errors->any()): ?>
        <div class="admin-alert-error">
          <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div><?php echo e($error); ?></div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      <?php endif; ?>

      <form action="<?php echo e(route('admin.login.submit')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        
        <div class="admin-form-group">
          <label for="email">Admin Email</label>
          <input type="email" id="email" name="email" class="admin-form-control" value="<?php echo e(old('email')); ?>" required autofocus placeholder="admin@domain.com">
        </div>

        <div class="admin-form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="admin-form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="admin-btn">Secure Login</button>
      </form>

      <div class="admin-auth-footer">
        No account? <a href="<?php echo e(route('admin.signup')); ?>">Sign up here</a>
      </div>
    </div>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Micro-animations for form focus
    const inputs = document.querySelectorAll('.admin-form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.style.transform = 'translateY(-2px)';
        });
        input.addEventListener('blur', () => {
            input.parentElement.style.transform = 'translateY(0)';
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\websystem\resources\views/admin/auth/login.blade.php ENDPATH**/ ?>