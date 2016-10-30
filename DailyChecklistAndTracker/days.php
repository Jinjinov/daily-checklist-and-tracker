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
        step_done VARCHAR(255)
    )";

    if ($conn->query($sql) === TRUE) {
        //echo "Table days created successfully<br>";
    } else {
        echo "Error creating table: $conn->error <br>";
    }
}

class Day
{
    var $id;
    var $task_id;
    var $completed;
    var $time_spent;
    var $step_done;
}

function get_day()
{
    $day = new Day();
    
    $day->id = filter_input(INPUT_POST, 'id');
    $day->task_id = filter_input(INPUT_POST, 'task_id');
    $day->completed = filter_has_var(INPUT_POST, 'completed');
    $day->time_spent = filter_input(INPUT_POST, 'time_spent');
    $day->step_done = filter_input(INPUT_POST, 'step_done');
    
    return $day;
}

function days_buttons($selectedTaskId,$selectedDayId)
{
    if($selectedTaskId > 0){
        echo '<input type="submit" name="input_day" value="Add new day"/>';
    }
    
    if($selectedDayId != null){
        echo '<input type="submit" name="sql_delete_day" value="Delete selected day"/>';
        echo '<input type="submit" name="update_day" value="Update selected day"/>';
    }
    
    if(filter_has_var(INPUT_POST, 'input_day')){
        echo '<input type="submit" name="sql_insert_day" value="Submit day"/>';
    }

    if(filter_has_var(INPUT_POST, 'update_day')){
        echo '<input type="submit" name="sql_update_day" value="Save changes"/>';
        echo '<br>';
        echo '<br>';
    }
}

function days_table($conn,$selectedUserId,&$selectedDayId,Day $day)
{
    $sql = "SELECT id, task_id, completed, time_spent, step_done FROM days WHERE user_id = $selectedUserId";
    $result = $conn->query($sql);
    // TODO: if ($conn->query($sql) === TRUE)

    echo "<table>";
    echo "<tr> <th>ID</th> <th>Task ID</th> <th>Completed</th> <th>Time spent</th> <th>Step done</th> </tr>";

    if ($result->num_rows > 0) {
        $rowDay = new Day();

        while($row = $result->fetch_assoc()) {
            $rowDay->id = $row["id"];
            $rowDay->task_id = $row["task_id"];
            $rowDay->completed = $row["completed"];
            $rowDay->time_spent = $row["time_spent"];
            $rowDay->step_done = $row["step_done"];

            $style = "";
            if($selectedDayId==$rowDay->id){
                $style = "style='background:red;'";
            }
            if($selectedDayId==$rowDay->id && filter_has_var(INPUT_POST, 'update_day')){
                echo "<tr> <td>$rowDay->id</td>".
                    "<td> <input type='text' name='task_id' value='$rowDay->task_id'> </td>".
                    "<td> <input type='checkbox' name='completed' value='$rowDay->completed'> </td>".
                    "<td> <input type='time' name='time_spent' value='$rowDay->time_spent'> </td>".
                    "<td> <input type='text' name='step_done' value='$rowDay->step_done'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedDayId\", this);' $style> <td>$rowDay->id</td>".
                    "<td> $rowDay->task_id </td>".
                    "<td> $rowDay->completed </td>".
                    "<td> $rowDay->time_spent </td>".
                    "<td> $rowDay->step_done </td> </tr>";
            }
        }
    }

    if(filter_has_var(INPUT_POST, 'input_day'))
    {
        echo "<tr> <td>$day->id</td>".
                "<td> <input type='text' name='task_id' value='$day->task_id'> </td>".
                "<td> <input type='checkbox' name='completed' value='$day->completed'> </td>".
                "<td> <input type='time' name='time_spent' value='$day->time_spent'> </td>".
                "<td> <input type='text' name='step_done' value='$day->step_done'> </td> </tr>";
    }

    echo "</table>";
}

function task_days($conn,$selectedTaskId)
{
    $sql = "SELECT id, completed, time_spent, step_done FROM days WHERE task_id = $selectedTaskId";
    $result = $conn->query($sql);
    // TODO: if ($conn->query($sql) === TRUE)
    
    //Copy result into a associative array
    $resultArray = $result->fetch_all(MYSQLI_ASSOC);

    //Copy result into a numeric array
    //$resultArray = $result->fetch_all(MYSQLI_NUM);

    //Copy result into both a associative and numeric array
    //$resultArray = $result->fetch_all(MYSQLI_BOTH);
    
    return $resultArray;
}

function insert_day($conn,$selectedUserId,$selectedTaskId,Day $day)
{
    $lastDay = -1;

    if(filter_has_var(INPUT_POST, 'sql_insert_day'))
    {    
        $sql = "INSERT INTO days (task_id, user_id, completed, time_spent, step_done) VALUES ('$selectedTaskId', '$selectedUserId', '$day->completed', '$day->time_spent', '$day->step_done')";

        if ($conn->query($sql) === TRUE) {
            $lastDay = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $lastDay <br>";

            postRedirect(1);
        } else {
            echo "Error: $sql <br> $conn->error <br>";
        }
    }
    
    return $lastDay;
}

function update_day($conn,&$selectedDayId,Day $day)
{
    if(filter_has_var(INPUT_POST, 'sql_update_day'))
    {
        $sql = "UPDATE days SET task_id='$day->task_id', completed='$day->completed', time_spent='$day->time_spent', step_done='$day->step_done' WHERE id=$selectedDayId";

        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
            
            postRedirect(2);
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

function delete_day($conn,&$selectedDayId)
{
    if(filter_has_var(INPUT_POST, 'sql_delete_day'))
    {
        // sql to delete a record
        $sql = "DELETE FROM days WHERE id=$selectedDayId";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";

            postRedirect(3);
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}