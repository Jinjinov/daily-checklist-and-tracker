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
            
            function postRedirect($code)
            {
                $_SESSION['redirect_in_progress'] = $code;
                
                $_SESSION['state'] = '0';
                
                // save variables to session, because we executed an action
                
                global $selectedUserId;
                global $selectedTaskId;
                global $selectedDayId;
                
                $_SESSION['selectedUserId'] = $selectedUserId;
                $_SESSION['selectedTaskId'] = $selectedTaskId;
                $_SESSION['selectedDayId'] = $selectedDayId;
            
                // Redirect to this page.
                //header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'), TRUE, 307); - this is bad idea, we are trying to avoid reposting the variables
                header("Location: " . filter_input(INPUT_SERVER, 'REQUEST_URI'));
                exit();
            }
            
            include 'users.php';
            include 'tasks.php';
            include 'days.php';

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
                $selectedUserId = filter_input(INPUT_POST, 'var_selectedUserId');
                $selectedTaskId = filter_input(INPUT_POST, 'var_selectedTaskId');
                $selectedDayId = filter_input(INPUT_POST, 'var_selectedDayId');
            }
            
            if(filter_has_var(INPUT_POST, 'state_create_new_account')){
                $_SESSION['state'] = 'state_create_new_account';
            }
            if(filter_has_var(INPUT_POST, 'state_input_user')){
                $_SESSION['state'] = 'state_input_user';
            }
            if(filter_has_var(INPUT_POST, 'state_update_user')){
                $_SESSION['state'] = 'state_update_user';
            }
            if(filter_has_var(INPUT_POST, 'state_input_task')){
                $_SESSION['state'] = 'state_input_task';
            }
            if(filter_has_var(INPUT_POST, 'state_update_task')){
                $_SESSION['state'] = 'state_update_task';
            }
            if(filter_has_var(INPUT_POST, 'state_input_day')){
                $_SESSION['state'] = 'state_input_day';
            }
            if(filter_has_var(INPUT_POST, 'state_update_day')){
                $_SESSION['state'] = 'state_update_day';
            }
            if(!isset($_SESSION['state'])) 
            { 
                $_SESSION['state'] = '0';
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
            
            if ($conn->query('CREATE DATABASE IF NOT EXISTS daily_cat') === TRUE) {
                $conn->select_db($dbname);
                create_users_table($conn);
                create_tasks_table($conn);
                create_days_table($conn);
            } else {
                echo "Error creating database: $conn->error <br>";
            }

            ///////////////////////////////////////////////////////////////////////
            // drop table
            ///////////////////////////////////////////////////////////////////////
            
            if(filter_has_var(INPUT_POST, 'action_sql_drop_table'))
            {
                $conn->query('DROP TABLE IF EXISTS users');
                $conn->query('DROP TABLE IF EXISTS tasks');
                $conn->query('DROP TABLE IF EXISTS days');
                
                postRedirect(4);
            }
            
            if(filter_has_var(INPUT_POST, 'action_clear_session'))
            {
                $_SESSION = array();
            }

            ///////////////////////////////////////////////////////////////////////
            // login
            ///////////////////////////////////////////////////////////////////////
            
            if(filter_has_var(INPUT_POST, 'action_login'))
            {
                $username = filter_input(INPUT_POST, 'var_username');
                $password = filter_input(INPUT_POST, 'var_password');

                $statement = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
                $statement->bind_param("s", $username);
                $statement->execute();
                $result = $statement->get_result();

                if ($result->num_rows === 1) {
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    if (password_verify($password, $row['password'])) {
                        $selectedUserId = $row['id'];
                    }
                }
                
                postRedirect(5);
            }
            
            ///////////////////////////////////////////////////////////////////////
            // add new user
            ///////////////////////////////////////////////////////////////////////
            
            if(filter_has_var(INPUT_POST, 'action_sql_insert_user'))
            {
                $user = get_submitted_user();
                $selectedUserId = insert_user($conn,$selectedUserId,$user); // action ends with postRedirect(1);
            }
            
            ///////////////////////////////////////////////////////////////////////
            // render page
            ///////////////////////////////////////////////////////////////////////
            
            $admin = filter_input(INPUT_POST, 'is_admin');
            
            if($admin)
            {
                echo "<br>";
                echo "<label><input type='checkbox' name='is_admin' value='Administrator' onchange='this.form.submit();' checked>Administrator</label>";

                echo " <input type='submit' name='action_sql_drop_table' value='Delete tables'/>";
                echo " <input type='submit' name='action_clear_session' value='Clear session'/>";
                echo "<br>";
                echo "<br>";
            }
            else
            {
                echo "<br>";
                echo "<label><input type='checkbox' name='is_admin' value='Administrator' onchange='this.form.submit();'>Administrator</label>";
                echo "<br>";
                echo "<br>";
            }
            
            ///////////////////////////////////////////////////////////////////////
            // users
            ///////////////////////////////////////////////////////////////////////
            
            if($admin)
            {
                // check for actions before rendering the page:
                $user = get_submitted_user();
                insert_user($conn,$selectedUserId,$user);
                update_user($conn,$selectedUserId,$user);
                delete_user($conn,$selectedUserId);
                
                // render the page:
                users_buttons($selectedUserId);
                users_table($conn,$selectedUserId,$user);
                
                echo "<br>";
            }

            if($selectedUserId == null)
            {
                ///////////////////////////////////////////////////////////////////////
                // users
                ///////////////////////////////////////////////////////////////////////

                if(!$admin)
                {
                    if($_SESSION['state'] == 'state_create_new_account')
                    {
                        echo "<label>Username: <input type='text' name='var_username'></label><br>";
                        echo "<label>Password: <input type='text' name='var_password'></label><br>";
                        echo "<label>Nickname: <input type='text' name='var_display_name'></label><br>";
                        echo "<label>Image: <select name='var_display_image'>";
                        echo "<option value='image.png'>Image 1</option>";
                        echo "<option value='image.png'>Image 2</option>";
                        echo "<option value='image.png'>Image 3</option>";
                        echo "</select></label><br>";
                        echo '<input type="submit" name="action_sql_insert_user" value="Create new account"/>';
                    }
                    else
                    {
                        echo '<input type="submit" name="state_create_new_account" value="Create new account"/><br>';
                        echo "<label>Username: <input type='text' name='var_username'></label><br>";
                        echo "<label>Password: <input type='text' name='var_password'></label><br>";
                        echo '<input type="submit" name="action_login" value="Login"/>';
                    }
                }
            }
            else
            {
                $user = get_selected_user($conn,$selectedUserId);
                
                ///////////////////////////////////////////////////////////////////////
                // display user name and image
                ///////////////////////////////////////////////////////////////////////
                
                echo "$user->display_name<br>";
                echo "<img src='$user->display_image' height='64' width='64'><br><br>";
                
                ///////////////////////////////////////////////////////////////////////
                // tasks
                ///////////////////////////////////////////////////////////////////////

                // check for actions before rendering the page:
                $task = get_submitted_task();
                insert_task($conn,$selectedUserId,$selectedTaskId,$task);
                update_task($conn,$selectedTaskId,$task);
                delete_task($conn,$selectedTaskId);
                
                // render the page:
                tasks_buttons($selectedTaskId);
                tasks_table($conn,$selectedUserId,$selectedTaskId,$task);

                echo "<br>";

                if($selectedTaskId != null)
                {
                    ///////////////////////////////////////////////////////////////////////
                    // days
                    ///////////////////////////////////////////////////////////////////////

                    // check for actions before rendering the page:
                    $day = get_submitted_day();
                    insert_day($conn,$selectedUserId,$selectedTaskId,$selectedDayId,$day);
                    update_day($conn,$selectedDayId,$day);
                    delete_day($conn,$selectedDayId);
                    
                    // render the page:
                    days_buttons($selectedTaskId,$selectedDayId);
                    days_table($conn,$selectedUserId,$selectedDayId,$day);
                }
            }
            
            ///////////////////////////////////////////////////////////////////////
            // close connection
            ///////////////////////////////////////////////////////////////////////
            
            $conn->close();
            
            ///////////////////////////////////////////////////////////////////////
            // for java script function RowClick(id, row)
            ///////////////////////////////////////////////////////////////////////
            
            echo "<input id='selectedUserId' type='hidden' name='var_selectedUserId' value='$selectedUserId'>";
            echo "<input id='selectedTaskId' type='hidden' name='var_selectedTaskId' value='$selectedTaskId'>";
            echo "<input id='selectedDayId' type='hidden' name='var_selectedDayId' value='$selectedDayId'>";

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
