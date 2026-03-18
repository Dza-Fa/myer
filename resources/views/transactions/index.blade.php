<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke Dashboard</a>
                    
                    <h3 class="text-lg font-semibold mb-4">Daftar Transaksi</h3>
                    
                    @if(count($transactions ?? []) > 0)
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-gray-500 text-sm border-b">
                                    <th class="pb-2">Tanggal</th>
                                    <th class="pb-2">Kategori</th>
                                    <th class="pb-2">Akun</th>
                                    <th class="pb-2">Tipe</th>
                                    <th class="pb-2 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($transactions as $tx)
                                    <tr>
                                        <td class="py-3">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                                        <td class="py-3">{{ $tx->category?->name ?? '-' }}</td>
                                        <td class="py-3">{{ $tx->account?->name ?? '-' }}</td>
                                        <td class="py-3">
                                            <span class="px-2 py-1 text-xs rounded {{ $tx->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $tx->type }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-right {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $tx->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @else
                        <x-empty-state message="Belum ada transaksi." />
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
