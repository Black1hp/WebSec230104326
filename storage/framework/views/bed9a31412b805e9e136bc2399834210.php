<?php $__env->startSection('title', 'Edit Page'); ?>
<?php $__env->startSection('content'); ?>

    <form action="<?php echo e(route('products.save', $product->id)); ?>" method="post">
        <?php echo e(csrf_field()); ?>


        <div class="row mb-2">
            <div class="col-6">
                <label for="code" class="form-label">Code:</label>
                <input type="text" class="form-control" placeholder="Code" name="code" required value="<?php echo e($product->code); ?>">
            </div>
            <div class="col-6">
                <label for="model" class="form-label">Model:</label>
                <input type="text" class="form-control" placeholder="Model" name="model" required value="<?php echo e($product->model); ?>">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" placeholder="Name" name="name" required value="<?php echo e($product->name); ?>">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-6">
                <label for="model" class="form-label">Price:</label>
                <input type="numeric" class="form-control" placeholder="Price" name="price" required value="<?php echo e($product->price); ?>">
            </div>
            <div class="col-6">
                <label for="model" class="form-label">Photo:</label>
                <input type="text" class="form-control" placeholder="Photo" name="photo" required value="<?php echo e($product->photo); ?>">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <label for="name" class="form-label">Description:</label>
                <textarea type="text" class="form-control" placeholder="Description" name="description" required><?php echo e($product->description); ?></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\test\WebSec230104326\resources\views/products/edit.blade.php ENDPATH**/ ?>