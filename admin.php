<?php 
    session_start();
    require_once("config.php");

    if (!(isset($_SESSION['access_token']) && $_SESSION['access_token']) && 
        !(isset($_SESSION['loggedin']) && $_SESSION['loggedin'])){
            header("location: login.php");
    }


    try{
        $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT * FROM person";
        $stmt = $db->query($query);
        $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(isset($_POST['del_placement_id'])){
            //var_dump($_POST["del_placement_id"]);
            $sql = "DELETE FROM placement WHERE id=:id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $_POST["del_placement_id"], PDO::PARAM_INT);
            $stmt->execute();
        }

        //Pridanie športovca
        if(!empty($_POST) && !empty($_POST['name'])){
            $sql= "INSERT INTO person (name,surname,birth_day, birth_place, birth_country,death_day,death_place,death_country) VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $db -> prepare($sql);
            $death_day = isset($_POST['death_day']) && !empty($_POST['death_day']) ? $_POST['death_day'] : NULL;
            $death_place = isset($_POST['death_place']) && !empty($_POST['death_place']) ? $_POST['death_place'] : NULL;
            $death_country = isset($_POST['death_country']) && !empty($_POST['death_country']) ? $_POST['death_country'] : NULL;
            $success = $stmt -> execute([$_POST['name'],$_POST['surname'],$_POST['birth_day'],$_POST['birth_place'],$_POST['birth_country'],$death_day,$death_place,$death_country]);
            header("Refresh:0");
        }

        //Úprava športovca
        if(!empty($_POST) && !empty($_POST['edit_name'])){
            $sql= "UPDATE person SET name=?, surname=?, birth_day=?, birth_place=?, birth_country=?,death_day=?, death_place=?, death_country=? WHERE id=?";
            $stmt = $db -> prepare($sql);
            $edit_death_day = isset($_POST['edit_death_day']) && !empty($_POST['edit_death_day']) ? $_POST['edit_death_day'] : NULL;
            $edit_death_place = isset($_POST['edit_death_place']) && !empty($_POST['edit_death_place']) ? $_POST['edit_death_place'] : NULL;
            $edit_death_country = isset($_POST['edit_death_country']) && !empty($_POST['edit_death_country']) ? $_POST['edit_death_country'] : NULL;
            $success = $stmt -> execute([$_POST['edit_name'],$_POST['edit_surname'],$_POST['edit_birth_day'],$_POST['edit_birth_place'],$_POST['edit_birth_country'], $edit_death_day, $edit_death_place, $edit_death_country, $_POST['id_athlete']]);
            header("Refresh:0");
        }

        // Pridanie noveho umiestnenia
        if(isset($_POST['plac_city']) && isset($_POST['plac_placement']) && isset($_POST['plac_discipline'])){
            $city = $_POST['plac_city'];
            $og_id = null;

            $sql= "SELECT id FROM olympic_game WHERE city=:city";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':city', $city, PDO::PARAM_STR);
            $stmt->execute();
            $city_result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($city_result){
                $og_id = $city_result['id'];
                $sql = "INSERT INTO placement (person_id,game_id, placing, discipline) VALUES (?,?,?,?)";
                $stmt = $db->prepare($sql);
                $newPlacement = $stmt->execute([$_POST['plac_id'],$og_id,$_POST['plac_placement'],$_POST['plac_discipline']]);
            } else{
                //var_dump("kokotina");
            }
            header("Refresh:0");
        }

        // Uprava existujuceho zaznamu
        if(isset($_POST['edit_city']) && isset($_POST['edit_placement']) && isset($_POST['edit_discipline'])){
            $city = $_POST['edit_city'];
            $og_id = null;

            $sql= "SELECT id FROM olympic_game WHERE city=:city";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':city', $city, PDO::PARAM_STR);
            $stmt->execute();
            $city_result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($city_result){
                $og_id = $city_result['id'];
                $sql = "UPDATE placement SET person_id=?,game_id=?, placing=?, discipline=? WHERE id=?";
                $stmt = $db->prepare($sql);
                $editPlacement = $stmt->execute([$_POST['edit_id'],$og_id,$_POST['edit_placement'],$_POST['edit_discipline'],$_POST['edit_placement_id']]);

                //var_dump([$_POST['edit_id'],$og_id,$_POST['edit_placement'],$_POST['edit_discipline'],$_POST['edit_placement_id']]);
            } else{
                //var_dump("kokotina");
            }
            header("Refresh:0");
 }

        if(isset($_POST['loadAthlete'])){

        $query = "SELECT og.city, placement.id,placement.placing,placement.discipline,person.name,person.surname
            FROM olympic_game AS og
            JOIN placement ON og.id = placement.game_id
            JOIN person ON placement.person_id = person.id
            WHERE person.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST["person_id"], PDO::PARAM_INT);
            $stmt->execute();
            $resultsAthlete = $stmt->fetchAll();


        $query2 = "SELECT * FROM person p WHERE p.id = :id";
        $stmt = $db->prepare($query2);
            $stmt->bindParam(':id', $_POST["person_id"], PDO::PARAM_INT);
            $stmt->execute();
            $athlete = $stmt->fetch();
        }

    } catch (PDOException $e){
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
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg  navbar-dark">
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
                <?php
                if ((isset($_SESSION['access_token']) && $_SESSION['access_token']) || (isset($_SESSION['loggedin']) && $_SESSION['loggedin'])){
                            echo '<a class="nav-link active" href="admin.php">Admin Panel</a>';
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

        <form action="admin.php" method="post">
                <h2>Zoznam všetkých športovcov</h2>
                <div class="row">
                    <div class="col-lg-12">
                    <select class="form-select" name="person_id">
                        <?php
                            foreach($persons as $person) {
                                echo '<option value="' . $person['id'] . '">' . $person['name'] . ' ' . $person['surname'] . '</option>';
                            }
                        ?> 
                    </select>
                    <button type="submit" class="btn btn-secondary show" name="loadAthlete">Zobraziť</button>
                    </div>
                </div>
            </form>
                        
            <?php
                    if(isset($_POST['loadAthlete'])){ ?>

                    <h3><?php echo $athlete["name"] . " " . $athlete["surname"]; ?></h3>
                    <table class="table table-striped" >
                        <thead>
                            <tr>
                                <th scope="col">Mesto</th> 
                                <th scope="col">Umiestnenie</th> 
                                <th scope="col">Disciplína</th>
                                <th scope="col">Akcia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($resultsAthlete as $resultAthlete){ ?>
                            <tr>
                                <td><?php echo $resultAthlete['city']; ?></td>
                                <td><?php echo $resultAthlete['placing']; ?></td>
                                <td><?php echo $resultAthlete['discipline']; ?></td>
                                <td><?php echo '<form action="#" method="post">

                                <input type="hidden" name="edit_placement_id" value="' . $resultAthlete['id'] . '">
                                <button type="button" class="btn btn-warning" onclick="editPlacement()">Upraviť</button>
                                
                                <input type="hidden" name="del_placement_id" value="' . $resultAthlete['id'] . '">
                                <button type="submit" class="btn btn-danger">Vymazať</button>
                       
                                </form>'; ?></td>
                            </tr>

                        <?php } ?>
                        </tbody>
                    </table>
                    
                    <div class="row">
                        <div class="col-6">
                            <button type="submit" class="btn btn-info" onClick="addPlacement()">Pridaj umiestnenie</button>
                            <div class="places"  id="placementDiv">
                                <h4>Pridanie umiestnenia</h4>
                                <form action="#" method="post" id="addPlace">
                                    <input type="hidden" name="plac_id" value="<?php echo $athlete["id"];?>">
                                    <div class="mb-3" id="addPlc">
                                        <span class="inputName" id="inputGroup-sizing-default">Mesto</span>
                                        <input type="text" name="plac_city" class="form-control" placeholder="Atlanta" required>
                                    </div>
                                    <div class="mb-3">
                                        <span class="inputName" id="inputGroup-sizing-default">Umiestnenie</span>
                                        <input type="number" name="plac_placement" class="form-control" placeholder="3" required>
                                    </div>
                                    <div class="mb-3">
                                        <span class="inputName" id="inputGroup-sizing-default">Disciplína</span>
                                        <input type="text" name="plac_discipline" class="form-control" placeholder="vodný slalom - C1" required>
                                    </div>
                                    <button type="submit" class="btn btn-success">Pridať</button>
                                </form>
                            </div>
                    
                        </div>

                        <div class="col-6">
                            <div class="edits"  id="editsDiv">
                                <h4>Úprava záznamu</h4>
                                <form action="#" method="post" id="editPlace">
                                    <input type="hidden" name="edit_id" value="<?php echo $athlete["id"];?>">
                                    <div class="mb-3" id="editPlc">
                                        <span class="inputName" id="inputGroup-sizing-default">Mesto</span>
                                        <input type="text" name="edit_city" class="form-control" placeholder="Atlanta" required>
                                    </div>
                                    <div class="mb-3">
                                        <span class="inputName" id="inputGroup-sizing-default">Umiestnenie</span>
                                        <input type="number" name="edit_placement" class="form-control" placeholder="3" required>
                                    </div>
                                    <div class="mb-3">
                                        <span class="inputName" id="inputGroup-sizing-default">Disciplína</span>
                                        <input type="text" name="edit_discipline" class="form-control" placeholder="vodný slalom - C1" required>
                                    </div>
                                    <input type="hidden" name="edit_placement_id" value="<?php echo $resultAthlete["id"];?>">
                                    <button type="submit" class="btn btn-success">Upraviť</button>
                                </form>
                            </div>
                    
                        </div>
                    </div>
                 <?php 
                    }
                ?>

        <div class="row">
            <div class="col-6">
                <h2>Pridanie športovca</h2>
                <form action="#" method="post" id="addAthlete">
                    <div class="mb-3" id="addos">
                        <span class="inputName" id="inputGroup-sizing-default">Meno</span>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Priezvisko</span>
                        <input type="text" name="surname"class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Dátum Narodenia</span>
                        <input type="date" name="birth_day" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Miesto Narodenia</span>
                        <input type="text" name="birth_place" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Krajina Narodenia</span>
                        <input type="text" name="birth_country" class="form-control" required>
                    </div>

                    <button type="button" class="btn btn-info" onClick="addDeath()">Informácie o úmrtí</button>
                    <div class="add-death" id="addDeathInfo">
                    <h6>Pokiaľ daný športovec zomrel, doplnte nasledujúce údaje</h6>
                    <p>Inak môžu zostať prázdne</p>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Dátum Úmrtia</span>
                        <input type="date" name="death_day" class="form-control">
                    </div>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Miesto Úmrtia</span>
                        <input type="text" name="death_place" class="form-control">
                    </div>
                    <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Krajina Úmrtia</span>
                        <input type="text" name="death_country" class="form-control">
                    </div>
                    </div>
                    <button type="submit" class="btn btn-secondary">Pridať</button>

                </form>
            </div>

            <div class="col-6">
                <h2><?php echo "Úprava Športovca - " . $athlete["name"] . " " . $athlete["surname"]; ?></h2>
                 <form action="#" method="post" id="editAthlete">
                        <input type="hidden" name="id_athlete" value="<?php echo $athlete["id"];?>">
                        <div class="mb-3" id="editos">
                                <span class="inputName" id="inputGroup-sizing-default">Meno</span>
                                <input type="text" name="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                                <span class="inputName" id="inputGroup-sizing-default">Priezvisko</span>
                                <input type="text" name="edit_surname"class="form-control" required>
                        </div>
                        <div class="mb-3">
                                <span class="inputName" id="inputGroup-sizing-default">Dátum Narodenia</span>
                                <input type="date" name="edit_birth_day" class="form-control" required>
                        </div>
                        <div class="mb-3">
                                <span class="inputName" id="inputGroup-sizing-default">Miesto Narodenia</span>
                                <input type="text" name="edit_birth_place" class="form-control" required>
                        </div>
                        <div class="mb-3">
                                <span class="inputName" id="inputGroup-sizing-default">Krajina Narodenia</span>
                                <input type="text" name="edit_birth_country" class="form-control" required>
                        </div>

                        <button type="button" class="btn btn-info" onClick="editDeath()">Informácie o úmrtí</button>
                        <div class="edit-death" id="deathInfo">
                        <h6>Pokiaľ daný športovec zomrel, doplnte nasledujúce údaje</h6>
                        <p>Inak môžu zostať prázdne</p>
                        <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Dátum Úmrtia</span>
                        <input type="date" name="edit_death_day" class="form-control">
                        </div>
                        <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Miesto Úmrtia</span>
                        <input type="text" name="edit_death_place" class="form-control">
                        </div>
                        <div class="mb-3">
                        <span class="inputName" id="inputGroup-sizing-default">Krajina Úmrtia</span>
                        <input type="text" name="edit_death_country" class="form-control">
                        </div>
                        </div>
                        <button type="submit" class="btn btn-secondary">Upraviť</button>
                        <!-- <div id="snackbar">Daný športovec bol upravený</div> -->
                    </form>
            </div>
        </div>
            
    </div>
</main>

    
</body>
    <footer> 
        <div class="container">Viliam Rideky &copy; 2023</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="script.js"></script>
</html>