<x-app-layout>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Kantor & Waktu</h2>
            <p class="text-sm text-gray-500 mt-0.5">Atur koordinat, radius, dan kebijakan waktu absensi</p>
        </div>
    </x-slot>

    <style>
        .field-label { display: block; font-size: 11px; font-weight: 600; color: #9ca3af; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px; }
        .field-input { width: 100%; padding: 9px 12px; font-size: 14px; color: #111827; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; appearance: none; -webkit-appearance: none; }
        .field-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); background: #fff; }
        .btn-secondary { width: 100%; padding: 10px 16px; font-size: 13px; font-weight: 500; color: #374151; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
        .btn-secondary:hover { background: #f3f4f6; }
        .btn-primary { width: 100%; padding: 10px 16px; font-size: 13px; font-weight: 600; color: #fff; background: #2563eb; border: none; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
        .btn-primary:hover { background: #1d4ed8; }
        .stat-card { background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 10px; padding: 10px 14px; }
        .stat-label { font-size: 10px; font-weight: 600; color: #9ca3af; text-transform: uppercase; }
        .stat-value { font-size: 13px; font-weight: 500; color: #111827; margin-top: 3px; }
        .section-divider { font-size: 10px; font-weight: 800; color: #6366f1; text-transform: uppercase; letter-spacing: 0.1em; margin: 20px 0 10px 0; display: block; border-bottom: 1px solid #f3f4f6; padding-bottom: 5px; }

        /* Auto-approve info panel */
        .auto-info-card {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 16px;
        }
        .auto-info-card.wfh {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-color: #f59e0b;
        }
        .auto-rule {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px dashed #bfdbfe;
            font-size: 12px;
            color: #1e40af;
        }
        .auto-rule:last-child { border-bottom: none; }
        .auto-rule .icon {
            width: 28px; height: 28px;
            border-radius: 8px;
            background: white;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .check-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-auto { background: #dcfce7; color: #15803d; }
        .badge-manual { background: #fef3c7; color: #92400e; }
    </style>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 flex items-center gap-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- ── INFO AUTO-APPROVE ────────────────────────────────────────────── --}}
            <div class="mb-6 bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                <div class="flex items-center gap-3 mb-4">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-sm">Logika Auto-Approve Absensi</h3>
                        <p class="text-xs text-gray-500">Sistem akan otomatis menyetujui jika SEMUA syarat terpenuhi</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Syarat Auto Approve --}}
                    <div class="auto-info-card">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <span style="font-size:11px;font-weight:800;color:#1e40af;text-transform:uppercase;letter-spacing:0.05em;">✓ Auto-Approve (Kantor)</span>
                            <span class="check-badge badge-auto">Langsung Disetujui</span>
                        </div>
                        <div class="auto-rule">
                            <div class="icon">📍</div>
                            <div><strong>Dalam radius {{ $setting->radius ?? 50 }}m</strong> dari koordinat kantor<br>
                            <span style="color:#3b82f6;font-size:11px;">{{ $setting->latitude ?? '-' }}, {{ $setting->longitude ?? '-' }}</span></div>
                        </div>
                        <div class="auto-rule">
                            <div class="icon">⏰</div>
                            <div><strong>Jam masuk</strong> antara {{ substr($setting->check_in_time ?? '08:00', 0, 5) }} s/d {{ \Carbon\Carbon::createFromFormat('H:i', substr($setting->check_in_time ?? '08:00', 0, 5))->addMinutes((int)($setting->late_tolerance ?? 15))->format('H:i') }}<br>
                            <span style="color:#3b82f6;font-size:11px;">Toleransi keterlambatan {{ $setting->late_tolerance ?? 15 }} menit</span></div>
                        </div>
                        <div class="auto-rule">
                            <div class="icon">📅</div>
                            <div><strong>Hari kerja</strong> (bukan libur & bukan Jumat)</div>
                        </div>
                    </div>

                    {{-- WFH / Perlu Validasi --}}
                    <div class="auto-info-card wfh">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <span style="font-size:11px;font-weight:800;color:#92400e;text-transform:uppercase;letter-spacing:0.05em;">⏳ Perlu Validasi Admin</span>
                            <span class="check-badge badge-manual">Menunggu Approval</span>
                        </div>
                        <div class="auto-rule" style="border-color:#fde68a;color:#78350f;">
                            <div class="icon">🏠</div>
                            <div><strong>Di luar radius kantor</strong>
                            <span style="color:#d97706;font-size:11px;">Butuh persetujuan admin di halaman Approval</span></div>
                        </div>
                        <div class="auto-rule" style="border-color:#fde68a;color:#78350f;">
                            <div class="icon">⌚</div>
                            <div><strong>Check-in terlambat</strong> melebihi toleransi<br>
                            <span style="color:#d97706;font-size:11px;">Setelah {{ \Carbon\Carbon::createFromFormat('H:i', substr($setting->check_in_time ?? '08:00', 0, 5))->addMinutes((int)($setting->late_tolerance ?? 15))->format('H:i') }}</span></div>
                        </div>
                        <div class="auto-rule" style="border-color:#fde68a;color:#78350f;border-bottom:none;">
                            <div class="icon">🏖️</div>
                            <div><strong>Hari libur / Jumat</strong> — tidak perlu absen</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                {{-- Panel Kiri: Form --}}
                <div class="md:col-span-1 bg-white border border-gray-100 rounded-2xl shadow-sm p-6 flex flex-col">
                    <form action="{{ route('admin.presence.updateSettings') }}" method="POST" class="flex flex-col flex-1">
                        @csrf
                        
                        <span class="section-divider" style="margin-top: 0">Lokasi & Radius</span>
                        <div class="space-y-4">
                            <div>
                                <label for="latitude" class="field-label">Latitude</label>
                                <input id="latitude" name="latitude" type="text" class="field-input" 
                                       value="{{ $setting->latitude ?? '-6.200000' }}" required />
                            </div>

                            <div>
                                <label for="longitude" class="field-label">Longitude</label>
                                <input id="longitude" name="longitude" type="text" class="field-input" 
                                       value="{{ $setting->longitude ?? '106.816600' }}" required />
                            </div>

                            <div>
                                <label for="radius" class="field-label">Radius Absensi</label>
                                <div class="relative">
                                    <input id="radius" name="radius" type="number" class="field-input" style="padding-right: 36px;"
                                           value="{{ $setting->radius ?? '50' }}" required />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">m</span>
                                </div>
                            </div>
                        </div>

                        <span class="section-divider">Kebijakan Waktu</span>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="field-label">Jam Masuk</label>
                                    <input name="check_in_time" type="time" class="field-input" 
                                           value="{{ substr($setting->check_in_time ?? '08:00', 0, 5) }}" required>
                                </div>
                                <div>
                                    <label class="field-label">Jam Pulang</label>
                                    <input name="check_out_time" type="time" class="field-input" 
                                           value="{{ substr($setting->check_out_time ?? '17:00', 0, 5) }}" required>
                                </div>
                            </div>
                            <div>
                                <label class="field-label">Toleransi (Menit)</label>
                                <div class="relative">
                                    <input name="late_tolerance" type="number" class="field-input" 
                                           value="{{ $setting->late_tolerance ?? '15' }}" required />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">min</span>
                                </div>
                            </div>
                        </div>

                        {{-- Preview auto-approve window --}}
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;margin-top:16px;" id="preview-box">
                            <p style="font-size:10px;font-weight:700;color:#15803d;text-transform:uppercase;margin-bottom:6px;">Preview Window Auto-Approve</p>
                            <p style="font-size:12px;color:#166534;">
                                Absen masuk disetujui otomatis jika:<br>
                                • Jam <strong id="preview-start">-</strong> s/d <strong id="preview-end">-</strong><br>
                                • Dalam radius <strong id="preview-radius">-</strong> m dari titik kantor<br>
                                • Bukan hari libur
                            </p>
                        </div>

                        <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col gap-2">
                            <button type="button" onclick="getLocation()" class="btn-secondary">Gunakan Lokasi Saya</button>
                            <button type="submit" class="btn-primary">Simpan Semua Pengaturan</button>
                        </div>
                    </form>
                </div>

                {{-- Panel Kanan: Peta & Stat --}}
                <div class="md:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <p class="field-label">Visualisasi Area</p>
                        <span class="text-xs font-medium text-emerald-600 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live Map
                        </span>
                    </div>

                    <div id="map" class="rounded-xl border border-gray-100" style="height: 400px;"></div>

                    <div class="grid grid-cols-3 gap-2.5 mt-4">
                        <div class="stat-card">
                            <div class="stat-label">Koordinat</div>
                            <div class="stat-value text-[11px]">
                                <span id="lat-disp">{{ $setting->latitude ?? '-6.200000' }}</span>, 
                                <span id="lng-disp">{{ $setting->longitude ?? '106.816600' }}</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Shift Kerja</div>
                            <div class="stat-value">{{ substr($setting->check_in_time ?? '08:00', 0, 5) }} - {{ substr($setting->check_out_time ?? '17:00', 0, 5) }}</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Radius & Tol.</div>
                            <div class="stat-value"><span id="radius-disp">{{ $setting->radius ?? '50' }}</span>m / {{ $setting->late_tolerance ?? '15' }}m</div>
                        </div>
                    </div>

                    {{-- Simulasi jarak --}}
                    <div class="mt-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-3">Cek Posisi Karyawan (Simulasi)</p>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="field-label">Lat Karyawan</label>
                                <input type="text" id="sim-lat" class="field-input" placeholder="-6.238000">
                            </div>
                            <div class="flex-1">
                                <label class="field-label">Lng Karyawan</label>
                                <input type="text" id="sim-lng" class="field-input" placeholder="107.138000">
                            </div>
                            <button onclick="simulateDistance()" style="background:#2563eb;color:white;border:none;border-radius:10px;padding:10px 16px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                                Cek Jarak
                            </button>
                        </div>
                        <div id="sim-result" class="mt-3 hidden text-sm font-semibold p-3 rounded-lg"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var initialLat = {{ $setting->latitude ?? -6.2000 }};
        var initialLng = {{ $setting->longitude ?? 106.8166 }};
        var radiusCircle = null;

        var map = L.map('map').setView([initialLat, initialLng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

        function drawRadiusCircle(lat, lng, radius) {
            if (radiusCircle) map.removeLayer(radiusCircle);
            radiusCircle = L.circle([lat, lng], {
                radius: radius,
                color: '#2563eb',
                fillColor: '#2563eb',
                fillOpacity: 0.1,
                weight: 2,
                dashArray: '5,5'
            }).addTo(map);
        }

        drawRadiusCircle(initialLat, initialLng, {{ $setting->radius ?? 50 }});
        updatePreview();

        function updateDisplay(lat, lng) {
            document.getElementById('lat-disp').textContent = parseFloat(lat).toFixed(6);
            document.getElementById('lng-disp').textContent = parseFloat(lng).toFixed(6);
        }

        function syncMapToInput(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            updateDisplay(lat, lng);
            var r = parseFloat(document.getElementById('radius').value) || 50;
            drawRadiusCircle(lat, lng, r);
        }

        function syncInputToMap() {
            var lat = parseFloat(document.getElementById('latitude').value);
            var lng = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) {
                var newPos = new L.LatLng(lat, lng);
                marker.setLatLng(newPos);
                map.panTo(newPos);
                updateDisplay(lat, lng);
                var r = parseFloat(document.getElementById('radius').value) || 50;
                drawRadiusCircle(lat, lng, r);
            }
        }

        marker.on('dragend', function () {
            var p = marker.getLatLng();
            syncMapToInput(p.lat, p.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng([e.latlng.lat, e.latlng.lng]);
            syncMapToInput(e.latlng.lat, e.latlng.lng);
        });

        document.getElementById('latitude').addEventListener('input', function() { syncInputToMap(); updatePreview(); });
        document.getElementById('longitude').addEventListener('input', function() { syncInputToMap(); updatePreview(); });

        document.getElementById('radius').addEventListener('input', function () {
            var r = parseInt(this.value) || 0;
            document.getElementById('radius-disp').textContent = r;
            var lat = parseFloat(document.getElementById('latitude').value);
            var lng = parseFloat(document.getElementById('longitude').value);
            if (!isNaN(lat) && !isNaN(lng)) drawRadiusCircle(lat, lng, r);
            updatePreview();
        });

        // Input jam masuk / toleransi → update preview
        document.querySelector('[name="check_in_time"]').addEventListener('change', updatePreview);
        document.querySelector('[name="late_tolerance"]').addEventListener('input', updatePreview);

        function updatePreview() {
            var checkIn = document.querySelector('[name="check_in_time"]').value;
            var tolerance = parseInt(document.querySelector('[name="late_tolerance"]').value) || 0;
            var radius = parseInt(document.getElementById('radius').value) || 50;

            if (!checkIn) return;

            var parts = checkIn.split(':');
            var h = parseInt(parts[0]);
            var m = parseInt(parts[1]);
            var endM = m + tolerance;
            var endH = h + Math.floor(endM / 60);
            endM = endM % 60;

            var start = ('0'+h).slice(-2) + ':' + ('0'+m).slice(-2);
            var end   = ('0'+endH).slice(-2) + ':' + ('0'+endM).slice(-2);

            document.getElementById('preview-start').textContent = start;
            document.getElementById('preview-end').textContent   = end;
            document.getElementById('preview-radius').textContent = radius;
        }

        function getLocation() {
            if (!navigator.geolocation) return alert('Browser tidak mendukung GPS.');
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                map.setView([lat, lng], 17);
                marker.setLatLng([lat, lng]);
                syncMapToInput(lat, lng);
            });
        }

        // ── Simulasi jarak ────────────────────────────────────────────────
        function simulateDistance() {
            var simLat = parseFloat(document.getElementById('sim-lat').value);
            var simLng = parseFloat(document.getElementById('sim-lng').value);
            var offLat = parseFloat(document.getElementById('latitude').value);
            var offLng = parseFloat(document.getElementById('longitude').value);
            var radius = parseFloat(document.getElementById('radius').value) || 50;

            if (isNaN(simLat) || isNaN(simLng)) {
                alert('Masukkan koordinat karyawan yang valid.');
                return;
            }

            var dist = haversine(simLat, simLng, offLat, offLng);
            var result = document.getElementById('sim-result');
            result.classList.remove('hidden');

            if (dist <= radius) {
                result.style.background = '#dcfce7';
                result.style.color      = '#15803d';
                result.style.border     = '1px solid #bbf7d0';
                result.innerHTML = '✓ <strong>Dalam Radius</strong> — Jarak ' + Math.round(dist) + ' m (batas ' + radius + ' m). Absensi akan <strong>disetujui otomatis</strong>.';
                // Tambah marker simulasi
                if (window._simMarker) map.removeLayer(window._simMarker);
                window._simMarker = L.marker([simLat, simLng], {
                    icon: L.divIcon({ html: '<div style="background:#15803d;color:white;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;box-shadow:0 2px 8px rgba(0,0,0,0.3);">K</div>', className: '' })
                }).addTo(map).bindPopup('Posisi Karyawan — ' + Math.round(dist) + ' m dari kantor').openPopup();
            } else {
                result.style.background = '#fef3c7';
                result.style.color      = '#92400e';
                result.style.border     = '1px solid #fde68a';
                result.innerHTML = '⚠ <strong>Di Luar Radius</strong> — Jarak ' + Math.round(dist) + ' m (batas ' + radius + ' m). Absensi akan masuk ke <strong>antrian Approval (WFH)</strong>.';
                if (window._simMarker) map.removeLayer(window._simMarker);
                window._simMarker = L.marker([simLat, simLng], {
                    icon: L.divIcon({ html: '<div style="background:#d97706;color:white;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;box-shadow:0 2px 8px rgba(0,0,0,0.3);">K</div>', className: '' })
                }).addTo(map).bindPopup('Posisi Karyawan — ' + Math.round(dist) + ' m dari kantor').openPopup();
            }
        }

        function haversine(lat1, lon1, lat2, lon2) {
            var R = 6371000;
            var dLat = (lat2 - lat1) * Math.PI / 180;
            var dLon = (lon2 - lon1) * Math.PI / 180;
            var a = Math.sin(dLat/2) * Math.sin(dLat/2)
                  + Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180)
                  * Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }
    </script>
</x-app-layout>