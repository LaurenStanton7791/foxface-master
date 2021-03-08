<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Foxface Systems</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <?php
  require_once '/var/www/html3/inc/bootstrap.php';
  ?>  
  <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
  
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                        <button type="button" class="btn default" data-toggle="modal" data-target="#ModalLoginForm">
                            Resident
                        </button>                        
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
    
    <!-- Login Modal -->
    <div class="modal fade" id="ModalLoginForm" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <span style="color: black">
            <form class="modal-content" method="post" action="/inc/doLogin.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel" style="color: grey">Log In</h5>
                    <button type="button" class="close" id="closeModal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3">
                    <div class="p-3">
                        <div class="form-group">
                            <label for="user_email">Email</label>
                            <input type="email" class="form-control" name="user_email" id="user_email" placeholder="you@example.com">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                        </div>
                        <a href="/register.php">Create An Account</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <span>
                        <input type="submit" class="btn btn-primary" id="submit" value="Submit">
                    </span>
                </div>
            </form>
            </span>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.js"></script>
    <script src="js/main.js"></script>    
</body>
</html>
