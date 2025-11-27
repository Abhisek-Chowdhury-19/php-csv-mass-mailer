<?php
// stop error
error_reporting(0);
ini_set('display_errors', 0);


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$statusMsg = '';
$statusType = '';

if (isset($_POST['send_blast'])) {
    
    // 1. Capture Basic Configuration
    $senderName = $_POST['sender_name'];
    $senderEmail = $_POST['sender_email'];
    $senderPass = $_POST['sender_pass'];
    $subject = $_POST['subject'];
    $templateBody = $_POST['html_body']; // Keep the original template safe

    // Capture Advanced Options
    $replyTo = !empty($_POST['reply_to']) ? $_POST['reply_to'] : '';
    $cc = !empty($_POST['cc_email']) ? $_POST['cc_email'] : '';
    $bcc = !empty($_POST['bcc_email']) ? $_POST['bcc_email'] : '';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $senderEmail;
        $mail->Password   = $senderPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->isHTML(true);

        // Process CSV
        if (isset($_FILES['receiver_file']) && $_FILES['receiver_file']['error'] == 0) {
            
            $fileName = $_FILES['receiver_file']['tmp_name'];
            $fileHandle = fopen($fileName, "r");
            
            // 2. GET HEADERS (The first row)
            $headers = fgetcsv($fileHandle, 1000, ",");
            
            // Clean headers (remove spaces, make lowercase for easier matching)
            // e.g., "Full Name" becomes "full name"
            $cleanHeaders = array_map(function($h) {
                return trim(strtolower($h)); 
            }, $headers);

            // Find which column is the email
            // We look for a header named 'email'. If not found, we assume column 0.
            $emailColIndex = array_search('email', $cleanHeaders);
            if ($emailColIndex === false) $emailColIndex = 0;

            $count = 0;
            
            // 3. LOOP THROUGH DATA ROWS
            while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
                
                // Skip empty rows
                if(empty($row) || count($row) < 1) continue;

                // Ensure row has same number of cols as headers to avoid errors
                if(count($row) != count($headers)) continue; 

                // Get Email Address
                $recipientEmail = trim($row[$emailColIndex]);

                if (filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                    
                    // --- RESET MAILER FOR THIS ITERATION ---
                    $mail->clearAddresses();
                    $mail->clearCCs();
                    $mail->clearBCCs();
                    $mail->clearReplyTos();

                    // --- SET HEADERS ---
                    $mail->setFrom($senderEmail, $senderName);
                    
                    // Add Reply-To
                    if($replyTo) {
                        $mail->addReplyTo($replyTo);
                    } else {
                        // Default logic: If no reply-to set, replies go to sender
                        $mail->addReplyTo($senderEmail); 
                    }

                    // Add CC/BCC (WARNING: This adds them to EVERY email sent)
                    if($cc) $mail->addCC($cc);
                    if($bcc) $mail->addBCC($bcc);

                    $mail->addAddress($recipientEmail);

                    // --- DYNAMIC VARIABLE REPLACEMENT ---
                    $currentBody = $templateBody;
                    $currentSubject = $subject;

                    // Create an associative array: ['name' => 'John', 'city' => 'NY']
                    $rowData = array_combine($cleanHeaders, $row);

                    foreach ($rowData as $key => $value) {
                        // Replace {{key}} with value in Body
                        $placeholder = "{{" . $key . "}}";
                        $currentBody = str_replace($placeholder, $value, $currentBody);
                        
                        // Replace {{key}} in Subject as well (optional bonus)
                        $currentSubject = str_replace($placeholder, $value, $currentSubject);
                    }

                    $mail->Subject = $currentSubject;
                    $mail->Body    = $currentBody;

                    $mail->send();
                    $count++;
                }
            }
            fclose($fileHandle);
            
            $statusMsg = "Broadcast successful! Sent to $count recipients.";
            $statusType = "success";
            
        } else {
            throw new Exception("Please upload a valid CSV file.");
        }

    } catch (Exception $e) {
        $statusMsg = "Error: {$mail->ErrorInfo}";
        $statusType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Mailer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="author" content="Abhisek">
    <style>
        :root { --primary: #4F46E5; --primary-hover: #4338ca; --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); --glass-bg: rgba(255, 255, 255, 0.95); --border-color: #e2e8f0; --text-dark: #1e293b; --text-gray: #64748b; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gradient); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .mailer-container { background: var(--glass-bg); width: 100%; max-width: 900px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; display: flex; flex-direction: column; }
        .header { padding: 25px 40px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .header h2 { color: var(--text-dark); font-weight: 600; font-size: 1.5rem; }
        .header span { font-size: 0.85rem; color: var(--primary); background: rgba(79, 70, 229, 0.1); padding: 5px 12px; border-radius: 20px; font-weight: 600; }
        .form-body { padding: 40px; }
        .grid-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .input-group { display: flex; flex-direction: column; }
        label { font-size: 0.85rem; color: var(--text-gray); margin-bottom: 8px; font-weight: 500; }
        input, textarea { padding: 12px 16px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 0.95rem; color: var(--text-dark); background: #f8fafc; transition: all 0.3s ease; }
        input:focus, textarea:focus { outline: none; border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .editor-container { border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; background: #fff; }
        .html-input { width: 100%; border: none; padding: 15px; min-height: 250px; resize: vertical; border-radius: 0; }
        .html-input:focus { box-shadow: none; }
        .footer { padding: 20px 40px; background: #f8fafc; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 15px; }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; transition: 0.2s; }
        .btn-secondary { background: transparent; color: var(--text-gray); border: 1px solid var(--border-color); }
        .btn-secondary:hover { background: #e2e8f0; color: var(--text-dark); }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.3); display: flex; align-items: center; gap: 8px; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-size: 0.9rem; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        /* Advanced Options Toggle */
        .advanced-options { background: #f1f5f9; padding: 20px; border-radius: 8px; margin-bottom: 25px; display: none; }
        .toggle-btn { color: var(--primary); font-size: 0.9rem; font-weight: 600; cursor: pointer; margin-bottom: 10px; display: inline-block; }
        
        @media (max-width: 768px) {
            .grid-row { grid-template-columns: 1fr; }
            .header, .form-body, .footer { padding: 20px; }
        }
    </style>
    <script>
        function toggleAdvanced() {
            var x = document.getElementById("advancedArea");
            if (x.style.display === "block") {
                x.style.display = "none";
            } else {
                x.style.display = "block";
            }
        }
    </script>
</head>
<body>

<form class="mailer-container" action="" method="POST" enctype="multipart/form-data">
    
    <div class="header">
        <h2><i class="fa-solid fa-paper-plane" style="margin-right: 10px; color: var(--primary);"></i>Pro Mail Blast</h2>
        <span><i class="fa-solid fa-circle-check"></i> System Ready</span>
    </div>

    <div class="form-body">
        
        <?php if(!empty($statusMsg)): ?>
            <div class="alert alert-<?php echo $statusType; ?>">
                <?php echo $statusMsg; ?>
            </div>
        <?php endif; ?>

        <h4 style="margin-bottom: 15px; color: var(--text-dark);">Sender Configuration</h4>
        <div class="grid-row">
            <div class="input-group">
                <label>Sender Name</label>
                <input type="text" name="sender_name" placeholder="e.g. Marketing Team" required>
            </div>
            <div class="input-group">
                <label>Sender Email ID</label>
                <input type="email" name="sender_email" placeholder="user@gmail.com" required>
            </div>
            <div class="input-group">
                <label>App Password</label>
                <input type="password" name="sender_pass" placeholder="••••••••••••" required>
            </div>
        </div>

        <div class="toggle-btn" onclick="toggleAdvanced()">
            <i class="fa-solid fa-gear"></i> Show Advanced (CC, BCC, Reply-To)
        </div>
        <div id="advancedArea" class="advanced-options">
            <div class="grid-row">
                <div class="input-group">
                    <label>Reply-To (Optional)</label>
                    <input type="email" name="reply_to" placeholder="noreply@domain.com">
                </div>
                <div class="input-group">
                    <label>CC (Optional)</label>
                    <input type="email" name="cc_email" placeholder="manager@domain.com">
                </div>
                <div class="input-group">
                    <label>BCC (Optional)</label>
                    <input type="email" name="bcc_email" placeholder="archive@domain.com">
                </div>
            </div>
        </div>

        <div class="input-group" style="margin-bottom: 25px;">
            <label>Receiver List (CSV with Headers)</label>
            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 5px;">
                Required: 1st row must contain headers (e.g., name, email, discount). 
            </div>

            <div class="receivers-area">
              <input type="file" name="receiver_file" accept=".csv" required style="margin-bottom: 10px;">
            </div>
        </div>
        <div class="footer">
            <a href="/Sample-CSV.csv" class="btn btn-primary">Download CSV Sample</a>
        </div>

        <div class="input-group" style="margin-bottom: 15px;">
            <label>Email Subject</label>
            <input type="text" name="subject" placeholder="Hello {{name}}!" required>
        </div>

        <div class="input-group">
            <label>HTML Message Body</label>
            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 5px;">
                Use double braces matching your CSV headers. Example: <b>{{name}}</b> or <b>{{company}}</b>
            </div>
            <div class="editor-container">
                <textarea class="html-input" name="html_body" placeholder="Hi {{name}}, here is your code: {{code}}"></textarea>
            </div>
        </div>

    </div>

    <div class="footer">
        <button type="submit" name="send_blast" class="btn btn-primary">
            Send Broadcast <i class="fa-solid fa-rocket"></i>
        </button>
    </div>

</form>

</body>
</html>