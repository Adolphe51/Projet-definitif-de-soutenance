<?php $__env->startSection('title', 'Vérification OTP'); ?>

<?php $__env->startSection('content'); ?>

<!-- Formulaire vérification OTP -->
<div class="auth-step">

    <form method="POST" action="<?php echo e(route('otp.verify')); ?>" id="otpForm">
        <?php echo csrf_field(); ?>

        
        <div class="form-group">
            <label for="email" class="form-label">Adresse email</label>
            <input
                type="email"
                class="form-input"
                id="email"
                name="email"
                value="<?php echo e(old('email', session('otp_email'))); ?>"
                readonly>
        </div>

        
        <div class="form-group">
            <label for="code" class="form-label">Code OTP</label>
            <div class="otp-inputs">
                <?php for($i = 0; $i < 6; $i++): ?>
                    <input maxlength="1" class="otp-digit" inputmode="numeric">
                    <?php endfor; ?>
            </div>
            <input type="hidden" name="code" id="otpCode">

            <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="form-error"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <?php if(session('otp_error')): ?>
            <div class="form-error"><?php echo e(session('otp_error')); ?></div>
            <?php endif; ?>

            
            <?php if(app()->environment('local') && session('debug_otp')): ?>
            <div class="form-debug">
                Code OTP (dev) : <strong><?php echo e(session('debug_otp')); ?></strong>
            </div>
            <?php endif; ?>
        </div>

        
        <div class="otp-info">
            <span>Code valable pendant</span>
            <strong id="otpTimer">02:00</strong>
        </div>

        <button type="submit" class="btn btn-primary btn-full">
            Vérifier le code
        </button>

    </form>

    
    <form method="POST" action="<?php echo e(route('otp.send')); ?>" class="otp-resend" id="resendForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="email" value="<?php echo e(old('email', session('otp_email'))); ?>">
        <button id="resendBtn" class="btn-link" disabled>
            Renvoyer le code (<span id="resendTimer">180</span>s)
        </button>
    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.auth.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/Projet_Soutenance/Projet-definitif-de-soutenance/resources/views/auth/verify-otp.blade.php ENDPATH**/ ?>