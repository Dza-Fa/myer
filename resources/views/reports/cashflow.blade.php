<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Arus Kas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Year Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="get" class="flex gap-4 items-center">
                    <a href="{{ route('reports.index') }}" class="text-blue-600 hover:underline">&larr; Kembali</a>
                    <label>Pilih Tahun:</label>
                    <select name="year" class="border rounded px-3 py-2" onchange="this.form.submit()">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
            </div>

            <!-- Summary -->
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
                    <div class="text-gray-500 text-sm">Total Tabungan</div>
                    <div class="text-2xl font-bold {{ ($report['summary']['total_savings'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($report['summary']['total_savings'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Rata-rata Expense</div>
                    <div class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($report['summary']['average_expense'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Grafik Arus Kas</h3>
                <canvas id="cashflowChart" height="100"></canvas>
            </div>

            <!-- Monthly Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Detail per Bulan</h3>
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm border-b">
                            <th class="pb-2">Bulan</th>
                            <th class="pb-2 text-right">Income</th>
                            <th class="pb-2 text-right">Expense</th>
                            <th class="pb-2 text-right">Tabungan</th>
                            <th class="pb-2 text-right">Savings Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($report['monthly'] ?? [] as $month)
                            <tr>
                                <td class="py-2">{{ $month['month_name'] }}</td>
                                <td class="py-2 text-right text-green-600">Rp {{ number_format($month['income'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-red-600">Rp {{ number_format($month['expense'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right {{ $month['savings'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($month['savings'], 0, ',', '.') }}
                                </td>
                                <td class="py-2 text-right">{{ $month['savings_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const cashflowData = @json($report['monthly'] ?? []);
        const ctx = document.getElementById('cashflowChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cashflowData.map(d => d.month_name),
                    datasets: [
                        {
                            label: 'Income',
                            data: cashflowData.map(d => d.income),
                            backgroundColor: '#22c55e',
                        },
                        {
                            label: 'Expense',
                            data: cashflowData.map(d => d.expense),
                            backgroundColor: '#ef4444',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
