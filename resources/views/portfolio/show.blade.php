@php($title = $user->name . ' – Portofolio Pencapaian')
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="Pencapaian publik milik {{ $user->name }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <header class="mb-6">
            <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">Portofolio Pencapaian (Publik)</p>
        </header>

        @if ($achievements->count())
            <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($achievements as $a)
                    <li class="rounded-xl border border-gray-200 dark:border-gray-800 p-4">
                        <div class="text-base font-semibold">{{ $a->title }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ $a->issuer ?? '—' }} • {{ optional($a->achieved_at)->format('d M Y') }}</div>
                        @if ($a->proof_image)
                            <img src="{{ Storage::disk('public')->url($a->proof_image) }}" alt="Bukti" class="rounded-md object-cover w-full h-32" loading="lazy">
                        @endif
                        @if ($a->description)
                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $a->description }}</p>
                        @endif
                        @if ($a->url)
                            <a href="{{ $a->url }}" class="mt-2 inline-flex items-center text-primary-600 hover:underline" target="_blank" rel="noopener">Lihat Tautan</a>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                {{ $achievements->onEachSide(1)->links() }}
            </div>
        @else
            <div class="text-sm text-gray-600 dark:text-gray-400">Belum ada pencapaian publik.</div>
        @endif
    </div>
</body>
</html>

