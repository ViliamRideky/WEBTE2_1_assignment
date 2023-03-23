<?php
 session_start();
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);

// Konfiguracia PDO
require_once './config.php';
// Kniznica pre 2FA
require_once './GoogleAuthenticator.php';

$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ------- Pomocne funkcie -------
function checkEmpty($field) {
    // Funkcia pre kontrolu, ci je premenna po orezani bielych znakov prazdna.
    // Metoda trim() oreze a odstrani medzery, tabulatory a ine "whitespaces".
    if (empty(trim($field))) {
        return true;
    }
    return false;
}

function checkLength($field, $min, $max) {
    // Funkcia, ktora skontroluje, ci je dlzka retazca v ramci "min" a "max".
    // Pouzitie napr. pre "login" alebo "password" aby mali pozadovany pocet znakov.
    $string = trim($field);     // Odstranenie whitespaces.
    $length = strlen($string);      // Zistenie dlzky retazca.
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}

function checkUsername($username) {
    // Funkcia pre kontrolu, ci username obsahuje iba velke, male pismena, cisla a podtrznik.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}

function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function userExist($db, $login, $email) {
    // Funkcia pre kontrolu, ci pouzivatel s "login" alebo "email" existuje.
    $exist = false;

    $param_login = trim($login);
    $param_email = trim($email);

    $sql = "SELECT id FROM users WHERE login = :login OR email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":login", $param_login, PDO::PARAM_STR);
    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $exist = true;
    }

    unset($stmt);

    return $exist;
}

// ------- ------- ------- -------



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errmsg = "";

    // Validacia username
    if (checkEmpty($_POST['login']) === true) {
        $errmsg .= "<p>Zadajte login.</p>";
    } elseif (checkLength($_POST['login'], 6,32) === false) {
        $errmsg .= "<p>Login musi mat min. 6 a max. 32 znakov.</p>";
    } elseif (checkUsername($_POST['login']) === false) {
        $errmsg .= "<p>Login moze obsahovat iba velke, male pismena, cislice a podtrznik.</p>";
    }

    // Kontrola pouzivatela
    if (userExist($pdo, $_POST['login'], $_POST['email']) === true) {
        $errmsg .= "Pouzivatel s tymto e-mailom / loginom uz existuje.</p>";
    }

    // Validacia mailu
    if (checkGmail($_POST['email'])) {
        $errmsg .= "Prihlaste sa pomocou Google prihlasenia";
        // Ak pouziva google mail, presmerujem ho na prihlasenie cez Google.
        // header("Location: google_login.php");
    }

    // TODO: Validacia hesla
    // TODO: Validacia mena, priezviska

    if (empty($errmsg)) {
        $sql = "INSERT INTO users (fullname, login, email, password, 2fa_code) VALUES (:fullname, :login, :email, :password, :2fa_code)";

        $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        // 2FA pomocou PHPGangsta kniznice: https://github.com/PHPGangsta/GoogleAuthenticator
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        $user_secret = $g2fa->createSecret();
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);

        // Bind parametrov do SQL
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
        } else {
            echo "Ups. Nieco sa pokazilo";
        }

        unset($stmt);
    }
    unset($pdo);
}

?> 


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-signin-client_id" content="335277553087-tqc7eft5k19h8rvile8o14j9u4r8s3rp.apps.googleusercontent.com">
    <title>Zadanie_1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Zadanie 1</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                 <a class="nav-link " aria-current="page" href="index.php"> Slovenskí Olympijskí víťazi</a>
                
                </li>
                <li class="nav-item">
                <a class="nav-link" href="index_success.php">Najúspešnejší olympionici</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="index_list.php">Zoznam športovcov</a>
                </li>
                
                <li class="nav-item">
                <!-- <a class="nav-link " href="login.php">Prihlásiť sa</a> -->
                <?php
                if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin'])){
                            echo '<a class="nav-link" href="admin.php">Admin Panel</a>';
                        }else{
                            echo '<a class="nav-link" href="login.php">Prihlásiť sa</a>';
                        } 
                ?>
                </li>
                <li>
                    <?php
                        // Ak som prihlaseny, existuje session premenna.
                        if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin'])){
                            echo '<a class="nav-link">Prihlásený: ' . $_SESSION['email'] . '</a>';
                        }
                        ?>
                </li>
                <li class="nav-item">
                <!-- <a class="nav-link" href="admin.php">Admin Panel</a> -->
                <?php
                if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin'])){
                            echo '<a class="nav-link" href="./oauth/logout.php">Odhlásiť sa</a>';
                        }else{
                            echo '<a class="nav-link active" href="register.php">Registrácia</a>';
                        } 
                ?>
                </li>
                </ul>
            </div>
        </div>
        </nav>
    </header>
        
