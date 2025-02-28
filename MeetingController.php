<?php
class MeetingController {
    private $conn;

    public function __construct(DatabaseConnection $db) {
        $this->conn = $db->getConnection();
    }

    public function createMeeting($data) {
        // Generate Meeting ID
        $meeting_id = $this->generateMeetingId($data['meeting_host'], $data['city']);

        // Prepare SQL statement
        $stmt = $this->conn->prepare("INSERT INTO meetings (
            meeting_id, meeting_title, meeting_theme, meeting_venue, 
            meeting_host, meeting_dates, meeting_timing, 
            registration_contact, special_instructions, meeting_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param(
            "ssssssssss", 
            $meeting_id, 
            $data['meeting_title'], 
            $data['meeting_theme'], 
            $data['meeting_venue'], 
            $data['meeting_host'], 
            $data['meeting_dates'], 
            $data['meeting_timing'], 
            $data['registration_contact'], 
            $data['special_instructions'], 
            $data['meeting_status']
        );

        // Execute statement
        if ($stmt->execute()) {
            $stmt->close();
            return $meeting_id;
        } else {
            return false;
        }
    }

    private function generateMeetingId($host, $city) {
        // Generate unique meeting ID
        $cityCode = strtoupper(substr($city, 0, 3));
        $serial = $this->getNextMeetingSerial();
        return $host . $cityCode . str_pad($serial, 4, '0', STR_PAD_LEFT);
    }

    private function getNextMeetingSerial() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM meetings");
        $row = $result->fetch_assoc();
        return $row['count'] + 1;
    }

    public function getMeetingDetails($meeting_id) {
        $stmt = $this->conn->prepare("SELECT * FROM meetings WHERE meeting_id = ?");
        $stmt->bind_param("s", $meeting_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}