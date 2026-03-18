<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kategori') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Dashboard</a>
                    
                    <h3 class="text-lg font-semibold mb-4">Daftar Kategori</h3>
                    
                    @if(count($categories ?? []) > 0)
                        <ul class="space-y-2">
                            @foreach($categories as $category)
                                <li class="flex items-center gap-2 p-2 border rounded">
                                    <span class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }}"></span>
                                    <span class="font-medium">{{ $category->name }}</span>
                                    <span class="text-gray-500 text-sm">({{ $category->type }})</span>
                                </li>
                                @if(count($category->children ?? []) > 0)
                                    <ul class="ml-6 space-y-2">
                                        @foreach($category->children as $child)
                                            <li class="flex items-center gap-2 p-2 border rounded">
                                                <span class="w-4 h-4 rounded-full" style="background-color: {{ $child->color }}"></span>
                                                <span>{{ $child->name }}</span>
                                                <span class="text-gray-500 text-sm">({{ $child->type }})</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <x-empty-state message="Belum ada kategori." />
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
