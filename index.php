<?php
session_start();
?>
<?php require "need_auth.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books reservations</title>

    <?php require "fonts_links.php"; ?>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <?php require "constants.php"; ?>
    <?php require "db_connect.php"; ?>

    <?php require "header.php"; ?>

    <?php 
    // keeping search values persistent
    $title = isset($_GET["title"]) ? $_GET["title"] : "";
    $author = isset($_GET["author"]) ? $_GET["author"] : "";
    $category = isset($_GET["category"]) ? $_GET["category"] : -1;

    $page = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? (int)($_GET["page"]) : 1;

    if($page < 1)
        $page = 1;

    // update page value according to button press (previous/next)
    if(isset($_GET["previous"]) && $page > 1)
        $page--;
    
    if(isset($_GET["next"]))
        $page++;
    
    ?>

    <?php
    function reserve_book() {
        global $conn;
        global $_POST;
        global $_SESSION;

        $isbn_esc = $conn->real_escape_string($_POST["isbn"]);
        $username_esc = $conn->real_escape_string($_SESSION["username"]);

        // check that book is not reserved
        $sql = "SELECT * FROM books WHERE ISBN = '$isbn_esc' AND Reserved = 'Y'";
        $result = $conn->query($sql);
        if($result->num_rows > 0)
            return;
        
        
        // setting the status of a book to reserved
        $sql = "UPDATE books SET Reserved = 'Y' WHERE ISBN = '$isbn_esc'";
        if ($conn->query($sql) === false) {
            echo "Error: " . $sql . "<br>" . $conn->error; 
            return;
        }
        
        // inserting new reservation
        $sql = "INSERT INTO reservations (ISBN, Username) VALUES ('$isbn_esc', '$username_esc')";
        if ($conn->query($sql) === false) {
            echo "Error: " . $sql . "<br>" . $conn->error; 
            return;
        }
    }

    // handling reserving a book
    if(
        isset($_POST["isbn"]) &&
        isset($_POST["reserve_book"])
    )
    {
        reserve_book();
    }
    ?>

    <main>
        <form class="book-searchbar">
            <input name="title" placeholder="Book Title" value="<?php echo htmlentities($title);?>"/>
            <input name="author" placeholder="Book Author" value="<?php echo htmlentities($author);?>"/>
            <div>
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="-1">All</option>
                    <?php 
                        // dynamically fetch categories from the database
                        $sql = "SELECT * FROM categories";
                        $result = $conn->query($sql);

                        while($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row["CategoryID"] . '"';

                            // select if it was selected before
                            if($category == $row["CategoryID"])
                                echo ' selected';

                            echo '>' . $row["CategoryDescription"] . '</option>';
                        }
                    ?>
                </select>
            </div>
            <input type="submit" value="Search" name="search" class="search-button"/>
        </form>

        <?php 
        // fetching search results
        $title_esc = $conn->real_escape_string($title);
        $author_esc = $conn->real_escape_string($author);
        $category_esc = $conn->real_escape_string($category);

        $sql_where_part = "WHERE BookTitle LIKE '%$title_esc%' and Author LIKE '%$author_esc%'";
        if($category != "-1")
            $sql_where_part = $sql_where_part . "AND b.Category = $category_esc";

        // getting overall number of results
        $sql = "SELECT count(*) as count FROM books b
            LEFT JOIN categories c ON b.Category = c.CategoryID
            " . $sql_where_part;

        $result = $conn->query($sql);
        if($result->num_rows === 0)
            die("Error retrieving data from the database: " . $conn->connect_error);
        
        $row = $result->fetch_assoc();
        $results_count = $row["count"];
        $pages_count = (int)ceil($results_count / $RESULTS_PER_PAGE);
        
        // getting the data itself
        $results_skipped = ($page - 1) * $RESULTS_PER_PAGE;
        $sql = "SELECT * FROM books b
            LEFT JOIN categories c ON b.Category = c.CategoryID" . " " . $sql_where_part . " " .
            "ORDER BY BookTitle ASC
            LIMIT $RESULTS_PER_PAGE OFFSET $results_skipped
            ";
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
                        
                            if($row["Reserved"] == "N"){
                                echo '<td><form method="POST">';
                                echo    '<input name="isbn" value="' . htmlentities($row["ISBN"]) . '" hidden/>';
                                echo    '<input class="reserve-label" value="Reserve" type="Submit" name="reserve_book" />';
                                echo '</form/></td>';
                            }
                            else {
                                echo '<td><span class="reserved-label">Reserved</span></td>';
                            }
                echo    '</tr>';
            }

            echo        '</table>
                        <form class="paging-bar">';
            echo            '<input name="title" value="' . htmlentities($title) . '" hidden/>';
            echo            '<input name="author" value="' . htmlentities($author) . '" hidden/>';
            echo            '<input name="category" value="' . htmlentities($category) . '" hidden/>';
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
            echo "<p>0 Results</p>";
        }
        ?>
    </main>

    <?php require "footer.php"; ?>

    <?php $conn->close() ?>
</body>
</html>