<main class="content"> 
  <div class="container">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-12 col-xl-11">
        <div class="card text-black" style="border-radius: 25px;">
          <div class="card-body p-md-5">
            <div class="row justify-content-center">
              <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">

                <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Registrácia</p>

                <form class="mx-1 mx-md-4" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="text" id="firstname" class="form-control" name="firstname" onchange="validateName()" required/>
                      <label class="form-label" for="firstname">Meno</label>
                      <div class="error-message" id="err-name"> Toto pole nemôže obsahovať čísla a špeciálne znaky vrátane medzier.</div>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="text" id="lastname" class="form-control" name="lastname" onchange="validateSurname()" required/>
                      <label class="form-label" for="lastname">Priezvisko</label>
                      <div class="error-message" id="err-surname"> Toto pole nemôže obsahovať čísla a špeciálne znaky vrátane medzier.</div>
                    </div>
                  </div>


                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="email" id="email" class="form-control" name="email" placeholder="abc@stuba.sk" value="" required
                      pattern="^[a-zA-Z0-9.+-]{3,}@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*\.[a-zA-Z]{2,4}$" onchange="validateEmail()"/>
                      <label class="form-label" for="email">Email</label>
                      <div class="error-message" id="err-mail"> Nesprávny formát pre e-mailovú adresu</div>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="login" id="login" class="form-control" name="login" onchange="validateLogin()" required/>
                      <label class="form-label" for="login">Login</label>
                      <div class="error-message" id="err-login">Login musí obsahovať minimálne 6 znakov, maximalné 32 znakov</div>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
                    <div class="form-outline flex-fill mb-0">
                      <input type="password" id="form3Example4c" class="form-control" name="password"/>
                      <label class="form-label" for="form3Example4c">Heslo</label>
                    </div>
                  </div>

                  <div class="d-flex flex-row align-items-center mb-4">
                    <i class="fas fa-user fa-lg me-3 fa-fw"></i>
                    <div class="row"> 
                      <div class="col-lg-12">
                        <button type="submit" class="btn btn-secondary">Registrácia</button>
                      </div>  
                        <p class="small fw-bold mt-2 pt-1 mb-0">Máte už účet? <a href="login.php"
                            class="link-success">Prihlásenie</a></p>

                    </div>
                  </div>

                  
                    <div class="auth">
                      <?php
                      if (!empty($errmsg)) {
                          // Tu vypis chybne vyplnene polia formulara.
                          echo $errmsg;
                      }
                      if (isset($qrcode)) {
                          // Pokial bol vygenerovany QR kod po uspesnej registracii, zobraz ho.
                          $message = '<p>Naskenujte QR kód do aplikácie Authenticator pre 2FA: <br><img src="'.$qrcode.'" alt="qr kod pre aplikaciu authenticator"></p>';
                          echo $message;
                          echo '<p>Po naskenovaní kódu sa môžte prihlásiť: <a href="login.php" role="button" class="btn btn-secondary">Prihlásiť sa</a></p>';
                      }
                      ?>
                    </div>

                </form>

              </div>
              <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">

                <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.svg"
                  class="img-fluid" alt="Sample image">

              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
  </div>
</main>
  
    <footer> 
      <div class="container">Viliam Rideky &copy; 2023</div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <script src="script.js"></script>
   
</body>
</html>