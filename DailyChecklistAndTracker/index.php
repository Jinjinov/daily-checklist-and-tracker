<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>Daily Checklist And Tracker</title>
        <link href="./StyleSheet.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <?php
        // put your code here
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "daily_cat";

        // Create connection
        $conn = new mysqli($servername, $username, $password);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Create database
        $sql = "CREATE DATABASE IF NOT EXISTS daily_cat";
        if ($conn->query($sql) === TRUE) {
            echo "Database created successfully<br>";
        } else {
            echo "Error creating database: " . $conn->error . "<br>";
        }
        
        $conn->select_db($dbname);
        
        ///////////////////////////////////////////////////////////////////////
        // users
        ///////////////////////////////////////////////////////////////////////

        // sql to create table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            username VARCHAR(30) NOT NULL
        )";

        if ($conn->query($sql) === TRUE) {
            echo "Table users created successfully<br>";
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }

        $sql = "SELECT id, username FROM users";
        $result = $conn->query($sql);

        $lastUser = -1;
        
        if ($result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>Username</th></tr>";
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>".$row["username"]."</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results<br>";
            
            $sql = "INSERT INTO users (username) VALUES ('JohnDoe')";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;
                echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
                $lastUser = $last_id;
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
            }
        }
        
        ///////////////////////////////////////////////////////////////////////
        // tasks
        ///////////////////////////////////////////////////////////////////////

        // sql to create table
        $sql = "CREATE TABLE IF NOT EXISTS tasks (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            user_id INT(6) UNSIGNED, 
            task VARCHAR(30) NOT NULL
        )";

        if ($conn->query($sql) === TRUE) {
            echo "Table tasks created successfully<br>";
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }

        $sql = "SELECT id, user_id, task FROM tasks";
        $result = $conn->query($sql);

        $lastTask = -1;
        
        if ($result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>User ID</th><th>Task</th></tr>";
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>".$row["user_id"]."</td><td>".$row["task"]."</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results<br>";
            
            $sql = "INSERT INTO tasks (user_id, task) VALUES (".$lastUser.", 'My Task')";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;
                echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
                $lastTask = $last_id;
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
            }
        }
        
        ///////////////////////////////////////////////////////////////////////
        // days
        ///////////////////////////////////////////////////////////////////////
        
        // sql to create table
        $sql = "CREATE TABLE IF NOT EXISTS days (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            task_id INT(6) UNSIGNED, 
            completed BOOL NOT NULL DEFAULT 0
        )";

        if ($conn->query($sql) === TRUE) {
            echo "Table days created successfully<br>";
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }

        $sql = "SELECT id, task_id, completed FROM days";
        $result = $conn->query($sql);

        $lastDay = -1;
        
        if ($result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>Task ID</th><th>Completed</th></tr>";
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>".$row["task_id"]."</td><td>".$row["completed"]."</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results<br>";
            
            $sql = "INSERT INTO days (task_id, completed) VALUES (".$lastTask.", true)";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;
                echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
                $lastDay = $last_id;
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
            }
        }
        
        $conn->close();
        ?>
    </body>
</html>
