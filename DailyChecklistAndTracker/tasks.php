<?php

function create_tasks_table($conn)
{
    // sql to create table
    $sqlString = "CREATE TABLE IF NOT EXISTS tasks (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        user_id INT UNSIGNED NOT NULL, 
        task VARCHAR(255) NOT NULL,
        next_step VARCHAR(255),
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

    if ($conn->query($sqlString) === TRUE) {
        //echo "Table tasks created successfully<br>";
    } else {
        echo "Error creating table: $conn->error <br>";
    }
}

class Task
{
    var $id;
    var $user_id;
    var $task;
    var $next_step;
    var $percent_completed;
    var $is_private;
    // asap, start / finish, repeat:
    var $type;
    // asap:
    var $duration;
    // start / finish:
    var $start_date;
    var $start_time;
    var $finish_date;
    var $finish_time;
    // repeat:
    var $repeat_interval;
}

function get_submitted_task()
{
    $task = new Task();
    
    $task->id = filter_input(INPUT_POST, 'var_task_id');
    $task->user_id = filter_input(INPUT_POST, 'var_user_id');
    $task->task = filter_input(INPUT_POST, 'var_task');
    $task->next_step = filter_input(INPUT_POST, 'var_next_step');
    $task->percent_completed = filter_input(INPUT_POST, 'var_percent_completed');
    $task->is_private = filter_has_var(INPUT_POST, 'var_is_private');
    // asap, start / finish, repeat:
    $task->type = filter_input(INPUT_POST, 'var_type');
    // asap:
    $task->duration = filter_input(INPUT_POST, 'var_duration');
    // start / finish:
    $task->start_date = filter_input(INPUT_POST, 'var_start_date');
    $task->start_time = filter_input(INPUT_POST, 'var_start_time');
    $task->finish_date = filter_input(INPUT_POST, 'var_finish_date');
    $task->finish_time = filter_input(INPUT_POST, 'var_finish_time');
    // repeat:
    $task->repeat_interval = filter_input(INPUT_POST, 'var_repeat_interval');
    
    return $task;
}

function tasks_buttons($selectedTaskId)
{
    echo '<input type="submit" name="state_input_task" value="Add new task"/>';
    
    if($selectedTaskId != null){
        echo '<input type="submit" name="action_sql_delete_task" value="Delete selected task"/>';
        echo '<input type="submit" name="state_update_task" value="Update selected task"/>';
    }
    
    if($_SESSION['state'] == 'state_input_task'){
        echo '<input type="submit" name="action_sql_insert_task" value="Submit new task"/>';
    }

    if($_SESSION['state'] == 'state_update_task'){
        echo '<input type="submit" name="action_sql_update_task" value="Save changes"/>';
        echo '<br>';
        echo '<br>';
    }
}

