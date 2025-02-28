<?php
class DelegateMeetingController {
    private $conn;

    public function __construct(DatabaseConnection $db) {
        $this->conn = $db->getConnection();
    }

    public function enrollDelegateForMeeting($data) {
        // Generate Badge Number
        $badge_number = $this->generateBadgeNumber($data['meeting_id']);

        // Prepare SQL statement
        $stmt = $this->conn->prepare("INSERT INTO delegate_meeting_data (
            delegate_uid, mobile_number, name, meeting_id, badge_number
        ) VALUES (?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param(
            "issss", 
            $data['delegate_uid'], 
            $data['mobile_number'], 
            $data['name'], 
            $data['meeting_id'], 
            $badge_number
        );

        // Execute statement
        if ($stmt->execute()) {
            $stmt->close();
            return $badge_number;
        } else {
            return false;
        }
    }

    private function generateBadgeNumber($meeting_id) {
        // Generate unique badge number
        $serial = $this->getNextBadgeSerial($meeting_id);
        return $meeting_id . '-' . str_pad($serial, 4, '0', STR_PAD_LEFT);
    }

    private function getNextBadgeSerial($meeting_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM delegate_meeting_data WHERE meeting_id = ?");
        $stmt->bind_param("s", $meeting_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] + 1;
    }

    public function getDelegatesPreviousMeetings($delegate_uid) {
        $stmt = $this->conn->prepare("
            SELECT m.meeting_id, m.meeting_host, m.meeting_dates, 
                   dmd.feedback_submitted, dmd.permit
            FROM delegate_meeting_data dmd
            JOIN meetings m ON dmd.meeting_id = m.meeting_id
            WHERE dmd.delegate_uid = ?
            ORDER BY m.meeting_dates DESC
            LIMIT 3
        ");
        $stmt->bind_param("i", $delegate_uid);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}