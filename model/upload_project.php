<?php
include '../server/server.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect Address info
    $province     = mysqli_real_escape_string($conn, $_POST['province']);
    $municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
    $barangay     = mysqli_real_escape_string($conn, $_POST['barangay']);
    $street       = mysqli_real_escape_string($conn, $_POST['street']);

    // Prefix based on municipality
    $prefix = ($municipality === 'Hagonoy') ? 'HAG' : (($municipality === 'Calumpit') ? 'CAL' : 'OTH');

    // Generate new AddressID
    $sqlLast = "SELECT AddressID FROM address WHERE AddressID LIKE '$prefix-%' ORDER BY AddressID DESC LIMIT 1";
    $resLast = $conn->query($sqlLast);

    if ($resLast && $resLast->num_rows > 0) {
        $row = $resLast->fetch_assoc();
        $lastNum = intval(substr($row['AddressID'], 4)); // get number part
        $newNum = str_pad($lastNum + 1, 3, "0", STR_PAD_LEFT);
    } else {
        $newNum = "001";
    }
    $addressID = "$prefix-$newNum";

    // Insert into Address
    $sqlAddress = "INSERT INTO address (AddressID, Province, Municipality, Barangay, Address) 
                   VALUES ('$addressID','$province','$municipality','$barangay','$street')";
    if ($conn->query($sqlAddress) === TRUE) {
        // Collect Project info
        $lotNo      = mysqli_real_escape_string($conn, $_POST['lot_no']);
        $fname      = mysqli_real_escape_string($conn, $_POST['client_name']);
        $lname      = mysqli_real_escape_string($conn, $_POST['last_name']);
        $surveyType = mysqli_real_escape_string($conn, $_POST['survey_type']);
        $startDate  = mysqli_real_escape_string($conn, $_POST['survey_start']);
        $endDate    = mysqli_real_escape_string($conn, $_POST['survey_end']);
        $agent      = mysqli_real_escape_string($conn, $_POST['agent']);
        $requestType= mysqli_real_escape_string($conn, $_POST['requestType']);
        $approval   = isset($_POST['approval']) ? mysqli_real_escape_string($conn, $_POST['approval']) : null;

        // 👉 Force Approval NULL if Sketch Plan
        if (strcasecmp($requestType, 'Sketch Plan') === 0) {
            $approval = null;
        }

        // Generate ProjectID with same logic
        $sqlLastProj = "SELECT ProjectID FROM project WHERE ProjectID LIKE '$prefix-%' ORDER BY ProjectID DESC LIMIT 1";
        $resLastProj = $conn->query($sqlLastProj);

        if ($resLastProj && $resLastProj->num_rows > 0) {
            $rowP = $resLastProj->fetch_assoc();
            $lastNumP = intval(substr($rowP['ProjectID'], 4));
            $newNumP = str_pad($lastNumP + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $newNumP = "001";
        }
        $projectID = "$prefix-$newNumP";

        // Insert into Project (handle NULL correctly)
        $sqlProject = "INSERT INTO project 
            (ProjectID, LotNo, ClientFName, ClientLName, SurveyType, SurveyStartDate, SurveyEndDate, Agent, RequestType, Approval, AddressID) 
            VALUES 
            ('$projectID','$lotNo','$fname','$lname','$surveyType','$startDate','$endDate','$agent','$requestType',"
            . ($approval !== null ? "'$approval'" : "NULL") . 
            ",'$addressID')";

        if ($conn->query($sqlProject) === TRUE) {
            echo "✅ Project uploaded successfully!<br>AddressID: $addressID<br>ProjectID: $projectID";
        } else {
            echo "❌ Error inserting project: " . $conn->error;
        }
    } else {
        echo "❌ Error inserting address: " . $conn->error;
    }

    $conn->close();
}
?>
