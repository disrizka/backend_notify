<div class="flex h-screen bg-gray-100" 
     x-data="{ 
        openKehadiran: {{ (request()->routeIs('admin.presence.*') || request()->routeIs('admin.perizinan.*') || request()->routeIs('admin.leaves.*')) ? 'true' : 'false' }},
        openRekrutmen: false,
        openMobile: false 
     }">
    
    <div class="w-64 bg-white shadow-sm border-r border-gray-200 hidden md:block">
        <div class="p-6 flex flex-col h-full">
            <div class="shrink-0 flex items-center mb-10 pl-2">
                <a href="{{ route('dashboard') }}">
                    <x-application-logo class="block h-10 w-auto fill-current text-gray-800" />
                </a>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto">
                {{-- 0. Dashboard --}}
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                    <i class="fas fa-home w-5 mr-3"></i> {{ __('Dashboard') }}
                </x-nav-link>

                @if(Auth::user()->role === 'kepala')
                    {{-- Menu Pengaturan --}}
                    <x-nav-link :href="route('divisions.index')" :active="request()->routeIs('divisions.*')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-layer-group w-5 mr-3"></i> {{ __('Pengaturan Divisi') }}
                    </x-nav-link>

                    <x-nav-link :href="route('users-management.index')" :active="request()->routeIs('users-management.*')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-users w-5 mr-3"></i> {{ __('Manajemen Karyawan') }}
                    </x-nav-link>

                    {{-- Dropdown Kehadiran --}}
                    <div class="space-y-1">
                        <button @click="openKehadiran = !openKehadiran" 
                            class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none rounded-md group {{ (request()->routeIs('admin.presence.*') || request()->routeIs('admin.perizinan.*') || request()->routeIs('admin.leaves.*')) ? 'text-pink-600 bg-pink-50' : 'text-gray-600 hover:bg-gray-50' }}">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-check w-5 mr-3 {{ (request()->routeIs('admin.presence.*') || request()->routeIs('admin.perizinan.*') || request()->routeIs('admin.leaves.*')) ? 'text-pink-600' : 'text-gray-400' }}"></i>
                                <span>{{ __('Kehadiran') }}</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openKehadiran}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="openKehadiran" x-cloak x-transition class="mt-1 ml-4 space-y-1 border-l-2 border-gray-100 pl-4">
                            <x-nav-link :href="route('admin.presence.index')" :active="request()->routeIs('admin.presence.index')" 
                                class="block py-2 text-xs border-none w-full justify-start">
                                1. Approval Absensi
                            </x-nav-link>

                            <x-nav-link :href="route('admin.perizinan')" :active="request()->routeIs('admin.perizinan')" 
                                class="block py-2 text-xs border-none w-full justify-start">
                                2. Izin & Cuti
                            </x-nav-link>

                            <x-nav-link :href="route('admin.presence.schedule')" :active="request()->routeIs('admin.presence.schedule')" 
                                class="block py-2 text-xs border-none w-full justify-start">
                                3. Jadwal Kerja
                            </x-nav-link>

                            <x-nav-link :href="route('admin.presence.history')" :active="request()->routeIs('admin.presence.history')" 
                                class="block py-2 text-xs border-none w-full justify-start">
                                4. Riwayat Presensi
                            </x-nav-link>
                            
                            <x-nav-link :href="route('admin.presence.settings')" :active="request()->routeIs('admin.presence.settings')"
                                class="block py-2 text-xs border-none w-full justify-start">
                                5. Settings Absensi
                            </x-nav-link>
                        </div>
                    </div>

                    {{-- Dropdown Rekrutmen --}}
                    <div class="space-y-1">
                        <button @click="openRekrutmen = !openRekrutmen" 
                            class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none rounded-md group text-gray-600 hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie w-5 mr-3 text-gray-400"></i>
                                <span>{{ __('Rekrutmen') }}</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openRekrutmen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="openRekrutmen" x-cloak x-transition class="mt-1 ml-4 space-y-1 border-l-2 border-gray-100 pl-4">
                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                1. Profil
                            </x-nav-link>

                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                2. Manajemen Rekrutmen
                            </x-nav-link>

                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                3. Lowongan Pekerjaan
                            </x-nav-link>

                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                4. Kandidat
                            </x-nav-link>
                        </div>
                    </div>

                    {{-- Menu Tugas --}}
                    <x-nav-link :href="route('admin.createTemplate')" :active="request()->routeIs('admin.createTemplate')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-file-alt w-5 mr-3"></i> {{ __('Atur Template Ceklis') }}
                    </x-nav-link>

                    <x-nav-link :href="route('jobs.create')" :active="request()->routeIs('jobs.create')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Buat Tugas Baru') }}
                    </x-nav-link>

                    <x-nav-link :href="route('jobs.history')" :active="request()->routeIs('jobs.history')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-history w-5 mr-3"></i> {{ __('Riwayat Tugas') }}
                    </x-nav-link>
                     <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Approval') }}
                    </x-nav-link>
                    <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Timeline') }}
                    </x-nav-link>
                    <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Clien') }}
                    </x-nav-link>

                       {{-- Dropdown Rekrutmen --}}
                    <div class="space-y-1">
                        <button @click="openRekrutmen = !openRekrutmen" 
                            class="flex items-center justify-between w-full px-3 py-2 text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none rounded-md group text-gray-600 hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie w-5 mr-3 text-gray-400"></i>
                                <span>{{ __('KPI') }}</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': openRekrutmen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="openRekrutmen" x-cloak x-transition class="mt-1 ml-4 space-y-1 border-l-2 border-gray-100 pl-4">
                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                1. Formulir
                            </x-nav-link>

                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                2. Jadwal
                            </x-nav-link>

                            <x-nav-link href="#" :active="false"
                                class="block py-2 text-xs border-none w-full justify-start">
                                3. Evaluasi
                            </x-nav-link>
                        </div>
                    </div>
                    <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Akuntansi') }}
                    </x-nav-link>
                    <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Tugas') }}
                    </x-nav-link>
                    <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Pengaturan') }}
                        <x-nav-link
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('FAQ') }}
                    </x-nav-link>
                    </x-nav-link>
                    <x-nav-link :href="route('admin.messages')" :active="request()->routeIs('admin.messages')" 
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start transition-colors duration-200">
                    <i class="fas fa-comments w-5 mr-3"></i> {{ __('Chat Group') }}
