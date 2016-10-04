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
            
            if(!isset($_SESSION)) 
            { 
                session_start(); 
            }
            if(isset($_SESSION['redirect_in_progress']))
            {
                unset($_SESSION['redirect_in_progress']);
                
                $selectedUserId = $_SESSION['selectedUserId'];
                $selectedTaskId = $_SESSION['selectedTaskId'];
                $selectedDayId = $_SESSION['selectedDayId'];
            }
            else
            {
                $selectedUserId = filter_input(INPUT_POST, 'selectedUserId');
                $selectedTaskId = filter_input(INPUT_POST, 'selectedTaskId');
                $selectedDayId = filter_input(INPUT_POST, 'selectedDayId');
            
                // TODO: read ID from hidden input field 3x - how to save variable on postRedirect()
            }
            
            function postRedirect()
            {
                // TODO: save post data on Post-Redirect-Get
                global $selectedUserId;
                global $selectedTaskId;
                global $selectedDayId;
                
                if(!isset($_SESSION)) 
                { 
                    session_start(); 
                }
                $_SESSION['redirect_in_progress'] = 1;
                
                $_SESSION['selectedUserId'] = $selectedUserId;
                $_SESSION['selectedTaskId'] = $selectedTaskId;
                $_SESSION['selectedDayId'] = $selectedDayId;
            
                // Redirect to this page.
                //header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'), TRUE, 307); - this is bad idea, we are trying to avoid reposting the variables
                header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'));
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
            
            if(filter_has_var(INPUT_POST, 'drop_table'))
            {
                $conn->query('DROP TABLE IF EXISTS users');
                $conn->query('DROP TABLE IF EXISTS tasks');
                $conn->query('DROP TABLE IF EXISTS days');
                
                postRedirect();
            }

            ///////////////////////////////////////////////////////////////////////
            //
            ///////////////////////////////////////////////////////////////////////
            
            if($selectedUserId == null)
            {
                ///////////////////////////////////////////////////////////////////////
                // users
                ///////////////////////////////////////////////////////////////////////

                include 'users.php';

                create_users_table($conn);
                users($conn,$selectedUserId);

                echo "<br>";
            }
            else
            {
                ///////////////////////////////////////////////////////////////////////
                // tasks
                ///////////////////////////////////////////////////////////////////////

                include 'tasks.php';

                create_tasks_table($conn);
                tasks($conn,$selectedUserId,$selectedTaskId);

                echo "<br>";

                ///////////////////////////////////////////////////////////////////////
                // days
                ///////////////////////////////////////////////////////////////////////

                include 'days.php';

                create_days_table($conn);
                days($conn,$selectedUserId,$selectedTaskId,$selectedDayId);
            }
            
            ///////////////////////////////////////////////////////////////////////
            //
            ///////////////////////////////////////////////////////////////////////
            
            $conn->close();
            
            echo "<input id='selectedUserId' type='hidden' name='selectedUserId' value='$selectedUserId'>";
            echo "<input id='selectedTaskId' type='hidden' name='selectedTaskId' value='$selectedTaskId'>";
            echo "<input id='selectedDayId' type='hidden' name='selectedDayId' value='$selectedDayId'>";

            ?>
        </form>
            
        <script>
        function RowClick(id, row)
        {
            if(Number(document.getElementById(id).value) === row.rowIndex) {
                document.getElementById(id).value = '';
            } else {
                document.getElementById(id).value = row.rowIndex;
            }
            
            document.getElementById("theForm").submit();
        }
        </script>
    </body>
</html>
