<?php

function create_days_table($conn)
{
    // sql to create table
    $sql = "CREATE TABLE IF NOT EXISTS days (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        task_id INT UNSIGNED, 
        user_id INT UNSIGNED, 
        completed BOOL NOT NULL DEFAULT 0,
        time_spent TIME,
        step_done VARCHAR(256)
    )";

    if ($conn->query($sql) === TRUE) {
        //echo "Table days created successfully<br>";
    } else {
        echo "Error creating table: $conn->error <br>";
    }
}

function days($conn,$selectedUserId,$selectedTaskId,&$selectedDayId)
{
    $id = filter_input(INPUT_POST, 'id');
    $task_id = filter_input(INPUT_POST, 'task_id');
    $completed = filter_has_var(INPUT_POST, 'completed');
    $time_spent = filter_input(INPUT_POST, 'time_spent');
    $step_done = filter_input(INPUT_POST, 'step_done');

    $sql = "SELECT id, task_id, completed, time_spent, step_done FROM days WHERE user_id = $selectedUserId";
    $result = $conn->query($sql);
    // TODO: if ($conn->query($sql) === TRUE)

    if($selectedTaskId > 0)
    {
        echo '<input type="submit" name="input_day" value="Add new day"/><br>';
        echo '<br>';
    }
    
    if($selectedDayId != null)
    {
        echo '<input type="submit" name="delete_day" value="Delete selected day"/><br>';
        echo '<br>';
        echo '<input type="submit" name="update_day" value="Update selected day"/><br>';
        echo '<br>';
    }

    echo "<table>";
    echo "<tr> <th>ID</th> <th>Task ID</th> <th>Completed</th> <th>Time spent</th> <th>Step done</th> </tr>";

    if ($result->num_rows > 0) {
        
        // output data of each row

        while($row = $result->fetch_assoc()) {
            $row_id = $row["id"];
            $row_task_id = $row["task_id"];
            $row_completed = $row["completed"];
            $row_time_spent = $row["time_spent"];
            $row_step_done = $row["step_done"];

            $style = "";
            if($selectedDayId==$row_id){
                $style = "style='background:red;'";
            }
            if($selectedDayId==$row_id && filter_has_var(INPUT_POST, 'update_day')){
                echo "<tr> <td>$row_id</td>".
                    "<td> <input type='text' name='task_id' value='$row_task_id'> </td>".
                    "<td> <input type='checkbox' name='completed' value='$row_completed'> </td>".
                    "<td> <input type='time' name='time_spent' value='$row_time_spent'> </td>".
                    "<td> <input type='text' name='step_done' value='$row_step_done'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedDayId\", this);' $style> <td>$row_id</td>".
                    "<td> $row_task_id </td>".
                    "<td> $row_completed </td>".
                    "<td> $row_time_spent </td>".
                    "<td> $row_step_done </td> </tr>";
            }

        }
    } else {
        echo "0 results<br>";
    }

    if(filter_has_var(INPUT_POST, 'input_day'))
    {
        echo "<tr> <td>$id</td>".
                "<td> <input type='text' name='task_id' value='$task_id'> </td>".
                "<td> <input type='checkbox' name='completed' value='$completed'> </td>".
                "<td> <input type='time' name='time_spent' value='$time_spent'> </td>".
                "<td> <input type='text' name='step_done' value='$step_done'> </td> </tr>";
    }

    echo "</table>";

    if(filter_has_var(INPUT_POST, 'input_day'))
    {
        echo '<br>';
        echo '<input type="submit" name="insert_day" value="Submit day"/><br>';
        echo '<br>';
    }

    if(filter_has_var(INPUT_POST, 'update_day'))
    {
        echo '<br>';
        echo '<input type="submit" name="save_day" value="Save changes"/><br>';
        echo '<br>';
    }

    $lastDay = -1;

    if(filter_has_var(INPUT_POST, 'insert_day'))
    {    
        $sql = "INSERT INTO days (task_id, user_id, completed, time_spent, step_done) VALUES ('$selectedTaskId', '$selectedUserId', '$completed', '$time_spent', '$step_done')";

        if ($conn->query($sql) === TRUE) {
            $lastDay = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $lastDay <br>";

            postRedirect();
        } else {
            echo "Error: $sql <br> $conn->error <br>";
        }
    }

    if(filter_has_var(INPUT_POST, 'save_day'))
    {
        $sql = "UPDATE days SET task_id='$task_id', completed='$completed', time_spent='$time_spent', step_done='$step_done' WHERE id=$selectedDayId";

        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }

    if(filter_has_var(INPUT_POST, 'delete_day'))
    {
        // sql to delete a record
        $sql = "DELETE FROM days WHERE id=$selectedDayId";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";

            postRedirect();
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
    
    return $lastDay;
}