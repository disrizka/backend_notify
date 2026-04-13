<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Persetujuan Presensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded shadow-sm flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg leading-none">×</button>
                </div>
            @endif

            {{-- Info banner --}}
            <div class="mb-4 flex flex-wrap gap-3 items-center bg-white border border-gray-100 rounded-xl px-4 py-3 shadow-sm text-xs">
                <span class="font-bold text-gray-500 uppercase tracking-wide">Halaman ini:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-bold">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    Hanya absensi yang butuh persetujuan manual (WFH / di luar radius)
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Setelah disetujui/ditolak → otomatis hilang & masuk Riwayat
                </span>
                <a href="{{ route('admin.presence.history') }}"
                   class="ml-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold hover:bg-blue-200 transition text-xs">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="3"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    Lihat Riwayat Lengkap →
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">

                    {{-- Counter --}}
                    <div class="mb-5 flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-600">Menunggu persetujuan:</span>
                        @php
                            $totalPendingIn  = $presences->where('is_approved', 'pending')->count();
                            $totalPendingOut = $presences->filter(fn($p) =>
                                $p->check_out &&
                                in_array($p->is_approved_out ?? 'pending', ['pending'])
                            )->count();
                            $grandTotal = $totalPendingIn + $totalPendingOut;
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full
                            {{ $grandTotal > 0 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500' }}
                            text-xs font-black">
                            {{ $grandTotal }} item
                        </span>
                        @if($totalPendingIn > 0)
                            <span class="text-xs text-gray-400">{{ $totalPendingIn }} Check-In</span>
                        @endif
                        @if($totalPendingOut > 0)
                            <span class="text-xs text-gray-400">{{ $totalPendingOut }} Check-Out</span>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">No</th>
                                    <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Karyawan</th>
                                    <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Tipe</th>
                                    <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Foto</th>
                                    <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Waktu & Lokasi</th>
                                    <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Keterangan</th>
                                    <th class="px-4 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-widest">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100" id="approval-tbody">

                                @php $rowNumber = 1; $hasRows = false; @endphp

                                @forelse($presences as $p)

                                    {{-- ── CHECK IN: hanya muncul jika is_approved = pending ── --}}
                                    @if($p->is_approved === 'pending')
                                        @php $hasRows = true; @endphp
                                        <tr id="row-in-{{ $p->id }}" class="hover:bg-amber-50/40 transition-colors">
                                            <td class="px-4 py-4 text-sm font-bold text-gray-400">{{ $rowNumber++ }}</td>
                                            <td class="px-4 py-4">
                                                <div class="font-extrabold text-sm text-gray-900">{{ $p->user->name }}</div>
                                                <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($p->date)->isoFormat('dddd, D MMM Y') }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="text-[10px] font-black px-2 py-1 rounded-lg bg-indigo-100 text-indigo-700 uppercase tracking-wide">
                                                    CHECK IN
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                @if($p->photo_in)
                                                    <a href="{{ asset($p->photo_in) }}" target="_blank">
                                                        <img src="{{ asset($p->photo_in) }}"
                                                             class="w-11 h-11 object-cover rounded-lg shadow-sm border border-gray-200 hover:scale-110 transition-transform cursor-pointer"
                                                             title="Klik untuk lihat foto">
                                                    </a>
                                                @else
                                                    <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-content-center">
                                                        <svg width="18" height="18" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto">
                                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                            <circle cx="12" cy="13" r="4"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm font-bold text-gray-800">{{ substr($p->check_in, 0, 5) }}</div>
                                                <a href="https://www.google.com/maps?q={{ $p->lat_in }},{{ $p->lng_in }}"
                                                   target="_blank"
                                                   class="text-[10px] font-bold text-indigo-600 hover:underline italic flex items-center gap-0.5 mt-0.5">
                                                    <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                    Lihat Lokasi
                                                </a>
                                            </td>
                                            <td class="px-4 py-4 text-xs text-gray-500 italic max-w-[150px] truncate" title="{{ $p->notes }}">
                                                {{ $p->notes ?? '—' }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="flex justify-center gap-2">
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'approved']) }}?type=in"
                                                          method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-sm transition">
                                                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                                            SETUJU
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'rejected']) }}?type=in"
                                                          method="POST">
                                                        @csrf
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-sm transition">
                                                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                                            TOLAK
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif

                                    {{-- ── CHECK OUT: hanya muncul jika is_approved_out = pending ── --}}
                                    @if($p->check_out && ($p->is_approved_out ?? 'pending') === 'pending')
                                        @php $hasRows = true; @endphp
                                        <tr id="row-out-{{ $p->id }}" class="hover:bg-rose-50/30 transition-colors">
                                            <td class="px-4 py-4 text-sm font-bold text-gray-400">{{ $rowNumber++ }}</td>
                                            <td class="px-4 py-4">
                                                <div class="font-extrabold text-sm text-gray-900">{{ $p->user->name }}</div>
                                                <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($p->date)->isoFormat('dddd, D MMM Y') }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <span class="text-[10px] font-black px-2 py-1 rounded-lg bg-rose-100 text-rose-700 uppercase tracking-wide">
                                                    CHECK OUT
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                @if($p->photo_out)
                                                    <a href="{{ asset($p->photo_out) }}" target="_blank">
                                                        <img src="{{ asset($p->photo_out) }}"
                                                             class="w-11 h-11 object-cover rounded-lg shadow-sm border border-gray-200 hover:scale-110 transition-transform cursor-pointer"
                                                             title="Klik untuk lihat foto">
                                                    </a>
                                                @else
                                                    <div class="w-11 h-11 bg-gray-100 rounded-lg flex items-center justify-content-center">
                                                        <svg width="18" height="18" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto">
                                                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                                            <circle cx="12" cy="13" r="4"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm font-bold text-gray-800">{{ substr($p->check_out, 0, 5) }}</div>
                                                <a href="https://www.google.com/maps?q={{ $p->lat_out }},{{ $p->lng_out }}"
                                                   target="_blank"
                                                   class="text-[10px] font-bold text-rose-600 hover:underline italic flex items-center gap-0.5 mt-0.5">
                                                    <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                                    Lihat Lokasi
                                                </a>
                                            </td>
                                            <td class="px-4 py-4 text-xs text-gray-500 italic max-w-[150px] truncate" title="{{ $p->notes_out }}">
                                                {{ $p->notes_out ?? '—' }}
                                            </td>
                                            <td class="px-4 py-4 text-center">
                                                <div class="flex justify-center gap-2">
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'approved']) }}"
                                                          method="POST">
                                                        @csrf
                                                        <input type="hidden" name="type" value="out">
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-sm transition">
                                                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                                            SETUJU
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'rejected']) }}"
                                                          method="POST">
                                                        @csrf
                                                        <input type="hidden" name="type" value="out">
                                                        <button type="submit"
                                                                class="inline-flex items-center gap-1 bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-sm transition">
                                                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                                            TOLAK
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif

                                @empty
                                @endforelse

                                {{-- Empty state --}}
                                @if(!$hasRows)
                                    <tr>
                                        <td colspan="7" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center gap-3">
                                                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center">
                                                    <svg width="32" height="32" fill="none" stroke="#10b981" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <p class="font-bold text-gray-500 text-sm">Tidak ada absensi yang perlu disetujui 🎉</p>
                                                <p class="text-gray-400 text-xs">Semua absensi sudah diproses atau disetujui otomatis</p>
                                                <a href="{{ route('admin.presence.history') }}"
                                                   class="mt-2 px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-xs font-bold hover:bg-blue-200 transition">
                                                    Lihat Riwayat Lengkap →
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>