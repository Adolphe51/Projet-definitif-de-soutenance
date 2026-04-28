<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $__env->yieldContent('title', 'CyberGuard'); ?> — CyberGuard</title>

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/auth.css', 'resources/js/auth.js']); ?>
</head>

<body>

    <div class="auth-container">

        <main class="auth-card">

            <?php echo $__env->make('layouts.auth.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <section class="auth-body">
                <?php echo $__env->yieldContent('content'); ?>
            </section>

        </main>

        <div id="toastContainer" class="toast-container"></div>

    </div>

    <?php if(session('success')): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session success détectée:', "<?php echo e(session('success')); ?>");
            toast.success("<?php echo e(session('success')); ?>");
        });
    </script>
    <?php endif; ?>

    <?php if(session('error')): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session error détectée:', "<?php echo e(session('error')); ?>");
            toast.error("<?php echo e(session('error')); ?>");
        });
    </script>
    <?php endif; ?>

    <?php if(session('info')): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session info détectée:', "<?php echo e(session('info')); ?>");
            toast.info("<?php echo e(session('info')); ?>");
        });
    </script>
    <?php endif; ?>

    <?php if(session('warning')): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log('Session warning détectée:', "<?php echo e(session('warning')); ?>");
            toast.warning("<?php echo e(session('warning')); ?>");
        });
    </script>
    <?php endif; ?>

    <?php if(session('debug_otp_toast')): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            toast.show("Code OTP (développement) : <?php echo e(session('debug_otp_toast')); ?>", "info", 45000);
        })
    </script>
    <?php endif; ?>

    <?php if(!empty($rateLimitError)): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            toast.error(<?php echo json_encode($rateLimitError, 15, 512) ?>);
        });
    </script>
    <?php endif; ?>

</body>

</html>
<?php /**PATH /home/kayc/Projet-definitif-de-soutenance/resources/views/layouts/auth/app.blade.php ENDPATH**/ ?>