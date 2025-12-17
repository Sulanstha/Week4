<?php
$errors = [];
$success = "";

$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $confirmPassword = $_POST['confirm_password'] ?? "";

    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8 || !preg_match('/[!@#$%^&*]/', $password)) {
        $errors['password'] = "Password must be at least 8 characters and contain a special character.";
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $file = "users.json";

        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }

        $jsonData = file_get_contents($file);
        if ($jsonData === false) {
            $errors['file'] = "Error reading user data file.";
        } else {
            $users = json_decode($jsonData, true);
            if (!is_array($users)) {
                $users = [];
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $newUser = [
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword
            ];

            $users[] = $newUser;

            $writeResult = file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
            if ($writeResult === false) {
                $errors['file'] = "Error writing to user data file.";
            } else {
                $success = "Registration successful!";
                $name = $email = "";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container { width: 400px; margin: 50px auto; background: #fff; padding: 20px; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; }
        .error { color: red; font-size: 14px; }
        .success { color: green; margin-top: 10px; }
        button { margin-top: 15px; padding: 10px; width: 100%; }
    </style>
</head>
<body>
<div class="container">
    <h2>User Registration</h2>

    <?php if (!empty($errors['file'])): ?>
        <div class="error"><?= $errors['file'] ?></div>
    <?php endif; ?>

    <form method="post">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
        <div class="error"><?= $errors['name'] ?? "" ?></div>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
        <div class="error"><?= $errors['email'] ?? "" ?></div>

        <label>Password</label>
        <div style="position: relative;">
            <input type="password" name="password" id="password">
            <span onclick="togglePassword('password', this)" style="position:absolute; right:10px; top:8px; cursor:pointer;">👁</span>
        </div>
        <div class="error"><?= $errors['password'] ?? "" ?></div>

        <label>Confirm Password</label>
        <div style="position: relative;">
            <input type="password" name="confirm_password" id="confirm_password">
            <span onclick="togglePassword('confirm_password', this)" style="position:absolute; right:10px; top:8px; cursor:pointer;">👁</span>
        </div>
        <div class="error"><?= $errors['confirm_password'] ?? "" ?></div>

        <button type="submit">Register</button>
    </form>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
</div>
<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.textContent = '🙈';
    } else {
        field.type = 'password';
        icon.textContent = '👁';
    }
}
</script>
</body>
</html>