<?php $__env->startSection('title', 'Présences - Intranet'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <h1>Présences</h1>

        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Cours</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($attendance->enrollment->student->first_name ?? 'N/A'); ?>

                            <?php echo e($attendance->enrollment->student->last_name ?? ''); ?></td>
                        <td><?php echo e($attendance->enrollment->course->name ?? 'N/A'); ?></td>
                        <td><?php echo e(optional($attendance->lecture_date)->format('Y-m-d')); ?></td>
                        <td><?php echo e($attendance->status); ?></td>
                        <td><?php echo e($attendance->notes ?? '—'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php echo e($attendances->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/intranet/attendances/index.blade.php ENDPATH**/ ?>