@props([
    'model'             => null,   // Alpine expression to bind to, e.g. "form.subject_id" (required for x-model)
    'options'           => [],     // array of ['value' => ..., 'label' => ...] OR Alpine expression string
    'placeholder'       => 'Pilih...',
    'searchPlaceholder' => 'Cari...',
    'emptyText'         => 'Tidak ada hasil',
    'allowClear'        => true,
])

@php
    // Options may be passed as a PHP array (json-encoded here) or as a raw
    // Alpine expression string (e.g. a property already in scope).
    $optionsExpr = is_string($options) ? $options : json_encode($options);
@endphp

<div
    x-data="searchableSelect({
        value: {{ $model ? $model : 'null' }},
        options: {{ $optionsExpr }},
        placeholder: @js($placeholder),
        searchPlaceholder: @js($searchPlaceholder),
        emptyText: @js($emptyText),
        allowClear: {{ $allowClear ? 'true' : 'false' }},
    })"
    @if ($model) x-modelable="selected" x-model="{{ $model }}" @endif
    @keydown.escape.stop="close()"
    @keydown.arrow-down.prevent="onArrowDown()"
    @keydown.arrow-up.prevent="onArrowUp()"
    @keydown.enter.prevent="onEnter()"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    {{-- Trigger button --}}
    <button type="button" @click="toggle()"
            class="input-base flex items-center justify-between gap-2 text-left cursor-pointer"
            :class="{ 'ring-2 ring-primary-400/40 border-primary-400': open }">
        <span class="truncate"
              :class="hasValue ? 'text-surface-800 dark:text-surface-100' : 'text-surface-400 dark:text-surface-500'"
              x-text="hasValue ? selectedLabel : placeholder"></span>

        <span class="flex-none flex items-center gap-1">
            {{-- Clear button --}}
            <template x-if="allowClear && hasValue">
                <span @click.stop="clear()"
                      class="p-0.5 rounded hover:bg-surface-100 dark:hover:bg-surface-700 text-surface-400 hover:text-surface-600 dark:hover:text-surface-200 transition-colors"
                      title="Hapus pilihan">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
            </template>
            {{-- Chevron --}}
            <svg class="w-4 h-4 text-surface-400 transition-transform duration-200"
                 :class="{ 'rotate-180': open }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </span>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-cloak
         @click.outside="close()"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute z-50 mt-1.5 w-full rounded-xl border border-surface-200 dark:border-surface-700
                bg-white dark:bg-surface-900 shadow-soft-lg overflow-hidden">

        {{-- Search box --}}
        <div class="p-2 border-b border-surface-100 dark:border-surface-800">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-surface-400 pointer-events-none"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                </svg>
                <input x-ref="searchInput" type="text" x-model="search"
                       :placeholder="searchPlaceholder"
                       class="w-full pl-8 pr-3 py-1.5 text-sm rounded-lg border border-surface-200 dark:border-surface-700
                              bg-surface-50 dark:bg-surface-800 text-surface-800 dark:text-surface-100
                              placeholder-surface-400 dark:placeholder-surface-500
                              focus:outline-none focus:ring-2 focus:ring-primary-400/40 focus:border-primary-400 transition-all">
            </div>
        </div>

        {{-- Options list --}}
        <ul x-ref="optionList" class="max-h-60 overflow-y-auto scrollbar-thin py-1">
            <template x-for="(option, i) in filtered" :key="option.value">
                <li @click="choose(option)"
                    @mouseenter="highlighted = i"
                    class="px-3 py-2 text-sm cursor-pointer flex items-center justify-between gap-2 transition-colors"
                    :class="{
                        'bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300': isHighlighted(i),
                        'text-surface-700 dark:text-surface-200': !isHighlighted(i),
                    }">
                    <span class="truncate" x-text="option.label"></span>
                    <svg x-show="isSelected(option)" class="w-4 h-4 flex-none text-primary-600 dark:text-primary-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </li>
            </template>

            {{-- Empty state --}}
            <li x-show="filtered.length === 0"
                class="px-3 py-4 text-sm text-center text-surface-400 dark:text-surface-500"
                x-text="emptyText"></li>
        </ul>
    </div>
</div>
