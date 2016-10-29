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

class User
{
    var $id;
    var $username;
    var $password;
    var $display_name;
    var $display_image;
}

function get_user()
{
    $user = new User();
    
    $user->id = filter_input(INPUT_POST, 'id');
    $user->username = filter_input(INPUT_POST, 'username');
    $user->password = filter_input(INPUT_POST, 'password');
    $user->display_name = filter_input(INPUT_POST, 'display_name');
    $user->display_image = filter_input(INPUT_POST, 'display_image');
    
    return $user;
}

function users_buttons($selectedUserId)
{
    echo '<input type="submit" name="input_user" value="Add new user"/><br>';
    echo '<br>';

    if($selectedUserId != null){
        echo '<input type="submit" name="sql_delete_user" value="Delete selected user"/><br>';
        echo '<br>';
        echo '<input type="submit" name="update_user" value="Update selected user"/><br>';
        echo '<br>';
    }
    
    if(filter_has_var(INPUT_POST, 'input_user')){
        echo '<input type="submit" name="sql_insert_user" value="Submit user"/><br>';
        echo '<br>';
    }

    if(filter_has_var(INPUT_POST, 'update_user')){
        echo '<input type="submit" name="sql_update_user" value="Save changes"/><br>';
        echo '<br>';
    }
}

function users_table($conn,&$selectedUserId,User $user)
{
    $sql = "SELECT id, username, password, display_name, display_image FROM users";
    $result = $conn->query($sql);
    // TODO: if ($conn->query($sql) === TRUE)

    echo "<table>";
    echo "<tr> <th>ID</th> <th>Username</th> <th>Password</th> <th>Display name</th> <th>Display image</th> </tr>";

    if ($result->num_rows > 0) {
        $rowUser = new User();

        while($row = $result->fetch_assoc()) {
            $rowUser->id = $row["id"];
            $rowUser->username = $row["username"];
            $rowUser->password = $row["password"];
            $rowUser->display_name = $row["display_name"];
            $rowUser->display_image = $row["display_image"];

            $style = "";
            if($selectedUserId==$rowUser->id){
                $style = "style='background:red;'";
            }
            if($selectedUserId==$rowUser->id && filter_has_var(INPUT_POST, 'update_user')){
                echo "<tr> <td>$rowUser->id</td>".
                "<td> <input type='text' name='username' value='$rowUser->username'> </td>".
                "<td> <input type='text' name='password' value='$rowUser->password'> </td>".
                "<td> <input type='text' name='display_name' value='$rowUser->display_name'> </td>".
                "<td> <input type='text' name='display_image' value='$rowUser->display_image'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedUserId\", this);' $style> <td>$rowUser->id</td>".
                "<td> $rowUser->username </td>".
                "<td> $rowUser->password </td>".
                "<td> $rowUser->display_name </td>".
                "<td> $rowUser->display_image </td> </tr>";
            }
        }
    }

    if(filter_has_var(INPUT_POST, 'input_user'))
    {
        echo "<tr> <td>$user->id</td>".
                "<td> <input type='text' name='username' value='$user->username'> </td>".
                "<td> <input type='text' name='password' value='$user->password'> </td>".
                "<td> <input type='text' name='display_name' value='$user->display_name'> </td>".
                "<td> <input type='text' name='display_image' value='$user->display_image'> </td> </tr>";
    }

    echo '</table>';
}

function insert_user($conn,User $user)
{
    $lastUser = -1;

    if(filter_has_var(INPUT_POST, 'sql_insert_user'))
    {
        $sql = "INSERT INTO users (username, password, display_name, display_image) VALUES ('$user->username', '$user->password', '$user->display_name', '$user->display_image')";

        if ($conn->query($sql) === TRUE) {
            $lastUser = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $lastUser <br>";

            postRedirect(1);
        } else {
            echo "Error: $sql <br> $conn->error <br>";
        }
    }
    
    return $lastUser;
}

function update_user($conn,&$selectedUserId,User $user)
{
    if(filter_has_var(INPUT_POST, 'sql_update_user'))
    {
        $sql = "UPDATE users SET username='$user->username', password='$user->password', display_name='$user->display_name', display_image='$user->display_image' WHERE id=$selectedUserId";

        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
            
            postRedirect(2);
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

function delete_user($conn,&$selectedUserId)
{
    if(filter_has_var(INPUT_POST, 'sql_delete_user'))
    {
        // sql to delete a record
        $sql = "DELETE FROM users WHERE id=$selectedUserId";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";

            postRedirect(3);
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}