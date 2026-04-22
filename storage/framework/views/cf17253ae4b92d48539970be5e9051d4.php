<!-- Ressource: resources/views/components/card.blade.php -->
<div class="card">
    <?php if($title ?? null): ?>
        <div class="card-header">
            <h3 class="card-title"><?php echo e($title); ?></h3>
            <?php if($action ?? null): ?>
                <div><?php echo e($action); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="card-content">
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/components/card.blade.php ENDPATH**/ ?>