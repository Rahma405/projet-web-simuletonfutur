<?php
session_start();
require_once __DIR__ . '/../../controller/UtilisateurC.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['message'] = ['type' => 'danger', 'texte' => 'Connectez-vous pour activer Face ID.'];
    header('Location: login.php');
    exit;
}

$ctrl = new UtilisateurC();
$pageTitle = stf_t('face_register');
$message = null;
$error = null;
$userId = (int) ($_SESSION['user']['id'] ?? 0);
$alreadyEnabled = $ctrl->hasFaceDescriptor($userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['disable_face_id'])) {
        if ($ctrl->deleteFaceDescriptor($userId)) {
            $alreadyEnabled = false;
            $message = 'Face ID a ete desactive pour votre compte.';
        } else {
            $error = 'Impossible de desactiver Face ID pour le moment.';
        }
    } else {
        $descriptorJson = $_POST['descriptor_json'] ?? '';
        $descriptor = json_decode($descriptorJson, true);

        if (!is_array($descriptor)) {
            $error = 'Aucune donnee faciale valide n a ete recue.';
        } elseif ($ctrl->saveFaceDescriptor($userId, $descriptor)) {
            $alreadyEnabled = true;
            $message = 'Face ID active avec succes pour votre compte.';
        } else {
            $error = 'Impossible d enregistrer votre visage pour le moment.';
        }
    }
}

require_once __DIR__ . '/layouts/header.php';
?>
<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div style="background:linear-gradient(135deg,#1d2b4f,#2a3f6f);color:#fff;padding:16px 24px;border-radius:16px 16px 0 0;">
          <h5 class="mb-0 fw-bold"><i class="fas fa-camera me-2" style="color:#e63946"></i><?= htmlspecialchars(stf_t('face_register')) ?></h5>
        </div>
        <div class="card-body p-4">
          <?php if ($message): ?>
            <div class="alert alert-success mb-3"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>
          <?php if ($error): ?>
            <div class="alert alert-danger mb-3"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <p class="text-muted mb-4">
            Ouvrez la webcam, placez votre visage au centre, puis enregistrez votre empreinte faciale pour la connexion Face ID.
          </p>

          <div class="face-id-box mb-4">
            <video id="faceRegisterVideo" autoplay muted playsinline class="face-id-video"></video>
            <canvas id="faceRegisterCanvas" class="d-none"></canvas>
          </div>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" id="startFaceRegister" class="btn-hero" style="border-radius:10px;padding:10px 22px;"><?= htmlspecialchars(stf_t('webcam_on')) ?></button>
            <button type="button" id="captureFaceRegister" class="btn btn-outline-primary" style="border-radius:10px;padding:10px 22px;"><?= htmlspecialchars(stf_t('scan_face')) ?></button>
          </div>

          <div id="faceRegisterStatus" class="text-muted small mb-3">
            <?= $alreadyEnabled ? 'Un visage est deja enregistre pour ce compte.' : 'Aucune empreinte faciale enregistree pour ce compte.' ?>
          </div>

          <form method="POST" id="faceRegisterForm">
            <input type="hidden" name="descriptor_json" id="faceRegisterDescriptor">
            <button type="submit" id="saveFaceRegister" class="btn-hero" style="border-radius:10px;padding:10px 22px;" disabled>
              <?= htmlspecialchars(stf_t('save_face')) ?>
            </button>
            <?php if ($alreadyEnabled): ?>
              <button type="submit" name="disable_face_id" value="1" class="btn btn-outline-danger ms-2" style="border-radius:10px;padding:10px 22px;">
                <?= htmlspecialchars(stf_t('disable_face')) ?>
              </button>
            <?php endif; ?>
            <a href="edit_utilisateur.php?id=<?= $userId ?>" class="btn btn-outline-secondary ms-2" style="border-radius:10px;padding:10px 22px;">Retour</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
(() => {
  const modelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';
  const video = document.getElementById('faceRegisterVideo');
  const canvas = document.getElementById('faceRegisterCanvas');
  const startBtn = document.getElementById('startFaceRegister');
  const captureBtn = document.getElementById('captureFaceRegister');
  const saveBtn = document.getElementById('saveFaceRegister');
  const status = document.getElementById('faceRegisterStatus');
  const hiddenInput = document.getElementById('faceRegisterDescriptor');
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
      status.textContent = 'Webcam activee. Regardez la camera puis capturez.';
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
      saveBtn.disabled = false;
      status.textContent = 'Visage capture. Vous pouvez maintenant enregistrer Face ID.';
    } catch (error) {
      status.textContent = 'Erreur pendant la capture du visage.';
    }
  });
})();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
