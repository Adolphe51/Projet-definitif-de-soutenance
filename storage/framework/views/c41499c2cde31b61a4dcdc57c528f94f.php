<!-- Ressource: resources/views/components/empty-state.blade.php -->
<div class="empty-state">
    <?php if($icon ?? null): ?>
        <div class="empty-state-icon"><?php echo e($icon); ?></div>
    <?php else: ?>
        <div class="empty-state-icon">📭</div>
    <?php endif; ?>
    <h3 class="empty-state-title"><?php echo e($title ?? 'Aucune donnée'); ?></h3>
    <p class="empty-state-text"><?php echo e($slot); ?></p>
    <?php if($action ?? null): ?>
        <div><?php echo e($action); ?></div>
    <?php endif; ?>
</div><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/components/empty-state.blade.php ENDPATH**/ ?>