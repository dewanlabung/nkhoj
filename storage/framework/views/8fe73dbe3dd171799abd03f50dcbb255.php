<?php $__env->startSection('title', 'Edit Question'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Question</h1>
                <p class="text-sm text-gray-400 mt-0.5">Update your question details</p>
            </div>
            <a href="/questions/<?php echo e($question->slug); ?>" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        </div>

        <?php if($errors->any()): ?>
        <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
            <?php echo e($errors->first()); ?>

        </div>
        <?php endif; ?>

        <form method="POST" action="/questions/<?php echo e($question->id); ?>" enctype="multipart/form-data" class="space-y-5">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">
                    Question Title <span class="text-red-400">*</span>
                </label>
                <input type="text" name="title" value="<?php echo e(old('title', $question->title)); ?>" required
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Category</label>
                <select name="category_id"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">— None —</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cat->id); ?>" <?php echo e(old('category_id', $question->category_id) == $cat->id ? 'selected' : ''); ?>>
                        <?php echo e($cat->name_ne ?? $cat->name_en); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Question Details</label>
                <textarea name="content" rows="7"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y"><?php echo e(old('content', $question->content)); ?></textarea>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Tags</label>
                <input type="text" name="tags" value="<?php echo e(old('tags', $selectedTags)); ?>"
                    placeholder="comma separated"
                    class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <?php if($tags->count()): ?>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    <?php $__currentLoopData = $tags->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button" onclick="addTag('<?php echo e($tag->name_en); ?>')"
                        class="text-xs px-2.5 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 hover:text-brand-600 text-gray-600 dark:text-gray-300 rounded-full transition-colors">
                        +<?php echo e($tag->name_en); ?>

                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">
                    Replace Image <span class="font-normal text-gray-400">(optional)</span>
                </label>
                <?php if($question->featured_image): ?>
                <img src="<?php echo e(asset('storage/'.$question->featured_image)); ?>" class="w-32 h-20 object-cover rounded-lg mb-2">
                <?php endif; ?>
                <input type="file" name="featured_image" accept="image/*"
                    class="w-full text-sm text-gray-500 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-2.5 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100">
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_anonymous" value="1" <?php echo e(old('is_anonymous', $question->is_anonymous) ? 'checked' : ''); ?>

                    class="w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                <span class="text-sm text-gray-700 dark:text-gray-300">Post anonymously</span>
            </label>

            <div class="flex gap-3 pt-2">
                <a href="/questions/<?php echo e($question->slug); ?>"
                    class="px-5 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</a>
                <button type="submit"
                    class="flex-1 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function addTag(name) {
    const input = document.querySelector('input[name="tags"]');
    const parts = input.value.split(',').map(t => t.trim()).filter(Boolean);
    if (!parts.includes(name)) parts.push(name);
    input.value = parts.join(', ');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\nkhoj\resources\views/questions/edit.blade.php ENDPATH**/ ?>