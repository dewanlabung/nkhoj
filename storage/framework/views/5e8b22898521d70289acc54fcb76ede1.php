<?php $__env->startSection('title', 'nkhoj — नेपाली समाचार'); ?>

<?php $__env->startSection('content'); ?>


<?php if($heroStrip->count()): ?>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
    <?php $__currentLoopData = $heroStrip; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $hero): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="/posts/<?php echo e($hero->slug); ?>" class="group relative rounded-xl overflow-hidden block <?php echo e($i === 0 ? 'sm:col-span-1' : ''); ?>" style="min-height:200px;">
        
        <?php if($hero->thumbnail_url): ?>
        <img src="<?php echo e($hero->thumbnail_url); ?>" alt="<?php echo e($hero->title); ?>"
            class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        <?php else: ?>
        <div class="absolute inset-0 bg-gradient-to-br
            <?php echo e($i === 0 ? 'from-brand-500 to-indigo-700' : ($i === 1 ? 'from-teal-500 to-cyan-700' : 'from-orange-500 to-pink-700')); ?>">
        </div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
        <div class="relative flex flex-col justify-end h-full p-4" style="min-height:200px;">
            <?php if($hero->is_featured): ?>
            <span class="text-xs font-bold text-brand-300 uppercase tracking-widest mb-1">Featured</span>
            <?php endif; ?>
            <span class="text-xs text-white/60 font-nepali mb-1"><?php echo e($hero->category->name_ne ?? $hero->category->name_en); ?></span>
            <h2 class="text-sm font-bold text-white line-clamp-2 leading-snug font-nepali group-hover:text-brand-200 transition-colors"><?php echo e($hero->title); ?></h2>
            <div class="flex items-center gap-2 mt-2 text-xs text-white/50">
                <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-xs"><?php echo e(strtoupper(substr($hero->author->name,0,1))); ?></div>
                <span><?php echo e($hero->author->name); ?></span>
                <span>·</span>
                <span><?php echo e($hero->published_at->diffForHumans()); ?></span>
            </div>
        </div>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="flex items-center gap-1.5 overflow-x-auto py-2 mb-6 border-b border-gray-200 scrollbar-hide">
    <a href="/" class="whitespace-nowrap px-4 py-1.5 text-sm font-semibold rounded-full <?php echo e(request()->is('/') && !request('cat') ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100'); ?> transition-colors">
        सम्पूर्ण
    </a>
    <?php $__currentLoopData = \App\Models\Category::orderBy('sort_order')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="/category/<?php echo e($cat->slug); ?>" class="whitespace-nowrap px-4 py-1.5 text-sm font-medium rounded-full <?php echo e(request()->is('category/'.$cat->slug) ? 'bg-brand-500 text-white' : 'text-gray-600 hover:bg-gray-100'); ?> transition-colors font-nepali">
        <?php echo e($cat->name_ne ?? $cat->name_en); ?>

    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    
    <div class="lg:col-span-3">

        
        <?php if($editorsPick->count()): ?>
        <div class="mb-7">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs font-bold text-brand-600 uppercase tracking-widest">सम्पादकको छनोट</span>
                <div class="flex-1 h-px bg-gray-100"></div>
                <span class="text-xs text-gray-400">Editor's Pick</span>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <?php $__currentLoopData = $editorsPick; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pick): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="/posts/<?php echo e($pick->slug); ?>" class="group bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow overflow-hidden block">
                    <div class="relative" style="aspect-ratio:16/9; overflow:hidden;">
                        <?php if($pick->thumbnail_url): ?>
                        <img src="<?php echo e($pick->thumbnail_url); ?>" alt="<?php echo e($pick->title); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-brand-100 to-indigo-100 flex items-center justify-center">
                            <span class="text-3xl font-black text-brand-200"><?php echo e(strtoupper(substr($pick->title,0,1))); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="absolute top-2 left-2">
                            <span class="text-xs px-2 py-0.5 bg-brand-500 text-white rounded-full font-semibold"><?php echo e($pick->category->name_ne ?? $pick->category->name_en); ?></span>
                        </div>
                    </div>
                    <div class="p-3">
                        <h3 class="text-sm font-bold text-gray-900 group-hover:text-brand-600 transition-colors line-clamp-2 font-nepali leading-snug"><?php echo e($pick->title); ?></h3>
                        <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-400">
                            <div class="w-4 h-4 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-xs"><?php echo e(strtoupper(substr($pick->author->name,0,1))); ?></div>
                            <span><?php echo e($pick->author->name); ?></span>
                            <span>·</span>
                            <span><?php echo e(number_format($pick->view_count)); ?> views</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-widest">नवीनतम</h2>
            <div class="flex-1 h-px bg-gray-100"></div>
            <span class="text-xs text-gray-400">Latest posts</span>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden group flex flex-col">

                
                <a href="/posts/<?php echo e($post->slug); ?>" class="block relative overflow-hidden flex-shrink-0" style="aspect-ratio:16/9;">
                    <?php if($post->thumbnail_url): ?>
                    <img src="<?php echo e($post->thumbnail_url); ?>" alt="<?php echo e($post->title); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-brand-50 via-indigo-50 to-brand-100 flex items-center justify-center">
                        <span class="text-4xl font-black text-brand-200/80"><?php echo e(strtoupper(substr($post->title,0,1))); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="absolute top-3 left-3">
                        <a href="/category/<?php echo e($post->category->slug); ?>"
                            class="text-xs px-2.5 py-1 bg-brand-500 text-white rounded-full font-bold hover:bg-brand-600 transition-colors">
                            <?php echo e($post->category->name_ne ?? $post->category->name_en); ?>

                        </a>
                    </div>
                </a>

                
                <div class="p-4 flex-1 flex flex-col">
                    
                    <div class="flex items-center gap-2 mb-2">
                        <a href="/profile/<?php echo e($post->author->username); ?>" class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0 hover:ring-2 hover:ring-brand-300 transition-all">
                            <?php echo e(strtoupper(substr($post->author->name,0,1))); ?>

                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="/profile/<?php echo e($post->author->username); ?>" class="text-xs font-semibold text-gray-700 hover:text-brand-600 transition-colors"><?php echo e($post->author->name); ?></a>
                            <div class="text-xs text-gray-400"><?php echo e($post->published_at->diffForHumans()); ?></div>
                        </div>
                        <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->id() !== $post->author_id): ?>
                        <button onclick="toggleFollow(this, <?php echo e($post->author_id); ?>)"
                            class="text-xs px-2 py-1 border border-gray-200 rounded-full text-gray-500 hover:border-brand-400 hover:text-brand-600 transition-colors flex-shrink-0"
                            data-following="false">
                            + Add
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    
                    <h3 class="font-bold text-gray-900 group-hover:text-brand-600 transition-colors line-clamp-2 text-sm leading-snug font-nepali flex-1 mb-2">
                        <a href="/posts/<?php echo e($post->slug); ?>"><?php echo e($post->title); ?></a>
                    </h3>

                    
                    <?php if($post->excerpt): ?>
                    <p class="text-xs text-gray-500 line-clamp-2 mb-3 font-nepali leading-relaxed"><?php echo e($post->excerpt); ?></p>
                    <?php endif; ?>

                    
                    <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                        <div class="flex gap-1">
                            <?php $__currentLoopData = $post->tags->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="/tag/<?php echo e($tag->slug); ?>" class="text-xs text-brand-500 hover:text-brand-700 font-medium">#<?php echo e($tag->name_en); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span><?php echo e($post->readingTimeMinutes()); ?>min</span>
                            <span>·</span>
                            <span><?php echo e(number_format($post->view_count)); ?> views</span>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-2 text-center py-16 bg-white rounded-xl border border-gray-100">
                <div class="text-5xl mb-4">📰</div>
                <p class="text-gray-500 font-nepali">अहिलेसम्म कुनै लेख छैन।</p>
                <?php if(auth()->guard()->check()): ?>
                <a href="/dashboard/posts/create" class="mt-4 inline-block px-4 py-2 bg-brand-500 text-white text-sm rounded-lg hover:bg-brand-600">
                    पहिलो लेख लेख्नुहोस्
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if($posts->hasPages()): ?>
        <div class="flex justify-center mt-8"><?php echo e($posts->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <aside class="space-y-4">

        
        <?php if(auth()->guard()->check()): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-brand-500 to-indigo-600 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">
                        <?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?>

                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm"><?php echo e(auth()->user()->name); ?></p>
                        <p class="text-white/60 text-xs">{{ auth()->user()->username }}</p>
                    </div>
                </div>
                <form method="POST" action="/logout" class="inline">
                    <?php echo csrf_field(); ?>
                    <button class="text-xs text-white/60 hover:text-white">log out</button>
                </form>
            </div>
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <a href="/profile/<?php echo e(auth()->user()->username); ?>"
                        class="text-center py-2 text-sm font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition-colors border border-brand-200">
                        मेरो ब्लग
                    </a>
                    <a href="/dashboard/posts/create"
                        class="text-center py-2 text-sm font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-lg transition-colors">
                        ✍ लेख्नुहोस्
                    </a>
                </div>
                <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                    <div class="flex border-b border-gray-100 dark:border-gray-700 mb-2 text-xs">
                        <span class="pb-1.5 px-2 font-semibold text-brand-600 border-b-2 border-brand-500">मेरो समाचार</span>
                        <a href="/dashboard" class="pb-1.5 px-2 text-gray-400 hover:text-gray-600">गतिविधि</a>
                    </div>
                    <p class="text-xs text-gray-400 text-center py-3 font-nepali">नयाँ समाचार छैन।</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 text-center">
            <div class="text-4xl mb-3">✍️</div>
            <h3 class="font-bold text-gray-900 dark:text-white mb-1 font-nepali">नखोजमा सामेल हुनुहोस्</h3>
            <p class="text-xs text-gray-400 mb-4">हजारौं नेपाली पाठकसँग आफ्नो कथा साझा गर्नुहोस्।</p>
            <a href="/register" class="block text-center bg-brand-500 text-white font-semibold text-sm py-2.5 rounded-lg hover:bg-brand-600 transition-colors mb-2">
                Free मा सुरु गर्नुहोस्
            </a>
            <a href="/login" class="block text-center text-sm text-gray-400 hover:text-gray-600">साइन इन</a>
        </div>
        <?php endif; ?>

        
        <?php if($sidebarWidgets->isNotEmpty()): ?>
            <?php $__currentLoopData = $sidebarWidgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('partials._widget', ['widget' => $widget, 'data' => $widgetData], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm flex items-center gap-1.5">
                    <span class="text-red-500">🔥</span> ट्रेन्डिङ
                </h3>
                <ol class="space-y-2.5">
                    <?php $__currentLoopData = $trending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex gap-2.5 items-start">
                        <span class="text-lg font-black leading-none mt-0.5 min-w-[18px] <?php echo e($i < 3 ? 'text-brand-500' : 'text-gray-200 dark:text-gray-600'); ?>"><?php echo e($i+1); ?></span>
                        <div class="flex-1 min-w-0">
                            <a href="/posts/<?php echo e($post->slug); ?>" class="text-xs font-medium text-gray-800 dark:text-gray-200 hover:text-brand-600 dark:hover:text-brand-400 transition-colors line-clamp-2 font-nepali leading-snug"><?php echo e($post->title); ?></a>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo e(number_format($post->view_count)); ?> views</p>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
                <h3 class="font-bold text-gray-900 dark:text-white mb-3 text-sm">📂 विषयहरू</h3>
                <div class="space-y-1">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="/category/<?php echo e($cat->slug); ?>"
                        class="flex items-center justify-between py-1.5 text-sm text-gray-600 dark:text-gray-300 hover:text-brand-600 dark:hover:text-brand-400 transition-colors group">
                        <span class="font-nepali text-sm"><?php echo e($cat->name_ne ?? $cat->name_en); ?></span>
                        <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full group-hover:bg-brand-50 dark:group-hover:bg-brand-900/30 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors"><?php echo e($cat->posts_count); ?></span>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

    </aside>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
async function toggleFollow(btn, userId) {
    btn.disabled = true;
    try {
        const r = await fetch('/follow/' + userId, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const d = await r.json();
        if (d.action === 'followed') {
            btn.textContent = '✓ Following';
            btn.classList.add('border-brand-400', 'text-brand-600');
            btn.dataset.following = 'true';
        } else {
            btn.textContent = '+ Add';
            btn.classList.remove('border-brand-400', 'text-brand-600');
            btn.dataset.following = 'false';
        }
    } catch(e) {}
    btn.disabled = false;
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\nkhoj\resources\views/home.blade.php ENDPATH**/ ?>