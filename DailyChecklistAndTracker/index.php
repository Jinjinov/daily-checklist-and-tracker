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
        <form action="" method="post">
            
            <input type="submit" name="drop_table" value="Delete tables"/>
            
            <br>
            
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
                //echo "Database created successfully<br>";
            } else {
                echo "Error creating database: " . $conn->error . "<br>";
            }

            $conn->select_db($dbname);
            
            ///////////////////////////////////////////////////////////////////////
            // drop table
            ///////////////////////////////////////////////////////////////////////
            
            if(isset($_POST['drop_table']))
            {
                $conn->query('DROP TABLE IF EXISTS users');
                $conn->query('DROP TABLE IF EXISTS tasks');
                $conn->query('DROP TABLE IF EXISTS days');
                
                // Redirect to this page.
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }

            echo "<br>";
            
            ///////////////////////////////////////////////////////////////////////
            // users
            ///////////////////////////////////////////////////////////////////////

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
                echo "Error creating table: " . $conn->error . "<br>";
            }

            $sql = "SELECT id, username, password, display_name, display_image FROM users";
            $result = $conn->query($sql);

            $lastUser = -1;

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr> <th>ID</th> <th>Username</th> <th>Password</th> <th>Display name</th> <th>Display image</th> </tr>";
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<tr> <td>".$row["id"]."</td> <td>".$row["username"]."</td> <td>".$row["password"]."</td> <td>".$row["display_name"]."</td> <td>".$row["display_image"]."</td> </tr>";
                }
                echo "</table>";
            } else {
                echo "0 results<br>";

                $sql = "INSERT INTO users (username, password, display_name, display_image) VALUES ('JohnDoe', 'JohnDoe123', 'Johnny', 'JohnDoe.jpg')";

                if ($conn->query($sql) === TRUE) {
                    $last_id = $conn->insert_id;
                    echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
                    $lastUser = $last_id;
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                }
            }

            echo "<br>";
            
            ///////////////////////////////////////////////////////////////////////
            // tasks
            ///////////////////////////////////////////////////////////////////////

            // sql to create table
            $sql = "CREATE TABLE IF NOT EXISTS tasks (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                user_id INT UNSIGNED NOT NULL, 
                task VARCHAR(256) NOT NULL,
                next_step VARCHAR(256),
                percent_completed TINYINT UNSIGNED,
                is_private BOOL,
                type ENUM('normal', 'repeat', 'asap'),
                duration DATETIME,
                start_date DATE,
                start_time TIME,
                finish_date DATE,
                finish_time TIME,
                repeat_interval DATETIME
            )";

            if ($conn->query($sql) === TRUE) {
                //echo "Table tasks created successfully<br>";
            } else {
                echo "Error creating table: " . $conn->error . "<br>";
            }

            $sql = "SELECT id, user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval FROM tasks";
            $result = $conn->query($sql);

            $lastTask = -1;

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr> <th>ID</th> <th>User ID</th> <th>Task</th> <th>Next step</th> <th>Completed %</th> <th>is private</th> <th>Type</th> ".
                        "<th>Duration</th> <th>Start</th> <th>Time</th> <th>Finish</th> <th>Time</th> <th>Repeat</th> </tr>";
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<tr> <td>".$row["id"]."</td> <td>".$row["user_id"]."</td> <td>".$row["task"]."</td> <td>".$row["next_step"]."</td> <td>".$row["percent_completed"]."</td> <td>".$row["is_private"]."</td> <td>".$row["type"]."</td> ".
                            "<td>".$row["duration"]."</td> <td>".$row["start_date"]."</td> <td>".$row["start_time"]."</td> <td>".$row["finish_date"]."</td> <td>".$row["finish_time"]."</td> <td>".$row["repeat_interval"]."</td> </tr>";
                }
                echo "</table>";
            } else {
                echo "0 results<br>";

                $sql = "INSERT INTO tasks (user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval) ".
                        "VALUES (".$lastUser.", 'My Task', 'Next step', 0, 0, 'repeat', '0000-00-00 08:00:00', '2016-11-22', '08:00:00', '2016-12-24', '08:00:00', '0000-00-07 00:00:00')";

                if ($conn->query($sql) === TRUE) {
                    $last_id = $conn->insert_id;
                    echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
                    $lastTask = $last_id;
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                }
            }

            echo "<br>";
            
            ///////////////////////////////////////////////////////////////////////
            // days
            ///////////////////////////////////////////////////////////////////////

            // sql to create table
            $sql = "CREATE TABLE IF NOT EXISTS days (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                task_id INT UNSIGNED, 
                completed BOOL NOT NULL DEFAULT 0,
                time_spent TIME,
                step_done VARCHAR(256)
            )";

            if ($conn->query($sql) === TRUE) {
                //echo "Table days created successfully<br>";
            } else {
                echo "Error creating table: " . $conn->error . "<br>";
            }

            $sql = "SELECT id, task_id, completed, time_spent, step_done FROM days";
            $result = $conn->query($sql);

            $lastDay = -1;

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr> <th>ID</th> <th>Task ID</th> <th>Completed</th> <th>Time spent</th> <th>Step done</th> </tr>";
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    echo "<tr> <td>".$row["id"]."</td> <td>".$row["task_id"]."</td> <td>".$row["completed"]."</td> <td>".$row["time_spent"]."</td> <td>".$row["step_done"]."</td> </tr>";
                }
                echo "</table>";
            } else {
                echo "0 results<br>";

                $sql = "INSERT INTO days (task_id, completed, time_spent, step_done) VALUES (".$lastTask.", true, '00:15:00', 'First step')";

                if ($conn->query($sql) === TRUE) {
                    $last_id = $conn->insert_id;
                    echo "New record created successfully. Last inserted ID is: " . $last_id . "<br>";
                    $lastDay = $last_id;
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                }
            }

            ///////////////////////////////////////////////////////////////////////
            //
            ///////////////////////////////////////////////////////////////////////
            
            $conn->close();
            ?>
        </form>
        
    </body>
</html>
