<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Divisi & Tracker') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form method="post" action="{{ route('divisions.store') }}" class="flex items-center gap-4">
                    @csrf
                    <x-text-input name="name" placeholder="Nama Divisi Baru (Contoh: Teknisi)" class="w-full" />
                    <x-primary-button>{{ __('Tambah') }}</x-primary-button>
                </form>
            </div>

         @foreach($divisions as $div)
<div class="p-6 bg-white shadow sm:rounded-lg mb-6 border-l-4 border-indigo-500">
    <h3 class="text-xl font-bold mb-6 text-gray-800 flex items-center">
        <span class="bg-indigo-500 text-white p-2 rounded mr-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </span>
        Divisi: {{ $div->name }}
    </h3>

    <form method="post" action="{{ route('divisions.update', $div->id) }}">
        @csrf @method('put')
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @for ($i = 1; $i <= 4; $i++)
            @php 
                $stepName = "step_$i"; 
                $photoName = "req_photo_$i";
                $videoName = "req_video_$i";
            @endphp
           <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 shadow-sm hover:border-indigo-300 transition">
            <label class="block text-sm font-bold text-indigo-700 mb-2 uppercase tracking-wider">Tahap {{ $i }}</label>
            
            <x-text-input name="step_{{ $i }}" value="{{ $div->{'step_'.$i} }}" class="w-full mb-3" placeholder="Contoh: Menuju Lokasi" />
            
            <div class="space-y-2 border-t pt-3 mt-2">
                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Syarat dari Kepala:</p>
                
                <label class="flex items-center text-sm cursor-pointer group">
                    <input type="checkbox" name="req_desc_{{ $i }}" {{ $div->{'req_desc_'.$i} ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                    <span class="text-gray-700 group-hover:text-indigo-600">Wajib Deskripsi</span>
                </label>

                <label class="flex items-center text-sm cursor-pointer group">
                    <input type="checkbox" name="req_photo_{{ $i }}" {{ $div->{'req_photo_'.$i} ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                    <span class="text-gray-700 group-hover:text-indigo-600">Wajib Foto</span>
                </label>

                <label class="flex items-center text-sm cursor-pointer group">
                    <input type="checkbox" name="req_video_{{ $i }}" {{ $div->{'req_video_'.$i} ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                    <span class="text-gray-700 group-hover:text-indigo-600">Wajib Video</span>
                </label>
            </div>
        </div>
            @endfor
        </div>

        <div class="mt-6 flex justify-end">
            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                {{ __('Simpan Perubahan Alur') }}
            </x-primary-button>
        </div>
    </form>
</div>
@endforeach

        </div>
    </div>
</x-app-layout>