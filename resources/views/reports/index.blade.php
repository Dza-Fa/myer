<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('reports.monthly', ['year' => $year, 'month' => $month]) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <h3 class="text-lg font-semibold">Laporan Bulanan</h3>
                    <p class="text-gray-500 text-sm mt-1">Ringkasan keuangan bulan tertentu</p>
                </a>
                <a href="{{ route('reports.cashflow', ['year' => $year]) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <h3 class="text-lg font-semibold">Laporan Arus Kas</h3>
                    <p class="text-gray-500 text-sm mt-1">Income vs Expense sepanjang tahun</p>
                </a>
                <a href="{{ route('reports.category', ['year' => $year]) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <h3 class="text-lg font-semibold">Laporan Kategori</h3>
                    <p class="text-gray-500 text-sm mt-1">Pengeluaran berdasarkan kategori</p>
                </a>
            </div>

            <!-- Current Month Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Ringkasan Bulan Ini</h3>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-gray-500 text-sm">Total Income</div>
                        <div class="text-xl font-bold text-green-600">
                            Rp {{ number_format($monthlyReport['summary']['total_income'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm">Total Expense</div>
                        <div class="text-xl font-bold text-red-600">
                            Rp {{ number_format($monthlyReport['summary']['total_expense'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm">Tabungan</div>
                        <div class="text-xl font-bold {{ ($monthlyReport['summary']['net_savings'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            Rp {{ number_format($monthlyReport['summary']['net_savings'] ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm">Savings Rate</div>
                        <div class="text-xl font-bold text-blue-600">
                            {{ $monthlyReport['summary']['savings_rate'] ?? 0 }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Year Category Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Pengeluaran per Kategori ({{ $year }})</h3>
                
                @if(count($categoryReport['categories'] ?? []) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-500 text-sm border-b">
                                <th class="pb-2">Kategori</th>
                                <th class="pb-2 text-right">Total</th>
                                <th class="pb-2 text-right">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($categoryReport['categories'] as $cat)
                                <tr>
                                    <td class="py-2">
                                        <span class="w-3 h-3 inline-block rounded-full mr-2" style="background-color: {{ $cat['category_color'] }}"></span>
                                        {{ $cat['category_name'] }}
                                    </td>
                                    <td class="py-2 text-right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-right">
                                        {{ $categoryReport['summary']['total_expense'] > 0 ? round(($cat['total'] / $categoryReport['summary']['total_expense']) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">Belum ada data.</p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
