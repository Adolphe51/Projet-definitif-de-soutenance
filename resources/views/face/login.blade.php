<?php set_time_limit(300); ?>
@extends('layouts.app')

@section('content')
<div class="container mx-auto mt-10 text-center">
    <h2 class="text-2xl font-bold mb-6">Connexion par Reconnaissance Faciale</h2>

    <!-- Vidéo webcam -->
    <video id="video" width="320" height="240" autoplay muted class="mx-auto rounded shadow-lg"></video>

    <p class="mt-4 text-gray-600">Positionnez votre visage devant la caméra.</p>

    <!-- Bouton pour capturer le visage -->
    <button id="capture-btn" class="mt-4 bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
        Capturer et se connecter
    </button>

    <p id="status" class="mt-4 text-red-500"></p>
</div>

<!-- face-api.js depuis CDN -->
<script defer src="https://cdn.jsdelivr.net/npm/face-api.js"></script>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const video = document.getElementById('video');
    const status = document.getElementById('status');

    // Charger les modèles face-api.js
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
        faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/models')
    ]);

    // Accès webcam
    navigator.mediaDevices.getUserMedia({ video: {} })
        .then(stream => video.srcObject = stream)
        .catch(err => status.textContent = 'Impossible d’accéder à la webcam.');

    document.getElementById('capture-btn').addEventListener('click', async () => {
        const detections = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detections) {
            status.textContent = 'Visage non détecté. Réessayez.';
            return;
        }

        const descriptor = Array.from(detections.descriptor);

        // Envoyer au backend
        fetch('{{ route('face.login.submit') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ descriptor: JSON.stringify(descriptor) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                status.textContent = data.message;
            }
        })
        .catch(err => status.textContent = 'Erreur serveur.');
    });
});
</script>
@endsection