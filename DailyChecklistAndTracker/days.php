<?php

function create_days_table($conn)
{
    // sql to create table
    $sqlString = "CREATE TABLE IF NOT EXISTS days (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        task_id INT UNSIGNED, 
        user_id INT UNSIGNED, 
        completed BOOL NOT NULL DEFAULT 0,
        time_spent TIME,
        step_done VARCHAR(255)
    )";

    if ($conn->query($sqlString) === TRUE) {
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

function get_submitted_day()
{
    $day = new Day();
    
    $day->id = filter_input(INPUT_POST, 'var_day_id');
    $day->task_id = filter_input(INPUT_POST, 'var_task_id');
    $day->completed = filter_has_var(INPUT_POST, 'var_completed');
    $day->time_spent = filter_input(INPUT_POST, 'var_time_spent');
    $day->step_done = filter_input(INPUT_POST, 'var_step_done');
    
    return $day;
}

function days_buttons($selectedTaskId,$selectedDayId)
{
    if($selectedTaskId > 0){
        echo '<input type="submit" name="state_input_day" value="Add new day"/>';
    }
    
    if($selectedDayId != null){
        echo '<input type="submit" name="action_sql_delete_day" value="Delete selected day"/>';
        echo '<input type="submit" name="state_update_day" value="Update selected day"/>';
    }
    
    if($_SESSION['state'] === 'state_input_day'){
        echo '<input type="submit" name="action_sql_insert_day" value="Submit day"/>';
    }

    if($_SESSION['state'] === 'state_update_day'){
        echo '<input type="submit" name="action_sql_update_day" value="Save changes"/>';
        echo '<br>';
        echo '<br>';
    }
}

function days_table($conn,$selectedUserId,$selectedDayId,Day $day)
{
    $statement = $conn->prepare("SELECT id, task_id, completed, time_spent, step_done FROM days WHERE user_id = ?");
    $statement->bind_param("i", $selectedUserId);
    $statement->execute();
    $result = $statement->get_result();
    
    //Copy result into a associative array
    $resultArray = $result->fetch_all(MYSQLI_ASSOC);

    //Copy result into a numeric array
    //$resultArray = $result->fetch_all(MYSQLI_NUM);

    //Copy result into both a associative and numeric array
    //$resultArray = $result->fetch_all(MYSQLI_BOTH);

    echo "<table>";
    echo "<tr> <th>ID</th> <th>Task ID</th> <th>Completed</th> <th>Time spent</th> <th>Step done</th> </tr>";

    //if ($result->num_rows > 0) {
    if (count($resultArray) > 0) {    
        $rowDay = new Day();

        //while($row = $result->fetch_assoc()) {
        foreach ($resultArray as $row) {    
            $rowDay->id = $row["id"];
            $rowDay->task_id = $row["task_id"];
            $rowDay->completed = $row["completed"];
            $rowDay->time_spent = $row["time_spent"];
            $rowDay->step_done = $row["step_done"];

            $style = "";
            if($selectedDayId == $rowDay->id){
                $style = "style='background:red;'";
            }
            if($selectedDayId == $rowDay->id && $_SESSION['state'] === 'state_update_day'){
                
                $currentDay = null;
                
                if($day->id === null) {
                    $currentDay = $rowDay;
                }
                else {
                    $currentDay = $day;
                }
                
                echo "<tr> <td><input type='hidden' name='var_day_id' value='$currentDay->id'>$currentDay->id</td>";
                echo "<td> <input type='text' name='var_task_id' value='$currentDay->task_id'> </td>";
                echo "<td> <input type='checkbox' name='var_completed' value='$currentDay->completed'> </td>";
                echo "<td> <input type='time' name='var_time_spent' value='$currentDay->time_spent'> </td>";
                echo "<td> <input type='text' name='var_step_done' value='$currentDay->step_done'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedDayId\", $rowDay->id);' $style> <td>$rowDay->id</td>";
                echo "<td> $rowDay->task_id </td>";
                echo "<td> $rowDay->completed </td>";
                echo "<td> $rowDay->time_spent </td>";
                echo "<td> $rowDay->step_done </td> </tr>";
            }
        }
    }

    if($_SESSION['state'] === 'state_input_day')
    {
        echo "<tr> <td>$day->id</td>";
        echo "<td> <input type='text' name='var_task_id' value='$day->task_id'> </td>";
        echo "<td> <input type='checkbox' name='var_completed' value='$day->completed'> </td>";
        echo "<td> <input type='time' name='var_time_spent' value='$day->time_spent'> </td>";
        echo "<td> <input type='text' name='var_step_done' value='$day->step_done'> </td> </tr>";
    }

    echo "</table>";
}

function task_days($conn,$selectedTaskId)
{
    $statement = $conn->prepare("SELECT id, completed, time_spent, step_done FROM days WHERE task_id = ?");
    $statement->bind_param("i", $selectedTaskId);
    $statement->execute();
    $result = $statement->get_result();
                
    //Copy result into a associative array
    $resultArray = $result->fetch_all(MYSQLI_ASSOC);

    //Copy result into a numeric array
    //$resultArray = $result->fetch_all(MYSQLI_NUM);

    //Copy result into both a associative and numeric array
    //$resultArray = $result->fetch_all(MYSQLI_BOTH);
    
    return $resultArray;
}

function insert_day($conn,$selectedUserId,$selectedTaskId,&$selectedDayId,Day $day)
{
    if(filter_has_var(INPUT_POST, 'action_sql_insert_day'))
    {
        $statement = $conn->prepare("INSERT INTO days (task_id, user_id, completed, time_spent, step_done) VALUES (?, ?, ?, ?, ?)");
        $statement->bind_param("iiiss", $selectedTaskId, $selectedUserId, $day->completed, $day->time_spent, $day->step_done);
        
        if ($statement->execute() === TRUE) {
            $selectedDayId = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $selectedDayId <br>";

            postRedirect(1);
        } else {
            echo "Error: $conn->error <br>";
        }
    }
}

function update_day($conn,$selectedDayId,Day $day)
{
    if(filter_has_var(INPUT_POST, 'action_sql_update_day'))
    {
        $statement = $conn->prepare("UPDATE days SET task_id=?, completed=?, time_spent=?, step_done=? WHERE id=?");
        $statement->bind_param("iissi", $day->task_id, $day->completed, $day->time_spent, $day->step_done, $selectedDayId);
        
        if ($statement->execute() === TRUE) {
            echo "Record updated successfully";
            
            postRedirect(2);
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

function delete_day($conn,$selectedDayId)
{
    if(filter_has_var(INPUT_POST, 'action_sql_delete_day'))
    {
        $statement = $conn->prepare("DELETE FROM days WHERE id=?");
        $statement->bind_param("i", $selectedDayId);
        
        if ($statement->execute() === TRUE) {
            echo "Record deleted successfully";

            postRedirect(3);
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}