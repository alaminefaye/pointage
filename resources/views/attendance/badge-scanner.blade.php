@extends('layouts.app')

@section('title', 'Scanner de Badges')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Scanner de Badges pour Pointage</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-4">
            <strong><i class="bx bx-info-circle"></i> Instructions :</strong>
            <ul class="mb-0 mt-2">
                <li>Sélectionnez le site où le pointage doit être enregistré</li>
                <li>Cliquez sur "Démarrer le scanner" pour activer votre caméra</li>
                <li>Scannez le badge QR code de l'employé</li>
                <li>Le système détectera automatiquement si c'est une entrée ou une sortie</li>
            </ul>
        </div>

        <!-- Site Selection -->
        <div class="mb-4">
            <label for="site_id" class="form-label"><strong>Site *</strong></label>
            <select id="site_id" class="form-select" required>
                <option value="">Sélectionnez un site</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                @endforeach
            </select>
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
            <button id="start-scanner" class="btn btn-primary btn-lg" disabled>
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
let scannedBadgeCode = null;

document.getElementById('site_id').addEventListener('change', function() {
    const startBtn = document.getElementById('start-scanner');
    if (this.value) {
        startBtn.disabled = false;
    } else {
        startBtn.disabled = true;
    }
});

document.getElementById('start-scanner').addEventListener('click', startScanner);
document.getElementById('stop-scanner').addEventListener('click', stopScanner);

async function startScanner() {
    const siteId = document.getElementById('site_id').value;
    if (!siteId) {
        alert('Veuillez sélectionner un site d\'abord.');
        return;
    }

    try {
        const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        
        if (!isSecureContext) {
            throw new Error('SECURITY_ERROR');
        }
        
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices = navigator.mediaDevices || {};
            navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia || 
                navigator.webkitGetUserMedia || 
                navigator.mozGetUserMedia || 
                navigator.msGetUserMedia;
            
            if (!navigator.mediaDevices.getUserMedia) {
                throw new Error('BROWSER_NOT_SUPPORTED');
            }
        }
        
        video = document.getElementById('video');
        canvas = document.getElementById('canvas');
        context = canvas.getContext('2d');
        
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } 
        });
        
        video.srcObject = stream;
        video.setAttribute('playsinline', true);
        video.play();
        
        document.getElementById('video-placeholder').style.display = 'none';
        video.style.display = 'block';
        document.getElementById('start-scanner').style.display = 'none';
        document.getElementById('stop-scanner').style.display = 'inline-block';
        
        scanning = true;
        scanQR();
        
    } catch (err) {
        let errorMessage = 'Erreur lors de l\'accès à la caméra: ';
        
        if (err.message === 'SECURITY_ERROR') {
            errorMessage = '⚠️ Accès à la caméra bloqué pour des raisons de sécurité.\n\nLes navigateurs modernes exigent HTTPS pour accéder à la caméra.';
        } else if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
            errorMessage += 'Permission refusée. Veuillez autoriser l\'accès à la caméra.';
        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
            errorMessage += 'Aucune caméra trouvée sur votre appareil.';
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
    
    if (video) {
        video.style.display = 'none';
    }
    document.getElementById('video-placeholder').style.display = 'flex';
    document.getElementById('start-scanner').style.display = 'inline-block';
    document.getElementById('stop-scanner').style.display = 'none';
    document.getElementById('result').innerHTML = '';
    scannedBadgeCode = null;
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
            scannedBadgeCode = code.data;
            handleBadgeScanned(code.data);
            scanning = false;
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
            if (video) {
                video.style.display = 'none';
            }
            document.getElementById('video-placeholder').style.display = 'flex';
            document.getElementById('start-scanner').style.display = 'inline-block';
            document.getElementById('stop-scanner').style.display = 'none';
        }
    }
    
    requestAnimationFrame(scanQR);
}

function handleBadgeScanned(badgeCode) {
    const siteId = document.getElementById('site_id').value;
    const resultDiv = document.getElementById('result');
    
    resultDiv.innerHTML = '<div class="alert alert-info"><i class="bx bx-loader bx-spin"></i> Traitement du badge...</div>';
    
    fetch('{{ route("attendance.scan-badge-admin") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            badge_qr_code: badgeCode,
            site_id: siteId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const alertClass = data.type === 'check_in' ? 'alert-success' : 'alert-warning';
            const icon = data.type === 'check_in' ? 'bx-log-in' : 'bx-log-out';
            const typeText = data.type === 'check_in' ? 'Entrée' : 'Sortie';
            
            resultDiv.innerHTML = `
                <div class="alert ${alertClass}">
                    <h5><i class="bx ${icon}"></i> Pointage de ${typeText} enregistré !</h5>
                    <p class="mb-1"><strong>Employé:</strong> ${data.employee.full_name}</p>
                    <p class="mb-1"><strong>Code:</strong> ${data.employee.employee_code}</p>
                    <p class="mb-0"><strong>Heure:</strong> ${data.attendance.check_in_time || data.attendance.check_out_time}</p>
                </div>
                <button class="btn btn-primary mt-2" onclick="location.reload()">
                    <i class="bx bx-refresh"></i> Scanner un autre badge
                </button>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bx bx-error"></i> Erreur</h5>
                    <p class="mb-0">${data.message}</p>
                </div>
                <button class="btn btn-secondary mt-2" onclick="startScanner()">
                    <i class="bx bx-refresh"></i> Réessayer
                </button>
            `;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        resultDiv.innerHTML = `
            <div class="alert alert-danger">
                <h5><i class="bx bx-error"></i> Erreur</h5>
                <p class="mb-0">Une erreur s'est produite lors du traitement du badge.</p>
            </div>
            <button class="btn btn-secondary mt-2" onclick="startScanner()">
                <i class="bx bx-refresh"></i> Réessayer
            </button>
        `;
    });
}
</script>
@endpush
@endsection

