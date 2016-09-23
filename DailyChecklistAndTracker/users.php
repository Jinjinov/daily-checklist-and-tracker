<?php

function create_users_table($conn)
{
    // sql to create table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        username VARCHAR(64) NOT NULL,
        password VARCHAR(64) NOT NULL,
        display_name VARCHAR(64) NOT NULL,
        display_image VARCHAR(64) NOT NULL
    )";

    if ($conn->query($sql) === TRUE) {
        //echo "Table users created successfully<br>";
    } else {
        echo "Error creating table: $conn->error <br>";
    }
}

function users($conn)
{
    $userRowIdx = filter_input(INPUT_POST, 'userRowIdx');

    $id = filter_input(INPUT_POST, 'id');
    $username = filter_input(INPUT_POST, 'username');
    $password = filter_input(INPUT_POST, 'password');
    $display_name = filter_input(INPUT_POST, 'display_name');
    $display_image = filter_input(INPUT_POST, 'display_image');

    $sql = "SELECT id, username, password, display_name, display_image FROM users";
    $result = $conn->query($sql);

    echo '<input type="submit" name="input_user" value="Add new user"/><br>';
    echo '<br>';

    if($userRowIdx != null)
    {
        echo '<input type="submit" name="delete_user" value="Delete selected user"/><br>';
        echo '<br>';
        echo '<input type="submit" name="update_user" value="Update selected user"/><br>';
        echo '<br>';
    }

    echo "<table>";

    if ($result->num_rows > 0) {
        echo "<tr> <th>ID</th> <th>Username</th> <th>Password</th> <th>Display name</th> <th>Display image</th> </tr>";
        // output data of each row
        $count = 0;
        while($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $username = $row["username"];
            $password = $row["password"];
            $display_name = $row["display_name"];
            $display_image = $row["display_image"];

            ++$count;
            $style = "";
            if($userRowIdx==$count){
                $style = "style='background:red;'";
            }
            if($userRowIdx==$count && filter_has_var(INPUT_POST, 'update_user')){
                echo "<tr> <td>$id</td>".
                "<td> <input type='text' name='username' value='$username'> </td>".
                "<td> <input type='text' name='password' value='$password'> </td>".
                "<td> <input type='text' name='display_name' value='$display_name'> </td>".
                "<td> <input type='text' name='display_image' value='$display_image'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"userRowIdx\", this);' $style> <td>$id</td>".
                "<td> $username </td>".
                "<td> $password </td>".
                "<td> $display_name </td>".
                "<td> $display_image </td> </tr>";
            }
        }
    } else {
        echo "0 results<br>";
    }

    if(filter_has_var(INPUT_POST, 'input_user'))
    {
        echo "<tr> <td>$id</td>".
                "<td> <input type='text' name='username' value='$username'> </td>".
                "<td> <input type='text' name='password' value='$password'> </td>".
                "<td> <input type='text' name='display_name' value='$display_name'> </td>".
                "<td> <input type='text' name='display_image' value='$display_image'> </td> </tr>";
    }

    echo '</table>';

    if(filter_has_var(INPUT_POST, 'input_user')){
        echo '<br>';
        echo '<input type="submit" name="insert_user" value="Submit user"/><br>';
        echo '<br>';
    }

    if(filter_has_var(INPUT_POST, 'update_user')){
        echo '<br>';
        echo '<input type="submit" name="save_user" value="Save changes"/><br>';
        echo '<br>';
    }

    $lastUser = -1;

    if(filter_has_var(INPUT_POST, 'insert_user'))
    {
        $sql = "INSERT INTO users (username, password, display_name, display_image) VALUES ('$username', '$password', '$display_name', '$display_image')";

        if ($conn->query($sql) === TRUE) {
            $last_id = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $last_id <br>";
            $lastUser = $last_id;

            postRedirect();
        } else {
            echo "Error: $sql <br> $conn->error <br>";
        }
    }

    if(filter_has_var(INPUT_POST, 'save_user'))
    {
        $sql = "UPDATE users SET username='$username', password='$password', display_name='$display_name', display_image='$display_image' WHERE id=$userRowIdx";

        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    if(filter_has_var(INPUT_POST, 'delete_user'))
    {
        // sql to delete a record
        $sql = "DELETE FROM users WHERE id=$userRowIdx";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";

            postRedirect();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
    
    echo "<input id='userRowIdx' type='hidden' name='userRowIdx' value='$userRowIdx'>";
    
    return $lastUser;
}