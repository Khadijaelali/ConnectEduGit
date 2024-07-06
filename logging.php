<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Connexion</title>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: "Trebuchet MS", "Lucida Sans Unicode", "Lucida Grande", "Lucida Sans", Arial, sans-serif;
        }
        section {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            width: 100%;
            background: url("img/R.jpg") no-repeat;
            background-position: center;
            background-size: cover;
        }
        .form-box {
            position: relative;
            width: 400px;
            height: 450px;
            background: transparent;
            border: none;
            border-radius: 20px;
            backdrop-filter: blur(15px) brightness(80%);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        h2 {
            font-size: 2em;
            color: #fff;
            text-align: center;
        }
        .inputbox {
            position: relative;
            margin: 30px 0;
            width: 310px;
            border-bottom: 2px solid #fff;
        }
        .inputbox label {
            position: absolute;
            top: 50%;
            left: 5px;
            transform: translateY(-50%);
            color: #fff;
            font-size: 1em;
            pointer-events: none;
            transition: 0.5s;
        }
        input:focus ~ label,
        input:valid ~ label {
            top: -5px;
        }
        .inputbox input {
            width: 100%;
            height: 50px;
            background: transparent;
            border: none;
            outline: none;
            font-size: 1em;
            padding: 0 35px 0 5px;
            color: #fff;
        }
        .inputbox ion-icon {
            position: absolute;
            right: 8px;
            color: #fff;
            font-size: 1.2em;
            top: 20px;
        }
        .error-message {
            color: white;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            height: 40px;
            border-radius: 40px;
            background-color: #fff;
            border: none;
            outline: none;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
        }
        .password-reset {
            margin-top: 20px;
            text-align: center;
        }
        .password-reset a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9em;
        }
        .password-reset a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section>
        <div class="form-box">
            <?php
                session_start();
                if (isset($_SESSION['error'])) {
                    echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
            ?>
            <form action="login.php" method="post">
                <h2>Connexion</h2>
                <div class="inputbox">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    <input type="text" name="userID" required>
                    <label>UserID</label>
                </div>
                <div class="inputbox">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="password" required>
                    <label>Mot de passe</label>
                </div>
                <button type="submit">Se connecter</button>
                <div class="password-reset">
                    <a href="password_reset_form.html">Mot de passe oublié?</a>
                </div>
            </form>
        </div>
    </section> 
</body>
</html>