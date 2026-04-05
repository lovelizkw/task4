<?php
$host = 'localhost';
$dbname = 'u82353_anketa';   
$username = 'u82353';
$password = '3228865';              

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name  = trim($_POST['full_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender     = $_POST['gender'] ?? '';
    $languages  = $_POST['languages'] ?? [];
    $bio        = trim($_POST['bio'] ?? '');
    $agreed     = isset($_POST['agreed']) ? 1 : 0;

    if (empty($full_name) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]{2,150}$/u', $full_name)) {
        $errors[] = "ФИО: только буквы, пробелы и дефис (2–150 символов).";
    }

    if (!preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors[] = "Телефон указан некорректно.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный e-mail.";
    }

    $birth = DateTime::createFromFormat('Y-m-d', $birth_date);
    if (!$birth || $birth > new DateTime() || $birth < new DateTime('-100 years')) {
        $errors[] = "Дата рождения указана некорректно.";
    }

    if (!in_array($gender, ['male', 'female', 'other'])) {
        $errors[] = "Выберите пол.";
    }

    $valid_langs = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Prolog','Scala','Go'];
    $selected_valid = array_intersect($languages, $valid_langs);
    if (empty($selected_valid)) {
        $errors[] = "Выберите хотя бы один язык программирования.";
    }

    if (strlen($bio) > 5000) {
        $errors[] = "Биография слишком длинная (макс. 5000 символов).";
    }

    if (!$agreed) {
        $errors[] = "Необходимо согласиться с контрактом.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, phone, email, birth_date, gender, bio, agreed)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $bio, $agreed]);

            $user_id = $pdo->lastInsertId();

            $stmt_lang = $pdo->prepare("
                INSERT INTO user_languages (user_id, language_id)
                SELECT ?, id FROM languages WHERE name = ?
            ");
            foreach ($selected_valid as $lang) {
                $stmt_lang->execute([$user_id, $lang]);
            }

            $success = "Данные успешно сохранены! Ваш ID: <strong>$user_id</strong>";
        } catch (Exception $e) {
            $errors[] = "Ошибка сохранения: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат отправки</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Результат отправки формы</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-box">
                <h2>Ошибки:</h2>
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="form.php">← Вернуться к форме</a>
            </div>
        <?php elseif ($success): ?>
            <div class="success-box">
                <h2>Успешно!</h2>
                <p><?= $success ?></p>
                <a href="form.php">Заполнить ещё одну анкету</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>