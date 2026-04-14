<?php $__env->startSection('title', 'Intranet Académique'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Intranet Académique</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Étudiants -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h2 class="text-xl font-semibold text-blue-800 mb-2">Étudiants</h2>
                    <p class="text-blue-600 mb-4">Gérer les informations des étudiants</p>
                    <a href="<?php echo e(route('intranet.students.index')); ?>"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Voir les étudiants
                    </a>
                </div>

                <!-- Cours -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <h2 class="text-xl font-semibold text-green-800 mb-2">Cours</h2>
                    <p class="text-green-600 mb-4">Gérer les cours et programmes</p>
                    <a href="<?php echo e(route('intranet.courses.index')); ?>"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        Voir les cours
                    </a>
                </div>

                <!-- Messages -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h2 class="text-xl font-semibold text-purple-800 mb-2">Messages</h2>
                    <p class="text-purple-600 mb-4">Communications internes</p>
                    <a href="<?php echo e(route('intranet.messages.index')); ?>"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                        Voir les messages
                    </a>
                </div>

                <!-- Inscriptions -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h2 class="text-xl font-semibold text-yellow-800 mb-2">Inscriptions</h2>
                    <p class="text-yellow-600 mb-4">Gérer les inscriptions aux cours</p>
                    <a href="<?php echo e(route('intranet.enrollments.index')); ?>"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                        Voir les inscriptions
                    </a>
                </div>

                <!-- Présences -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h2 class="text-xl font-semibold text-red-800 mb-2">Présences</h2>
                    <p class="text-red-600 mb-4">Suivre les présences aux cours</p>
                    <a href="<?php echo e(route('intranet.attendances.index')); ?>"
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                        Voir les présences
                    </a>
                </div>

                <!-- Ressources -->
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <h2 class="text-xl font-semibold text-indigo-800 mb-2">Ressources</h2>
                    <p class="text-indigo-600 mb-4">Matériels pédagogiques</p>
                    <a href="#" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                        Voir les ressources
                    </a>
                </div>
            </div>

            <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">⚠️ Zone de Test Sécurisé</h3>
                <p class="text-gray-600">
                    Cet intranet est un environnement de test isolé pour CyberGuard.
                    Toutes les données sont fictives et générées automatiquement.
                </p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/intranet/index.blade.php ENDPATH**/ ?>