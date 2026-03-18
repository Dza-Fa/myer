<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Budget') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Month Selector -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="get" class="flex gap-4 items-center">
                    <label>Pilih Bulan:</label>
                    <select name="month" class="border rounded px-3 py-2">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][$i-1] }}
                            </option>
                        @endfor
                    </select>
                    <select name="year" class="border rounded px-3 py-2">
                        @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tampilkan</button>
                </form>
            </div>

            <!-- Budget Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Planned</div>
                    <div class="text-xl font-bold text-gray-900">
                        Rp {{ number_format($budgetData['summary']['total_planned'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Total Actual</div>
                    <div class="text-xl font-bold text-red-600">
                        Rp {{ number_format($budgetData['summary']['total_actual'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Remaining</div>
                    <div class="text-xl font-bold {{ ($budgetData['summary']['total_remaining'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($budgetData['summary']['total_remaining'] ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm">Usage</div>
                    <div class="text-xl font-bold text-blue-600">
                        {{ $budgetData['summary']['total_percentage'] ?? 0 }}%
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if(count($budgetData['alerts'] ?? []) > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <h4 class="font-semibold text-yellow-800 mb-2">Peringatan Budget:</h4>
                    <ul class="space-y-1">
                        @foreach($budgetData['alerts'] as $alert)
                            <li class="text-yellow-700">
                                <span class="font-medium">{{ $alert['category_name'] }}</span>: 
                                {{ $alert['percentage'] }}% used 
                                @if($alert['status'] === 'exceeded')
                                    <span class="text-red-600">(Melebihi budget!)</span>
                                @else
                                    <span class="text-orange-600">(Hampir penuh)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Budget Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold">Detail Budget - {{ $budgetData['budget']['month_name'] ?? '' }}</h3>
                </div>
                <div class="p-6">
                    @if(count($budgetData['items'] ?? []) > 0)
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-gray-500 text-sm border-b">
                                    <th class="pb-2">Kategori</th>
                                    <th class="pb-2 text-right">Planned</th>
                                    <th class="pb-2 text-right">Actual</th>
                                    <th class="pb-2 text-right">Remaining</th>
                                    <th class="pb-2 text-right">Usage</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($budgetData['items'] as $item)
                                    <tr>
                                        <td class="py-3">
                                            <span class="w-3 h-3 inline-block rounded-full mr-2" style="background-color: {{ $item['category_color'] }}"></span>
                                            {{ $item['category_name'] }}
                                        </td>
                                        <td class="py-3 text-right">Rp {{ number_format($item['planned_amount'], 0, ',', '.') }}</td>
                                        <td class="py-3 text-right">Rp {{ number_format($item['actual_amount'], 0, ',', '.') }}</td>
                                        <td class="py-3 text-right {{ $item['remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Rp {{ number_format($item['remaining'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 text-right">
                                            <span class="px-2 py-1 text-xs rounded 
                                                {{ $item['status'] === 'exceeded' ? 'bg-red-100 text-red-800' : ($item['status'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                                {{ $item['percentage'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <x-empty-state message="Belum ada budget untuk bulan ini." />
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
