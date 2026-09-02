@extends('guest.template')

@section('content')
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Foto Kunjungan</h1>
                <p class="text-gray-600">Silakan ambil foto kunjungan</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">

                {{-- INFO --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-600">Nama Pengunjung</p>
                    <p class="font-semibold text-lg">{{ $guest->name }}</p>
                </div>

                {{-- KAMERA --}}
                <div class="relative mb-4">

                    <video id="video" class="w-full rounded-xl border" autoplay playsinline></video>

                    <button onclick="takePhoto()"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2
bg-blue-600 text-white px-6 py-3 rounded-full shadow-lg">
                        Ambil Foto
                    </button>

                </div>

                <canvas id="canvas" class="hidden"></canvas>

                {{-- PREVIEW --}}
                <div id="previewContainer" class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6"></div>

                {{-- ACTION --}}
                <div class="flex gap-3">
                    <button onclick="resetPhotos()" class="flex-1 bg-gray-200 py-3 rounded-lg font-semibold">
                        Hapus Semua
                    </button>

                    <button onclick="uploadPhotos()" id="uploadBtn"
                        class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold">
                        Simpan Foto
                    </button>
                </div>

            </div>

            {{-- SUCCESS --}}
            <div id="successMessage" class="hidden mt-6 bg-green-50 border border-green-200 rounded-xl p-6 text-center">
                <h2 class="text-xl font-bold mb-2">Foto berhasil disimpan 🎉</h2>

                <a href="{{ route('daftar.buku.tamu') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg">
                    Kembali ke Beranda
                </a>

            </div>

        </div>
    </div>

    <script>
        let video = document.getElementById('video')
        let canvas = document.getElementById('canvas')
        let previewContainer = document.getElementById('previewContainer')

        let photos = []

        async function startCamera() {

            try {

                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "environment"
                    }
                })

                video.srcObject = stream

            } catch (err) {

                alert("Kamera tidak bisa diakses")

            }

        }

        startCamera()

        function takePhoto() {

            const context = canvas.getContext('2d')

            canvas.width = video.videoWidth
            canvas.height = video.videoHeight

            context.drawImage(video, 0, 0)

            canvas.toBlob(function(blob) {

                photos.push(blob)

                const img = document.createElement("img")
                img.src = URL.createObjectURL(blob)
                img.className = "rounded-lg shadow"

                previewContainer.appendChild(img)

            }, 'image/jpeg', 0.9)

        }

        function resetPhotos() {

            photos = []
            previewContainer.innerHTML = ""

        }

        async function uploadPhotos() {

            if (photos.length === 0) {
                alert("Silakan ambil foto terlebih dahulu")
                return
            }

            const formData = new FormData()

            photos.forEach((photo, index) => {
                formData.append("photos[]", photo, "photo_" + index + ".jpg")
            })

            const response = await fetch("{{ route('buku.tamu.lampiran.store', $guest->id) }}", {
                method: "POST",
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })

            if (!response.ok) {

                const text = await response.text()
                console.log(text)
                alert("Server error")
                return

            }

            const data = await response.json()

            if (data.success) {

                window.location.href = data.redirect + "?success=1"

            } else {

                alert("Upload gagal")

            }
        }
    </script>
@endsection
