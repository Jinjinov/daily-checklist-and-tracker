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
            // users
            ///////////////////////////////////////////////////////////////////////

            include 'users.php';
            
            create_users_table($conn);
            $lastUser = users($conn);

            echo "<br>";
            
            ///////////////////////////////////////////////////////////////////////
            // tasks
            ///////////////////////////////////////////////////////////////////////

            include 'tasks.php';
            
            create_tasks_table($conn);
            $lastTask = tasks($conn,$lastUser);
            
            echo "<br>";

            ///////////////////////////////////////////////////////////////////////
            // days
            ///////////////////////////////////////////////////////////////////////

            include 'days.php';
            
            create_days_table($conn);
            $lastDay = days($conn,$lastTask);
            
            ///////////////////////////////////////////////////////////////////////
            //
            ///////////////////////////////////////////////////////////////////////
            
            $conn->close();
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
