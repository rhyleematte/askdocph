<?php
  $affirmation = $currentDailyAffirmation ?? null;
  $quote = $affirmation && $affirmation->quote ? $affirmation->quote : 'You are worthy of support and belonging. Your journey is unique, and every step forward is progress.';
  $author = $affirmation && $affirmation->author ? $affirmation->author : null;
?>

<div class="panel mini-panel">
  <div class="mini-title"><i data-lucide="sparkles"></i><span>Daily Affirmation</span></div>
  <p class="mini-text" style="font-style:italic; color:#7c3aed;">"<?php echo e($quote); ?>"</p>
  <?php if($author): ?>
    <p class="mini-sub" style="margin-top:8px;">- <?php echo e($author); ?></p>
  <?php endif; ?>
</div>
<?php /**PATH C:\websystem\resources\views/partials/daily_affirmation_panel.blade.php ENDPATH**/ ?>