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

function users($conn,&$selectedUserId)
{
    $id = filter_input(INPUT_POST, 'id');
    $username = filter_input(INPUT_POST, 'username');
    $password = filter_input(INPUT_POST, 'password');
    $display_name = filter_input(INPUT_POST, 'display_name');
    $display_image = filter_input(INPUT_POST, 'display_image');

    $sql = "SELECT id, username, password, display_name, display_image FROM users";
    $result = $conn->query($sql);
    // TODO: if ($conn->query($sql) === TRUE)

    echo '<input type="submit" name="input_user" value="Add new user"/><br>';
    echo '<br>';

    if($selectedUserId != null)
    {
        echo '<input type="submit" name="delete_user" value="Delete selected user"/><br>';
        echo '<br>';
        echo '<input type="submit" name="update_user" value="Update selected user"/><br>';
        echo '<br>';
    }

    echo "<table>";
    echo "<tr> <th>ID</th> <th>Username</th> <th>Password</th> <th>Display name</th> <th>Display image</th> </tr>";

    if ($result->num_rows > 0) {
        
        // output data of each row

        while($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_username = $row["username"];
            $row_password = $row["password"];
            $row_display_name = $row["display_name"];
            $row_display_image = $row["display_image"];

            $style = "";
            if($selectedUserId==$row_id){
                $style = "style='background:red;'";
            }
            if($selectedUserId==$row_id && filter_has_var(INPUT_POST, 'update_user')){
                echo "<tr> <td>$row_id</td>".
                "<td> <input type='text' name='username' value='$row_username'> </td>".
                "<td> <input type='text' name='password' value='$row_password'> </td>".
                "<td> <input type='text' name='display_name' value='$row_display_name'> </td>".
                "<td> <input type='text' name='display_image' value='$row_display_image'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedUserId\", this);' $style> <td>$row_id</td>".
                "<td> $row_username </td>".
                "<td> $row_password </td>".
                "<td> $row_display_name </td>".
                "<td> $row_display_image </td> </tr>";
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
            $lastUser = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $lastUser <br>";

            postRedirect();
        } else {
            echo "Error: $sql <br> $conn->error <br>";
        }
    }

    if(filter_has_var(INPUT_POST, 'save_user'))
    {
        $sql = "UPDATE users SET username='$username', password='$password', display_name='$display_name', display_image='$display_image' WHERE id=$selectedUserId";

        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    if(filter_has_var(INPUT_POST, 'delete_user'))
    {
        // sql to delete a record
        $sql = "DELETE FROM users WHERE id=$selectedUserId";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";

            postRedirect();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
    
    return $lastUser;
}