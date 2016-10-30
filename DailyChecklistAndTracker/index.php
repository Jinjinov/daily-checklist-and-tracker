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
            
            <?php
            
            $admin = filter_input(INPUT_POST, 'admin');
            
            if($admin)
            {
                echo "<br>";
                echo "<label><input type='checkbox' name='admin' value='Administrator' onchange='this.form.submit();' checked>Administrator</label>";

                echo "<input type='submit' name='sql_drop_table' value='Delete tables'/><br>";
                echo "<br>";
            }
            else
            {
                echo "<br>";
                echo "<label><input type='checkbox' name='admin' value='Administrator' onchange='this.form.submit();'>Administrator</label>";
                echo "<br>";
                echo "<br>";
            }
            
            if(!isset($_SESSION)) 
            { 
                session_start(); 
            }
            if(isset($_SESSION['redirect_in_progress']))
            {
                $code = $_SESSION['redirect_in_progress'];
                
                echo "<script type='text/javascript'>alert('$code submitted successfully!')</script>";
                
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
            
            function postRedirect($code)
            {
                // TODO: save post data on Post-Redirect-Get
                global $selectedUserId;
                global $selectedTaskId;
                global $selectedDayId;
                
                if(!isset($_SESSION)) 
                { 
                    session_start(); 
                }
                $_SESSION['redirect_in_progress'] = $code;
                
                $_SESSION['selectedUserId'] = $selectedUserId;
                $_SESSION['selectedTaskId'] = $selectedTaskId;
                $_SESSION['selectedDayId'] = $selectedDayId;
            
                // Redirect to this page.
                //header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'), TRUE, 307); - this is bad idea, we are trying to avoid reposting the variables
                header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'));
                exit();
            }
            
            ///////////////////////////////////////////////////////////////////////
            // connect to mysql
            ///////////////////////////////////////////////////////////////////////
            
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

            ///////////////////////////////////////////////////////////////////////
            // create database
            ///////////////////////////////////////////////////////////////////////
            
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
            
            if(filter_has_var(INPUT_POST, 'sql_drop_table'))
            {
                $conn->query('DROP TABLE IF EXISTS users');
                $conn->query('DROP TABLE IF EXISTS tasks');
                $conn->query('DROP TABLE IF EXISTS days');
                
                postRedirect(4);
            }

            ///////////////////////////////////////////////////////////////////////
            // users
            ///////////////////////////////////////////////////////////////////////
            
            include 'users.php';

            if($admin)
            {
                create_users_table($conn);
                $user = get_user();
                users_buttons($selectedUserId);
                users_table($conn,$selectedUserId,$user);
                insert_user($conn,$user);
                update_user($conn,$selectedUserId,$user);
                delete_user($conn,$selectedUserId);
            }
            
            if(filter_has_var(INPUT_POST, 'login'))
            {
                $username = filter_input(INPUT_POST, 'username');
                $password = filter_input(INPUT_POST, 'password');

                $sql = "SELECT id, password FROM users WHERE username='$username'";
                $result = $conn->query($sql);
                if ($result->num_rows === 1) {
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    if (password_verify($password, $row['password'])) {
                        $selectedUserId = $row['id'];
                    }
                }
            }
            
            if($selectedUserId == null)
            {
                ///////////////////////////////////////////////////////////////////////
                // users
                ///////////////////////////////////////////////////////////////////////

                if(filter_has_var(INPUT_POST, 'create_new_account'))
                {
                    echo "<label>Username: <input type='text' name='username'></label><br>";
                    echo "<label>Password: <input type='text' name='password'></label><br>";
                    echo "<label>Nickname: <input type='text' name='display_name'></label><br>";
                    echo "<label>Image: <select name='display_image'>";
                    echo "<option value='image1'>Image 1</option>";
                    echo "<option value='image2'>Image 2</option>";
                    echo "<option value='image3'>Image 3</option>";
                    echo "</select></label><br>";
                    echo '<input type="submit" name="sql_insert_user" value="Create new account"/>';
                }
                else
                {
                    echo '<input type="submit" name="create_new_account" value="Create new account"/><br>';
                    echo "<label>Username: <input type='text' name='username'></label><br>";
                    echo "<label>Password: <input type='text' name='password'></label><br>";
                    echo '<input type="submit" name="login" value="Login"/>';
                }

                echo "<br>";
            }
            else
            {
                ///////////////////////////////////////////////////////////////////////
                // tasks
                ///////////////////////////////////////////////////////////////////////

                include 'tasks.php';

                create_tasks_table($conn);
                $task = get_task();
                tasks_buttons($selectedTaskId);
                tasks_table($conn,$selectedUserId,$selectedTaskId,$task);
                insert_task($conn,$selectedUserId,$task);
                update_task($conn,$selectedTaskId,$task);
                delete_task($conn,$selectedTaskId);

                echo "<br>";

                if($selectedTaskId != null)
                {
                    ///////////////////////////////////////////////////////////////////////
                    // days
                    ///////////////////////////////////////////////////////////////////////

                    include 'days.php';

                    create_days_table($conn);
                    $day = get_day();
                    days_buttons($selectedTaskId,$selectedDayId);
                    days_table($conn,$selectedUserId,$selectedDayId,$day);
                    insert_day($conn,$selectedUserId,$selectedTaskId,$day);
                    update_day($conn,$selectedDayId,$day);
                    delete_day($conn,$selectedDayId);
                }
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
