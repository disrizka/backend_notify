{{-- FILE: resources/views/admin/presence/schedule.blade.php --}}
{{-- Aturan: Jumat otomatis libur. Sabtu & Minggu = hari kerja. --}}
{{-- Sinkron dengan Flutter JadwalKerjaScreen. --}}
<x-app-layout>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="p-6 bg-gray-50 min-h-screen font-[Inter]">
        <div class="max-w-5xl mx-auto">

            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Kalender Jadwal Kerja
                    </h2>
                    <p class="text-gray-500 text-sm mt-1">
                        Klik tanggal <strong>Senin – Kamis</strong> untuk toggle Libur / Hari Kerja.<br>
                        <span class="text-red-500 font-semibold">Hari Jumat otomatis libur</span> dan tidak dapat diubah.
                    </p>
                </div>

                <div class="flex items-center gap-4 bg-white p-3 rounded-lg border shadow-sm text-xs font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-100 border border-red-400"></span>
                        <span class="text-gray-600">Hari Libur</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-white border border-gray-300"></span>
                        <span class="text-gray-600">Hari Kerja</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-2 sm:p-6">
                    <div id="calendar"></div>
                </div>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400 italic">
                * Hari Jumat otomatis libur — sinkron dengan aplikasi mobile.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const holidaysData = @json($holidays);
        const holidaySet   = new Set(holidaysData.map(h => h.start));

        const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView:  'dayGridMonth',
            locale:       'id',
            height:       'auto',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,dayGridWeek',
            },

            events: holidaysData,

            // Warnai sel hari libur (merah soft)
            dayCellDidMount: function (info) {
                const dateStr = info.date.toLocaleDateString('en-CA');
                const dow     = info.date.getDay(); // 5 = Jumat

                if (dow === 5 || holidaySet.has(dateStr)) {
                    info.el.style.backgroundColor = '#fef2f2';
                    const num = info.el.querySelector('.fc-daygrid-day-number');
                    if (num) {
                        num.style.color      = '#dc2626';
                        num.style.fontWeight = '700';
                    }
                }
            },

            dateClick: function (info) {
                const dow = info.date.getDay();

                // Blokir klik Jumat
                if (dow === 5) {
                    Swal.fire({
                        icon:  'info',
                        title: 'Tidak Dapat Diubah',
                        text:  'Hari Jumat otomatis libur dan tidak dapat diubah.',
                        confirmButtonColor: '#2563eb',
                    });
                    return;
                }

                const isHoliday = holidaySet.has(info.dateStr);
                const action    = isHoliday ? 'jadikan HARI KERJA' : 'jadikan HARI LIBUR';

                Swal.fire({
                    title:              'Update Jadwal?',
                    text:               `${info.dateStr} akan di-${action}.`,
                    icon:               'question',
                    showCancelButton:   true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor:  '#64748b',
                    confirmButtonText:  'Ya, Ubah',
                    cancelButtonText:   'Batal',
                    reverseButtons:     true,
                }).then(r => { if (r.isConfirmed) toggleHoliday(info.dateStr); });
            },
        });

        calendar.render();

        function toggleHoliday(date) {
            Swal.showLoading();
            fetch("{{ route('admin.presence.toggle') }}", {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ date }),
            })
            .then(r => r.json())
            .then(data => {
                Swal.fire({
                    title:             data.success ? 'Berhasil!' : 'Info',
                    text:              data.message,
                    icon:              data.success ? 'success' : 'info',
                    timer:             1600,
                    showConfirmButton: false,
                }).then(() => location.reload());
            })
            .catch(() => Swal.fire('Error!', 'Gagal menyambung ke server.', 'error'));
        }
    });
    </script>

    <style>
        :root { --fc-border-color: #f1f5f9; }
        #calendar { font-family: 'Inter', sans-serif; }
        .fc .fc-button-primary {
            background-color: #fff; border-color: #e2e8f0;
            color: #475569; font-weight: 600; text-transform: capitalize; transition: all .2s;
        }
        .fc .fc-button-primary:hover { background-color: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #2563eb; border-color: #2563eb; color: white;
        }
        .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; }
        .fc .fc-daygrid-day-top { padding: 6px; }
        /* Jumat (Jum) merah di header */
        .fc-day-fri .fc-col-header-cell-cushion { color: #ef4444 !important; font-weight: 700; }
        .fc-daygrid-day:hover { background-color: #f1f5f9 !important; cursor: pointer; }
        .fc-event {
            border: none !important; padding: 2px 5px !important;
            font-size: .72rem !important; font-weight: 600 !important; border-radius: 4px !important;
        }
        @media (max-width: 640px) { .fc .fc-toolbar { flex-direction: column; gap: 10px; } }
    </style>
</x-app-layout>