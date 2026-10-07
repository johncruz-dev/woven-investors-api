@props([
    'name',
    'class' => 'icon',
])

@php
$paths = [
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V20a1.5 1.5 0 0 1-1.5 1.5H15v-6H9v6H4.5A1.5 1.5 0 0 1 3 20V10.5z"/>',
    'courses' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.25h10.5A1.5 1.5 0 0 1 16.5 6.75v12l-4.5-2.25L7.5 18.75v-12A1.5 1.5 0 0 1 9 5.25"/><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 5.25V4.5A1.5 1.5 0 0 1 9 3h10.5A1.5 1.5 0 0 1 21 4.5v12a1.5 1.5 0 0 1-1.5 1.5H16.5"/>',
    'messages' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v7.5A2.25 2.25 0 0 1 17.25 16.5H9l-4.5 3V6.75z"/>',
    'students' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 19.5a6.75 6.75 0 0 1 13.5 0"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 9a2.25 2.25 0 1 0 0-4.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 19.5a4.5 4.5 0 0 0-3.2-4.3"/>',
    'support' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75a2.25 2.25 0 1 1 3.6 1.8c-.7.52-1.35 1.05-1.35 2.2v.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 17.25h.01"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5z"/>',
    'roster' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 9 4.5-4.5L16.5 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 16.5v1.5A1.5 1.5 0 0 0 6 19.5h12a1.5 1.5 0 0 0 1.5-1.5v-1.5"/>',
    'admin' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>',
    'profile' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5a7.5 7.5 0 0 1 15 0"/>',
    'logout' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 12h8.25"/><path stroke-linecap="round" stroke-linejoin="round" d="m18 8.25 3.75 3.75L18 15.75"/>',
    'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>',
    'close' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>',
    'collapse' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h6v15h-6A1.5 1.5 0 0 1 3 18V6A1.5 1.5 0 0 1 4.5 4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 8.25 10.5 12l3.75 3.75"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 12H21"/>',
];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="20" height="20" aria-hidden="true">
    {!! $paths[$name] ?? '' !!}
</svg>
