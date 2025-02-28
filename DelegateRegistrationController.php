<?php
class DelegateController {
    private $conn;

    public function __construct(DatabaseConnection $db) {
        $this->conn = $db->getConnection();
    }

    public function registerDelegate($data) {
        // Prepare SQL statement
        $stmt = $this->conn->prepare("INSERT INTO delegates (
            name, father_name, dob, mobile_number, address, city, state, 
            pincode, church_name, pastor_name, baptism, 
            holy_spirit_anointing, spiritual_calling, 
            ministry_involvement, salvation_testimony
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters
        $stmt->bind_param(
            "sssssssssssssss", 
            $data['name'], 
            $data['father_name'], 
            $data['dob'], 
            $data['mobile_number'], 
            $data['address'], 
            $data['city'], 
            $data['state'], 
            $data['pincode'], 
            $data['church_name'], 
            $data['pastor_name'], 
            $data['baptism'], 
            $data['holy_spirit_anointing'], 
            $data['spiritual_calling'], 
            $data['ministry_involvement'], 
            $data['salvation_testimony']
        );

        // Execute statement
        if ($stmt->execute()) {
            $delegate_uid = $this->conn->insert_id;
            $stmt->close();
            return $delegate_uid;
        } else {
            // Handle error
            return false;
        }
    }

    public function getDelegateByMobile($mobile_number) {
        $stmt = $this->conn->prepare("SELECT * FROM delegates WHERE mobile_number = ?");
        $stmt->bind_param("s", $mobile_number);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}