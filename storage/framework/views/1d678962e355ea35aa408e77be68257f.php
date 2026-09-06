<?php $__env->startSection('title', ($category->name_ne ?? $category->name_en) . ' — nkhoj'); ?>

<?php $__env->startSection('content'); ?>
<?php $activeTab = request('tab', 'posts'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-8 bg-brand-500 rounded-full"></div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($category->name_ne ?? $category->name_en); ?></h1>
                <p class="text-sm text-gray-400"><?php echo e($posts->total()); ?> articles · <?php echo e($questions->total()); ?> questions</p>
            </div>
        </div>

        
        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl mb-5">
            <a href="?tab=posts"
                class="flex-1 text-center py-1.5 text-sm font-medium rounded-lg transition-colors <?php echo e($activeTab === 'posts' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
                Articles (<?php echo e($posts->total()); ?>)
            </a>
            <a href="?tab=questions"
                class="flex-1 text-center py-1.5 text-sm font-medium rounded-lg transition-colors <?php echo e($activeTab === 'questions' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
                Questions (<?php echo e($questions->total()); ?>)
            </a>
        </div>

        
        <?php if($activeTab === 'posts'): ?>
        <div class="space-y-5">
            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow p-5 flex gap-4">
                <?php if($post->thumbnail_url): ?>
                <a href="/posts/<?php echo e($post->slug); ?>" class="flex-shrink-0">
                    <img src="<?php echo e($post->thumbnail_url); ?>" alt="" class="w-28 h-20 object-cover rounded-lg">
                </a>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 dark:text-white hover:text-brand-600 transition-colors line-clamp-2 mb-1">
                        <a href="/posts/<?php echo e($post->slug); ?>"><?php echo e($post->title); ?></a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-2"><?php echo e($post->excerpt); ?></p>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <span><?php echo e($post->author->name); ?></span>
                        <span>·</span>
                        <span><?php echo e($post->published_at->diffForHumans()); ?></span>
                    </div>
                </div>
            </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <p class="text-gray-400">No articles in this category yet.</p>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-6"><?php echo e($posts->appends(['tab' => 'posts'])->links()); ?></div>
        <?php endif; ?>

        
        <?php if($activeTab === 'questions'): ?>
        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 flex gap-4 hover:shadow-md transition-shadow">
                
                <div class="flex flex-col items-center gap-3 flex-shrink-0 text-center w-12">
                    <div class="text-sm font-bold <?php echo e($q->votes > 0 ? 'text-brand-600' : 'text-gray-500 dark:text-gray-400'); ?>">
                        <?php echo e($q->votes); ?>

                        <span class="block text-[10px] font-normal text-gray-400">votes</span>
                    </div>
                    <div class="text-sm font-bold <?php echo e($q->best_answer_id ? 'text-green-500' : 'text-gray-500 dark:text-gray-400'); ?>">
                        <?php echo e($q->answers_count); ?>

                        <span class="block text-[10px] font-normal text-gray-400">answers</span>
                    </div>
                </div>
                
                <div class="flex-1 min-w-0">
                    <a href="/questions/<?php echo e($q->slug); ?>"
                        class="text-base font-semibold text-gray-900 dark:text-white hover:text-brand-600 leading-snug block mb-1">
                        <?php echo e($q->title); ?>

                    </a>
                    <?php if($q->content): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-2"><?php echo e(strip_tags($q->content)); ?></p>
                    <?php endif; ?>
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <?php if($q->best_answer_id): ?>
                        <span class="px-2 py-0.5 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-full font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Answered
                        </span>
                        <?php endif; ?>
                        <span><?php echo e($q->is_anonymous ? 'Anonymous' : ($q->user->name ?? '')); ?></span>
                        <span>·</span>
                        <span><?php echo e($q->created_at->diffForHumans()); ?></span>
                        <span>·</span>
                        <span><?php echo e($q->views_count); ?> views</span>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <p class="text-gray-400">No questions in this category yet.</p>
                <a href="/ask-question" class="inline-block mt-3 px-4 py-2 bg-brand-500 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 transition-colors">Ask the first question</a>
            </div>
            <?php endif; ?>
        </div>
        <div class="mt-6"><?php echo e($questions->appends(['tab' => 'questions'])->links()); ?></div>
        <?php endif; ?>
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4">All Categories</h3>
            <div class="space-y-1">
                <?php
                try {
                    $allCats = \App\Models\Category::withCount(['posts' => fn($q) => $q->published()])
                        ->withCount('questions')
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->get();
                } catch (\Exception $e) { $allCats = collect(); }
                ?>
                <?php $__currentLoopData = $allCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="/category/<?php echo e($cat->slug); ?>"
                    class="flex justify-between items-center py-2 text-sm <?php echo e($cat->id === $category->id ? 'text-brand-600 font-semibold' : 'text-gray-600 dark:text-gray-400 hover:text-brand-600'); ?> transition-colors border-b border-gray-50 dark:border-gray-700 last:border-0">
                    <span><?php echo e($cat->name_ne ?? $cat->name_en); ?></span>
                    <div class="flex items-center gap-1 text-xs text-gray-400">
                        <span class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded"><?php echo e($cat->posts_count); ?>p</span>
                        <?php if($cat->questions_count > 0): ?>
                        <span class="bg-brand-50 dark:bg-brand-900/20 text-brand-600 px-1.5 py-0.5 rounded"><?php echo e($cat->questions_count); ?>q</span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <a href="/ask-question"
            class="w-full flex items-center justify-center gap-2 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors block">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Ask a Question
        </a>
    </aside>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\nkhoj\resources\views/categories/show.blade.php ENDPATH**/ ?>