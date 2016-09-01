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
        <form action="" method="post" id="theForm">
            
            <br>
            
            <input type="submit" name="drop_table" value="Delete tables"/><br>
            
            <br>
            
            <?php
            
            function postRedirect()
            {
                // Redirect to this page.
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit();
            }
            
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
                echo "Error creating database: $conn->error <br>";
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
                
                postRedirect();
            }

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
                echo "Error creating table: $conn->error <br>";
            }

            $userRowIdx = filter_input(INPUT_POST, 'userRowIdx');
            $taskRowIdx = filter_input(INPUT_POST, 'taskRowIdx');
            $dayRowIdx = filter_input(INPUT_POST, 'dayRowIdx');

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
            }
                    
            echo "<table>";
            
            if ($result->num_rows > 0) {
                //echo "<table>";
                echo "<tr> <th>ID</th> <th>Username</th> <th>Password</th> <th>Display name</th> <th>Display image</th> </tr>";
                // output data of each row
                $count = 0;
                while($row = $result->fetch_assoc()) {
                    ++$count;
                    $style = "";
                    if($userRowIdx==$count){
                        $style = "style='background:red;'";
                    }
                    echo "<tr onclick='RowClick(\"userRowIdx\", this);' $style> <td>".$row["id"]."</td> <td>".$row["username"]."</td> <td>".$row["password"]."</td> <td>".$row["display_name"]."</td> <td>".$row["display_image"]."</td> </tr>";
                }
                //echo "</table>";
            } else {
                echo "0 results<br>";
            }
            
            if(isset($_POST['input_user']))
            {
                //echo '<br>';
                //echo '<table>';
                //echo '<tr> <th>ID</th> <th>Username</th> <th>Password</th> <th>Display name</th> <th>Display image</th> </tr>';
                echo "<tr> <td>$id</td>".
                        "<td> <input type='text' name='username' value='$username'> </td>".
                        "<td> <input type='text' name='password' value='$password'> </td>".
                        "<td> <input type='text' name='display_name' value='$display_name'> </td>".
                        "<td> <input type='text' name='display_image' value='$display_image'> </td> </tr>";
                echo '</table>';
                echo '<br>';
                echo '<input type="submit" name="insert_user" value="Submit new user"/><br>';
                echo '<br>';
            }
            else
            {
                echo '</table>';
            }
            
            $lastUser = -1;
            
            if(isset($_POST['insert_user']))
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
            
            if(isset($_POST['delete_user']))
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
                echo "Error creating table: $conn->error <br>";
            }

            $id = filter_input(INPUT_POST, 'id');
            $user_id = filter_input(INPUT_POST, 'user_id');
            $task = filter_input(INPUT_POST, 'task');
            $next_step = filter_input(INPUT_POST, 'next_step');
            $percent_completed = filter_input(INPUT_POST, 'percent_completed');
            $is_private = filter_input(INPUT_POST, 'is_private');
            $type = filter_input(INPUT_POST, 'type');
            $duration = filter_input(INPUT_POST, 'duration');
            $start_date = filter_input(INPUT_POST, 'start_date');
            $start_time = filter_input(INPUT_POST, 'start_time');
            $finish_date = filter_input(INPUT_POST, 'finish_date');
            $finish_time = filter_input(INPUT_POST, 'finish_time');
            $repeat_interval = filter_input(INPUT_POST, 'repeat_interval');
            
            $sql = "SELECT id, user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval FROM tasks";
            $result = $conn->query($sql);

            echo '<input type="submit" name="input_task" value="Add new task"/><br>';
            echo '<br>';
            
            if($taskRowIdx != null)
            {
                echo '<input type="submit" name="delete_task" value="Delete selected task"/><br>';
                echo '<br>';
            }
                    
            echo "<table>";
            
            if ($result->num_rows > 0) {
                //echo "<table>";
                echo "<tr> <th>ID</th> <th>User ID</th> <th>Task</th> <th>Next step</th> <th>Completed %</th> <th>is private</th> <th>Type</th> ".
                        "<th>Duration</th> <th>Start</th> <th>Time</th> <th>Finish</th> <th>Time</th> <th>Repeat</th> </tr>";
                // output data of each row
                $count = 0;
                while($row = $result->fetch_assoc()) {
                    ++$count;
                    $style = "";
                    if($taskRowIdx==$count){
                        $style = "style='background:red;'";
                    }
                    echo "<tr onclick='RowClick(\"taskRowIdx\", this);' $style> <td>".$row["id"]."</td> <td>".$row["user_id"]."</td> <td>".$row["task"]."</td> <td>".$row["next_step"]."</td> <td>".$row["percent_completed"]."</td> <td>".$row["is_private"]."</td> <td>".$row["type"]."</td> ".
                            "<td>".$row["duration"]."</td> <td>".$row["start_date"]."</td> <td>".$row["start_time"]."</td> <td>".$row["finish_date"]."</td> <td>".$row["finish_time"]."</td> <td>".$row["repeat_interval"]."</td> </tr>";
                }
                //echo "</table>";
            } else {
                echo "0 results<br>";
            }

            if(isset($_POST['input_task']))
            {
                //echo '<br>';
                //echo "<table>";
                //echo "<tr> <th>ID</th> <th>User ID</th> <th>Task</th> <th>Next step</th> <th>Completed %</th> <th>is private</th> <th>Type</th> ".
                //        "<th>Duration</th> <th>Start</th> <th>Time</th> <th>Finish</th> <th>Time</th> <th>Repeat</th> </tr>";
                echo "<tr> <td>$id</td>".
                        "<td> <input type='text' name='user_id' value='$user_id'> </td>".
                        "<td> <input type='text' name='task' value='$task'> </td>".
                        "<td> <input type='text' name='next_step' value='$next_step'> </td>".
                        "<td> <input type='text' name='percent_completed' value='$percent_completed'> </td>".
                        "<td> <input type='checkbox' name='is_private' value='$is_private'> </td>".
                        //"<td> <input type='text' name='type' value='$type'> </td>".
                        "<td> <select name='type'>".
                        "<option value='normal'>Normal</option>".
                        "<option value='repeat'>Repeat</option>".
                        "<option value='asap'>ASAP</option>".
                        "</select> </td>".
                        "<td> <input type='datetime-local' name='duration' value='$duration'> </td>".
                        "<td> <input type='date' name='start_date' value='$start_date'> </td>".
                        "<td> <input type='time' name='start_time' value='$start_time'> </td>".
                        "<td> <input type='date' name='finish_date' value='$finish_date'> </td>".
                        "<td> <input type='time' name='finish_time' value='$finish_time'> </td>".
                        "<td> <input type='datetime-local' name='repeat_interval' value='$repeat_interval'> </td> </tr>";
                echo "</table>";
                echo '<br>';
                echo '<input type="submit" name="insert_task" value="Submit new task"/><br>';
                echo '<br>';
            }
            else
            {
                echo '</table>';
            }
            
            $lastTask = -1;
            
            if(isset($_POST['insert_task']))
            {    
                $sql = "INSERT INTO tasks (user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval) ".
                        "VALUES ('$lastUser', '$task', '$next_step', '$percent_completed', '$is_private', '$type', '$duration', '$start_date', '$start_time', '$finish_date', '$finish_time', '$repeat_interval')";

                if ($conn->query($sql) === TRUE) {
                    $last_id = $conn->insert_id;
                    echo "New record created successfully. Last inserted ID is: $last_id <br>";
                    $lastTask = $last_id;
                    
                    postRedirect();
                } else {
                    echo "Error: $sql <br> $conn->error <br>";
                }
            }
            
            if(isset($_POST['delete_task']))
            {
                // sql to delete a record
                $sql = "DELETE FROM tasks WHERE id=$taskRowIdx";

                if ($conn->query($sql) === TRUE) {
                    echo "Record deleted successfully";
                    
                    postRedirect();
                } else {
                    echo "Error deleting record: " . $conn->error;
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
                echo "Error creating table: $conn->error <br>";
            }

            $id = filter_input(INPUT_POST, 'id');
            $task_id = filter_input(INPUT_POST, 'task_id');
            $completed = filter_input(INPUT_POST, 'completed');
            $time_spent = filter_input(INPUT_POST, 'time_spent');
            $step_done = filter_input(INPUT_POST, 'step_done');
            
            $sql = "SELECT id, task_id, completed, time_spent, step_done FROM days";
            $result = $conn->query($sql);

            echo '<input type="submit" name="input_day" value="Add new day"/><br>';
            echo '<br>';
            
            if($dayRowIdx != null)
            {
                echo '<input type="submit" name="delete_day" value="Delete selected day"/><br>';
                echo '<br>';
            }
                    
            echo "<table>";
            
            if ($result->num_rows > 0) {
                //echo "<table>";
                echo "<tr> <th>ID</th> <th>Task ID</th> <th>Completed</th> <th>Time spent</th> <th>Step done</th> </tr>";
                // output data of each row
                $count = 0;
                while($row = $result->fetch_assoc()) {
                    ++$count;
                    $style = "";
                    if($dayRowIdx==$count){
                        $style = "style='background:red;'";
                    }
                    echo "<tr onclick='RowClick(\"dayRowIdx\", this);' $style> <td>".$row["id"]."</td> <td>".$row["task_id"]."</td> <td>".$row["completed"]."</td> <td>".$row["time_spent"]."</td> <td>".$row["step_done"]."</td> </tr>";
                }
                //echo "</table>";
            } else {
                echo "0 results<br>";
            }

            if(isset($_POST['input_day']))
            {
                //echo '<br>';
                //echo "<table>";
                //echo "<tr> <th>ID</th> <th>Task ID</th> <th>Completed</th> <th>Time spent</th> <th>Step done</th> </tr>";
                echo "<tr> <td>$id</td>".
                        "<td> <input type='text' name='task_id' value='$task_id'> </td>".
                        "<td> <input type='checkbox' name='completed' value='$completed'> </td>".
                        "<td> <input type='time' name='time_spent' value='$time_spent'> </td>".
                        "<td> <input type='text' name='step_done' value='$step_done'> </td> </tr>";
                echo "</table>";
                echo '<br>';
                echo '<input type="submit" name="insert_day" value="Submit new day"/><br>';
                echo '<br>';
            }
            else
            {
                echo '</table>';
            }
            
            $lastDay = -1;
            
            if(isset($_POST['insert_day']))
            {    
                $sql = "INSERT INTO days (task_id, completed, time_spent, step_done) VALUES ('$lastTask', '$completed', '$time_spent', '$step_done')";

                if ($conn->query($sql) === TRUE) {
                    $last_id = $conn->insert_id;
                    echo "New record created successfully. Last inserted ID is: $last_id <br>";
                    $lastDay = $last_id;
                    
                    postRedirect();
                } else {
                    echo "Error: $sql <br> $conn->error <br>";
                }
            }

            if(isset($_POST['delete_day']))
            {
                // sql to delete a record
                $sql = "DELETE FROM days WHERE id=$dayRowIdx";

                if ($conn->query($sql) === TRUE) {
                    echo "Record deleted successfully";
                    
                    postRedirect();
                } else {
                    echo "Error deleting record: " . $conn->error;
                }
            }
            
            ///////////////////////////////////////////////////////////////////////
            //
            ///////////////////////////////////////////////////////////////////////
            
            $conn->close();
            ?>
            
            <input id="userRowIdx" type='hidden' name='userRowIdx' value='<?php echo $userRowIdx; ?>'>
            <input id="taskRowIdx" type='hidden' name='taskRowIdx' value='<?php echo $taskRowIdx; ?>'>
            <input id="dayRowIdx" type='hidden' name='dayRowIdx' value='<?php echo $dayRowIdx; ?>'>
        </form>
            
        <script>
        function RowClick(id, row)
        {
            if(document.getElementById(id).value == row.rowIndex) {
                document.getElementById(id).value = '';
            } else {
                document.getElementById(id).value = row.rowIndex;
            }
            
            document.getElementById("theForm").submit();
        }
        </script>
    </body>
</html>
