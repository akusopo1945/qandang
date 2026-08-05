<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Side: Scanner -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm">
            <h2 class="text-sm font-extrabold text-[#4a6741] uppercase tracking-wider mb-4 flex items-center gap-2">
                📹 Kamera Scanner
            </h2>

            <!-- HTML5 QR Scanner Container -->
            <div class="relative overflow-hidden bg-slate-50 dark:bg-zinc-950 rounded-2xl border border-gray-100 dark:border-zinc-800 flex flex-col items-center justify-center p-4">
                <!-- Scanner viewport -->
                <div id="qr-reader" class="w-full max-w-md mx-auto" style="border: none !important;"></div>
                
                <!-- Loading indicator or fallback info -->
                <div id="scanner-placeholder" class="text-center py-10">
                    <svg class="w-16 h-16 text-[#4a6741]/25 mx-auto animate-pulse mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 20h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-gray-500 font-bold">Menginisialisasi Kamera...</p>
                </div>
            </div>

            <!-- Controls -->
            <div class="mt-4 flex flex-wrap gap-3 items-center justify-between">
                <div>
                    <label for="camera-select" class="block text-[10px] uppercase tracking-wider font-extrabold text-gray-400 mb-1">Pilih Kamera</label>
                    <select id="camera-select" class="bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl px-3 py-2 text-xs focus:outline-none focus:border-[#4a6741] min-w-[200px]">
                        <option value="">Memuat kamera...</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button onclick="startScanning()" class="px-4 py-2 bg-[#4a6741] hover:bg-[#3a5233] text-white rounded-xl text-xs font-bold transition-all shadow-md">
                        Mulai Scan
                    </button>
                    <button onclick="stopScanning()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                        Hentikan
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Side: Scanned Result -->
        <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col">
            <h2 class="text-sm font-extrabold text-[#4a6741] uppercase tracking-wider mb-4 flex items-center gap-2">
                📋 Hasil Scan & Data Kambing
            </h2>

            <!-- Default info when empty -->
            <div id="result-placeholder" class="flex-1 flex flex-col items-center justify-center text-center py-10">
                <svg class="w-16 h-16 text-gray-200 dark:text-zinc-800 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-xs text-gray-400 font-bold">Belum ada QR Code dipindai.</p>
                <p class="text-[10px] text-gray-400 mt-1 max-w-[200px]">Posisikan kode QR di depan kamera untuk mencari data ternak.</p>
            </div>

            <!-- Loading Spinner during AJAX fetch -->
            <div id="result-loader" class="hidden flex-1 flex flex-col items-center justify-center py-10">
                <div class="w-8 h-8 border-4 border-[#4a6741] border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs text-gray-500 mt-2 font-bold">Mencari data kambing...</p>
            </div>

            <!-- Goat Details Card (hidden initially) -->
            <div id="goat-details-card" class="hidden flex-col gap-4 flex-1">
                <!-- Image Header -->
                <div class="relative w-full h-36 bg-slate-100 dark:bg-zinc-950 rounded-2xl overflow-hidden border border-gray-100 dark:border-zinc-800">
                    <img id="goat-image" src="" alt="Foto Kambing" class="w-full h-full object-cover hidden">
                    <div id="goat-image-placeholder" class="w-full h-full flex items-center justify-center text-[#4a6741]/20">
                        <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <!-- QR Badge -->
                    <span id="goat-qr-badge" class="absolute top-3 left-3 bg-[#4a6741] text-white px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider"></span>
                </div>

                <!-- Info Grid -->
                <div class="space-y-3 text-xs text-gray-800 dark:text-gray-200">
                    <div class="flex justify-between border-b border-gray-50 dark:border-zinc-800 pb-2">
                        <span class="text-gray-400 font-medium">Nama Ternak</span>
                        <span id="goat-name" class="font-bold">-</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-zinc-800 pb-2">
                        <span class="text-gray-400 font-medium">Jenis Ras (Breed)</span>
                        <span id="goat-breed" class="font-bold">-</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-zinc-800 pb-2">
                        <span class="text-gray-400 font-medium">Jenis Kelamin</span>
                        <span id="goat-gender" class="font-bold">-</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-zinc-800 pb-2">
                        <span class="text-gray-400 font-medium">Berat Saat Ini</span>
                        <span id="goat-weight" class="font-bold">-</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-50 dark:border-zinc-800 pb-2">
                        <span class="text-gray-400 font-medium">Tujuan Ternak</span>
                        <span id="goat-purpose" class="font-bold">-</span>
                    </div>
                </div>

                <!-- Quick Action Redirect Link -->
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800 flex gap-2">
                    <a id="goat-edit-link" href="#" class="flex-1 text-center py-2.5 bg-[#4a6741] text-white font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-[#3a5233] transition-all shadow-md">
                        ✏️ Edit Data
                    </a>
                    <!-- PDF Certificate Link -->
                    <a id="goat-pdf-link" href="#" target="_blank" class="px-4 py-2.5 bg-rose-600 text-white font-bold text-xs uppercase tracking-wider rounded-xl hover:bg-rose-700 transition-all shadow-md flex items-center justify-center gap-1.5">
                        📄 PDF
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Include html5-qrcode library from CDN -->
    <script src="https://unpkg.com/html5-qrcode" defer></script>

    <script>
        let html5QrScanner = null;

        document.addEventListener('DOMContentLoaded', () => {
            // Wait for library load
            setTimeout(initializeCameraList, 1000);
        });

        function initializeCameraList() {
            if (typeof Html5Qrcode === 'undefined') {
                setTimeout(initializeCameraList, 300);
                return;
            }

            const cameraSelect = document.getElementById('camera-select');
            const placeholder = document.getElementById('scanner-placeholder');

            // Request permission explicitly first if needed to get labels & device list
            navigator.mediaDevices?.getUserMedia({ video: true })
                .then(stream => {
                    // Stop initial test stream right away
                    stream.getTracks().forEach(track => track.stop());
                    return Html5Qrcode.getCameras();
                })
                .catch(err => {
                    // Fallback to directly getCameras if getUserMedia error/rejected
                    return Html5Qrcode.getCameras();
                })
                .then(devices => {
                    if (devices && devices.length) {
                        cameraSelect.innerHTML = '';
                        devices.forEach(device => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Kamera ${cameraSelect.options.length + 1}`;
                            cameraSelect.appendChild(option);
                        });

                        if (placeholder) {
                            placeholder.innerHTML = `
                                <svg class="w-16 h-16 text-[#4a6741]/40 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0120 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <p class="text-xs text-[#4a6741] font-bold">Kamera Terdeteksi & Siap</p>
                                <p class="text-[10px] text-gray-400 mt-1">Pilih kamera lalu klik Mulai Scan untuk memindai QR Code.</p>
                            `;
                        }
                    } else {
                        cameraSelect.innerHTML = '<option value="">Kamera tidak ditemukan</option>';
                        if (placeholder) {
                            placeholder.innerHTML = `
                                <svg class="w-16 h-16 text-rose-500/30 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-xs text-rose-500 font-bold">Kamera Tidak Ditemukan</p>
                                <p class="text-[10px] text-gray-400 mt-1">Pastikan izin kamera browser diaktifkan atau periksa koneksi kamera.</p>
                            `;
                        }
                    }
                })
                .catch(err => {
                    console.error("Camera enumeration error:", err);
                    cameraSelect.innerHTML = '<option value="">Gagal mendeteksi kamera</option>';
                });
        }

        function startScanning() {
            const cameraId = document.getElementById('camera-select').value;
            if (!cameraId) {
                alert('Silakan pilih kamera terlebih dahulu.');
                return;
            }

            const placeholder = document.getElementById('scanner-placeholder');
            if (placeholder) placeholder.classList.add('hidden');

            if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    launchScanner(cameraId);
                });
            } else {
                launchScanner(cameraId);
            }
        }

        function launchScanner(cameraId) {
            html5QrScanner = new Html5Qrcode("qr-reader");
            
            html5QrScanner.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // QR Code successfully scanned!
                    handleQrDecoded(decodedText);
                    // Stop scanning after a successful scan to prevent multiple alerts
                    stopScanning();
                },
                (errorMessage) => {
                    // parse error (standard, silent)
                }
            ).catch(err => {
                console.error("Gagal memulai scanner: ", err);
            });
        }

        function stopScanning() {
            if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    html5QrScanner = null;
                    const placeholder = document.getElementById('scanner-placeholder');
                    if (placeholder) placeholder.classList.remove('hidden');
                }).catch(err => {
                    console.error("Gagal menghentikan scanner: ", err);
                });
            }
        }

        function handleQrDecoded(code) {
            // Set up views
            const placeholder = document.getElementById('result-placeholder');
            const loader = document.getElementById('result-loader');
            const details = document.getElementById('goat-details-card');

            placeholder.classList.add('hidden');
            details.classList.add('hidden');
            loader.classList.remove('hidden');

            // We perform fetch request to the public API endpoint
            fetch(`/api/goats/${encodeURIComponent(code)}`)
                .then(res => {
                    if (!res.ok) throw new Error('Kambing tidak ditemukan');
                    return res.json();
                })
                .then(data => {
                    loader.classList.add('hidden');
                    details.classList.remove('hidden');

                    // Populate data
                    document.getElementById('goat-qr-badge').innerText = data.qr_code || 'N/A';
                    document.getElementById('goat-name').innerText = data.name || '-';
                    document.getElementById('goat-breed').innerText = data.breed || '-';
                    document.getElementById('goat-gender').innerText = data.gender === 'male' ? 'Pejantan ♂' : 'Betina ♀';
                    document.getElementById('goat-weight').innerText = data.current_weight ? `${data.current_weight} kg` : '-';
                    document.getElementById('goat-purpose').innerText = data.purpose === 'milk' ? 'Pemerahan Susu' : (data.purpose === 'meat' ? 'Pedaging / Potong' : 'Breeding (Indukan)');

                    // Image handling
                    const imgEl = document.getElementById('goat-image');
                    const imgPlaceholder = document.getElementById('goat-image-placeholder');
                    if (data.image_url) {
                        imgEl.src = data.image_url;
                        imgEl.classList.remove('hidden');
                        imgPlaceholder.classList.add('hidden');
                    } else {
                        imgEl.classList.add('hidden');
                        imgPlaceholder.classList.remove('hidden');
                    }

                    // Links
                    document.getElementById('goat-edit-link').href = `/admin/goats/${data.id}/edit`;
                    document.getElementById('goat-pdf-link').href = `/admin/goats/${data.id}/pdf`;
                })
                .catch(err => {
                    loader.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    alert(`Gagal: QR Code "${code}" tidak terdaftar di database Qandang.`);
                });
        }
    </script>
</x-filament-panels::page>
