<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }} - {{ $period['month'] ?? '' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Balance</div>
                    <div class="text-2xl font-bold text-gray-900">
                        Rp {{ number_format($overview['total_balance'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Income (Bulan Ini)</div>
                    <div class="text-2xl font-bold text-green-600">
                        +Rp {{ number_format($overview['monthly_income'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Expense (Bulan Ini)</div>
                    <div class="text-2xl font-bold text-red-600">
                        -Rp {{ number_format($overview['monthly_expense'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Savings Rate</div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $overview['savings_rate'] ?? 0 }}%
                    </div>
                </div>
            </div>

            <!-- Accounts & Top Expenses -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Accounts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold">Akun Saya</h3>
                    </div>
                    <div class="p-6">
                        @if(count($accounts ?? []) > 0)
                            <ul class="space-y-3">
                                @foreach($accounts as $account)
                                    <li class="flex justify-between items-center">
                                        <span>{{ $account['name'] }}</span>
                                        <span class="font-semibold">
                                            Rp {{ number_format($account['balance'], 0, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">Belum ada akun. Tambahkan akun pertama Anda!</p>
                        @endif
                    </div>
                </div>

                <!-- Top Expenses -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold">Pengeluaran Terbesar</h3>
                    </div>
                    <div class="p-6">
                        @if(count($topExpenses ?? []) > 0)
                            <ul class="space-y-3">
                                @foreach($topExpenses as $expense)
                                    <li class="flex justify-between items-center">
                                        <span>{{ $expense['category_name'] ?? 'Uncategorized' }}</span>
                                        <span class="font-semibold text-red-600">
                                            -Rp {{ number_format($expense['total'], 0, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">Belum ada pengeluaran bulan ini.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Transaksi Terbaru</h3>
                </div>
                <div class="p-6">
                    @if(count($recentTransactions ?? []) > 0)
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-gray-500 text-sm">
                                    <th class="pb-2">Tanggal</th>
                                    <th class="pb-2">Kategori</th>
                                    <th class="pb-2">Akun</th>
                                    <th class="pb-2 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($recentTransactions as $tx)
                                    <tr>
                                        <td class="py-2">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                                        <td class="py-2">{{ $tx->category?->name ?? '-' }}</td>
                                        <td class="py-2">{{ $tx->account?->name ?? '-' }}</td>
                                        <td class="py-2 text-right {{ $tx->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $tx->type === 'income' ? '+' : '-' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                        </td>
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
    </div>
</x-app-layout>
