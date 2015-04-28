<?php session_start();
// If user is logged in, header them away
/*if (isset($_SESSION["username"])) {
	header("location: message.php?msg=You are already logged in");
	exit();
}*/
?>
<?php // Ajax calls this NAME CHECK code to execute
	if (isset($_POST["usernamecheck"])) {
		include_once ("php_includes/db_conx.php");
		$username = preg_replace('#[^a-z0-9]#i', '', $_POST['usernamecheck']);
		$sql = "SELECT id FROM users WHERE username='$username' LIMIT 1";
		$query = mysqli_query($db_conx, $sql);
		$uname_check = mysqli_num_rows($query);
		if (strlen($username) < 3 || strlen($username) > 16) {
			echo '<strong style="color:#F00;">3 - 16 characters please</strong>';
			exit();
		}
		if (is_numeric($username[0])) {
			echo '<strong style="color:#F00;">Usernames must begin with a letter</strong>';
			exit();
		}
		if ($uname_check < 1) {
			echo '<strong style="color:#009900;">' . $username . ' is OK</strong>';
			exit();
		} else {
			echo '<strong style="color:#F00;">' . $username . ' is taken</strong>';
			exit();
		}
	}
?><?php // Ajax calls this REGISTRATION code to execute
		if (isset($_POST["u"])) {
			// CONNECT TO THE DATABASE
			include_once ("php_includes/db_conx.php");
			// GATHER THE POSTED DATA INTO LOCAL VARIABLES
			$u = preg_replace('#[^a-z0-9]#i', '', $_POST['u']);
			//$e = mysqli_real_escape_string($db_conx, $_POST['e']);
			$e = $_POST['e'];
			$p = $_POST['p'];
			$g = preg_replace('#[^a-z]#', '', $_POST['g']);
			$c = preg_replace('#[^a-z ]#i', '', $_POST['c']);
			// GET USER IP ADDRESS
			$ip = preg_replace('#[^0-9.]#', '', getenv('REMOTE_ADDR'));
			// DUPLICATE DATA CHECKS FOR USERNAME AND EMAIL
			$sql = "SELECT id FROM users WHERE username='$u' LIMIT 1";
			$query = mysqli_query($db_conx, $sql);
			$u_check = mysqli_num_rows($query);
			// -------------------------------------------
			$sql = "SELECT id FROM users WHERE email='$e' LIMIT 1";
			$query = mysqli_query($db_conx, $sql);
			$e_check = mysqli_num_rows($query);
			// FORM DATA ERROR HANDLING
			if ($u == "" || $e == "" || $p == "" || $g == "" || $c == "") {
				echo "The form submission is missing values.";
				exit();
			} else if ($u_check > 0) {
				echo "The username you entered is already taken";
				exit();
			} else if ($e_check > 0) {
				echo "That email address is already in use in the system";
				exit();
			} else if (strlen($u) < 3 || strlen($u) > 16) {
				echo "Username must be between 3 and 16 characters";
				exit();
			} else if (is_numeric($u[0])) {
				echo 'Username cannot begin with a number';
				exit();
			} else {
				// END FORM DATA ERROR HANDLING
				// Begin Insertion of data into the database
				// Hash the password and apply your own mysterious unique salt
				$p_hash = md5($p);
				/*$cryptpass = crypt($p);
				 include_once ("php_includes/randStrGen.php");
				 $p_hash = randStrGen(20)."$cryptpass".randStrGen(20);*/
				// Add user info into the database table for the main site table
				$sql = "INSERT INTO users (username, email, password, gender, country, ip, signup, lastlogin, notescheck)       
		        VALUES('$u','$e','$p_hash','$g','$c','$ip',now(),now(),now())";
				$query = mysqli_query($db_conx, $sql);
				$uid = mysqli_insert_id($db_conx);
				// Establish their row in the useroptions table
				$sql = "INSERT INTO useroptions (id, username, background) VALUES ('$uid','$u','original')";
				$query = mysqli_query($db_conx, $sql);
				// Create directory(folder) to hold each user's files(pics, MP3s, etc.)
				if (!file_exists("user/$u")) {
					mkdir("user/$u", 0755);
				}
				// Email the user their activation link
				$to = "$e";
				$from = "garden.project.setju@gmail.com";
				$subject = 'Garden Account Activation';

				$message = '<!DOCTYPE html>
		 <html>
		 <head>
		 <meta charset="UTF-8"><title>yoursitename Message</title>
		 </head>
		 <body style="margin:0px; font-family:Tahoma, Geneva, sans-serif;"><div style="padding:10px; background:#333; font-size:24px; color:#CCC;"><a href="http://localhost/xampp/phptest/SocialNetwork/root/signup.php">
		 </a>Garden Project Account Activation</div><div style="padding:24px; font-size:17px;">Hello ' . $u . ',<br /><br />Click the link below to activate your account when ready:<br /><br />
		 <a href="http://localhost/xampp/phptest/Social-Network/root/activation.php?id=' . $uid . '&u=' . $u . '&e=' . $e . '&p=' . $p_hash . '">
		 Click here to activate your account now</a><br /><br />Login after successful activation using your:<br />* E-mail Address: <b>' . $e . '</b></div></body></html>';

				$headers = "From: $from\n";
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\n";
				//send email
				mail($to, $subject, $message, $headers);
				echo "signup_success";
				exit();

				//deepak's code begins
				/*
				 //require '../PHPMailer/PHPMailerAutoload.php';
				 //include '../';
				 include_once ("PHPMailer/PHPMailerAutoload.php");
				 include_once ("privateEmailDetails/emailDetails.php");
				 $mail = new PHPMailer;
				 //$mail->SMTPDebug = 3;                               // Enable verbose debug outpu

				 $mail -> isSMTP();
				 // Set mailer to use SMTP

				 $mail -> Host = 'ssl://smtp.gmail.com';
				 // Specify main and backup SMTP servers

				 $mail -> SMTPAuth = true;
				 // Enable SMTP authentication

				 $mail -> Username = '$username';
				 // SMTP username

				 $mail -> Password = '$password';
				 // SMTP password

				 $mail -> SMTPSecure = 'ssl';
				 // Enable TLS encryption, `ssl` also accepted

				 $mail -> Port = 465;
				 // TCP port to connect to

				 $mail -> From = 'nikipentium@gmail.com';

				 $mail -> FromName = 'Nikhil Pereira(Admin)';

				 $mail -> addAddress('nikipentium@gmail.com', 'Pereira, Nikhil');
				 // Add a recipient

				 $mail -> addAddress('$e');

				 $mail -> isHTML(true);
				 // Set email format to HTML

				 $subject = 'Garden Account Activation';

				 $mail -> Subject = $subject;

				 $message = '<!DOCTYPE html>
				 <html>
				 <head>
				 <meta charset="UTF-8"><title>yoursitename Message</title>
				 </head>
				 <body style="margin:0px; font-family:Tahoma, Geneva, sans-serif;"><div style="padding:10px; background:#333; font-size:24px; color:#CCC;">
				 </a>yoursitename Account Activation</div><div style="padding:24px; font-size:17px;">Hello '.$u.',<br /><br />Click the link below to activate your account when ready:<br /><br />
				 <a href="http://www.yoursitename.com/activation.php?id='.$uid.'&u='.$u.'&e='.$e.'&p='.$p_hash.'">
				 Click here to activate your account now</a><br /><br />Login after successful activation using your:<br />* E-mail Address: <b>'.$e.'</b></div></body></html>';

				 $mail -> Body = $message;

				 if (!$mail -> send()) {

				 echo 'Message could not be sent.';

				 echo 'Mailer Error: ' . $mail -> ErrorInfo;

				 } else {

				 echo 'Message has been sent';

				 }

				 // deepak's code ends*/
			}
			exit();
		}
	?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Social Network Sign Up</title>
