<?php

function create_tasks_table($conn)
{
    // sql to create table
    $sql = "CREATE TABLE IF NOT EXISTS tasks (
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

    if ($conn->query($sql) === TRUE) {
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
    var $type;
    var $duration;
    var $start_date;
    var $start_time;
    var $finish_date;
    var $finish_time;
    var $repeat_interval;
}

function get_task()
{
    $task = new Task();
    
    $task->id = filter_input(INPUT_POST, 'id');
    $task->user_id = filter_input(INPUT_POST, 'user_id');
    $task->task = filter_input(INPUT_POST, 'task');
    $task->next_step = filter_input(INPUT_POST, 'next_step');
    $task->percent_completed = filter_input(INPUT_POST, 'percent_completed');
    $task->is_private = filter_has_var(INPUT_POST, 'is_private');
    $task->type = filter_input(INPUT_POST, 'type');
    $task->duration = filter_input(INPUT_POST, 'duration');
    $task->start_date = filter_input(INPUT_POST, 'start_date');
    $task->start_time = filter_input(INPUT_POST, 'start_time');
    $task->finish_date = filter_input(INPUT_POST, 'finish_date');
    $task->finish_time = filter_input(INPUT_POST, 'finish_time');
    $task->repeat_interval = filter_input(INPUT_POST, 'repeat_interval');
    
    return $task;
}

function tasks_buttons($selectedTaskId)
{
    echo '<input type="submit" name="input_task" value="Add new task"/>';
    
    if($selectedTaskId != null){
        echo '<input type="submit" name="sql_delete_task" value="Delete selected task"/>';
        echo '<input type="submit" name="update_task" value="Update selected task"/>';
    }
    
    if(filter_has_var(INPUT_POST, 'input_task')){
        echo '<input type="submit" name="sql_insert_task" value="Submit new task"/>';
    }

    if(filter_has_var(INPUT_POST, 'update_task')){
        echo '<input type="submit" name="sql_update_task" value="Save changes"/>';
        echo '<br>';
        echo '<br>';
    }
}

function tasks_table($conn,$selectedUserId,&$selectedTaskId,Task $task)
{
    $sql = "SELECT id, user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval FROM tasks WHERE user_id = $selectedUserId";
    $result = $conn->query($sql);
    // TODO: if ($conn->query($sql) === TRUE)

    echo "<table>";
    echo "<tr> <th>ID</th> <th>User ID</th> <th>Task</th> <th>Next step</th> <th>Completed %</th> <th>is private</th> <th>Type</th> ".
                "<th>Duration</th> <th>Start</th> <th>Time</th> <th>Finish</th> <th>Time</th> <th>Repeat</th> </tr>";

    if ($result->num_rows > 0) {
        $rowTask = new Task();

        while($row = $result->fetch_assoc()) {
            $rowTask->id = $row["id"];
            $rowTask->user_id = $row["user_id"];
            $rowTask->task = $row["task"];
            $rowTask->next_step = $row["next_step"];
            $rowTask->percent_completed = $row["percent_completed"];
            $rowTask->is_private = $row["is_private"];
            $rowTask->type = $row["type"];
            $rowTask->duration = $row["duration"];
            $rowTask->start_date = $row["start_date"];
            $rowTask->start_time = $row["start_time"];
            $rowTask->finish_date = $row["finish_date"];
            $rowTask->finish_time = $row["finish_time"];
            $rowTask->repeat_interval = $row["repeat_interval"];

            $style = "";
            if($selectedTaskId==$rowTask->id){
                $style = "style='background:red;'";
            }
            if($selectedTaskId==$rowTask->id && filter_has_var(INPUT_POST, 'update_task')){
                echo "<tr> <td>$rowTask->id</td>".
                "<td> <input type='text' name='user_id' value='$rowTask->user_id'> </td>".
                "<td> <input type='text' name='task' value='$rowTask->task'> </td>".
                "<td> <input type='text' name='next_step' value='$rowTask->next_step'> </td>".
                "<td> <input type='text' name='percent_completed' value='$rowTask->percent_completed'> </td>".
                "<td> <input type='checkbox' name='is_private' value='$rowTask->is_private'> </td>".
                //"<td> <input type='text' name='type' value='$type'> </td>".
                "<td> <select name='type'>".
                "<option value='normal'>Normal</option>".
                "<option value='repeat'>Repeat</option>".
                "<option value='asap'>ASAP</option>".
                "</select> </td>".
                "<td> <input type='datetime-local' name='duration' value='$rowTask->duration'> </td>".
                "<td> <input type='date' name='start_date' value='$rowTask->start_date'> </td>".
                "<td> <input type='time' name='start_time' value='$rowTask->start_time'> </td>".
                "<td> <input type='date' name='finish_date' value='$rowTask->finish_date'> </td>".
                "<td> <input type='time' name='finish_time' value='$rowTask->finish_time'> </td>".
                "<td> <input type='datetime-local' name='repeat_interval' value='$rowTask->repeat_interval'> </td> </tr>";
            } else {
                echo "<tr onclick='RowClick(\"selectedTaskId\", this);' $style> <td>$rowTask->id</td>".
                "<td> $rowTask->user_id </td>".
                "<td> $rowTask->task </td>".
                "<td> $rowTask->next_step </td>".
                "<td> $rowTask->percent_completed </td>".
                "<td> $rowTask->is_private </td>".
                "<td> $rowTask->type </td>".
                "<td> $rowTask->duration </td>".
                "<td> $rowTask->start_date </td>".
                "<td> $rowTask->start_time </td>".
                "<td> $rowTask->finish_date </td>".
                "<td> $rowTask->finish_time </td>".
                "<td> $rowTask->repeat_interval </td> </tr>";
            }
        }
    }
    
    if(filter_has_var(INPUT_POST, 'input_task'))
    {
        echo "<tr> <td>$task->id</td>".
                "<td> <input type='text' name='user_id' value='$task->user_id'> </td>".
                "<td> <input type='text' name='task' value='$task->task'> </td>".
                "<td> <input type='text' name='next_step' value='$task->next_step'> </td>".
                "<td> <input type='text' name='percent_completed' value='$task->percent_completed'> </td>".
                "<td> <input type='checkbox' name='is_private' value='$task->is_private'> </td>".
                //"<td> <input type='text' name='type' value='$type'> </td>".
                "<td> <select name='type'>".
                "<option value='normal'>Normal</option>".
                "<option value='repeat'>Repeat</option>".
                "<option value='asap'>ASAP</option>".
                "</select> </td>".
                "<td> <input type='datetime-local' name='duration' value='$task->duration'> </td>".
                "<td> <input type='date' name='start_date' value='$task->start_date'> </td>".
                "<td> <input type='time' name='start_time' value='$task->start_time'> </td>".
                "<td> <input type='date' name='finish_date' value='$task->finish_date'> </td>".
                "<td> <input type='time' name='finish_time' value='$task->finish_time'> </td>".
                "<td> <input type='datetime-local' name='repeat_interval' value='$task->repeat_interval'> </td> </tr>";
    }

    echo "</table>";
}

function insert_task($conn,$selectedUserId,Task $task)
{
    $lastTask = -1;

    if(filter_has_var(INPUT_POST, 'sql_insert_task'))
    {    
        $sql = "INSERT INTO tasks (user_id, task, next_step, percent_completed, is_private, type, duration, start_date, start_time, finish_date, finish_time, repeat_interval) ".
                "VALUES ('$selectedUserId', '$task->task', '$task->next_step', '$task->percent_completed', '$task->is_private', '$task->type', '$task->duration', '$task->start_date', '$task->start_time', '$task->finish_date', '$task->finish_time', '$task->repeat_interval')";

        if ($conn->query($sql) === TRUE) {
            $lastTask = $conn->insert_id;
            echo "New record created successfully. Last inserted ID is: $lastTask <br>";

            postRedirect(1);
        } else {
            echo "Error: $sql <br> $conn->error <br>";
        }
    }
    
    return $lastTask;
}

function update_task($conn,&$selectedTaskId,Task $task)
{
    if(filter_has_var(INPUT_POST, 'sql_update_task'))
    {
        $sql = "UPDATE tasks SET user_id='$task->user_id', task='$task->task', next_step='$task->next_step', percent_completed='$task->percent_completed', ".
                "is_private='$task->is_private', type='$task->type', duration='$task->duration', start_date='$task->start_date', ".
                "start_time='$task->start_time', finish_date='$task->finish_date', finish_time='$task->finish_time', repeat_interval='$task->repeat_interval' ".
                "WHERE id=$selectedTaskId";

        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
            
            postRedirect(2);
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}

function delete_task($conn,&$selectedTaskId)
{
    if(filter_has_var(INPUT_POST, 'sql_delete_task'))
    {
        // sql to delete a record
        $sql = "DELETE FROM tasks WHERE id=$selectedTaskId";

        if ($conn->query($sql) === TRUE) {
            echo "Record deleted successfully";

            postRedirect(3);
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    }
}