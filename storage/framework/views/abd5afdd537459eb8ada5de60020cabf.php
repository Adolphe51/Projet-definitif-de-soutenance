<?php $__env->startSection('title', 'Cours - Intranet'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <h1>Cours</h1>
        <p><a class="button primary" href="<?php echo e(route('intranet.courses.create')); ?>">Créer un cours</a></p>

        <?php if(session('success')): ?>
            <div class="alert"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Titre</th>
                    <th>Département</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($course->course_code); ?></td>
                        <td><?php echo e($course->title); ?></td>
                        <td><?php echo e($course->department); ?></td>
                        <td><?php echo e(ucfirst($course->status)); ?></td>
                        <td>
                            <a class="button secondary" href="<?php echo e(route('intranet.courses.show', $course)); ?>">Voir</a>
                            <a class="button secondary" href="<?php echo e(route('intranet.courses.edit', $course)); ?>">Éditer</a>
                            <form action="<?php echo e(route('intranet.courses.destroy', $course)); ?>" method="POST"
                                style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="button secondary"
                                    data-confirm="Supprimer ce cours ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php echo e($courses->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/intranet/courses/index.blade.php ENDPATH**/ ?>