</x-nav-link>
                @endif

                @if(Auth::user()->role === 'karyawan')
                    <x-nav-link :href="route('checklists.index')" :active="request()->routeIs('checklists.index')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start">
                        <i class="fas fa-check-double w-5 mr-3"></i> {{ __('Ceklis Harian') }}
                    </x-nav-link>

                    <x-nav-link :href="route('technician.dashboard')" :active="request()->routeIs('technician.dashboard')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start">
                        <i class="fas fa-stopwatch w-5 mr-3"></i> {{ __('Tracker Kerja') }}
                    </x-nav-link>

                    <x-nav-link :href="route('jobs.history')" :active="request()->routeIs('jobs.history')" 
                        class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start">
                        <i class="fas fa-history w-5 mr-3"></i> {{ __('Riwayat Tugas') }}
                    </x-nav-link>
                    
                    @if(Auth::user()->division && Auth::user()->division->name == 'Customer Service')
                        <x-nav-link :href="route('jobs.create')" :active="request()->routeIs('jobs.create')" 
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-md border-none w-full justify-start">
                            <i class="fas fa-plus-circle w-5 mr-3"></i> {{ __('Buat Tugas Baru') }}
                        </x-nav-link>
                        
                    @endif
                @endif
            </nav>

            <div class="border-t border-gray-200 pt-4 mt-6">
                <div class="flex items-center px-3 mb-4">
                    <div class="ml-1">
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-tighter">{{ Auth::user()->role }}</p>
                        <p class="text-sm font-medium text-gray-700 truncate w-32">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="space-y-1 px-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center w-full px-2 py-2 text-xs text-red-600 hover:bg-red-50 rounded-md transition duration-150">
                            <i class="fas fa-sign-out-alt mr-2"></i> {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="md:hidden bg-white border-b p-4 flex justify-between items-center text-gray-800">
            <x-application-logo class="h-8 w-auto" />
            <button @click="openMobile = !openMobile" class="focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-8">
            {{ $slot }}
        </main>
    </div>
</div>