<?php $__env->startSection('title', 'Étudiants - Intranet'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <h1>Étudiants</h1>
        <p><a class="button primary" href="<?php echo e(route('intranet.students.create')); ?>">Créer un étudiant</a></p>

        <?php if(session('success')): ?>
            <div class="alert"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($student->student_id); ?></td>
                        <td><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
                        <td><?php echo e($student->email); ?></td>
                        <td><?php echo e(ucfirst($student->status)); ?></td>
                        <td>
                            <a class="button secondary" href="<?php echo e(route('intranet.students.show', $student)); ?>">Voir</a>
                            <a class="button secondary" href="<?php echo e(route('intranet.students.edit', $student)); ?>">Éditer</a>
                            <form action="<?php echo e(route('intranet.students.destroy', $student)); ?>" method="POST"
                                style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="button secondary"
                                    data-confirm="Supprimer cet étudiant ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php echo e($students->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/intranet/students/index.blade.php ENDPATH**/ ?>