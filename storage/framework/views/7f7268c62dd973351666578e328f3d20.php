<?php $__env->startSection('title', 'OTP'); ?>

<?php $__env->startSection('content'); ?>
<p>Bonjour <?php echo e($authCode->user->name); ?>,</p>
<p>Votre code d'accès SecureAccess : <strong><?php echo e($code); ?></strong></p>
<p>Ce code expire dans 10 minutes.</p>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/emails/otp.blade.php ENDPATH**/ ?>