<?php $__env->startSection('title', 'Questions'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Questions</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage community Q&A questions</p>
        </div>
        <a href="/ask-question" target="_blank"
            class="flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Ask a Question
        </a>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <?php $__currentLoopData = [
            ['Total', $stats['total'], 'text-gray-700 dark:text-white', 'bg-gray-50 dark:bg-gray-800'],
            ['Open', $stats['open'], 'text-blue-600', 'bg-blue-50 dark:bg-blue-900/20'],
            ['Answered', $stats['answered'], 'text-green-600', 'bg-green-50 dark:bg-green-900/20'],
            ['Pending', $stats['pending'], 'text-amber-600', 'bg-amber-50 dark:bg-amber-900/20'],
            ['Closed', $stats['closed'], 'text-red-500', 'bg-red-50 dark:bg-red-900/20'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $count, $text, $bg]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-xl <?php echo e($bg); ?> p-4 text-center">
            <p class="text-2xl font-bold <?php echo e($text); ?>"><?php echo e($count); ?></p>
            <p class="text-xs text-gray-500 mt-0.5"><?php echo e($label); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search questions…"
            class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 w-64">
        <select name="status"
            class="text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">All Status</option>
            <?php $__currentLoopData = ['open','closed','pending']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($s); ?>" <?php echo e(request('status') === $s ? 'selected' : ''); ?>><?php echo e(ucfirst($s)); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <button class="px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold rounded-xl hover:bg-gray-700 transition-colors">Filter</button>
        <?php if(request('q') || request('status')): ?>
        <a href="/admin/questions" class="text-sm text-gray-400 hover:text-gray-600">Clear</a>
        <?php endif; ?>
    </form>

    <?php if(session('success')): ?>
    <div class="px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl text-green-700 dark:text-green-400 text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Question</th>
                        <th class="px-4 py-3 text-left">Author</th>
                        <th class="px-4 py-3 text-center">Answers</th>
                        <th class="px-4 py-3 text-center">Views</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Date</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-5 py-4 max-w-xs">
                            <div class="flex items-start gap-2">
                                <?php if($q->best_answer_id): ?>
                                <span title="Has best answer" class="mt-1 w-4 h-4 flex-shrink-0 text-green-500">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <?php endif; ?>
                                <div>
                                    <a href="/questions/<?php echo e($q->slug); ?>" target="_blank"
                                        class="font-medium text-gray-900 dark:text-white hover:text-brand-600 leading-snug line-clamp-2">
                                        <?php echo e($q->title); ?>

                                    </a>
                                    <?php if($q->category): ?>
                                    <span class="text-xs text-brand-500 mt-0.5 block"><?php echo e($q->category->name_ne ?? $q->category->name_en); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="text-gray-700 dark:text-gray-300">
                                <?php echo e($q->is_anonymous ? 'Anonymous' : ($q->user->name ?? '—')); ?>

                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="font-semibold <?php echo e($q->answers_count > 0 ? 'text-green-600' : 'text-gray-400'); ?>">
                                <?php echo e($q->answers_count); ?>

                            </span>
                        </td>
                        <td class="px-4 py-4 text-center text-gray-500"><?php echo e(number_format($q->views_count)); ?></td>
                        <td class="px-4 py-4 text-center">
                            <form method="POST" action="/admin/questions/<?php echo e($q->id); ?>/status">
                                <?php echo csrf_field(); ?>
                                <select name="status" onchange="this.form.submit()"
                                    class="text-xs px-2 py-1 rounded-lg border <?php echo e($q->status === 'open' ? 'border-blue-300 text-blue-600 bg-blue-50 dark:bg-blue-900/20' : ($q->status === 'closed' ? 'border-red-300 text-red-600 bg-red-50 dark:bg-red-900/20' : 'border-amber-300 text-amber-600 bg-amber-50 dark:bg-amber-900/20')); ?> focus:outline-none">
                                    <option value="open"    <?php echo e($q->status === 'open'    ? 'selected' : ''); ?>>Open</option>
                                    <option value="closed"  <?php echo e($q->status === 'closed'  ? 'selected' : ''); ?>>Closed</option>
                                    <option value="pending" <?php echo e($q->status === 'pending' ? 'selected' : ''); ?>>Pending</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-4 py-4 text-center text-xs text-gray-400 whitespace-nowrap">
                            <?php echo e($q->created_at->format('M j, Y')); ?>

                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="/questions/<?php echo e($q->slug); ?>" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-brand-100 text-gray-500 hover:text-brand-600 transition-colors" title="View">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <form method="POST" action="/admin/questions/<?php echo e($q->id); ?>"
                                    onsubmit="return confirm('Delete this question and all its answers?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-red-100 text-gray-500 hover:text-red-600 transition-colors" title="Delete">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            No questions found.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($questions->hasPages()): ?>
        <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
            <?php echo e($questions->appends(request()->query())->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\nkhoj\resources\views/admin/questions.blade.php ENDPATH**/ ?>