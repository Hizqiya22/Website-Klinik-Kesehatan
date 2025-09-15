<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Klinik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #d6e5e0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        .login-box img {
           width: 160px;     /* sebelumnya 120px */
    height: auto;
    margin-bottom: 20px;

        }

        .login-box h2 {
            color: #2b4c3f;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            background-color: #2e7d71;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
        }

        .login-box button:hover {
            background-color: #256d62;
        }

        .register-link {
            margin-top: 15px;
            font-size: 0.95em;
        }

        .register-link a {
            text-decoration: none;
            color: #2e7d71;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error {
            margin-top: 10px;
            color: red;
            font-size: 0.9em;
        }

        @media screen and (max-width: 400px) {
            .login-box {
                padding: 30px 20px;
            }

            .login-box img {
                width: 90px;
                margin-bottom: 15px;
            }

            .login-box h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="logo-klinik.png" alt="Logo Klinik">
    
        <form action="proses_login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required />
            <input type="password" name="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Daftar sebagai Pasien</a>
        </div>
    </div>
</body>
</html>
