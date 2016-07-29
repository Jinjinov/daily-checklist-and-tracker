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
        
        // sql to create table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            firstname VARCHAR(30) NOT NULL,
            lastname VARCHAR(30) NOT NULL
        )";

        if ($conn->query($sql) === TRUE) {
            echo "Table users created successfully<br>";
        } else {
            echo "Error creating table: " . $conn->error . "<br>";
        }

        $sql = "SELECT id, firstname, lastname FROM users";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>Name</th></tr>";
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>".$row["id"]."</td><td>".$row["firstname"]." ".$row["lastname"]."</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results<br>";
            
            $sql = "INSERT INTO users (firstname, lastname) VALUES ('John', 'Doe')";

            if ($conn->query($sql) === TRUE) {
                $last_id = $conn->insert_id;
                echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
            }
        }
        $conn->close();
        ?>
    </body>
</html>
