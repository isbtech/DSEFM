<?php
class SEFRatingController {
    private $conn;

    public function __construct(DatabaseConnection $db) {
        $this->conn = $db->getConnection();
    }

    public function rateSEFSubmission($data, $user) {
        // Prepare SQL statement
        $stmt = $this->conn->prepare("UPDATE sef_submissions SET 
            review_status = ?, 
            reviewer_name = ?, 
            reviewer_datetime = NOW(),
            reviewer_comments = ?, 
            rating = ?, 
            rating_locked = ?
            WHERE submission_id = ?
        ");

        // Bind parameters
        $stmt->bind_param(
            "sssiis", 
            $data['review_status'], 
            $user['username'], 
            $data['reviewer_comments'], 
            $data['rating'], 
            $data['rating_locked'],
            $data['submission_id']
        );

        // Execute statement
        if ($stmt->execute()) {
            // Log audit trail
            $this->logAuditTrail($data, $user);
            $stmt->close();
            return true;
        } else {
            return false;
        }
    }

    private function logAuditTrail($data, $user) {
        $stmt = $this->conn->prepare("INSERT INTO sef_audit_trail (
            submission_id, action, old_data, new_data, 
            username, ip_address
        ) VALUES (?, ?, ?, ?, ?, ?)");

        // Get old data
        $oldData = $this->getOldSubmissionData($data['submission_id']);

        $stmt->bind_param(
            "isssss", 
            $data['submission_id'], 
            'SEF Rating Update',
            json_encode($oldData),
            json_encode($data),
            $user['username'],
            $_SERVER['REMOTE_ADDR']
        );

        $stmt->execute();
        $stmt->close();
    }

    private function getOldSubmissionData($submission_id) {
        $stmt = $this->conn->prepare("SELECT * FROM sef_submissions WHERE submission_id = ?");
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}