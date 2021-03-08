<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Foxface Systems</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <?php
  require_once '/var/www/html3/inc/bootstrap.php';
  ?>  
  
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="css/modal.css">
</head>

<body>
    <div class="mobile-container">
        <!-- Top Navigation Menu -->
        <div class="topnav">
            <div class="header">
                <img class="img-logo" src="images/FoxFaceLogo-Recovered.png" class="active">
                <h1 class="name">Foxface Systems</h1>
                <div class="form-group">
                    <?php echo display_errors(); ?>
                    <?php echo display_success(); ?>
                </div>                
            </div>
            <div class="main-nav" id="myLinks">
                <a href="team.html">Team</a>
                <a href="#">Resources</a>
                <a href="contact.html">Contact</a>
            </div>
            <a href="javascript:void(0);" class="icon" onclick="myFunction()">
                <i class="fa fa-bars"></i>
            </a>
        </div>
        <div class="wrap">
            <section class="wrapper">
                <div class="background">
                    <h2 class="tag"><strong>Compliance Simplified</strong></h2>
                    <p class="phrase"><em>Affordable housing certification on one cohesive platform</em></p>
                </div>
                <div>
                    <div>
                        <p class="form-title">Let's Get Started</p>
                        <button class="btn default" type="submit" value="Resident" id='LogInR'>Resident</button>
                        <button class="btn default" type="submit" value="Property" id='LogInP'>Property Manager</button>
                    </div>
                </div>
            </section>
        </div>

        <main class="flex" id="info">
            <div class="card">
                <h2 class="card-title"><strong>Company info</strong></h2>
                <p class="card-info">FoxFace Systems, Inc. was created out of necessity during the Covid-19 pandemic for property managers by property managers. Our simplified compliance system allows for property mangers to work with their residents on a cohesive platform that asks LIHTC IRS questions in one easy to use format.</p>
                <p class="card-info">To sign up, email <a href="contact@foxfacesystems.com">contact@foxfacesystems.com.</a></p>
            </div>
            <div class="card">
                <h2 class="card-title"><strong>Connect with us</strong></h2>
                <p class="card-info">Follow us on our social media channels for all the latest industry updates and information.</p>
                <ul class="media">
                <li><a href="https://www.facebook.com/foxfacesystemsinc" class="fa fa-facebook"></a></li>
                <li><a href="https://www.linkedin.com/in/alicia-vennes-cam%C2%AE-8a404469/" class="social linkedin"></a></li>
                <li><a href="https://www.instagram.com/foxfacesystems"><i style="font-size:24px" class="fa instagram">&#xf16d;</i></a></li>
                </ul>
                <p></p>
            </div>
        </main>

        <footer>
            <p class="copyright">Copyright 2020, Foxface Systems</p>
        </footer>
    
    </div>
    
    <!-- The Modal -->
    <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <form class="modal-content animate" action="/inc/doLogin.php" method="post">
                <div class="container">
                    <span class="closeModal">&times;</span>
                    
                    <label for="uname"><b style="color:blue">Username</b></label>
                    <input type="text" style="color:black" placeholder="Enter Username" name="user_email" required>
                    
                    <label for="psw"><b style="color:blue">Password</b></label>
                    <input type="password" style="color:black" placeholder="Enter Password" name="password" required>
                    
                    <button type="submit" style="color:black">Login</button>
                </div>
            </form>
        </div>
    </div> 
    
    <script src="js/main.js"></script>
    <script>
        // Get the modal
        var modal = document.getElementById("myModal");
        
        // Get the button that opens the modal
        var btnR = document.getElementById("LogInR");
        var btnP = document.getElementById("LogInP");
        
        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("closeModal")[0];
        
        // When the user clicks the button, open the modal 
        btnR.onclick = function() {
          modal.style.display = "block";
        }
        btnP.onclick = function() {
          modal.style.display = "block";
        }
        
        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
          modal.style.display = "none";
        }
        
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
          if (event.target == modal) {
            modal.style.display = "none";
          }
        }
    </script>
    
</body>
</html>
