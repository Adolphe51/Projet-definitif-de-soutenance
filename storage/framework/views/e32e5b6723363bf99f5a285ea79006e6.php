<?php $__env->startSection('title', 'Connexion'); ?>

<?php $__env->startSection('content'); ?>

<div class="auth-step">

    <form method="POST" action="<?php echo e(route('otp.send')); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="form-group">
            <label for="email" class="form-label">Adresse email</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-input <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="votre@email.com"
                value="<?php echo e(old('email')); ?>"
                autocomplete="email"
                required
                autofocus>
            <p class="form-help">
                Un code de vérification OTP sera envoyé à cette adresse.
            </p>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="form-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="form-group">
            <label for="password" class="form-label">Mot de passe</label>
            <input
                type="password"
                id="password"
                name="password"
                class="form-input <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                placeholder="Votre mot de passe"
                autocomplete="current-password"
                required minlength="8">
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="form-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button class="btn btn-primary btn-full" type="submit">

            <span class="btn-text">Envoyer le code OTP</span>

            <span class="btn-loader hidden spinner">⏳</span>

        </button>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/Projet_Soutenance/Projet-definitif-de-soutenance/resources/views/auth/login.blade.php ENDPATH**/ ?>