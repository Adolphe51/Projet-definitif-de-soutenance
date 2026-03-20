<!-- resources/views/welcome.blade.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberGuard</title>
    <!-- Alpine.js pour la dynamique -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Bienvenue sur CyberGuard</h1>

        <p class="text-center mb-6 text-gray-600">
            Connectez-vous pour accéder au tableau de bord sécurisé.
        </p>

        <!-- Bouton login classique -->
        <div class="flex flex-col gap-4">
            <a href="<?php echo e(route('login')); ?>" 
               class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-center">
               Connexion Admin
            </a>

            <!-- Bouton optionnel pour reconnaissance faciale -->
            <a href="<?php echo e(route('face.login')); ?>" 
               class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700 text-center">
               Connexion par Reconnaissance Faciale
            </a>
        </div>

        <p class="text-center mt-6 text-gray-400 text-sm">
            © <?php echo e(date('Y')); ?> CyberGuard. Tous droits réservés.
        </p>
    </div>

</body>
</html><?php /**PATH /home/olivierfatombi/Desktop/Projet_Soutenance/Projet-definitif-de-soutenance/resources/views/welcome.blade.php ENDPATH**/ ?>