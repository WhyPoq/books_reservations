<?php
session_start();
?>
<?php require "need_auth.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My reservations</title>

    <?php require "fonts_links.php"; ?>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <?php require "constants.php"; ?>
    <?php require "db_connect.php"; ?>

    <?php require "header.php"; ?>

    <?php
    function remove_reservations() {
        global $conn;
        global $_POST;
        global $_SESSION;

        $isbn_esc = $conn->real_escape_string($_POST["isbn"]);
        $username_esc = $conn->real_escape_string($_SESSION["username"]);

        // check that book is reserved
        $sql = "SELECT * FROM books WHERE ISBN = '$isbn_esc' AND Reserved = 'Y'";
        $result = $conn->query($sql);
        if($result->num_rows === 0)
            return;
        
        
        // setting the status of a book to not reserved
        $sql = "UPDATE books SET Reserved = 'N' WHERE ISBN = '$isbn_esc'";
        if ($conn->query($sql) === false) {
            echo "Error: " . $sql . "<br>" . $conn->error; 
            return;
        }
        
        // deleting the reservation
        $sql = "DELETE FROM reservations WHERE ISBN = '$isbn_esc' AND Username = '$username_esc'";
        if ($conn->query($sql) === false) {
            echo "Error: " . $sql . "<br>" . $conn->error; 
            return;
        }
    }

    // handling confirmation to remove a book from reservations
    if(isset($_POST["conf_remove_book"]) && isset($_POST["isbn"])) {
        remove_reservations();
    }
    ?>

    <?php
    // handling request to remove a book from reservations
    if(isset($_POST["request_remove_book"]) && isset($_POST["isbn"])) {
        $isbn = $_POST["isbn"];
        $title = $_POST["title"];

        echo '
            <div class="tint-screen"></div>
            <form method="POST" class="confirm-removal-form">';
        echo    '<input name="isbn" value="' . htmlentities($isbn) . '" hidden/>';
        echo    '<p class="confirm-removal-form-title">Remove reservation for <span class="book-title">\'' . htmlentities($title) . '\'</span> ?</p>';
        echo    '<div class="options-wrapper">
                    <div><input type="submit" value="Yes" class="yes-option" name="conf_remove_book"/></div>
                    <div><input type="submit" value="No" class="no-option"/></div>
                </div>
            </form>';
    }

    ?>

    <?php 
    $page = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? (int)($_GET["page"]) : 1;

    if($page < 1)
        $page = 1;

    // update page value according to button press (previous/next)
    if(isset($_GET["previous"]) && $page > 1)
        $page--;
    
    if(isset($_GET["next"]))
        $page++;
    
    ?>

    <main>
        <h1>Your reservations</h1>
        <?php 
        // fetching reservations
        $username_esc = $conn->real_escape_string($_SESSION["username"]);

        $sql_main_part = "FROM reservations r
        INNER JOIN books b USING(ISBN)
        LEFT JOIN categories c ON b.Category = c.CategoryID
        WHERE r.Username = '$username_esc'
        ORDER BY BookTitle ASC";

        // getting overall number of results
        $sql = "SELECT count(*) as count " . $sql_main_part;
        $result = $conn->query($sql);
        if($result->num_rows === 0)
            die("Error retrieving data from the database: " . $conn->connect_error);
        
        $row = $result->fetch_assoc();
        $results_count = $row["count"];
        $pages_count = (int)ceil($results_count / $RESULTS_PER_PAGE);


        // getting the actual reservations according to the page
        $results_skipped = ($page - 1) * $RESULTS_PER_PAGE;
        $sql = "SELECT * " . $sql_main_part . " LIMIT $RESULTS_PER_PAGE OFFSET $results_skipped";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // displaying results table
            echo    '<div class="search-results">
                        <table>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Edition</th>
                                <th>Release Year</th>
                                <th>Category</th>
                                <th></th>
                            </tr>';
            
            // displaying data rows
            while($row = $result->fetch_assoc()) {
                echo    '<tr>
                            <td>' . htmlentities($row["BookTitle"]) . '</td>
                            <td>' . htmlentities($row["Author"]) . '</td>
                            <td>' . htmlentities($row["Edition"]) . '</td>
                            <td>' . htmlentities($row["Year"]) . '</td>
                            <td>' . htmlentities($row["CategoryDescription"]) . '</td>';
                            
                            echo '<td><form method="POST">';
                            echo    '<input name="title" value="' . htmlentities($row["BookTitle"]) . '" hidden/>';
                            echo    '<input name="isbn" value="' . htmlentities($row["ISBN"]) . '" hidden/>';
                            echo    '<input class="reserve-label" value="Remove" type="Submit" name="request_remove_book" />';
                            echo '</form/></td>';
                echo    '</tr>';
            }

            echo        '</table>
                        <form class="paging-bar">';
            echo            '<input name="page" value="' . htmlentities($page) . '" hidden/>';
            echo            '<div><input type="submit" value="Previous" name="previous"';
            if($page === 1)
                echo ' class="hide"';
            echo'/></div>';

            echo            "<p>Page: $page / $pages_count</p>";

            echo            '<div><input type="submit" value="Next" name="next"';
            if($page === $pages_count)
                echo ' class="hide"';
            echo'/></div>';

            echo         '</form>
                    </div>';
        }
        else {
            echo "<p>No reservations</p>";
        }
        ?>
    </main>

    <?php require "footer.php"; ?>

    <?php $conn->close() ?>
</body>
</html>