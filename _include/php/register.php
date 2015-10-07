<?php
/*
* Contact Form Class
*/


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');

require_once("commonfunctions.php");
$admin_email = 'joelmeister1209@gmail.com'; // Your Email
$message_min_length = 5; // Min Message Length


class Register_Form{
	function __construct($details){
		
		$this->username = test_input(stripslashes($details['username']));
		$this->name = test_input(stripslashes($details['name']));
		$this->email = test_input(trim($details['email']));
		$this->password = hash('sha256', $details['password']);
	
		$this->response_status = 1;
		$this->response_html = '';
	}


	private function validateEmail(){
		$regex = '/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i';
	
		if($this->email == '') { 
			return false;
		} else {
			$string = preg_replace($regex, '', $this->email);
		}
		return empty($string) ? true : false;
	}


	private function validateFields(){
		// Check name
		if(!$this->name)
		{
			$this->response_html .= '<p>Please enter your name</p>';
			$this->response_status = 0;
		}

		// Check email
		if(!$this->email)
		{
			$this->response_html .= '<p>Please enter an e-mail address</p>';
			$this->response_status = 0;
		}
		
		// Check valid email
		if($this->email && !$this->validateEmail())
		{
			$this->response_html .= '<p>Please enter a valid e-mail address</p>';
			$this->response_status = 0;
		}
	}
	function dbConnect($servername="localhost",
		$username="joelmeis_joel",
		$db_pass="Spiderman0",
		$dbname="joelmeis_test_create_DB"){

		// Create connection
		$con = new mysqli($servername, $username, $db_pass, $dbname);
		if (mysqli_connect_errno($con)){
			output_error('Could not connect to database');
		}
		return $con;
	}
	function getProfile(){
		$db_field = 'USERNAME,EMAIL';
		$db_table = 'ESPORTS_USERS';
		/**/
		$con=$this->dbConnect();
		/**/
		$result = mysqli_query($con, "SELECT ".$db_field." FROM ".$db_table." WHERE EMAIL = '" . $this->email . "'");
		
		if($row = mysqli_fetch_array($result)){
//email already exists
			$this->response_status = 0;	
			$this->response_html .= '<p>Email address already in use</p>';
		}else{
			$result = mysqli_query($con, "SELECT ".$db_field." FROM ".$db_table." WHERE USERNAME = '" . $this->username . "'");		
			if($row = mysqli_fetch_array($result)){
//username already exists
				$this->response_status = 0;	
				$this->response_html .= '<p>Username already in use</p>';
			}else{
				if ($this->update($con)){
					$this->response_html .= '<p>Success!</p>';
				}
			}
		}
		mysqli_close($con);
	}
	function update($con){		
		$db_field = 'USERNAME,EMAIL,ACTUALNAME,PASSWORD';
		$db_table = 'ESPORTS_USERS';
		$db_value = "'".$this->username."','".$this->email."','".$this->name."','".$this->password."'";
		/**/
		$sql="INSERT INTO ".$db_table."  (".$db_field.") VALUES (".$db_value.");";
		/**/
		if (!mysqli_query($con,$sql)){
			$this->response_html .= 'Failed to insert : '.mysqli_error($con);
			$this->response_status = 0;	
			return false;
		}
		return true;
	}
	function sendRequest(){
		$this->validateFields();
		$this->getProfile();
		
		$response = array();
		$response['status'] = $this->response_status;	
		$response['html'] = $this->response_html;
		
		echo json_encode($response);
	}
	
}
$register_form = new Register_Form($_POST, $admin_email, $message_min_length);
$register_form->sendRequest();

?>