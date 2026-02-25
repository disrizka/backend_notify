<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Karyawan JONUSA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">Tambah Karyawan Baru</h2>
                    <p class="mt-1 text-sm text-gray-600">Password default otomatis diset ke: **jonusa123**</p>
                </header>

                <form method="post" action="{{ route('users-management.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-text-input name="name" placeholder="Nama Lengkap" class="w-full" required />
                        <x-text-input name="email" type="email" placeholder="Email Kantor" class="w-full" required />
                        <select name="division_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" required>
                            <option value="">-- Pilih Divisi --</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div->id }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Daftarkan Karyawan') }}</x-primary-button>
                </form>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="py-3 px-4">Nama</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Divisi</th>
                            <th class="py-3 px-4">Status Password</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-b text-sm hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $user->name }}</td>
                            <td class="py-3 px-4">{{ $user->email }}</td>
                            <td class="py-3 px-4 font-bold text-indigo-600">
                                {{ $user->division->name ?? 'Belum Set' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 rounded text-xs {{ $user->is_default_password ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                    {{ $user->is_default_password ? 'Wajib Ganti' : 'Sudah Aman' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <form action="{{ route('users-management.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 font-bold hover:text-red-700 underline">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($users->isEmpty())
                    <p class="text-center py-4 text-gray-500">Belum ada karyawan terdaftar.</p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>