<link rel="stylesheet" href="style/style.css">
<link rel="stylesheet" href="style/signup.css">
<script src="js/main.js"></script>
<script src="js/ajax.js"></script>
<script src="js/fadeEffects.js"></script>
<script src="js/signup.js"></script>
</head>
<body>
<?php
include_once ("template_pageTop.php");
 ?>
<div id="pageMiddle">
   <h3>Sign Up Here</h3>
  <form name="signupform" id="signupform" onsubmit="return false;">
    <div>Username: </div>
    <input id="username" type="text" onblur="checkusername()" onkeyup="restrict('username')" maxlength="16">
    <span id="unamestatus"></span>
    <div>Email Address:</div>
    <input id="email" type="text" onfocus="emptyElement('status')" onkeyup="restrict('email')" maxlength="88">
    <div>Create Password:</div>
    <input id="pass1" type="password" onfocus="emptyElement('status')" maxlength="160">
    <div>Confirm Password:</div>
    <input id="pass2" type="password" onfocus="emptyElement('status')" maxlength="16">
    <div>Gender:</div>
    <select id="gender" onfocus="emptyElement('status')">
      <option value=""></option>
      <option value="m">Male</option>
      <option value="f">Female</option>
    </select>
    <div>Country:</div>
    <select id="country" onfocus="emptyElement('status')">
      <!--<?php include_once("template_country_list.php"); ?>-->
      <option value="m">India</option>
    </select>
    <br /><br />
    <button id="signupbtn" onclick="signup()">Create Account</button>
    <span id="status"></span>
  </form>
</div>
<?php
include_once ("template_pageBottom.php");
?>
</body>
</html>