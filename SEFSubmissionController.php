<?php
class SEFController {
    private $conn;

    public function __construct(DatabaseConnection $db) {
        $this->conn = $db->getConnection();
    }

    public function submitSEFForm($data) {
        // Generate GUID
        $submission_guid = $this->generateGUID();

        // Prepare SQL statement
        $stmt = $this->conn->prepare("INSERT INTO sef_submissions (
            delegate_uid, mobile_number, meeting_id, submission_type, 
            submission_data, submission_guid, ip_address, device_info
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param(
            "issssss", 
            $data['delegate_uid'], 
            $data['mobile_number'], 
            $data['meeting_id'], 
            $data['submission_type'],
            $data['submission_data'],
            $submission_guid,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        );

        // Execute statement
        if ($stmt->execute()) {
            $submission_id = $this->conn->insert_id;
            $stmt->close();
            
            // Send email to admin
            $this->sendAdminNotification($submission_id, $data);

            return $submission_guid;
        } else {
            return false;
        }
    }

    private function generateGUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function sendAdminNotification($submission_id, $data) {
        // Implement email sending logic
        $to = 'admin@example.com';
        $subject = 'New SEF Submission';
        $message = "Delegate UID: " . $data['delegate_uid'] . "\n";
        $message .= "Submission Type: " . $data['submission_type'] . "\n";
        // Add more details

        mail($to, $subject, $message);
    }
}