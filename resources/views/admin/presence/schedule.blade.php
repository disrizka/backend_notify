<x-app-layout>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js" rel="stylesheet">
    
    <div class="p-6">
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-2xl font-bold mb-4">Kalender Jadwal Kerja</h2>
            <div id='calendar'></div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            // Data 'events' ini yang bikin warna merah muncul
            events: @json($holidays), 
            dateClick: function(info) {
                Swal.fire({
                    title: 'Update Jadwal Kerja',
                    text: "Ubah status libur untuk tanggal " + info.dateStr + "?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ubah!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jalankan fungsi kirim ke database
                        toggleHoliday(info.dateStr);
                    }
                });
            }
        });
        calendar.render();

       function toggleHoliday(date) {
            fetch("{{ route('admin.presence.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}" 
                },
                body: JSON.stringify({ date: date })
            })
            .then(res => res.json())
            .then(data => {
                // Gunakan SweetAlert untuk memberi tahu user berhasil
                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                    location.reload(); 
                });
            })
            .catch(err => {
                Swal.fire('Error!', 'Gagal mengubah jadwal', 'error');
            });
        }
    });
</script>

    <style>
        #calendar { max-height: 600px; }
        .fc-day-sun { background-color: #fff1f2; }
    </style>
</x-app-layout>