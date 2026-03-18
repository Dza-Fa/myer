<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Akun') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Dashboard</a>
                    
                    <h3 class="text-lg font-semibold mb-4">Daftar Akun</h3>
                    
                    @if(count($accounts ?? []) > 0)
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-gray-500 text-sm border-b">
                                    <th class="pb-2">Nama Akun</th>
                                    <th class="pb-2">Tipe</th>
                                    <th class="pb-2">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($accounts as $account)
                                    <tr>
                                        <td class="py-3">{{ $account->name }}</td>
                                        <td class="py-3">{{ ucfirst($account->type) }}</td>
                                        <td class="py-3 font-semibold">
                                            Rp {{ number_format($account->calculateBalance(), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <x-empty-state message="Belum ada akun." />
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
