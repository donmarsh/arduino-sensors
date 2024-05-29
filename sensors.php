<?php
	include_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
	$connection=mysqli_connect($host,$user,$pass,$dbname);
	
	// Check connection
	if (mysqli_connect_errno())
	{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	if(isset($_POST["device_id"]) && isset($_POST["username"]) && isset($_POST["password"])  && isset($_POST["sensor_type"]) && isset($_POST["sensor"]) 
	&& isset($_POST["sensor2"]) && isset($_POST["sensor3"]))
	{
		$username = $_POST["username"];
		$password = $_POST["password"];
		$device_id = $_POST["device_id"];
		$sensor= $_POST["sensor"];
		$sensor2 = $_POST["sensor2"];
		$sensor3 = $_POST["sensor3"];

        $hash = hash('sha256', $device_id);
		$timestamp = date('Y-m-d H:i:s') ;
		$query = "SELECT * from users where device_hash='$hash'";
		$result = $connection->query($query);
		$num_rows = $result->num_rows;
		if($num_rows==0)
		{
			registerUser($username,$password, $device_id);
			$query = "SELECT * from users where device_hash='$hash'";
			$result = $connection->query($query);
			$row = $result->fetch_assoc();
			$user_id = $row['user_id'];
			saveEvent($user_id, $sensor, $sensor2,$sensor3, $timestamp);


		}
		else
		{
			$row = $result->fetch_assoc();
			$user_id = $row['user_id'];
			saveEvent($user_id, $sensor, $sensor2,$sensor3, $timestamp);

		}





	}
    else
    {
        http_response_code(400);
        echo json_encode([
        'status' => "failure",
        'message' => "Missing variables in request"]);
        exit();
    }

	function saveEvent($user_id, $sensor, $sensor2,$sensor3, $date)
	{
		global $connection;
		$sqlnewMetric = "INSERT into metrics(user_id,date,sensor,sensor2,sensor3) values ('$user_id','$date','$sensor','$sensor2','$sensor3')";
		$result = $connection->query($sqlnewMetric);
		if($result)
		{
			echo json_encode([
				'status' => "success",
				'message' => "Entry created successfully"]);
				exit();
		}
		else
		{
			echo json_encode([
				'status' => "failed",
				'message' => "Entry not created."]);
				exit();
		}

		
	}
	function registerUser($username,$password,$device_id)
    {
        global $connection;
        $hash = hash('sha256', $device_id);
        $query = "SELECT * from users where device_hash='$hash' and device_id='$device_id'";
        $result = $connection->query($query);
        $num_rows = $result->num_rows;
        if($num_rows==0)
        {
            $sqlnewUser = "insert into users(username,password,device_id,device_hash) values ('$username','$password','$device_id','$hash')";
            $result = $connection->query($sqlnewUser);
            if($result)
            {
                echo json_encode([
                    'status' => "success",
                    'message' => "User created successfully"]);
                    exit();
            }
            else
            {
                echo json_encode([
                    'status' => "failed",
                    'message' => "User not created."]);
                    exit();
            }

        }
        else
        {
            http_response_code(400);
            echo json_encode([
                'status' => "failure",
                'message' => "User already exists in the system"]);
                exit();

        }
    }


?>