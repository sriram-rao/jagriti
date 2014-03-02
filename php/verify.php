
<!--Mail Attachment code-->
<?php 
function mailAttachments($to, $from, $subject, $message, $attachments = array(), $headers = array(), $additional_parameters = '') {
	$headers['From'] = $from;

	// Define the boundray we're going to use to separate our data with.
	$mime_boundary = '==MIME_BOUNDARY_' . md5(time());

	// Define attachment-specific headers
	$headers['MIME-Version'] = '1.0';
	$headers['Content-Type'] = 'multipart/mixed; boundary="' . $mime_boundary . '"';

	// Convert the array of header data into a single string.
	$headers_string = '';
	foreach($headers as $header_name => $header_value) {
		if(!empty($headers_string)) {
			$headers_string .= "\r\n";
		}
		$headers_string .= $header_name . ': ' . $header_value;
	}

	// Message Body
	$message_string  = '--' . $mime_boundary;
	$message_string .= "\r\n";
	$message_string .= 'Content-Type: text/plain; charset="iso-8859-1"';
	$message_string .= "\r\n";
	$message_string .= 'Content-Transfer-Encoding: 7bit';
	$message_string .= "\r\n";
	$message_string .= "\r\n";
	$message_string .= $message;
	$message_string .= "\r\n";
	$message_string .= "\r\n";

	// Add attachments to message body
	foreach($attachments as $local_filename => $attachment_filename) {
		if(is_file($local_filename)) {
			$message_string .= '--' . $mime_boundary;
			$message_string .= "\r\n";
			$message_string .= 'Content-Type: application/octet-stream; name="' . $attachment_filename . '"';
			$message_string .= "\r\n";
			$message_string .= 'Content-Description: ' . $attachment_filename;
			$message_string .= "\r\n";

			$fp = @fopen($local_filename, 'rb'); // Create pointer to file
			$file_size = filesize($local_filename); // Read size of file
			$data = @fread($fp, $file_size); // Read file contents
			$data = chunk_split(base64_encode($data)); // Encode file contents for plain text sending

			$message_string .= 'Content-Disposition: attachment; filename="' . $attachment_filename . '"; size=' . $file_size.  ';';
			$message_string .= "\r\n";
			$message_string .= 'Content-Transfer-Encoding: base64';
			$message_string .= "\r\n\r\n";
			$message_string .= $data;
			$message_string .= "\r\n\r\n";
		}
	}

	// Signal end of message
	$message_string .= '--' . $mime_boundary . '--';

	// Send the e-mail.
	return mail($to, $subject, $message_string, $headers_string, $additional_parameters);
}
?>

<!-- reCAPTCHA and mailer stuff -->
<?php
  require_once('recaptchalib.php');
  $privatekey = "6Le3F-8SAAAAAMVsukqZkG2d4_JSDy47lEJ1EmXP";
  $resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

  if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
    die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
         "(reCAPTCHA said: " . $resp->error . ")");
  } else {
    // Your code here to handle a successful verification
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = explode(".", $_FILES["file"]["name"]);
    $extension = end($temp);
		if ((($_FILES["file"]["type"] == "image/gif")
		|| ($_FILES["file"]["type"] == "image/jpeg")
		|| ($_FILES["file"]["type"] == "image/jpg")
		|| ($_FILES["file"]["type"] == "image/pjpeg")
		|| ($_FILES["file"]["type"] == "image/x-png")
		|| ($_FILES["file"]["type"] == "image/png"))
		&& ($_FILES["file"]["size"] < 20000)
		&& in_array($extension, $allowedExts))
  	{
  		if ($_FILES["file"]["error"] > 0)
    	{
    		echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
    	}
  		else
    	{
    		echo "Upload: " . $_FILES["file"]["name"] . "<br>";
    		echo "Type: " . $_FILES["file"]["type"] . "<br>";
    		echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
    		echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

		    if (file_exists("upload/" . $_FILES["file"]["name"]))
   		  {
      			echo $_FILES["file"]["name"] . " already exists. ";
     		}
    		else
      	{
      			move_uploaded_file($_FILES["file"]["tmp_name"],
      			"upload/" . $_FILES["file"]["name"]);
      			echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
      	}
    	}
  	}
		else
  	{
  		echo "Invalid file";
  	}

    $address=$_POST['address'];
    $emailid=$_POST['email'];
    $description=$_POST['description'];
    $name = $_POST['name'];
    $number = $_POST['number'];
    $category = $_POST['category'];
    $n=count($category);

    $categoryList="";
    for ($i=0; $i < $n; $i++) { 
      $categoryList .= $category[$i]."\n";
    }

    $username = "a2414660_jagriti";
    $password = "projasha1234";

    $attachments = array(
		'/public-html/img/logo.png' => 'first-attachment.png',
		);
    #Mailer without attachment
    #Testing Attachment. Might result in delayed mail!
    $to="jagritiproject@gmail.com";
    $from=$emailid;
    $headers="";
    $subject = "Complaint Submission";
    $body="Complaint received from $emailid .\nThe address of the location is:\n$address.\nThe categories under which complaints has been received is:\n$categoryList\nDescription is:\n$description\nattached are: print_r($attachments)\n";

    $status= mailAttachments($to, $from, $subject, $body, $attachments, $headers);
?>
<!--Database Stuff-->
<?php    
    #storing in database
    try {
          $conn = new PDO('mysql:host=mysql3.000webhost.com;dbname=a2414660_maindb', $username, $password);
         # $conn = new PDO('mysql:host=localhost:3036;dbname=jtest', $username, $password);
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          # Prepare Query
          $stmt = $conn->prepare('INSERT INTO USERS VALUES(:address, :email, :description, :name, :num, :categories)');
          $stmt->execute(array(
              ':name' => $name,
              ':num' => $number,
              ':description' => $description,
              ':email' => $emailid,
              ':address' => $address,
              ':categories' => $categoryList,
              #':file'
            ));
          #link to successful submission page
          header("Location: http://jagriti.site90.net/success.html"); /* Redirect browser */
					exit();
    } catch(PDOException $e) {
    		echo "Form submission failed. Please try again.";
        echo 'Error: ' . $e->getMessage();
    }
  }
  ?>
