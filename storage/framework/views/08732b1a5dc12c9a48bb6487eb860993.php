<?php $__env->startSection('title', 'Messages - Intranet'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container">
        <h1>Messages</h1>
        <p><a class="button primary" href="<?php echo e(route('intranet.messages.create')); ?>">Créer un message</a></p>

        <?php if(session('success')): ?>
            <div class="alert"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Sujet</th>
                    <th>Lu</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($message->sender->first_name ?? 'N/A'); ?> <?php echo e($message->sender->last_name ?? ''); ?></td>
                        <td><?php echo e($message->recipient->first_name ?? 'N/A'); ?> <?php echo e($message->recipient->last_name ?? ''); ?></td>
                        <td><?php echo e($message->subject); ?></td>
                        <td><?php echo e($message->is_read ? 'Oui' : 'Non'); ?></td>
                        <td>
                            <a class="button secondary" href="<?php echo e(route('intranet.messages.show', $message)); ?>">Voir</a>
                            <a class="button secondary" href="<?php echo e(route('intranet.messages.edit', $message)); ?>">Éditer</a>
                            <form action="<?php echo e(route('intranet.messages.destroy', $message)); ?>" method="POST"
                                style="display:inline;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="button secondary"
                                    data-confirm="Supprimer ce message ?">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <?php echo e($messages->links()); ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/intranet/messages/index.blade.php ENDPATH**/ ?>