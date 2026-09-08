@extends('layouts.app')
@section('title', 'Share a Recipe – Nkhoj')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Share a Recipe</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Share your favourite recipe with the community</p>
    </div>

    <form method="POST" action="/recipe" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Recipe Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="200"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                       placeholder="e.g. Momo with Achar Sauce">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="3" maxlength="1000"
                          class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"
                          placeholder="A short description of this recipe…">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cuisine Type</label>
                    <select name="cuisine_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <option value="">Select cuisine</option>
                        @foreach(['Nepali','Indian','Chinese','Italian','Mexican','Japanese','Thai','Continental','Newari','Tibetan'] as $c)
                            <option value="{{ $c }}" {{ old('cuisine_type') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Meal Type</label>
                    <select name="meal_type" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <option value="">Select meal</option>
                        @foreach(['Breakfast','Lunch','Dinner','Snack','Dessert','Drinks'] as $m)
                            <option value="{{ $m }}" {{ old('meal_type') === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Difficulty <span class="text-red-500">*</span></label>
                    <select name="difficulty" required class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <option value="easy" {{ old('difficulty','easy') === 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty') === 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Prep (min)</label>
                    <input type="number" name="prep_time" value="{{ old('prep_time') }}" min="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="15">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Cook (min)</label>
                    <input type="number" name="cook_time" value="{{ old('cook_time') }}" min="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400" placeholder="30">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Servings</label>
                    <input type="number" name="servings" value="{{ old('servings', 2) }}" min="1"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
            </div>
        </div>

        {{-- Ingredients --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6" x-data="ingredientList()">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">🧂 Ingredients</h2>
            <div class="space-y-2 mb-3">
                <template x-for="(ing, i) in ingredients" :key="i">
                    <div class="flex gap-2">
                        <input type="text" :name="'ingredients['+i+']'" x-model="ingredients[i]"
                               class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                               placeholder="e.g. 2 cups flour">
                        <button type="button" @click="ingredients.splice(i,1)" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="ingredients.push('')" class="text-sm font-semibold text-orange-500 hover:text-orange-600 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add ingredient
            </button>
        </div>

        {{-- Steps --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6" x-data="stepList()">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">👨‍🍳 Instructions</h2>
            <div class="space-y-3 mb-3">
                <template x-for="(step, i) in steps" :key="i">
                    <div class="flex gap-2">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0 mt-1.5" style="background:linear-gradient(135deg,#f97316,#ef4444)" x-text="i+1"></span>
                        <textarea :name="'steps['+i+']'" x-model="steps[i]" rows="2"
                                  class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"
                                  :placeholder="'Step '+(i+1)+': describe this step…'"></textarea>
                        <button type="button" @click="steps.splice(i,1)" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="steps.push('')" class="text-sm font-semibold text-orange-500 hover:text-orange-600 flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add step
            </button>
        </div>

        {{-- Photo --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Recipe Photo</label>
            <input type="file" name="thumbnail" accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 transition-colors">
        </div>

        <button type="submit" class="w-full py-4 rounded-2xl font-black text-white text-base transition-all hover:opacity-90 shadow-lg"
                style="background:linear-gradient(135deg,#f97316,#ef4444)">Share Recipe</button>
    </form>
</div>

<script>
function ingredientList() {
    return { ingredients: ['', '', ''] };
}
function stepList() {
    return { steps: [''] };
}
</script>
@endsection