function tasks_table($conn,$selectedUserId,$selectedTaskId,Task $task)
{
    $statement = $conn->prepare("SELECT id, user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval FROM tasks WHERE user_id = ?");
    $statement->bind_param("i", $selectedUserId);
    $statement->execute();
    $result = $statement->get_result();
    
    echo "<table>";
    echo "<tr>";
    echo "<th>ID</th> <th>User ID</th> <th>Task</th> <th>Next step</th> <th>Completed %</th> <th>is private</th> <th>Type</th> ";
    //echo "<th>Duration</th> <th>Start</th> <th>Time</th> <th>Finish</th> <th>Time</th> <th>Repeat</th>";
    echo "</tr>";

    if ($result->num_rows > 0) {
        $rowTask = new Task();

        while($row = $result->fetch_assoc()) {
            $rowTask->id = $row["id"];
            $rowTask->user_id = $row["user_id"];
            $rowTask->task = $row["task"];
            $rowTask->next_step = $row["next_step"];
            $rowTask->percent_completed = $row["percent_completed"];
            $rowTask->is_private = $row["is_private"];
            // asap, start / finish, repeat:
            $rowTask->type = $row["type"];
            // asap:
            $rowTask->duration = $row["duration"];
            // start / finish:
            $rowTask->start_date = $row["start_date"];
            $rowTask->start_time = $row["start_time"];
            $rowTask->finish_date = $row["finish_date"];
            $rowTask->finish_time = $row["finish_time"];
            // repeat:
            $rowTask->repeat_interval = $row["repeat_interval"];

            $style = "";
            if($selectedTaskId==$rowTask->id){
                $style = "style='background:red;'";
            }
            if($selectedTaskId==$rowTask->id && $_SESSION['state'] == 'state_update_task'){
                echo "<tr> <td>$rowTask->id</td>";
                echo "<td> <input type='text' name='var_user_id' value='$rowTask->user_id'> </td>";
                echo "<td> <input type='text' name='var_task' value='$rowTask->task'> </td>";
                echo "<td> <input type='text' name='var_next_step' value='$rowTask->next_step'> </td>";
                echo "<td> <input type='text' name='var_percent_completed' value='$rowTask->percent_completed'> </td>";
                echo "<td> <input type='checkbox' name='var_is_private' value='$rowTask->is_private'> </td>";
                //echo "<td> <input type='text' name='var_type' value='$type'> </td>";
                echo "<td> <select name='var_type' onchange='this.form.submit();'>";
                echo "<option value='normal'".($rowTask->type == 'normal' ? 'selected' : '').">Normal</option>";
                echo "<option value='repeat'".($rowTask->type == 'repeat' ? 'selected' : '').">Repeat</option>";
                echo "<option value='asap'".($rowTask->type == 'asap' ? 'selected' : '').">ASAP</option>";
                echo "</select> </td>";
                if($rowTask->type == "asap") {
                    echo "<td> <input type='datetime-local' name='var_duration' value='$rowTask->duration'> </td>";
                }
                if($rowTask->type == "normal" || $rowTask->type == "repeat") {
                    echo "<td> <input type='date' name='var_start_date' value='$rowTask->start_date'> </td>";
                    echo "<td> <input type='time' name='var_start_time' value='$rowTask->start_time'> </td>";
                }
                if($rowTask->type == "normal") {
                    echo "<td> <input type='date' name='var_finish_date' value='$rowTask->finish_date'> </td>";
                    echo "<td> <input type='time' name='var_finish_time' value='$rowTask->finish_time'> </td>";
                }
                if($rowTask->type == "repeat") {
                    echo "<td> <input type='datetime-local' name='var_repeat_interval' value='$rowTask->repeat_interval'> </td> </tr>";
                }
            } else {
                echo "<tr onclick='RowClick(\"selectedTaskId\", this);' $style> <td>$rowTask->id</td>";
                echo "<td> $rowTask->user_id </td>";
                echo "<td> $rowTask->task </td>";
                echo "<td> $rowTask->next_step </td>";
                echo "<td> $rowTask->percent_completed </td>";
                echo "<td> $rowTask->is_private </td>";
                echo "<td> $rowTask->type </td>";
                if($rowTask->type == "asap") {
                    echo "<td> $rowTask->duration </td>";
                }
                if($rowTask->type == "normal" || $rowTask->type == "repeat") {
                    echo "<td> $rowTask->start_date </td>";
                    echo "<td> $rowTask->start_time </td>";
                }
                if($rowTask->type == "normal") {
                    echo "<td> $rowTask->finish_date </td>";
                    echo "<td> $rowTask->finish_time </td>";
                }
                if($rowTask->type == "repeat") {
                    echo "<td> $rowTask->repeat_interval </td> </tr>";
                }
            }
        }
    }
    
    if($_SESSION['state'] == 'state_input_task')
    {
        echo "<tr> <td>$task->id</td>";
        echo "<td> <input type='text' name='var_user_id' value='$task->user_id'> </td>";
        echo "<td> <input type='text' name='var_task' value='$task->task'> </td>";
        echo "<td> <input type='text' name='var_next_step' value='$task->next_step'> </td>";
        echo "<td> <input type='text' name='var_percent_completed' value='$task->percent_completed'> </td>";
        echo "<td> <input type='checkbox' name='var_is_private' value='$task->is_private'> </td>";
        //echo "<td> <input type='text' name='var_type' value='$type'> </td>";
        echo "<td> <select name='var_type' onchange='this.form.submit();'>";
        echo "<option value='normal'".($task->type == 'normal' ? 'selected' : '').">Normal</option>";
        echo "<option value='repeat'".($task->type == 'repeat' ? 'selected' : '').">Repeat</option>";
        echo "<option value='asap'".($task->type == 'asap' ? 'selected' : '').">ASAP</option>";
        echo "</select> </td>";
        if($task->type == "asap") {
            echo "<td> <input type='datetime-local' name='var_duration' value='$task->duration'> </td>";
        }
        if($task->type == "normal" || $task->type == "repeat") {
            echo "<td> <input type='date' name='var_start_date' value='$task->start_date'> </td>";
            echo "<td> <input type='time' name='var_start_time' value='$task->start_time'> </td>";
        }
        if($task->type == "normal") {
            echo "<td> <input type='date' name='var_finish_date' value='$task->finish_date'> </td>";
            echo "<td> <input type='time' name='var_finish_time' value='$task->finish_time'> </td>";
        }
        if($task->type == "repeat") {
            echo "<td> <input type='datetime-local' name='var_repeat_interval' value='$task->repeat_interval'> </td> </tr>";
        }
    }

    echo "</table>";
}

function insert_task($conn,$selectedUserId,&$selectedTaskId,Task $task)
{
    if(filter_has_var(INPUT_POST, 'action_sql_insert_task'))
    {
        $statement = $conn->prepare(
                "INSERT INTO tasks (user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval) ".
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->bind_param("issiisssssss", $selectedUserId, $task->task, $task->next_step, $task->percent_completed, $task->is_private, $task->type, $task->duration, $task->start_date, $task->start_time, $task->finish_date, $task->finish_time, $task->repeat_interval);
        
        if ($statement->execute() === TRUE) {
            $selectedTaskId = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $selectedTaskId <br>";

            postRedirect(1);
        } else {
            echo "Error: $conn->error <br>";
        }
    }
}

function update_task($conn,$selectedTaskId,Task $task)
{
    if(filter_has_var(INPUT_POST, 'action_sql_update_task'))
    {
        $statement = $conn->prepare("UPDATE tasks SET user_id=?, task=?, next_step=?, percent_completed=?, ".
                "is_private=?, type=?, duration=?, start_date=?, ".
                "start_time=?, finish_date=?, finish_time=?, repeat_interval=? ".
                "WHERE id=?");
        $statement->bind_param("issiisssssssi", $task->user_id, $task->task, $task->next_step, $task->percent_completed, $task->is_private, $task->type, $task->duration, $task->start_date, $task->start_time, $task->finish_date, $task->finish_time, $task->repeat_interval, $selectedTaskId);

        if ($statement->execute() === TRUE) {
            echo "Record updated successfully";
            
            postRedirect(2);
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

function delete_task($conn,$selectedTaskId)
{
    if(filter_has_var(INPUT_POST, 'action_sql_delete_task'))
    {
        // TODO: delete all task days
        
        $statement = $conn->prepare("DELETE FROM tasks WHERE id=?");
        $statement->bind_param("i", $selectedTaskId);
        
        if ($statement->execute() === TRUE) {
            echo "Record deleted successfully";

            postRedirect(3);
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}