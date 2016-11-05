<?php

function create_users_table($conn)
{
    // sql to create table
    $sqlString = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        username VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        display_name VARCHAR(255) NOT NULL,
        display_image VARCHAR(255) NOT NULL
    )";

    if ($conn->query($sqlString) === TRUE) {
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

function get_submitted_user()
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
    echo '<input type="submit" name="input_user" value="Add new user"/>';

    if($selectedUserId != null){
        echo '<input type="submit" name="sql_delete_user" value="Delete selected user"/>';
        echo '<input type="submit" name="update_user" value="Update selected user"/>';
    }
    
    if(filter_has_var(INPUT_POST, 'input_user')){
        echo '<input type="submit" name="sql_insert_user" value="Submit user"/>';
    }

    if(filter_has_var(INPUT_POST, 'update_user')){
        echo '<input type="submit" name="sql_update_user" value="Save changes"/>';
        echo '<br>';
        echo '<br>';
    }
}

function users_table($conn,$selectedUserId,User $user)
{
    $result = $conn->query('SELECT id, username, password, display_name, display_image FROM users');

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
                echo "<tr> <td>$rowUser->id</td>";
                echo "<td> <input type='text' name='username' value='$rowUser->username'> </td>";
                echo "<td> <input type='text' name='password' value='$rowUser->password'> </td>";
                echo "<td> <input type='text' name='display_name' value='$rowUser->display_name'> </td>";
                echo "<td> <input type='text' name='display_image' value='$rowUser->display_image'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedUserId\", this);' $style> <td>$rowUser->id</td>";
                echo "<td> $rowUser->username </td>";
                echo "<td> $rowUser->password </td>";
                echo "<td> $rowUser->display_name </td>";
                echo "<td> $rowUser->display_image </td> </tr>";
            }
        }
    }

    if(filter_has_var(INPUT_POST, 'input_user'))
    {
        echo "<tr> <td>$user->id</td>";
        echo "<td> <input type='text' name='username' value='$user->username'> </td>";
        echo "<td> <input type='text' name='password' value='$user->password'> </td>";
        echo "<td> <input type='text' name='display_name' value='$user->display_name'> </td>";
        echo "<td> <input type='text' name='display_image' value='$user->display_image'> </td> </tr>";
    }

    echo '</table>';
}

function get_selected_user($conn,$selectedUserId)
{
    $statement = $conn->prepare("SELECT username, password, display_name, display_image FROM users WHERE id = ?");
    $statement->bind_param("i", $selectedUserId);
    $statement->execute();
    $result = $statement->get_result();
                
    $row = $result->fetch_assoc();

    $rowUser = new User();
    
    $rowUser->id = $selectedUserId;
    $rowUser->username = $row["username"];
    $rowUser->password = $row["password"];
    $rowUser->display_name = $row["display_name"];
    $rowUser->display_image = $row["display_image"];
    
    return $rowUser;
}

function insert_user($conn,&$selectedUserId,User $user)
{
    if(filter_has_var(INPUT_POST, 'sql_insert_user'))
    {
        $password = password_hash($user->password, PASSWORD_DEFAULT);
        
        $statement = $conn->prepare("INSERT INTO users (username, password, display_name, display_image) VALUES (?, ?, ?, ?)");
        $statement->bind_param("ssss", $user->username, $password, $user->display_name, $user->display_image);
        
        if ($statement->execute() === TRUE) {
            $selectedUserId = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $selectedUserId <br>";

            postRedirect(1);
        } else {
            echo "Error: $conn->error <br>";
        }
    }
}

function update_user($conn,$selectedUserId,User $user)
{
    if(filter_has_var(INPUT_POST, 'sql_update_user'))
    {
        $password = password_hash($user->password, PASSWORD_DEFAULT);
        
        $statement = $conn->prepare("UPDATE users SET username=?, password=?, display_name=?, display_image=? WHERE id=?");
        $statement->bind_param("ssssi", $user->username, $password, $user->display_name, $user->display_image, $selectedUserId);
        
        if ($statement->execute() === TRUE) {
            echo "Record updated successfully";
            
            postRedirect(2);
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

function delete_user($conn,$selectedUserId)
{
    if(filter_has_var(INPUT_POST, 'sql_delete_user'))
    {
        // TODO: delete all user tasks
        
        $statement = $conn->prepare("DELETE FROM users WHERE id=?");
        $statement->bind_param("i", $selectedUserId);
        
        if ($statement->execute() === TRUE) {
            echo "Record deleted successfully";

            postRedirect(3);
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}