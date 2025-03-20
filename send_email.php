<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

// Validate inputs
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";

// Email to admin
$admin_subject = "New Contact Form Submission from $name";
$admin_message = "
<html>
<body>
    <h2>New Contact Form Submission</h2>
    <p><strong>Name:</strong> $name</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Message:</strong></p>
    <p>$message</p>
</body>
</html>
";

// Email to sender
$sender_subject = "Thank you for contacting AI Test Generator";
$sender_message = "
<html>
<body>
    <h2>Thank you for contacting us!</h2>
    <p>Dear $name,</p>
    <p>We have received your message and will get back to you as soon as possible.</p>
    <p>Here's a copy of your message:</p>
    <p>$message</p>
    <br>
    <p>Best regards,</p>
    <p>AI Test Generator Team</p>
</body>
</html>
";

try {
    // Send emails
    $admin_sent = mail('aistudentsoffical@gmail.com', $admin_subject, $admin_message, $headers);
    $sender_sent = mail($email, $sender_subject, $sender_message, $headers);

    if (!$admin_sent) {
        error_log("Failed to send admin email to aistudentsoffical@gmail.com");
    }
    if (!$sender_sent) {
        error_log("Failed to send confirmation email to $email");
    }

    if ($admin_sent && $sender_sent) {
        echo json_encode(['success' => true, 'message' => 'Thank you for your message. We will get back to you soon!']);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'There was an error sending your message. Please try again later.',
            'debug' => [
                'admin_sent' => $admin_sent,
                'sender_sent' => $sender_sent,
                'error' => error_get_last()
            ]
        ]);
    }
} catch (Exception $e) {
    error_log("Email sending error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.',
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
}
?> 