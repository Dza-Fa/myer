<div {{ $attributes->merge(['class' => 'flex justify-center items-center py-8']) }}>
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    <span class="ml-3 text-gray-500">{{ $message ?? 'Memuat...' }}</span>
</div>
