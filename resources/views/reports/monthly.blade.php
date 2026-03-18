<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Bulanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Month Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="get" class="flex gap-4 items-center">
                    <a href="{{ route('reports.index') }}" class="text-blue-600 hover:underline">&larr; Kembali</a>
                    <label>Pilih Bulan:</label>
                    <select name="month" class="border rounded px-3 py-2" onchange="this.form.submit()">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$i-1] }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="border rounded px-3 py-2" onchange="this.form.submit()">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Income</div>
                    <div class="text-2xl font-bold text-green-600">
                        Rp {{ number_format($report['summary']['total_income'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Expense</div>
                    <div class="text-2xl font-bold text-red-600">
                        Rp {{ number_format($report['summary']['total_expense'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Tabungan</div>
                    <div class="text-2xl font-bold {{ ($report['summary']['net_savings'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($report['summary']['net_savings'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Savings Rate</div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $report['summary']['savings_rate'] ?? 0 }}%
                    </div>
                </div>
            </div>

            <!-- By Category -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Pengeluaran per Kategori</h3>
                @if(count($report['by_category'] ?? []) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-500 text-sm border-b">
                                <th class="pb-2">Kategori</th>
                                <th class="pb-2 text-right">Total</th>
                                <th class="pb-2 text-right">Jumlah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($report['by_category'] as $cat)
                                <tr>
                                    <td class="py-2">
                                        <span class="w-3 h-3 inline-block rounded-full mr-2" style="background-color: {{ $cat['category_color'] }}"></span>
                                        {{ $cat['category_name'] }}
                                    </td>
                                    <td class="py-2 text-right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-right">{{ $cat['count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">Belum ada transaksi.</p>
                @endif
            </div>

            <!-- By Account -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Transaksi per Akun</h3>
                @if(count($report['by_account'] ?? []) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-500 text-sm border-b">
                                <th class="pb-2">Akun</th>
                                <th class="pb-2 text-right">Income</th>
                                <th class="pb-2 text-right">Expense</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($report['by_account'] as $acc)
                                <tr>
                                    <td class="py-2">{{ $acc['account_name'] }}</td>
                                    <td class="py-2 text-right text-green-600">Rp {{ number_format($acc['income'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-right text-red-600">Rp {{ number_format($acc['expense'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">Belum ada transaksi.</p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
