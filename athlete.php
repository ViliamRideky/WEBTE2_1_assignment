<?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once('config.php');
    
    $athlete_id = $_GET['id'];

    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "SELECT * FROM person p WHERE id = $athlete_id";
        $stmt = $db->query($query); 
        $athlete = $stmt->fetch();

        $query2 = "SELECT og.type, og.year, og.city, og.country, placement.placing,placement.discipline,person.name,person.surname
        FROM olympic_game AS og
        JOIN placement ON og.id = placement.game_id
        JOIN person ON placement.person_id = person.id
        WHERE person.id = $athlete_id";
        $stmt = $db->query($query2);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo $e->getMessage();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zadanie 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.css">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg fixed-top navbar-dark">
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
                <a class="nav-link active" href="index_list.php">Zoznam športovcov</a>
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
            <h1><?php echo $athlete["name"] . " " . $athlete["surname"]; ?></h1>
            <div class="row">
                <div class="col-lg-12">
                    <img src="useros.png" alt="profile_picture" class="image" width=65 height=65>
                    <div>
                        <p><strong>Dátum narodenia: </strong><?php echo $athlete["birth_day"];?></p>
                        <p><strong>Miesto narodenia: </strong><?php echo $athlete["birth_place"] . "," . $athlete["birth_country"];?></p>
                    </div>
                </div>
            </div >
            <table class="table table-striped" >
                <thead>
                    <tr>
                        <th scope="col">Typ</th>
                        <th scope="col">Disciplína</th>
                        <th scope="col">Mesto</th> 
                        <th scope="col">Krajina</th> 
                        <th scope="col">Umiestnenie</th> 
                        <th scope="col">Rok</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach($results as $result){ ?>
                        <tr>
                            
                            <td><?php echo $result['type']; ?></td>
                            <td><?php echo $result['discipline']; ?></td>
                            <td><?php echo $result['city']; ?></td>
                            <td><?php echo $result['country']; ?></td>
                            <td><?php echo $result['placing']; ?></td>
                            <td><?php echo $result['year']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody> 
            </table>
                    
        <a href="index_list.php" class="btn btn-secondary">Späť</a>
   
        </div>
    </main>   
    
</body>
    <footer> 
      <div class="container">Viliam Rideky &copy; 2023</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
</html>
