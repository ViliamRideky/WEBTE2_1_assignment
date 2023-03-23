<?php
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
 error_reporting(E_ALL);

session_start();


// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "./config.php";
require_once './GoogleAuthenticator.php';

$pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // TODO: Skontrolovat ci login a password su zadane (podobne ako v register.php).

    $sql = "SELECT fullname, email, login, password, created_at, 2fa_code FROM users WHERE login = :login";

    $stmt = $pdo->prepare($sql);

    // TODO: Upravit SQL tak, aby mohol pouzivatel pri logine zadat login aj email.
    $stmt->bindParam(":login", $_POST["login"], PDO::PARAM_STR);

    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            // Uzivatel existuje, skontroluj heslo.
            $row = $stmt->fetch();
            $hashed_password = $row["password"];

            if (password_verify($_POST['password'], $hashed_password)) {
                // Heslo je spravne.
                $g2fa = new PHPGangsta_GoogleAuthenticator();
                if ($g2fa->verifyCode($row["2fa_code"], $_POST['2fa'], 2)) {
                    // Heslo aj kod su spravne, pouzivatel autentifikovany.

                    // Uloz data pouzivatela do session.
                    $_SESSION["loggedin"] = true;
                    $_SESSION["login"] = $row['login'];
                    $_SESSION["fullname"] = $row['fullname'];
                    $_SESSION["email"] = $row['email'];
                    $_SESSION["created_at"] = $row['created_at'];

                    // Presmeruj pouzivatela na zabezpecenu stranku.
                    header("location: index.php");
                }
                else {
                    $error_msg = "Neplatný kód 2FA.";
                }
            } else {
                $error_msg = "Nesprávne meno alebo heslo.";
            }
        } else {
            $error_msg = "Nesprávne meno alebo heslo.";
        }
    } else {
        $error_msg = "Ups. Niečo sa pokazilo!";
    }

    unset($stmt);
    unset($pdo);


}

require_once './vendor/autoload.php';

// Inicializacia Google API klienta
$client = new Google\Client();

// Definica konfiguracneho JSON suboru pre autentifikaciu klienta.
// Subor sa stiahne z Google Cloud Console v zalozke Credentials.
$client->setAuthConfig('client_secret.json');

// Nastavenie URI, na ktoru Google server presmeruje poziadavku po uspesnej autentifikacii.
$redirect_uri = "https://site201.webte.fei.stuba.sk/zadanie_1_oh/oauth/redirect.php";
$client->setRedirectUri($redirect_uri);

// Definovanie Scopes - rozsah dat, ktore pozadujeme od pouzivatela z jeho Google uctu.
$client->addScope("email");
$client->addScope("profile");

// Vytvorenie URL pre autentifikaciu na Google server - odkaz na Google prihlasenie.
$auth_url = $client->createAuthUrl();

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
                 <a class="nav-link" aria-current="page" href="index.php"> Slovenskí Olympijskí víťazi</a>
                
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
                            echo '<a class="nav-link active" href="login.php">Prihlásiť sa</a>';
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
                            echo '<a class="nav-link" href="register.php">Registrácia</a>';
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
    <?php if(isset($error_msg)) { ?>
    <div class="alert alert-danger" role="alert">
      <?php echo $error_msg; ?>
    </div>
  <?php } ?>
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-lg-12 col-xl-11">
        <div class="card text-black" style="border-radius: 25px;">
          <div class="card-body p-md-5">
            <div class="row justify-content-center">
              <div class="col-md-10 col-lg-6 col-xl-5 order-2 order-lg-1">

                <p class="text-center h1 fw-bold mb-5 mx-1 mx-md-4 mt-4">Prihlásenie</p>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                  <div class="form-outline mb-4">
                    <input type="text" id="form3Example3" class="form-control form-control-lg" name="login"/>
                    <label class="form-label" for="form3Example3">Login</label>
                  </div>

                  <div class="form-outline mb-3">
                    <input type="password" id="form3Example4" class="form-control form-control-lg" name="password"/>
                    <label class="form-label" for="form3Example4">Heslo</label>
                  </div>

                  <div class="form-outline mb-4">
                    <input type="number" id="form3Example3" class="form-control form-control-lg" name="2fa"/>
                    <label class="form-label" for="form3Example3">2FA kód</label>
                  </div>

                  <div class="row">
                    <div class="col-lg-12">
                      <button type="submit" class="btn btn-secondary">Prihlásiť sa</button>
                      <?php $mojaURL = filter_var($auth_url, FILTER_SANITIZE_URL)?>
                      <button type="button" class="btn btn-info google" onclick="window.location.href=  '<?php echo $mojaURL?>'">Prihlásiť sa cez Google</button>
                      
                    </div>  
                      <p class="small fw-bold mt-2 pt-1 mb-0">Nemáte učet? <a href="register.php"
                          class="link-success">Registrácia</a></p>
                  </div>  

                </form>

              </div>
              <div class="col-md-10 col-lg-6 col-xl-7 d-flex align-items-center order-1 order-lg-2">

                <img src="olympic_logo.png"
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
   
</body>
</html>