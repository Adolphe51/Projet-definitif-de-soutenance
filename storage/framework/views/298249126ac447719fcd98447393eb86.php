<!-- Ressource: resources/views/components/form-field.blade.php -->
<div class="form-group <?php echo e($errors->has($name ?? '') ? 'has-error' : ''); ?>">
    <label for="<?php echo e($id ?? $name ?? ''); ?>">
        <?php if($required ?? false): ?>
            <span class="required">*</span>
        <?php endif; ?>
        <?php echo e($label ?? ucfirst(str_replace('_', ' ', $name ?? ''))); ?>

    </label>
    
    <?php if($type === 'textarea'): ?>
        <textarea 
            id="<?php echo e($id ?? $name ?? ''); ?>"
            name="<?php echo e($name ?? ''); ?>"
            rows="<?php echo e($rows ?? 4); ?>"
            <?php if($required ?? false): ?> required <?php endif; ?>
            <?php echo e($attributes); ?>

        ><?php echo e($value ?? old($name ?? '')); ?></textarea>
    <?php elseif($type === 'select'): ?>
        <select 
            id="<?php echo e($id ?? $name ?? ''); ?>"
            name="<?php echo e($name ?? ''); ?>"
            <?php if($required ?? false): ?> required <?php endif; ?>
            <?php echo e($attributes); ?>

        >
            <option value=""><?php echo e($placeholder ?? 'Sélectionner...'); ?></option>
            <?php $__currentLoopData = $options ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optValue => $optLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($optValue); ?>" <?php if(($value ?? old($name)) == $optValue): echo 'selected'; endif; ?>>
                    <?php echo e($optLabel); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    <?php else: ?>
        <input 
            type="<?php echo e($type ?? 'text'); ?>"
            id="<?php echo e($id ?? $name ?? ''); ?>"
            name="<?php echo e($name ?? ''); ?>"
            value="<?php echo e($value ?? old($name ?? '')); ?>"
            placeholder="<?php echo e($placeholder ?? ''); ?>"
            <?php if($required ?? false): ?> required <?php endif; ?>
            <?php echo e($attributes); ?>

        >
    <?php endif; ?>

    <?php if($errors->has($name ?? '')): ?>
        <div class="form-error">
            <?php echo e($errors->first($name ?? '')); ?>

        </div>
    <?php endif; ?>
    
    <?php if($hint ?? null): ?>
        <small class="text-muted"><?php echo e($hint); ?></small>
    <?php endif; ?>
</div>
<?php /**PATH /home/olivierfatombi/Desktop/prog/dev/memo/Projet-definitif-de-soutenance/resources/views/components/form-field.blade.php ENDPATH**/ ?>