@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100 dark:focus:border-indigo-600 dark:focus:ring-indigo-600']) !!}>