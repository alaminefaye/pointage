@extends('layouts.employee')

@section('title', 'Scanner QR Code')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Scanner QR Code pour Pointage</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <strong><i class="bx bx-info-circle"></i> Instructions :</strong>
            <ul class="mb-0 mt-2">
                <li>Cliquez sur "Démarrer le scanner" pour activer votre caméra</li>
                <li>Autorisez l'accès à la caméra lorsque votre navigateur le demande</li>
                <li>Pointez votre caméra vers le QR code affiché sur l'écran de l'admin</li>
                <li>Le pointage se fera automatiquement une fois le QR code détecté</li>
            </ul>
        </div>
        
        <div id="scanner-container" class="text-center mb-4">
            <div id="video-placeholder" class="border rounded p-5 bg-light" style="min-height: 400px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                <i class="bx bx-camera bx-lg text-muted mb-3" style="font-size: 64px;"></i>
                <p class="text-muted mb-0">La caméra apparaîtra ici après avoir cliqué sur "Démarrer le scanner"</p>
            </div>
            <video id="video" width="100%" style="max-width: 500px; border: 2px solid #ddd; border-radius: 8px; display: none;" autoplay playsinline></video>
            <canvas id="canvas" style="display: none;"></canvas>
        </div>
        
        <div class="text-center mb-3">
            <button id="start-scanner" class="btn btn-primary btn-lg">
                <i class="bx bx-camera"></i> Démarrer le scanner
            </button>
            <button id="stop-scanner" class="btn btn-secondary btn-lg" style="display: none;">
                <i class="bx bx-stop"></i> Arrêter le scanner
            </button>
        </div>
        
        <div id="result" class="mt-3"></div>
    </div>
</div>

@push('page-js')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
let video, canvas, context;
let scanning = false;

document.getElementById('start-scanner').addEventListener('click', startScanner);
document.getElementById('stop-scanner').addEventListener('click', stopScanner);

async function startScanner() {
    try {
        // Vérifier si on est en HTTPS ou localhost
        const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        
        if (!isSecureContext) {
            throw new Error('SECURITY_ERROR');
        }
        
        // Vérifier si getUserMedia est disponible
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            // Fallback pour les navigateurs plus anciens
            navigator.mediaDevices = navigator.mediaDevices || {};
            navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia || 
                navigator.webkitGetUserMedia || 
                navigator.mozGetUserMedia || 
                navigator.msGetUserMedia;
            
            if (!navigator.mediaDevices.getUserMedia) {
                throw new Error('BROWSER_NOT_SUPPORTED');
            }
        }
        
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment' 
            } 
        });
        
        video = document.getElementById('video');
        canvas = document.getElementById('canvas');
        context = canvas.getContext('2d');
        
        video.srcObject = stream;
        video.setAttribute('playsinline', true);
        video.play();
        
        // Masquer le placeholder et afficher la vidéo
        document.getElementById('video-placeholder').style.display = 'none';
        video.style.display = 'block';
        
        scanning = true;
        document.getElementById('start-scanner').style.display = 'none';
        document.getElementById('stop-scanner').style.display = 'inline-block';
        
        scanQR();
    } catch (err) {
        let errorMessage = 'Erreur d\'accès à la caméra: ';
        
        if (err.message === 'SECURITY_ERROR') {
            errorMessage = '⚠️ Accès à la caméra bloqué pour des raisons de sécurité.\n\n' +
                          'Les navigateurs modernes (Chrome, Firefox, etc.) exigent HTTPS pour accéder à la caméra.\n\n' +
                          'Solutions:\n' +
                          '1. Utilisez HTTPS (recommandé pour la production)\n' +
                          '2. Ou utilisez localhost au lieu de votre domaine .test\n' +
                          '3. Ou configurez Chrome avec le flag: chrome://flags/#unsafely-treat-insecure-origin-as-secure';
        } else if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            errorMessage += 'Permission refusée. Veuillez:\n' +
                          '1. Cliquer sur l\'icône de caméra dans la barre d\'adresse\n' +
                          '2. Autoriser l\'accès à la caméra\n' +
                          '3. Recharger la page';
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            errorMessage += 'Aucune caméra trouvée sur votre appareil.';
        } else if (err.name === 'NotSupportedError' || err.name === 'ConstraintNotSatisfiedError') {
            errorMessage += 'Votre navigateur ne supporte pas cette fonctionnalité.';
        } else if (err.message === 'BROWSER_NOT_SUPPORTED') {
            errorMessage += 'Votre navigateur ne supporte pas l\'accès à la caméra. Veuillez utiliser un navigateur moderne (Chrome, Firefox, Safari, Edge).';
        } else {
            errorMessage += err.message || 'Erreur inconnue';
        }
        
        alert(errorMessage);
        console.error('Erreur caméra:', err);
    }
}

function stopScanner() {
    scanning = false;
    if (video && video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
    }
    
    // Réafficher le placeholder et masquer la vidéo
    if (video) {
        video.style.display = 'none';
    }
    document.getElementById('video-placeholder').style.display = 'flex';
    
    document.getElementById('start-scanner').style.display = 'inline-block';
    document.getElementById('stop-scanner').style.display = 'none';
    document.getElementById('result').innerHTML = '';
}

function scanQR() {
    if (!scanning) return;
    
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        
        if (code) {
            handleQRCode(code.data);
            stopScanner();
        }
    }
    
    requestAnimationFrame(scanQR);
}

async function handleQRCode(qrData) {
    const employeeId = {{ session('employee_id') }};
    
    // Get current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(async (position) => {
            const data = {
                employee_id: employeeId,
                qr_code: qrData,
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };
            
            // Determine if check-in or check-out
            const response = await fetch('{{ route("attendance.today-status") }}?employee_id=' + employeeId);
            const status = await response.json();
            
            const endpoint = status.has_checked_in ? '{{ route("attendance.check-out") }}' : '{{ route("attendance.check-in") }}';
            
            try {
                const result = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });
                
                const resultData = await result.json();
                
                if (resultData.success) {
                    document.getElementById('result').innerHTML = 
                        '<div class="alert alert-success">' + resultData.message + '</div>';
                } else {
                    document.getElementById('result').innerHTML = 
                        '<div class="alert alert-danger">' + resultData.message + '</div>';
                }
            } catch (error) {
                document.getElementById('result').innerHTML = 
                    '<div class="alert alert-danger">Erreur: ' + error.message + '</div>';
            }
        }, (error) => {
            document.getElementById('result').innerHTML = 
                '<div class="alert alert-danger">Erreur de géolocalisation: ' + error.message + '</div>';
        });
    } else {
        document.getElementById('result').innerHTML = 
            '<div class="alert alert-danger">La géolocalisation n\'est pas supportée par votre navigateur.</div>';
    }
}
</script>
@endpush
@endsection

