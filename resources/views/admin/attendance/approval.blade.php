<x-app-layout>
    @php $status = $status ?? null; @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log & Persetujuan Presensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 px-4 py-3 rounded shadow-sm flex justify-between items-center">
                    <span>{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">×</button>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    
                    {{-- Filter --}}
                    <div class="mb-6 flex flex-wrap gap-2">
                        <a href="{{ route('admin.presence.index') }}" class="px-4 py-2 rounded-full text-sm font-bold transition {{ !$status ? 'bg-indigo-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</a>
                        <a href="{{ route('admin.presence.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition {{ $status == 'pending' ? 'bg-amber-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Menunggu</a>
                        <a href="{{ route('admin.presence.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition {{ $status == 'approved' ? 'bg-emerald-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Disetujui</a>
                        <a href="{{ route('admin.presence.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-full text-sm font-bold transition {{ $status == 'rejected' ? 'bg-rose-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Ditolak</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Karyawan</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Tipe</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Foto</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Waktu & Lokasi</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest">Keterangan</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-widest">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @php $rowNumber = 1; @endphp
                                @forelse($presences as $p)
                                    
                                    {{-- BARIS 1: CHECK IN --}}
                                    <tr class="hover:bg-indigo-50/30 transition">
                                        <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ $rowNumber++ }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-extrabold text-sm text-gray-900">{{ $p->user->name }}</div>
                                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 uppercase">IN</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{-- FIX: asset() langsung tanpa 'storage/' --}}
                                            <a href="{{ asset($p->photo_in) }}" target="_blank">
                                                <img src="{{ asset($p->photo_in) }}" class="w-10 h-10 object-cover rounded shadow-sm border border-gray-200">
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-700">{{ substr($p->check_in, 0, 5) }}</div>
                                            <a href="https://www.google.com/maps?q={{ $p->lat_in }},{{ $p->lng_in }}" target="_blank" class="text-[10px] font-bold text-indigo-600 hover:underline italic">Lihat Maps</a>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500 italic">{{ $p->notes ?? '—' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wider 
                                                {{ $p->is_approved == 'approved' ? 'bg-emerald-100 text-emerald-700' : ($p->is_approved == 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $p->is_approved }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($p->is_approved == 'pending')
                                                <div class="flex justify-center gap-1">
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'approved']) }}?type=in" method="POST">
                                                        @csrf
                                                        <button class="bg-emerald-600 text-white px-2 py-1 rounded text-[9px] font-bold shadow-sm">SETUJU</button>
                                                    </form>
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'rejected']) }}?type=in" method="POST">
                                                        @csrf
                                                        <button class="bg-rose-600 text-white px-2 py-1 rounded text-[9px] font-bold shadow-sm">TOLAK</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-[10px] text-gray-400 font-bold uppercase italic">Locked</span>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- BARIS 2: CHECK OUT --}}
                                    @if($p->check_out)
                                    @php $approvedOut = $p->is_approved_out ?? 'pending'; @endphp
                                    <tr class="hover:bg-rose-50/30 transition">
                                        <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ $rowNumber++ }}</td>
                                        <td class="px-6 py-4">
                                            <div class="font-extrabold text-sm text-gray-900">{{ $p->user->name }}</div>
                                            <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($p->date)->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded bg-rose-100 text-rose-700 uppercase">OUT</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{-- FIX: asset() langsung tanpa 'storage/' --}}
                                            <a href="{{ asset($p->photo_out) }}" target="_blank">
                                                <img src="{{ asset($p->photo_out) }}" class="w-10 h-10 object-cover rounded shadow-sm border border-gray-200">
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-700">{{ substr($p->check_out, 0, 5) }}</div>
                                            <a href="https://www.google.com/maps?q={{ $p->lat_out }},{{ $p->lng_out }}" target="_blank" class="text-[10px] font-bold text-rose-600 hover:underline italic">Lihat Maps</a>
                                        </td>
                                        <td class="px-6 py-4 text-xs text-gray-500 italic">{{ $p->notes_out ?? '—' }}</td>
                                        {{-- Status khusus OUT pakai is_approved_out --}}
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-wider 
                                                {{ $approvedOut == 'approved' ? 'bg-emerald-100 text-emerald-700' : ($approvedOut == 'rejected' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                                {{ $approvedOut }}
                                            </span>
                                        </td>
                                        {{-- Aksi khusus OUT --}}
                                        <td class="px-6 py-4 text-center">
                                            @if($approvedOut == 'pending')
                                                <div class="flex justify-center gap-1">
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'approved']) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="type" value="out">  {{-- FIX: pakai hidden input --}}
                                                        <button class="bg-emerald-600 text-white px-2 py-1 rounded text-[9px] font-bold shadow-sm">SETUJU</button>
                                                    </form>
                                                    <form action="{{ route('admin.presence.updateStatus', [$p->id, 'rejected']) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="type" value="out">  {{-- FIX: pakai hidden input --}}
                                                        <button class="bg-rose-600 text-white px-2 py-1 rounded text-[9px] font-bold shadow-sm">TOLAK</button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-[10px] text-gray-400 font-bold uppercase italic">Locked</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif

                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-20 text-center text-gray-400 font-bold">Data tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>