<?php $__env->startSection('title', 'Inscriptions - Intranet'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <h1>Inscriptions</h1>

        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Cours</th>
                    <th>Semestre</th>
                    <th>Note</th>
                    <th>Score final</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $enrollment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($enrollment->student->first_name ?? 'N/A'); ?> <?php echo e($enrollment->student->last_name ?? ''); ?></td>
                        <td><?php echo e($enrollment->course->name ?? 'N/A'); ?></td>
                        <td><?php echo e($enrollment->semester); ?></td>
                        <td><?php echo e($enrollment->grade ?? 'N/A'); ?></td>
                        <td><?php echo e($enrollment->final_score !== null ? number_format($enrollment->final_score, 2) : 'N/A'); ?></td>
                        <td><?php echo e($enrollment->status); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php echo e($enrollments->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/intranet/enrollments/index.blade.php ENDPATH**/ ?>