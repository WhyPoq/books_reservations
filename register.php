<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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
            <h1>Register</h1>
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
    $conf_password = isset($_POST["conf_password"]) ? $_POST["conf_password"] : "";
    $firstname = isset($_POST["firstname"]) ? $_POST["firstname"] : "";
    $surname = isset($_POST["surname"]) ? $_POST["surname"] : "";
    $address_line_1 = isset($_POST["address_line_1"]) ? $_POST["address_line_1"] : "";
    $address_line_2 = isset($_POST["address_line_2"]) ? $_POST["address_line_2"] : "";
    $city = isset($_POST["city"]) ? $_POST["city"] : "";
    $telephone = isset($_POST["telephone"]) ? $_POST["telephone"] : "";
    $mobile = isset($_POST["mobile"]) ? $_POST["mobile"] : "";

    $err_username = false;
    $err_conf_password = false;

    function register() {
        global $username;
        global $password;
        global $conf_password;
        global $firstname;
        global $surname;
        global $address_line_1;
        global $address_line_2;
        global $city;
        global $telephone;
        global $mobile;

        global $err_username;
        global $err_conf_password;

        global $conn;
        
        // check that fields are not empty
        if(strlen($username) == 0)
            return;
        if(strlen($password) == 0)
            return;
        if(strlen($conf_password) == 0)
            return;
        if(strlen($firstname) == 0)
            return;
        if(strlen($surname) == 0)
            return;
        if(strlen($address_line_1) == 0)
            return;
        if(strlen($address_line_2) == 0)
            return;
        if(strlen($city) == 0)
            return;
        if(strlen($telephone) == 0)
            return;
        if(strlen($mobile) == 0)
            return;

        // check that fields are proper length
        if(strlen($password) != 6)
            return;
        if(strlen($mobile) != 10)
            return;

        // check that password and confirmed password match
        if($password !== $conf_password) {
            $err_conf_password = true;
            return;
        }

        // check that telephone and mobile are numeric
        if(!is_numeric($telephone))
            return;
        if(!is_numeric($mobile))
            return;

        $username_esc = $conn->real_escape_string($username);
        $password_esc = $conn->real_escape_string($password);
        $firstname_esc = $conn->real_escape_string($firstname);
        $surname_esc = $conn->real_escape_string($surname);
        $address_line_1_esc = $conn->real_escape_string($address_line_1);
        $address_line_2_esc = $conn->real_escape_string($address_line_2);
        $city_esc = $conn->real_escape_string($city);
        $telephone_esc = $conn->real_escape_string($telephone);
        $mobile_esc = $conn->real_escape_string($mobile);

        // check that the username is not taken
        $sql = "SELECT * from users WHERE Username = '$username_esc'";
        $result = $conn->query($sql);
        if($result->num_rows > 0) {
            $err_username = true;
            return;
        }

        // inserting new user
        $sql = "INSERT INTO users (Username, Password, FirstName, Surname, AddressLine1, AddressLine2, City, Telephone, Mobile) 
        values ('$username_esc', '$password_esc', '$firstname_esc', '$surname_esc', '$address_line_1_esc', '$address_line_2_esc', 
        '$city_esc', '$telephone_esc', '$mobile_esc')";

        if ($conn->query($sql) === true) {
            $_SESSION["username"] = $username_esc;
            // redirect to main page
            header("Location: index.php");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error; 
        }
    }

    if( 
        isset($_POST["username"]) &&
        isset($_POST["password"]) &&  
        isset($_POST["conf_password"]) &&  
        isset($_POST["firstname"]) &&  
        isset($_POST["surname"]) &&  
        isset($_POST["address_line_1"]) &&  
        isset($_POST["address_line_2"]) &&  
        isset($_POST["city"]) &&  
        isset($_POST["telephone"]) &&  
        isset($_POST["mobile"])  
    )
    {
        register();
    }
    ?>

    <main>
        <h1>Register</h1>
        <form class="default-form" method="POST">
            <div class="form-field">
                <label for="username">Username</label>
                <input id="username" name="username" required value="<?php echo htmlentities($username);?>"/>
                <?php 
                if($err_username)
                    echo '<p class="form-field-error">Username is already taken</p>';
                ?>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label for="password">Password (6 characters)</label>
                    <input type="password" id="password" name="password" minlength="6" maxlength="6" required value="<?php echo htmlentities($password);?>"/>
                </div>
                <div class="form-field">
                    <label for="conf_password">Confirm password</label>
                    <input type="password" id="conf_password" name="conf_password" minlength="6" maxlength="6" required value="<?php echo htmlentities($conf_password);?>"/>
                    <?php 
                    if($err_conf_password)
                        echo '<p class="form-field-error">Passwords do not match</p>';
                    ?>
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label for="firstname">First name</label>
                    <input id="firstname" name="firstname" required value="<?php echo htmlentities($firstname);?>"/>
                </div>
                <div class="form-field">
                    <label for="surname">Surname</label>
                    <input id="surname" name="surname" required value="<?php echo htmlentities($surname);?>"/>
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label for="address_line_1">Address line 1</label>
                    <input id="address_line_1" name="address_line_1" required value="<?php echo htmlentities($address_line_1);?>"/>
                </div>
                <div class="form-field">
                    <label for="address_line_2">Address line 2</label>
                    <input id="address_line_2" name="address_line_2" required value="<?php echo htmlentities($address_line_2);?>"/>
                </div>
                <div class="form-field">
                    <label for="city">City</label>
                    <input id="city" name="city" required value="<?php echo htmlentities($city);?>"/>
                </div>
            </div>

            <div class="form-group">
                <div class="form-field">
                    <label for="telephone">Telephone</label>
                    <input type="number" id="telephone" name="telephone" required value="<?php echo htmlentities($telephone);?>"/>
                </div>
                <div class="form-field">
                    <label for="mobile">Mobile (10 digits)</label>
                    <input type="text" id="mobile" name="mobile" pattern="\d*" minlength="10" maxlength="10" required value="<?php echo htmlentities($mobile);?>"/>
                </div>
            </div>

            <input type="submit" value="Register">
        </form>
    </main>

    <?php require "footer.php"; ?>

    <?php $conn->close() ?>
</body>
</html>