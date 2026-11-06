@props(['name', 'class' => ''])
@php
$paths = [
 'bank'=>'m12 2 10 6H2l10-6ZM4 11v8m5-8v8m6-8v8m5-8v8M2 22h20M2 19h20',
 'card'=>'M2 4h20v16H2V4Zm0 5h20M5 16h4m4 0h3',
 'phone'=>'m4 3 4 1 1 5-2 2a14 14 0 0 0 6 6l2-2 5 1 1 4c-9 5-23-9-17-17Z',
 'mail'=>'M3 5h18v14H3V5Zm0 0 9 8 9-8',
 'pin'=>'M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Zm-5 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
 'filter'=>'M3 4h18l-7 8v8l-4-2v-6L3 4Z',
 'clock'=>'M12 6v6l4 3M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z',
 'printer'=>'M6 8V2h12v6M6 17H3V8h18v9h-3M6 14h12v8H6V14Zm11-3h.01',
 'trash'=>'M3 6h18M9 6V3h6v3M5 6l1 16h12l1-16M10 10v8m4-8v8',
 'truck'=>'M2 5h12v12H2V5Zm12 5h4l4 4v3h-8M7 19a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm13 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z',
'warning'=>'m12 3 10 18H2L12 3Zm0 6v5m0 3h.01',
'warehouse'=>'M3 21V8l9-5 9 5v13M7 21V11h10v10M9 15h6m-6 3h6',
'download'=>'M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5',
'upload'=>'M12 15V3m-5 5 5-5 5 5M4 16v5h16v-5',
'edit'=>'m15 4 5 5M3 21l5-1L21 7l-5-5L3 15v6Z',
'more'=>'M12 5h.01M12 12h.01M12 19h.01',
'plus'=>'M12 4v16M4 12h16',
'balance'=>'M12 2v20M6 22h12M3 6h18M5 6l-3 9h6L5 6Zm14 0-3 9h6l-3-9Z',
'bed'=>'M3 21V4m0 12h18v5M3 9h5v7m0-7h10a3 3 0 0 1 3 3v4M5 12h.01',
'light'=>'M12 3v4m-5 3 5-3 5 3v4H7v-4Zm5 4v7m-5 0h10M3 3l4 7m14-7-4 7',
'home'=>'M3 10 12 3l9 7M5 9v12h5v-7h4v7h5V9',
'box'=>'m12 3 9 5v9l-9 5-9-5V8l9-5Zm0 9 9-4M12 12 3 8m9 4v10M7 5.8l9 5v5',
'cart'=>'M2 3h3l3 13h11l3-10H6M9 21h.01M18 21h.01',
'tag'=>'M3 4h9l9 9-9 9-9-9V4Zm5 5h.01',
'users'=>'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-7.87a4 4 0 0 1 0 7.75',
'calculator'=>'M5 3h14v19H5V3Zm3 4h8M8 12h1m6 0h1m-8 4h1m6 0h1m-8 3h1m6 0h1',
'chart'=>'M5 21V13m7 8V7m7 14V3',
'settings'=>'m9 3-1 3-3 1-2 3 2 2-1 3 2 3 3-1 2 3h3l1-3 3-1 2-3-2-2 1-3-2-3-3 1-2-3H9Zm3 6a3 3 0 1 1 0 6 3 3 0 0 1 0-6Z',
'help'=>'M12 17h.01M9 8a3 3 0 0 1 6 1c0 2-3 2-3 5M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z',
'search'=>'M21 21l-5-5M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z',
'bell'=>'M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4',
'calendar'=>'M3 5h18v17H3V5Zm0 5h18M7 2v5m10-5v5M7 14h.01m5 0h.01m5 0h.01M7 18h.01m5 0h.01',
'user'=>'M20 21a8 8 0 0 0-16 0M17 8A5 5 0 1 1 7 8a5 5 0 0 1 10 0Z',
'chevron'=>'m9 5 7 7-7 7','down'=>'m6 9 6 6 6-6','arrow'=>'M4 12h16m-6-6 6 6-6 6',
'clipboard'=>'M9 4H5v18h14V4h-4M9 2h6v5H9V2Zm-1 9h8m-8 4h8m-8 4h5',
'money'=>'M3 6h18v15H3V6Zm0 0 4-4h10l4 4M15 13a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
'trend'=>'m3 17 6-6 4 4 8-11m-7 0h7v7',
'shield'=>'m12 2 9 4v6c0 6-9 10-9 10S3 18 3 12V6l9-4Zm0 5v10m-3-5h6',
'profit'=>'M12 7v10m-4-4 4 4 4-4M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z',
'menu'=>'M3 6h18M3 12h18M3 18h18','close'=>'m6 6 12 12M6 18 18 6',
'monitor'=>'M3 3h18v14H3V3Zm5 19h8m-4-5v5M5 11h3l2-5 3 8 2-4h4',
];
@endphp
<svg {{ $attributes->merge(['class' => 'icon '.$class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['box'] }}"/></svg>





