<?php
    session_start();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once('config.php');
    

    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query =   "SELECT p.name, p.surname, COUNT(*) 
                    AS gold_medals
                    FROM person p
                    JOIN placement pl 
                    ON p.id = pl.person_id
                    WHERE pl.placing = 1 
                    GROUP BY p.name, p.surname 
                    ORDER BY gold_medals DESC LIMIT 10;";
        $stmt = $db->query($query); 
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
        echo $e->getMessage();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zadanie_1</title>
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
                <a class="nav-link active" href="index_success.php">Najúspešnejší olympionici</a>
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
            <h1>Najúspešnejší olympionici</h1>
            <table id="myTable" class="table table-striped">
                <thead >
                    <tr>
                        <th scope="col">Meno</th>
                        <th scope="col">Priezisko</th>
                        <th scope="col">Počet medailí</th>
                    </tr>
                </thead>
                <tbody>
                    <?php //var_dump($results) 
                    foreach($results as $result){
                        echo "<tr>
                        <td>" . $result["name"] . "</td><td>" . $result["surname"] . "</td><td>" . $result["gold_medals"] . "</td></tr>"; 
                    }
                    ?>
                </tbody> 
            </table>
        </div>
    </main>            
    <footer>
      <div class="container">Viliam Rideky &copy; 2023</div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.3/js/jquery.dataTables.js"></script>
    <script>
        $(document).ready(function () {
            $('#myTable').DataTable();
        });
    </script>
</body>
</html>