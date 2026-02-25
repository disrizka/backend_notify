<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Riwayat Tugas JONUSA</h2>
            
            @if(session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm" role="alert">
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @foreach($jobs as $job)
            <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-l-8 {{ $job->status == 'completed' ? 'border-green-500' : 'border-yellow-500' }}">
                
                <div class="flex justify-between items-start border-b pb-4 mb-6">
                    <div>
                        <h3 class="text-xl font-black uppercase text-indigo-800">{{ $job->title }}</h3>
                        <p class="text-sm text-gray-500 italic">Teknisi: <strong>{{ $job->technician->name }}</strong> | CS: <strong>{{ $job->cs->name }}</strong></p>
                    </div>
                    <span class="px-4 py-1 rounded-full text-xs font-black uppercase tracking-widest {{ $job->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $job->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    @foreach($job->trackers as $tracker)
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-200 shadow-sm">
                        <span class="bg-indigo-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-md mb-2 inline-block">TAHAP {{ $tracker->step_number }}</span>
                        <p class="text-gray-700 text-xs mb-4 font-semibold">{{ $tracker->description_value ?? 'Tanpa deskripsi.' }}</p>
                        
                        @if($tracker->photo_path)
                            <img src="{{ asset('storage/' . $tracker->photo_path) }}" class="w-full h-40 object-cover rounded-xl border-2 border-white shadow-sm mb-2">
                        @endif
                        
                        @if($tracker->video_path)
                            <video controls class="w-full h-40 rounded-xl bg-black shadow-sm">
                                <source src="{{ asset('storage/' . $tracker->video_path) }}" type="video/mp4">
                            </video>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-6 border-t-2 border-dashed border-gray-100">
                    
                    @if(Auth::user()->role === 'kepala')
                        <form action="{{ route('jobs.feedback', $job->id) }}" method="POST" class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100 mb-4">
                            @csrf
                            <label class="block font-black text-indigo-900 mb-3 text-xs uppercase tracking-widest">Update Feedback / Instruksi :</label>
                            <textarea name="feedback" rows="2" class="w-full border-gray-200 rounded-xl shadow-inner text-sm mb-3 focus:ring-indigo-500">{{ old('feedback', $job->feedback) }}</textarea>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-indigo-700 hover:bg-indigo-800 text-white px-8 py-2 rounded-xl font-black text-xs uppercase tracking-widest shadow-lg transition active:scale-95">
                                    Simpan Feedback
                                </button>
                            </div>
                        </form>
                    @endif

                    @if($job->feedback)
                        <div class="p-5 bg-blue-600 rounded-2xl shadow-lg text-white relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                    <span class="font-black text-[10px] uppercase tracking-tighter text-blue-100">Feedback Pak Adam :</span>
                                </div>
                                <p class="text-sm font-medium leading-relaxed italic">" {{ $job->feedback }} "</p>
                            </div>
                            <div class="absolute -right-4 -bottom-4 opacity-10">
                                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"></path></svg>
                            </div>
                        </div>
                    @elseif(Auth::user()->role !== 'kepala')
                        <div class="text-center py-2">
                            <p class="text-[10px] text-gray-400 font-bold italic uppercase tracking-widest">Belum ada feedback dari pimpinan.</p>
                        </div>
                    @endif

                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>