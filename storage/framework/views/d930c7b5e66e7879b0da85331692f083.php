<!-- Ressource: resources/views/components/breadcrumb.blade.php -->
<nav class="breadcrumb" aria-label="Chemin de navigation">
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($index < count($items) - 1): ?>
            <li>
                <a href="<?php echo e($item['url'] ?? '#'); ?>"><?php echo e($item['label']); ?></a>
            </li>
        <?php else: ?>
            <li>
                <span><?php echo e($item['label']); ?></span>
            </li>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/components/breadcrumb.blade.php ENDPATH**/ ?>