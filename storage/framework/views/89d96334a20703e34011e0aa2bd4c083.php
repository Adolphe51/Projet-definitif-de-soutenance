<?php $__env->startSection('content'); ?>

    <div>
        <form method="POST" action="<?php echo e(route('otp.send')); ?>" class="auth-form">
            <?php echo csrf_field(); ?>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Adresse email professionnelle</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="administrateur@entreprise.com"
                    value="<?php echo e(old('email')); ?>" autocomplete="email" required autofocus>
                <p class="form-help">
                    Un code de vérification à 8 chiffres sera envoyé à cette adresse email.
                </p>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Mot de passe administrateur</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••••••"
                    autocomplete="current-password" required minlength="8">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-button">
                <div class="spinner"></div>
                <span>Continuer vers la vérification</span>
            </button>
        </form>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/auth/login.blade.php ENDPATH**/ ?>