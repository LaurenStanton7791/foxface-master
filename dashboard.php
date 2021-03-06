<?php
require_once './inc/bootstrap.php';

requireAuth();
$user = findUserByAccessToken();

$property_id = getHohProperty($user["user_id"]);
$property_listing = getPMProperty($property_id);
$manager = getPropertyManager($property_listing["pm_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/normalize.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet"> 
    <title>Resident Dashboard</title>
</head>
<body>
    <div class="grid-container">
        <header class="header">
            <div>
                <h1 class="dashboard">Dashboard</h1>
                <h4>Welcome Back, Todd</h4>
            </div>
            <div class="second-nav">
                <button class="unit">Add Document</button>
                <div class="header__avatar"><?php echo $user["user_name"]; ?>
                </div>
            </div>         
        </header>

        <aside class="sidenav"></aside>
        <aside class="sidenav">
            <ul class="sidenav__list">
                <img class="foxface-logo" src="images/foxfacelogo@1x.png">
                <div class="sidenav__list-item">
                    <img class="icons" src="images/dashboardicon.png">
                    <li class="sidenav__list-item">Dashboard</li>
                </div>
                <div class="sidenav__list-item">
                    <img class="icons" src="images/Notificationicon.png">
                    <li class="sidenav__list-item">Notifications</li>
                </div>
                <div class="sidenav__list-item">
                    <img class="icons" src="images/messageicon.png">
                    <li class="sidenav__list-item">Messages</li>
                </div>
                <div class="sidenav__list-item">
                    <img class="icons" src="images/calendaricon.png">
                    <li class="sidenav__list-item">Calendar</li>
                </div>
                <div class="sidenav__list-item">
                    <img class="icons" src="images/settingsicon.png">
                    <li class="sidenav__list-item">Settings</li>
                </div>
                <div class="logout sidenav__list-item">
                    <img class="icons" src="images/logouticon.png"><br>
                    <li class="sidenav__list-item"><a href="index.html">Logout</a></li>
                </div>
            </ul>
        </aside>

        <main class="main">
            <div class="main-overview">
                <div class="overviewcard">
                    <div class="overview-header">Location</div>
                    <div class="overviewcard__info">
                        <?php
                            echo $property_listing["property_name"] . "<br>\n";
                            echo $property_listing["addr_street_number"] . " " . $property_listing["addr_street_name"] . ", " . $property_listing["addr_city"] . ", " . $property_listing["addr_state"] . " " . $property_listing["addr_zip"];
                        ?>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overview-header">Contact:</div>
                    <div class="overviewcard__info">
                        <?php
                            echo $manager["forename"] . " "  . $manager["surname"] . "<br>\n";
                            echo $manager["phone"] . "\n";
                        ?>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overview-header">Unit</div>
                    <div class="overviewcard__info">
                        <?php
                            echo "# " . $property_listing["unit_number"] . "<br>\n";
                        ?>
                    </div>
                </div>
                <div class="overviewcard">
                    <div class="overview-header">Household Members</div>
                    <div class="overviewcard__info">
                    <?php
                        $hh_members = getAllHousehold( $user["user_id"] );
                        foreach ($hh_members as &$hh_member) {
                            echo $hh_member["forename"] . " " . $hh_member["surname"] . "<br>\n";
                        }                    
                    ?>                
                    </div>
                </div>
                <div class="main-cards">
                    <div class="card one">
                        <div class="card-header">Current Progress</div>
                        <ul class="items">
                            <li class="list-item"><button> Pre-Screening</button></li>
                            <li class="list-item"><button> Demographic / Student Status</button></li>
                            <li class="list-item"><button> Employment / Income</button></li>
                            <li class="list-item"><button> Other Income</button></li>
                            <li class="list-item"><button> Assistance Income</button></li>
                            <li class="list-item"><button> Assets</button></li>
                            <li class="list-item"><button> Review</button></li>
                            <li class="list-item"><button> Finish & File</button></li>
                        </ul>
                    </div>
                    <div class="card two">
                        <div class="card-header">Attached Documents</div>
                        <ul class="items">
                            <li class="card-item"><button> 3 PV</button></li>
                            <li class="card-item"><button> Employment Verification</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer">
            &copy;Foxface Systems
        </footer>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
