<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=UTF-8');

$errors = [];
$values = [];
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $values['full_name'] = trim($_POST['full_name'] ?? '');
    $values['phone']     = trim($_POST['phone'] ?? '');
    $values['email']     = trim($_POST['email'] ?? '');
    $values['birth_date']= $_POST['birth_date'] ?? '';
    $values['gender']    = $_POST['gender'] ?? '';
    $values['languages'] = $_POST['languages'] ?? [];
    $values['bio']       = trim($_POST['bio'] ?? '');
    $values['agreed']    = isset($_POST['agreed']) ? 1 : 0;

    if (empty($values['full_name']) || !preg_match('/^[А-ЯЁа-яё\s\-]{2,100}$/u', $values['full_name'])) {
        $errors['full_name'] = 'ФИО может содержать только русские буквы, пробел и дефис';
    }
    if (empty($values['phone']) || !preg_match('/^\+?7\d{10}$/', $values['phone'])) {
        $errors['phone'] = 'Телефон должен быть в формате +7XXXXXXXXXX';
    }
    if (empty($values['email']) || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email';
    }
    if (empty($values['birth_date'])) {
        $errors['birth_date'] = 'Укажите дату рождения';
    }
    if (empty($values['gender'])) {
        $errors['gender'] = 'Выберите пол';
    }
    if (empty($values['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования';
    }
    if (strlen($values['bio']) > 1000) {
        $errors['bio'] = 'Биография не должна превышать 1000 символов';
    }
    if (!$values['agreed']) {
        $errors['agreed'] = 'Необходимо согласиться с контрактом';
    }

    if (!empty($errors)) {
        foreach ($values as $key => $val) {
            if (is_array($val)) {
                setcookie($key.'_value', json_encode($val), 0, '/');
            } else {
                setcookie($key.'_value', $val, 0, '/');
            }
        }
        foreach ($errors as $key => $msg) {
            setcookie($key.'_error', $msg, 0, '/');
        }
        header('Location: index.php');
        exit();
    }

    foreach ($values as $key => $val) {
        if (is_array($val)) {
            setcookie($key.'_value', json_encode($val), time() + 365*24*60*60, '/');
        } else {
            setcookie($key.'_value', $val, time() + 365*24*60*60, '/');
        }
    }

    setcookie('save', '1', time() + 3600, '/');
    header('Location: index.php');
    exit();
}

if (!empty($_COOKIE['save'])) {
    $messages[] = '<div style="color:green;background:#e6ffe6;padding:15px;border-radius:10px;margin-bottom:20px;">✅ Данные успешно сохранены!</div>';
    setcookie('save', '', time()-3600, '/');
}

$fields = ['full_name','phone','email','birth_date','gender','bio','agreed'];
foreach ($fields as $f) {
    $values[$f] = $_COOKIE[$f.'_value'] ?? '';
    if (!empty($_COOKIE[$f.'_error'])) {
        $errors[$f] = $_COOKIE[$f.'_error'];
        setcookie($f.'_error', '', time()-3600, '/');
    }
}
if (!empty($_COOKIE['languages_value'])) {
    $values['languages'] = json_decode($_COOKIE['languages_value'], true) ?? [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 4</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error { border: 2px solid red !important; }
        .error-msg { color: red; margin: 5px 0 10px 0; font-size: 0.95em; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Анкета</h1>
        <p class="subtitle">Проверка корректного заполнения с использованием Cookies</p>

        <?php foreach ($messages as $msg) echo $msg; ?>

        <form action="index.php" method="POST">
            <label>ФИО</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($values['full_name']); ?>" 
                   class="<?php echo isset($errors['full_name']) ? 'error' : ''; ?>">
            <?php if (isset($errors['full_name'])) echo '<div class="error-msg">'.$errors['full_name'].'</div>'; ?>

            <label>Телефон</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($values['phone']); ?>" 
                   class="<?php echo isset($errors['phone']) ? 'error' : ''; ?>">
            <?php if (isset($errors['phone'])) echo '<div class="error-msg">'.$errors['phone'].'</div>'; ?>

            <label>E-mail</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($values['email']); ?>" 
                   class="<?php echo isset($errors['email']) ? 'error' : ''; ?>">
            <?php if (isset($errors['email'])) echo '<div class="error-msg">'.$errors['email'].'</div>'; ?>

            <label>Дата рождения</label>
            <input type="date" name="birth_date" value="<?php echo htmlspecialchars($values['birth_date']); ?>" 
                   class="<?php echo isset($errors['birth_date']) ? 'error' : ''; ?>">
            <?php if (isset($errors['birth_date'])) echo '<div class="error-msg">'.$errors['birth_date'].'</div>'; ?>

            <label>Пол</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="male" <?php if($values['gender']=='male') echo 'checked'; ?>> Мужской</label>
                <label><input type="radio" name="gender" value="female" <?php if($values['gender']=='female') echo 'checked'; ?>> Женский</label>
                <label><input type="radio" name="gender" value="other" <?php if($values['gender']=='other') echo 'checked'; ?>> Другой</label>
            </div>
            <?php if (isset($errors['gender'])) echo '<div class="error-msg">'.$errors['gender'].'</div>'; ?>

            <label>Любимые языки программирования</label>
            <select name="languages[]" multiple size="6" class="<?php echo isset($errors['languages']) ? 'error' : ''; ?>">
                <?php
                $langs = ['Pascal','C','C++','JavaScript','PHP','Python','Java','Haskell','Clojure','Go'];
                foreach ($langs as $lang) {
                    $selected = in_array($lang, $values['languages'] ?? []) ? 'selected' : '';
                    echo "<option value=\"$lang\" $selected>$lang</option>";
                }
                ?>
            </select>
            <?php if (isset($errors['languages'])) echo '<div class="error-msg">'.$errors['languages'].'</div>'; ?>

            <label>Биография</label>
            <textarea name="bio" rows="5" class="<?php echo isset($errors['bio']) ? 'error' : ''; ?>"><?php echo htmlspecialchars($values['bio']); ?></textarea>
            <?php if (isset($errors['bio'])) echo '<div class="error-msg">'.$errors['bio'].'</div>'; ?>

            <label class="checkbox-label">
                <input type="checkbox" name="agreed" <?php if(!empty($values['agreed'])) echo 'checked'; ?>>
                Я ознакомлен(а) с контрактом
            </label>
            <?php if (isset($errors['agreed'])) echo '<div class="error-msg">'.$errors['agreed'].'</div>'; ?>

            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>