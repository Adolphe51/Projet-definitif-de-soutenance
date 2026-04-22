<!-- Ressource: resources/views/components/alert.blade.php -->
<div class="alert alert-<?php echo e($type ?? 'info'); ?>">
    <strong><?php echo e($title ?? (ucfirst($type ?? 'info') . ':')); ?></strong>
    <?php echo e($slot); ?>

</div><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/components/alert.blade.php ENDPATH**/ ?>