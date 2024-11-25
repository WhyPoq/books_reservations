<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <?php require "fonts_links.php"; ?>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <?php require "db_connect.php"; ?>

    <?php require "header.php"; ?>
    
    <?php
    if(isset($_SESSION["username"]))
    {
        echo "
        <main>
            <h1>Login</h1>
            <p>Already logged in</p>
        </main>
        ";
        return 0;
    }
    ?>

    <?php
    // keeping entered information persistent
    $username = isset($_POST["username"]) ? $_POST["username"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    $err_username = false;
    $err_password = false;

    function login() {
        global $username;
        global $password;

        global $err_username;
        global $err_password;

        global $conn;
        
        // check that fields are not empty
        if(strlen($username) == 0)
            return;
        if(strlen($password) == 0)
            return;

        $username_esc = $conn->real_escape_string($username);
        $password_esc = $conn->real_escape_string($password);

        // check that the user exists
        $sql = "SELECT * from users WHERE Username = '$username_esc'";
        $result = $conn->query($sql);
        if($result->num_rows === 0) {
            $err_username = true;
            return;
        }

        $row = $result->fetch_assoc();
        // check that password is right
        if($password_esc !== $row["Password"]) {
            $err_password = true;
            return;
        }

        $_SESSION["username"] = $username_esc;
        // redirect to main page
        header("Location: index.php");
    }

    if( 
        isset($_POST["username"]) &&
        isset($_POST["password"]) 
    )
    {
        login();
    }
    ?>

    <main>
        <h1>Login</h1>
        <form class="default-form" method="POST">
            <div class="form-field">
                <label for="username">Username</label>
                <input id="username" name="username" required value="<?php echo htmlentities($username);?>"/>
                <?php 
                if($err_username)
                    echo '<p class="form-field-error">No user with this username</p>';
                ?>
            </div>

            <div class="form-field">
                <label for="password">Password (6 characters)</label>
                <input type="password" id="password" name="password" minlength="6" maxlength="6" required value="<?php echo htmlentities($password);?>"/>
                <?php 
                if($err_password)
                    echo '<p class="form-field-error">Wrong password</p>';
                ?>
            </div>

            <input type="submit" value="Login">
        </form>
    </main>

    <?php require "footer.php"; ?>

    <?php $conn->close() ?>
</body>
</html>