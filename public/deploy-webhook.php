<?php
/**
 * Webhook de déploiement automatique
 *
 * Ce script est appelé par GitHub à chaque push sur la branche main.
 * Il déclenche automatiquement le déploiement de l'application.
 *
 * URL: https://etsgobel.com/deploy-webhook.php
 */

// Configuration
$secret = getenv('DEPLOY_WEBHOOK_SECRET') ?: 'VOTRE_SECRET_WEBHOOK_ICI';
$branch = 'main';
$logFile = '/home/deploy/salon-gobel/shared/storage/logs/deploy.log';

// Headers
header('Content-Type: application/json');

// Fonction de log
function logMessage($message) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Récupérer le payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Vérifier la signature GitHub (sécurité)
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expectedSignature, $signature)) {
    logMessage("❌ Signature invalide - Tentative de déploiement rejetée");
    http_response_code(403);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Vérifier que c'est un push sur la bonne branche
$ref = $data['ref'] ?? '';
if ($ref !== "refs/heads/$branch") {
    logMessage("ℹ️ Push sur $ref ignoré (seul $branch déclenche le déploiement)");
    http_response_code(200);
    echo json_encode(['message' => 'Push ignored, not on main branch']);
    exit;
}

// Récupérer les infos du commit
$commitHash = $data['after'] ?? 'unknown';
$commitMessage = $data['head_commit']['message'] ?? 'No message';
$pusher = $data['pusher']['name'] ?? 'unknown';

logMessage("🚀 Déploiement déclenché par $pusher");
logMessage("   Commit: $commitHash");
logMessage("   Message: $commitMessage");

// Exécuter le script de déploiement en arrière-plan
$deployScript = '/home/deploy/salon-gobel/deploy-from-webhook.sh';
$command = "nohup $deployScript > /dev/null 2>&1 &";

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    logMessage("✅ Script de déploiement lancé");
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Deployment started',
        'commit' => $commitHash
    ]);
} else {
    logMessage("❌ Erreur lors du lancement du script de déploiement");
    http_response_code(500);
    echo json_encode(['error' => 'Failed to start deployment']);
}

