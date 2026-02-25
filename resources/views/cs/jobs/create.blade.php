<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto bg-white p-8 shadow rounded-lg">
            <h2 class="text-2xl font-bold mb-6">Buat Tugas Baru (Job Order)</h2>
            <form action="{{ route('jobs.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <x-text-input name="title" placeholder="Judul Tugas" class="w-full" required />
                    <textarea name="description" class="w-full border-gray-300 rounded-md" placeholder="Detail Tugas..."></textarea>
                    
                    <label class="block font-medium text-sm text-gray-700">Pilih  :</label>
                    <select name="technician_id" class="w-full border-gray-300 rounded-md shadow-sm">
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->division->name ?? 'Tanpa Divisi' }})</option>
                        @endforeach
                    </select>
                    
                    <x-primary-button>Kirim</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>