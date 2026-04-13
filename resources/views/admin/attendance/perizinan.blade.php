<x-app-layout>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="py-12 bg-gray-50/50" x-data="{ showHistory: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Persetujuan Perizinan</h2>
                    <p class="text-sm text-gray-500">Validasi pengajuan sakit, izin, dan cuti karyawan.</p>
                </div>
                <button @click="showHistory = true"
                    class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-200 rounded-xl font-bold text-xs text-indigo-600 uppercase tracking-widest hover:bg-indigo-50 hover:border-indigo-200 shadow-sm transition-all active:scale-95">
                    <i class="fas fa-history mr-2"></i>
                    Riwayat Perizinan
                </button>
            </div>

            {{-- Alert --}}
            @if(session('success'))
                <div class="mb-5 bg-emerald-500 text-white p-4 rounded-xl shadow-lg flex justify-between items-center animate-fade-in">
                    <div class="flex items-center font-bold text-sm">
                        <i class="fas fa-check-circle mr-3"></i>
                        {{ session('success') }}
                    </div>
                    <button onclick="this.parentElement.remove()" class="opacity-70 hover:opacity-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            {{-- Tabel Pending --}}
            <div class="bg-white shadow-xl shadow-gray-200/50 rounded-2xl overflow-hidden border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Antrian Pengajuan</h3>
                    <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded-full border border-amber-100">
                        {{ $permissions->where('status', 'pending')->count() }} Pending
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Karyawan</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Periode Izin</th>
                                <th class="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alasan</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Foto</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dokumen</th>
                                <th class="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @forelse($permissions->where('status', 'pending') as $p)
                                <tr class="hover:bg-indigo-50/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-xs flex-shrink-0">
                                                {{ strtoupper(substr($p->user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-800">{{ $p->user->name }}</p>
                                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight">{{ $p->user->division->name ?? 'Staff' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border
                                            {{ $p->type == 'sakit' ? 'bg-rose-50 text-rose-600 border-rose-100' : ($p->type == 'cuti' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-blue-50 text-blue-600 border-blue-100') }}">
                                            {{ $p->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-600">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded">{{ \Carbon\Carbon::parse($p->start_date)->format('d M Y') }}</span>
                                            <span class="text-gray-300">→</span>
                                            <span class="bg-gray-100 px-2 py-0.5 rounded">{{ \Carbon\Carbon::parse($p->end_date)->format('d M Y') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500 max-w-[180px]">{{ $p->reason }}</td>

                                    {{-- Kolom Foto --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($p->attachment_photo)
                                            <a href="{{ asset($p->attachment_photo) }}" target="_blank"
                                               class="inline-flex flex-col items-center gap-0.5 border border-gray-200 rounded-lg p-1 hover:bg-gray-50 transition-all">
                                                <img src="{{ asset($p->attachment_photo) }}" class="h-8 w-8 object-cover rounded">
                                                <span class="text-[8px] text-gray-400 font-bold uppercase">foto</span>
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Dokumen --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($p->attachment_file)
                                            <a href="{{ asset($p->attachment_file) }}" target="_blank"
                                               class="inline-flex flex-col items-center gap-0.5 border border-blue-100 rounded-lg p-1 bg-blue-50 hover:bg-blue-100 transition-all w-11">
                                                <i class="fas fa-file-pdf text-blue-500 text-sm"></i>
                                                <span class="text-[8px] text-blue-500 font-bold uppercase">doc</span>
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <form action="{{ route('admin.presence.approve', $p->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button class="w-8 h-8 flex items-center justify-center bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-lg hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.presence.reject', $p->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button class="w-8 h-8 flex items-center justify-center bg-rose-50 text-rose-500 border border-rose-100 rounded-lg hover:bg-rose-500 hover:text-white hover:border-rose-500 transition-all">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-20 text-center text-gray-300 italic text-sm">
                                        <i class="fas fa-inbox text-2xl mb-2 block"></i>
                                        Tidak ada pengajuan pending.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- MODAL RIWAYAT --}}
        <div x-show="showHistory"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60"
             @click.self="showHistory = false"
             x-cloak>

            <div x-show="showHistory"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden border border-gray-100 shadow-2xl">

                {{-- Header Modal --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-sm font-black text-gray-800">Riwayat Perizinan</h3>
                        <p class="text-[10px] text-gray-400 mt-0.5">Semua pengajuan yang sudah diproses</p>
                    </div>
                    <button @click="showHistory = false"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-400 hover:bg-gray-100 text-lg leading-none transition-colors">
                        &times;
                    </button>
                </div>

                {{-- Tabel Scrollable --}}
                <div class="overflow-y-auto flex-1">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-white sticky top-0 z-10 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Karyawan</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Periode</th>
                                <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Foto</th>
                                <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Dokumen</th>
                                <th class="px-6 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu Proses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            @forelse($permissions->whereIn('status', ['approved', 'rejected']) as $h)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black text-[10px] flex-shrink-0">
                                                {{ strtoupper(substr($h->user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-800">{{ $h->user->name }}</p>
                                                <p class="text-[9px] text-gray-400 uppercase font-bold tracking-tight">{{ $h->user->division->name ?? 'Staff' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase border
                                            {{ $h->type == 'sakit' ? 'bg-rose-50 text-rose-600 border-rose-100' : ($h->type == 'cuti' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-blue-50 text-blue-600 border-blue-100') }}">
                                            {{ $h->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-600">
                                            <span class="bg-gray-100 px-2 py-0.5 rounded">{{ \Carbon\Carbon::parse($h->start_date)->format('d M y') }}</span>
                                            <span class="text-gray-300">→</span>
                                            <span class="bg-gray-100 px-2 py-0.5 rounded">{{ \Carbon\Carbon::parse($h->end_date)->format('d M y') }}</span>
                                        </div>
                                    </td>

                                    {{-- Foto --}}
                                    <td class="px-6 py-3 text-center">
                                        @if($h->attachment_photo)
                                            <a href="{{ asset($h->attachment_photo) }}" target="_blank"
                                               class="inline-block w-7 h-7 rounded-lg overflow-hidden border border-gray-200 hover:scale-110 transition-transform">
                                                <img src="{{ asset($h->attachment_photo) }}" class="w-full h-full object-cover">
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Dokumen --}}
                                    <td class="px-6 py-3 text-center">
                                        @if($h->attachment_file)
                                            <a href="{{ asset($h->attachment_file) }}" target="_blank"
                                               class="inline-flex w-7 h-7 rounded-lg bg-blue-50 border border-blue-100 items-center justify-center text-blue-500 hover:bg-blue-500 hover:text-white transition-all">
                                                <i class="fas fa-file-pdf text-[9px]"></i>
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase border
                                            {{ $h->status == 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100' }}">
                                            {{ $h->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono text-[10px] text-gray-400">
                                        {{ $h->updated_at->format('d/m/y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-gray-300 italic text-xs">
                                        <i class="fas fa-folder-open text-xl mb-2 block"></i>
                                        Belum ada riwayat perizinan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between flex-shrink-0">
                    <span class="text-[10px] text-gray-400">
                        {{ $permissions->whereIn('status', ['approved','rejected'])->count() }} data riwayat
                    </span>
                    <button @click="showHistory = false"
                            class="px-5 py-1.5 bg-white border border-gray-200 rounded-lg text-[10px] font-black text-gray-500 hover:bg-gray-100 uppercase tracking-widest transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes fade-in { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }
    </style>
</x-app-layout>