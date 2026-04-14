<?php $__env->startSection('content'); ?>

    <div>
        <form method="POST" action="<?php echo e(route('otp.verify')); ?>" class="auth-form otp-form">
            <?php echo csrf_field(); ?>

            <!-- Email Display -->
            <div class="form-group">
                <label for="email" class="form-label">Compte vérifié</label>

                <input type="email" class="form-input" id="email" name="email"
                    value="<?php echo e(old('email', session('otp_email'))); ?>" readonly aria-readonly="true">
            </div>

            <!-- OTP Input -->
            <div class="form-group">
                <label for="code" class="form-label">Code de vérification à 6 chiffres</label>
                <div class="otp-container" aria-label="Code OTP">
                    <?php for($i = 0; $i < 8; $i++): ?>
                        <input type="text" class="otp-input" inputmode="numeric" maxlength="1" autocomplete="one-time-code">
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="code" id="otpCode">

            </div>

            <!-- OTP Timer -->
            <div class="otp-info" role="status" aria-live="polite">
                <span>Code valable pendant</span>
                <strong id="otpTimer" class="otp-timer">02:00</strong>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="auth-button">
                <div class="spinner"></div>
                <span>Authentifier et accéder au tableau de bord</span>
            </button>
        </form>

        <!-- Resend Form -->
        <div class="form-actions">
            <form method="POST" action="<?php echo e(route('otp.resend')); ?>" class="resend-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="email" value="<?php echo e(old('email', session('otp_email'))); ?>">
                <button id="resendBtn" class="auth-button secondary" disabled>
                    <span>Renvoyer le code dans <span id="resendTimer">180</span>s</span>
                </button>
            </form>

            <a href="<?php echo e(route('login')); ?>" class="form-link">
                Retour à la connexion
            </a>
        </div>
    </div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.auth.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/auth/verify-otp.blade.php ENDPATH**/ ?>