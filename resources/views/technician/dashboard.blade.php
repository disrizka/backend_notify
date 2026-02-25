<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard - Tracker Kerja') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-md mb-6" role="alert">
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @forelse($jobs as $job)
                <div class="p-6 bg-white shadow sm:rounded-lg border-l-4 {{ $job->status == 'pending' ? 'border-yellow-400' : 'border-indigo-500' }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $job->title }}</h3>
                            <p class="text-sm text-gray-500">Instruksi CS: {{ $job->description }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $job->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-indigo-100 text-indigo-700' }}">
                            {{ $job->status }}
                        </span>
                    </div>

                    <div class="mt-6">
                        @if($job->status == 'pending')
                            <form action="{{ route('jobs.accept', $job->id) }}" method="POST">
                                @csrf
                                <x-primary-button class="bg-blue-600 hover:bg-blue-700 w-full justify-center py-3">
                                    {{ __('Ambil Tugas & Mulai') }}
                                </x-primary-button>
                            </form>
                        @else
                            @php 
                                $nextStep = $job->current_step; 
                                $division = Auth::user()->division;
                                $stepLabel = "step_" . $nextStep;
                            @endphp

                            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                                <div class="flex items-center mb-4">
                                    <div class="bg-indigo-600 text-white rounded-full h-8 w-8 flex items-center justify-center font-bold mr-3">
                                        {{ $nextStep }}
                                    </div>
                                    <h4 class="font-bold text-indigo-900 uppercase tracking-wide">
                                        Proses: {{ $division->$stepLabel }}
                                    </h4>
                                </div>

                                <form action="{{ route('jobs.progress', $job->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    
                                    @if($division->{"req_desc_$nextStep"})
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Pekerjaan (Wajib)</label>
                                            <textarea name="description" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500" placeholder="Jelaskan apa yang kamu lakukan di tahap ini..." required></textarea>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if($division->{"req_photo_$nextStep"})
                                            <div class="p-3 bg-white rounded-lg border-2 border-dashed border-gray-300">
                                                <label class="block text-xs font-bold text-gray-400 mb-2 uppercase">Bukti Foto</label>
                                                <input type="file" name="photo" accept="image/*" class="text-sm" required>
                                            </div>
                                        @endif

                                        @if($division->{"req_video_$nextStep"})
                                            <div class="p-3 bg-white rounded-lg border-2 border-dashed border-gray-300">
                                                <label class="block text-xs font-bold text-gray-400 mb-2 uppercase">Bukti Video</label>
                                                <input type="file" name="video" accept="video/*" class="text-sm" required>
                                            </div>
                                        @endif
                                    </div>

                                    <x-primary-button class="w-full justify-center py-3 shadow-lg shadow-indigo-200">
                                        {{ $nextStep == 4 ? 'Konfirmasi Pekerjaan Selesai' : 'Simpan & Lanjut ke Tahap ' . ($nextStep + 1) }}
                                    </x-primary-button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 bg-white shadow sm:rounded-lg text-center">
                    <div class="text-gray-400 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada tugas baru untukmu saat ini.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>