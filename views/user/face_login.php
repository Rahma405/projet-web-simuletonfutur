<?php
session_start();
require_once __DIR__ . '/../../controllers/UtilisateurC.php';
require_once __DIR__ . '/../../controllers/ProfilC.php';

$ctrl = new UtilisateurC();
$profilCtrl = new ProfilC();
$pageTitle = stf_t('face_login');
$error = null;

if (isset($_SESSION['user'])) {
    header('Location: ' . $baseUrl . '/public/index.php?route=/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descriptorJson = $_POST['descriptor_json'] ?? '';
    $descriptor = json_decode($descriptorJson, true);

    if (!is_array($descriptor)) {
        $error = 'Aucune empreinte faciale valide n a ete recue.';
    } else {
        $utilisateur = $ctrl->loginWithFaceDescriptor($descriptor);

        if ($utilisateur) {
            $profilLangue = $profilCtrl->getByIdUtilisateur((int) $utilisateur->getIdUtilisateur());
            $_SESSION['user'] = [
                'id' => $utilisateur->getIdUtilisateur(),
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'role' => $utilisateur->getRole(),
                'statut' => $utilisateur->getStatut(),
            ];
            $_SESSION['site_lang'] = stf_language_code_from_value($profilLangue?->getLangue());
            $_SESSION['message'] = [
                'type' => 'success',
                'texte' => 'Connexion Face ID reussie. Bienvenue ' . $utilisateur->getPrenom() . ' !',
            ];
            header('Location: ' . ($utilisateur->getRole() === 'admin' ? '../backoffice/list_utilisateurs.php' : '../../index.php'));
            exit;
        }

        $error = 'Aucun visage correspondant n a ete trouve.';
    }
}


?>
<section class="login-page">
  <div class="login-heading">
    <h1><?= htmlspecialchars(stf_t('face_login')) ?></h1>
    <p>Ouvrez la webcam puis laissez le systeme reconnaitre votre visage.</p>
  </div>

  <div class="login-card">
    <h2>Face ID</h2>
    <p class="login-subtitle">Connexion biométrique par reconnaissance faciale</p>

    <?php if ($error): ?>
      <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="face-id-box mb-4">
      <video id="faceLoginVideo" autoplay muted playsinline class="face-id-video"></video>
      <canvas id="faceLoginCanvas" class="d-none"></canvas>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
      <button type="button" id="startFaceLogin" class="btn-login"><?= htmlspecialchars(stf_t('webcam_on')) ?></button>
      <button type="button" id="captureFaceLogin" class="btn btn-outline-primary" style="border-radius:12px;padding:14px 18px;"><?= htmlspecialchars(stf_t('scan_face')) ?></button>
    </div>

    <div id="faceLoginStatus" class="text-muted small mb-3">
      Activez la webcam pour commencer la verification Face ID.
    </div>

    <form method="POST" id="faceLoginForm">
      <input type="hidden" name="descriptor_json" id="faceLoginDescriptor">
      <button type="submit" id="submitFaceLogin" class="btn-login" disabled><?= htmlspecialchars(stf_t('face_login')) ?></button>
    </form>

    <div class="login-register">
      <a href="<?= $baseUrl ?>/public/index.php?route=/login"><?= htmlspecialchars(stf_t('classic_login')) ?></a>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
(() => {
  const modelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';
  const video = document.getElementById('faceLoginVideo');
  const startBtn = document.getElementById('startFaceLogin');
  const captureBtn = document.getElementById('captureFaceLogin');
  const submitBtn = document.getElementById('submitFaceLogin');
  const status = document.getElementById('faceLoginStatus');
  const hiddenInput = document.getElementById('faceLoginDescriptor');
  let modelsLoaded = false;

  async function loadModels() {
    if (modelsLoaded) return;
    status.textContent = 'Chargement du moteur Face ID...';
    await Promise.all([
      faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
      faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
      faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
    ]);
    modelsLoaded = true;
    status.textContent = 'Mode Face ID pret.';
  }

  async function detectFace() {
    return faceapi
      .detectSingleFace(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.2 }))
      .withFaceLandmarks()
      .withFaceDescriptor();
  }

  startBtn?.addEventListener('click', async () => {
    try {
      await loadModels();
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
      await new Promise((resolve) => {
        if (video.readyState >= 2) {
          resolve();
          return;
        }
        video.onloadedmetadata = () => resolve();
      });
      status.textContent = 'Webcam activee. Regardez la camera puis lancez le scan.';
    } catch (error) {
      status.textContent = 'Impossible d ouvrir la webcam ou de charger Face ID.';
    }
  });

  captureBtn?.addEventListener('click', async () => {
    if (!video.srcObject) {
      status.textContent = 'Activez d abord la webcam.';
      return;
    }

    try {
      status.textContent = 'Analyse du visage en cours...';
      const detection = await detectFace();

      if (!detection) {
        status.textContent = 'Aucun visage detecte. Regardez bien la camera, reculez un peu et reessayez.';
        return;
      }

      hiddenInput.value = JSON.stringify(Array.from(detection.descriptor));
      submitBtn.disabled = false;
      status.textContent = 'Visage detecte. Vous pouvez maintenant vous connecter.';
    } catch (error) {
      status.textContent = 'Erreur pendant le scan du visage.';
    }
  });
})();
</script>

<?php  ?>
