<!-- Ressource: resources/views/components/progress-bar.blade.php -->
<div class="progress-bar">
    <div class="progress-bar-fill" style="width: <?php echo e(($percentage ?? 0)); ?>%"></div>
</div>
<?php if($showLabel ?? true): ?>
    <p class="text-muted text-center"><?php echo e($label ?? ''); ?> <?php echo e($percentage ?? 0); ?>%</p>
<?php endif; ?>
<?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/components/progress-bar.blade.php ENDPATH**/ ?>