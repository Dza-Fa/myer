<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kategori') }}
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Expense</div>
                    <div class="text-2xl font-bold text-red-600">
                        Rp {{ number_format($report['summary']['total_expense'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Jumlah Kategori</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ $report['summary']['category_count'] ?? 0 }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Rata-rata per Bulan</div>
                    <div class="text-2xl font-bold text-blue-600">
                        Rp {{ number_format($report['summary']['average_monthly'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Distribusi Pengeluaran</h3>
                @if(count($report['categories'] ?? []) > 0)
                    <canvas id="categoryPieChart" height="100"></canvas>
                @else
                    <p class="text-gray-500">Belum ada data.</p>
                @endif
            </div>

            <!-- Category Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Detail per Kategori</h3>
                @if(count($report['categories'] ?? []) > 0)
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-gray-500 text-sm border-b">
                                <th class="pb-2">Kategori</th>
                                <th class="pb-2 text-right">Total</th>
                                <th class="pb-2 text-right">%</th>
                                <th class="pb-2 text-right">Jumlah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($report['categories'] as $cat)
                                <tr>
                                    <td class="py-2">
                                        <span class="w-3 h-3 inline-block rounded-full mr-2" style="background-color: {{ $cat['category_color'] }}"></span>
                                        {{ $cat['category_name'] }}
                                    </td>
                                    <td class="py-2 text-right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                                    <td class="py-2 text-right">
                                        {{ $report['summary']['total_expense'] > 0 ? round(($cat['total'] / $report['summary']['total_expense']) * 100, 1) : 0 }}%
                                    </td>
                                    <td class="py-2 text-right">{{ $cat['count'] }}</td>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const categoryData = @json($report['categories'] ?? []);
        const ctx = document.getElementById('categoryPieChart');
        if (ctx && categoryData.length > 0) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(d => d.category_name),
                    datasets: [{
                        data: categoryData.map(d => d.total),
                        backgroundColor: categoryData.map(d => d.category_color || '#666666'),
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = ((value / total) * 100).toFixed(1);
                                    return context.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(value) + ' (' + pct + '%)';
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
