 <form method="POST" action="<?php echo e(route('logout')); ?>">
     <?php echo csrf_field(); ?>
     <button type="submit" class="btn-logout" title="Déconnexion">
         <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
             <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
             <polyline points="16 17 21 12 16 7"></polyline>
             <line x1="21" y1="12" x2="9" y2="12"></line>
         </svg>
     </button>
 </form><?php /**PATH /home/olivierfatombi/Desktop/Projet_Soutenance/Projet-definitif-de-soutenance/resources/views/layouts/header.blade.php ENDPATH**/ ?>