<?php

// Replace this with your own email address
$siteOwnersEmail = 'djaculture@gmail.com';
$siteOwnersName  = "Douglas James";
$sendgridApiKey  = getenv( 'SENDGRID_APIKEY' );
$url             = 'https://api.sendgrid.com/';
$error           = null;

if ( $_POST ) {

    $name            = trim( stripslashes( $_POST['contactName'] ) );
    $email           = trim( stripslashes( $_POST['contactEmail'] ) );
    $subject         = trim( stripslashes( $_POST['contactSubject'] ) );
    $contact_message = trim( stripslashes( $_POST['contactMessage'] ) );

    // Check Name
    if ( strlen( $name ) < 2 ) {
        $error['name'] = "Please enter your name.";
    }
    // Check Email
    if ( ! preg_match( '/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*+[a-z]{2}/is', $email ) ) {
        $error['email'] = "Please enter a valid email address.";
    }
    // Check Message
    if ( strlen( $contact_message ) < 15 ) {
        $error['message'] = "Please enter your message. It should have at least 15 characters.";
    }
    // Subject
    if ( $subject == '' ) {
        $subject = "Contact Form Submission";
    }


    // Set Message
    $htmlMessage = "
Email from: $name <br />
Email address: $email <br />
Message: <br />
$contact_message
<br /> ----- <br /> This email was sent from your site's contact form. <br />
";

    $textMessage = "
Email from: $name
Email address: $email
Message:
$contact_message

-----
This email was sent from your site's contact form.";

    $params = [
        'to'       => $siteOwnersEmail,
        'toname'   => $siteOwnersName,
        'from'     => "no-reply@djaculture.com",
        'fromname' => "Website Contact",
        'subject'  => "[Website Contact] $subject",
        'text'     => $textMessage,
        'html'     => $htmlMessage,
        'replyto'  => $email,
    ];

    if ( ! $error ) {

        $request = $url . 'api/mail.send.json';

        // Generate curl request
        $session = curl_init( $request );
        // Tell PHP not to use SSLv3 (instead opting for TLS)
        curl_setopt( $session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2 );
        curl_setopt( $session, CURLOPT_HTTPHEADER, array( 'Authorization: Bearer ' . $sendgridApiKey ) );
        // Tell curl to use HTTP POST
        curl_setopt( $session, CURLOPT_POST, true );
        // Tell curl that this is the body of the POST
        curl_setopt( $session, CURLOPT_POSTFIELDS, $params );
        // Tell curl not to return headers, but do return the response
        curl_setopt( $session, CURLOPT_HEADER, false );
        curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );

        // obtain response
        $response = curl_exec( $session );
        curl_close( $session );

        $jsonResponse = json_decode( $response );

        if ( $jsonResponse === null && json_last_error() !== JSON_ERROR_NONE ) {
            echo "Something went wrong. Please try again.";
            exit( 1 );
        }

        if ( $jsonResponse->message == "success" ) {
            echo "OK";
        } else {
            echo "Something went wrong. Please try again.";
        }


    } # end if - no validation error

    else {

        $response = ( isset( $error['name'] ) ) ? $error['name'] . "<br /> \n" : null;
        $response .= ( isset( $error['email'] ) ) ? $error['email'] . "<br /> \n" : null;
        $response .= ( isset( $error['message'] ) ) ? $error['message'] . "<br />" : null;

        echo $response;

    } # end if - there was a validation error

}
