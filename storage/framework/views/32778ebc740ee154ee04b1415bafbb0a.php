<?php $__env->startSection('title', $question->title); ?>

<?php $__env->startSection('content'); ?>
<?php $sort = request('sort', 'voted'); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    
    <div class="lg:col-span-2 space-y-6">

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

            
            <div class="px-6 pt-5 flex items-center gap-2 text-xs text-gray-400 mb-3">
                <a href="/questions" class="hover:text-brand-600">Questions</a>
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="truncate"><?php echo e(Str::limit($question->title, 55)); ?></span>
            </div>

            <div class="flex gap-4 px-6 pb-6">

                
                <div class="flex flex-col items-center gap-1 flex-shrink-0 pt-1" id="q-vote-box">
                    <button onclick="voteQ('up')"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-brand-100 dark:hover:bg-brand-900/30 hover:text-brand-600 text-gray-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <span id="q-vote-count" class="text-lg font-bold <?php echo e($question->votes > 0 ? 'text-brand-600' : ($question->votes < 0 ? 'text-red-500' : 'text-gray-700 dark:text-gray-300')); ?>">
                        <?php echo e($question->votes >= 1000 ? round($question->votes / 1000, 1).'k' : $question->votes); ?>

                    </span>
                    <button onclick="voteQ('down')"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 text-gray-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>

                
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white leading-snug mb-3"><?php echo e($question->title); ?></h1>

                    
                    <div class="flex flex-wrap items-center gap-2 mb-4 text-xs text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 rounded-full bg-brand-500 flex items-center justify-center text-white text-[10px] font-bold">
                                <?php echo e($question->is_anonymous ? '?' : strtoupper(substr($question->user->name ?? 'A', 0, 1))); ?>

                            </div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">
                                <?php echo e($question->is_anonymous ? 'Anonymous' : ($question->user->name ?? 'Unknown')); ?>

                            </span>
                        </div>
                        <span>·</span>
                        <span>Asked <?php echo e($question->created_at->format('M j, Y')); ?></span>
                        <?php if($question->category): ?>
                        <span>·</span>
                        <a href="/category/<?php echo e($question->category->slug); ?>?tab=questions"
                            class="text-brand-600 hover:underline">In: <?php echo e($question->category->name_ne ?? $question->category->name_en); ?></a>
                        <?php endif; ?>
                    </div>

                    
                    <?php if($question->tags->count()): ?>
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        <?php $__currentLoopData = $question->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="/questions?tag=<?php echo e($tag->slug); ?>"
                            class="text-xs px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 text-gray-600 dark:text-gray-300 rounded-full transition-colors">
                            <?php echo e($tag->name_en); ?>

                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>

                    <?php if($question->featured_image): ?>
                    <div class="mb-4 rounded-xl overflow-hidden">
                        <img src="<?php echo e(asset('storage/'.$question->featured_image)); ?>" alt="<?php echo e($question->title); ?>" class="w-full max-h-72 object-cover">
                    </div>
                    <?php endif; ?>

                    <?php if($question->content): ?>
                    <div class="prose dark:prose-invert prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-5">
                        <?php echo nl2br(e($question->content)); ?>

                    </div>
                    <?php endif; ?>

                    
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-400">
                        <div class="flex items-center gap-3">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <?php echo e($question->answers_count); ?> <?php echo e(Str::plural('Answer', $question->answers_count)); ?>

                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <?php echo e(number_format($question->views_count)); ?> Views
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            
                            <?php if(auth()->guard()->check()): ?>
                            <?php if(auth()->id() === $question->user_id || in_array(auth()->user()->role, ['admin','editor'])): ?>
                            <a href="/questions/<?php echo e($question->id); ?>/edit"
                                class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-brand-50 dark:hover:bg-brand-900/20 hover:text-brand-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                            
                            <span class="flex items-center gap-1">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode(url()->current())); ?>" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors text-[10px] font-bold">f</a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode(url()->current())); ?>&text=<?php echo e(urlencode($question->title)); ?>" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-sky-100 text-sky-600 hover:bg-sky-200 transition-colors text-[10px] font-bold">𝕏</a>
                                <a href="https://api.whatsapp.com/send?text=<?php echo e(urlencode($question->title . ' ' . url()->current())); ?>" target="_blank"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors text-[10px] font-bold">W</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if($question->answers->count()): ?>
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">
                    <?php echo e($question->answers->count()); ?> <?php echo e(Str::plural('Answer', $question->answers->count())); ?>

                </h2>
                
                <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                    <?php $__currentLoopData = ['voted' => 'Voted', 'oldest' => 'Oldest', 'recent' => 'Recent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="?sort=<?php echo e($key); ?>"
                        class="px-3 py-1 text-xs font-medium rounded-lg transition-colors <?php echo e($sort === $key ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
                        <?php echo e($label); ?>

                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <?php
            $sorted = $question->answers;
            if ($sort === 'voted')  $sorted = $sorted->sortByDesc('votes')->sortByDesc('is_best');
            elseif ($sort === 'oldest') $sorted = $sorted->sortBy('created_at');
            else $sorted = $sorted->sortByDesc('created_at');
            ?>

            <div class="space-y-4">
                <?php $__currentLoopData = $sorted; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border <?php echo e($answer->is_best ? 'border-green-300 dark:border-green-700' : 'border-gray-100 dark:border-gray-700'); ?> shadow-sm p-5 flex gap-4 relative">
                    <?php if($answer->is_best): ?>
                    <div class="absolute top-3 right-3 flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Best Answer
                    </div>
                    <?php endif; ?>

                    
                    <div class="flex flex-col items-center gap-1 flex-shrink-0 pt-1">
                        <button onclick="voteA(<?php echo e($answer->id); ?>, 'up')"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-green-100 hover:text-green-600 text-gray-500 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <span id="a-vote-<?php echo e($answer->id); ?>" class="text-sm font-bold text-gray-700 dark:text-gray-300"><?php echo e($answer->votes); ?></span>
                        <button onclick="voteA(<?php echo e($answer->id); ?>, 'down')"
                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-red-100 hover:text-red-500 text-gray-500 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="prose dark:prose-invert prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-4">
                            <?php echo nl2br(e($answer->content)); ?>

                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <div class="w-6 h-6 rounded-full bg-gray-400 flex items-center justify-center text-white text-[10px] font-bold">
                                    <?php echo e($answer->is_anonymous ? '?' : strtoupper(substr($answer->user->name ?? 'A', 0, 1))); ?>

                                </div>
                                <span class="font-medium"><?php echo e($answer->is_anonymous ? 'Anonymous' : ($answer->user->name ?? 'Unknown')); ?></span>
                                <span>·</span>
                                <span><?php echo e($answer->created_at->diffForHumans()); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if(auth()->guard()->check()): ?>
                                <?php if((auth()->id() === $question->user_id || in_array(auth()->user()->role, ['admin','editor'])) && !$answer->is_best): ?>
                                <form method="POST" action="/questions/<?php echo e($question->id); ?>/best/<?php echo e($answer->id); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button class="text-xs px-2.5 py-1 border border-green-300 dark:border-green-700 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors">
                                        ✓ Best Answer
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <?php if(auth()->guard()->check()): ?>
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-brand-500">
                <button onclick="document.getElementById('answer-form').classList.toggle('hidden')"
                    class="w-full text-center text-white font-semibold text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Leave An Answer
                </button>
            </div>
            <div id="answer-form" class="p-6 <?php echo e(old('content') ? '' : ''); ?>">
                <?php if(session('success')): ?>
                <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400 text-sm">
                    <?php echo e(session('success')); ?>

                </div>
                <?php endif; ?>
                <form method="POST" action="/questions/<?php echo e($question->id); ?>/answers">
                    <?php echo csrf_field(); ?>
                    <textarea name="content" rows="5" required minlength="10"
                        placeholder="Write a helpful, detailed answer…"
                        class="w-full text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-y mb-3"><?php echo e(old('content')); ?></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_anonymous" value="1" class="w-4 h-4 rounded border-gray-300 text-brand-500">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Post anonymously</span>
                        </label>
                        <button type="submit" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                            Post Answer
                        </button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-3">Sign in to post an answer</p>
                <a href="/login" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors inline-block">Sign In to Answer</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="space-y-5">

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="text-center p-3 bg-brand-50 dark:bg-brand-900/20 rounded-xl">
                    <p class="text-xl font-bold text-brand-600"><?php echo e($totalQuestions); ?></p>
                    <p class="text-[11px] text-gray-500">Questions</p>
                </div>
                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <p class="text-xl font-bold text-green-600"><?php echo e($totalAnswers); ?></p>
                    <p class="text-[11px] text-gray-500">Answers</p>
                </div>
                <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                    <p class="text-xl font-bold text-amber-600"><?php echo e($bestAnswers); ?></p>
                    <p class="text-[11px] text-gray-500">Best Answers</p>
                </div>
                <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <p class="text-xl font-bold text-purple-600"><?php echo e($totalUsers); ?></p>
                    <p class="text-[11px] text-gray-500">Users</p>
                </div>
            </div>
            <a href="/ask-question"
                class="w-full flex items-center justify-center gap-2 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Ask a Question
            </a>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5"
            x-data="{ sideTab: 'popular' }">
            <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 p-1 rounded-xl mb-4">
                <button @click="sideTab = 'popular'"
                    :class="sideTab === 'popular' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500'"
                    class="flex-1 py-1 text-xs font-medium rounded-lg transition-colors">Popular</button>
                <button @click="sideTab = 'related'"
                    :class="sideTab === 'related' ? 'bg-white dark:bg-gray-600 shadow-sm text-gray-900 dark:text-white' : 'text-gray-500'"
                    class="flex-1 py-1 text-xs font-medium rounded-lg transition-colors">Related</button>
            </div>

            
            <div x-show="sideTab === 'popular'" class="space-y-3">
                <?php $__currentLoopData = $popularQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="/questions/<?php echo e($pq->slug); ?>" class="flex items-start gap-2 group">
                    <div class="w-6 h-6 rounded flex-shrink-0 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-600 leading-snug"><?php echo e(Str::limit($pq->title, 65)); ?></p>
                        <p class="text-[10px] text-gray-400 mt-0.5"><?php echo e($pq->answers_count); ?> answers</p>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div x-show="sideTab === 'related'" class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $relatedQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="/questions/<?php echo e($rq->slug); ?>" class="flex items-start gap-2 group">
                    <div class="w-6 h-6 rounded flex-shrink-0 bg-brand-50 dark:bg-brand-900/20 flex items-center justify-center">
                        <svg class="w-3 h-3 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-600 leading-snug"><?php echo e(Str::limit($rq->title, 65)); ?></p>
                        <p class="text-[10px] text-gray-400 mt-0.5"><?php echo e($rq->answers_count); ?> answers</p>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-xs text-gray-400">No related questions yet.</p>
                <?php endif; ?>
            </div>
        </div>

        
        <?php if($topMembers->count()): ?>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-3">Top Contributors</h3>
            <div class="space-y-2.5">
                <?php $__currentLoopData = $topMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-2.5">
                    <span class="text-xs font-bold text-gray-300 w-4"><?php echo e($i + 1); ?></span>
                    <div class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        <?php echo e(strtoupper(substr($member->name, 0, 1))); ?>

                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-900 dark:text-white truncate"><?php echo e($member->name); ?></p>
                        <p class="text-[10px] text-gray-400"><?php echo e($member->questions_count); ?>q · <?php echo e($member->answers_count); ?>a</p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function voteQ(dir) {
    fetch('/questions/<?php echo e($question->id); ?>/vote', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
        body: JSON.stringify({vote: dir})
    }).then(r => r.json()).then(d => {
        const el = document.getElementById('q-vote-count');
        if (el) el.textContent = Math.abs(d.votes) >= 1000 ? (d.votes/1000).toFixed(1)+'k' : d.votes;
    });
}

function voteA(id, dir) {
    fetch('/answers/'+id+'/vote', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken},
        body: JSON.stringify({vote: dir})
    }).then(r => r.json()).then(d => {
        const el = document.getElementById('a-vote-'+id);
        if (el) el.textContent = d.votes;
    });
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\project\nkhoj\resources\views/questions/show.blade.php ENDPATH**/ ?>