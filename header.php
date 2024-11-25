<header>
    <div class="header-left">
        <h1><a href="index.php">Books reservations</a></h1>
        <a href="index.php">Search books</a>
        <a href="my_reservations.php">My reservations</a>
    </div>
    <div class="header-right">
        <?php 
            if(isset($_SESSION["username"])) {
                echo '<a href="logout.php">Log out</a>';
            }
            else {
                echo '<a href="login.php">Login</a>';
                echo '<a href="register.php">Register</a>';
            }
        ?>
    </div>
</header>