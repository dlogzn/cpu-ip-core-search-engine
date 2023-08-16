<?php
ini_set('max_execution_time', '0');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$performance = validate($_POST['performance']);
$numberOfBits = validate($_POST['number_of_bits']);
$isaType = validate($_POST['isa_type']);
$keyword = validate($_POST['keyword']);
$email = validate($_POST['email']);
$getPriceQuote = $_POST['get_price_quote'];
$validationErrors = [];
if ($performance === null || $performance === '') {
    $validationErrors['performance'] = 'Performance field is required.';
}
if ($numberOfBits === null || $numberOfBits === '') {
    $validationErrors['number_of_bits'] = 'Number of Bits field is required.';
}
if ($isaType === null || $isaType === '') {
    $validationErrors['isa_type'] = 'ISA Type field is required.';
}
if ($email === null || $email === '') {
    $validationErrors['email'] = 'Email field is required.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $validationErrors['email'] = 'Invalid Email format.';
}

if (count($validationErrors) === 0) {
    $spreadsheetUrl='https://docs.google.com/spreadsheets/d/e/2PACX-1vTfRFYhr_OosuY7X9_sEhXe73ZRnXH4VicU_EUK1jz3GmAMBc1vv1g9eRN8v2kGvg/pub?gid=248249695&single=true&output=csv';
    if (($handle = fopen($spreadsheetUrl, "r")) !== FALSE) {
        ob_start();
        echo json_encode(['success' => 1, 'message' => 'Your search results will be sent to your email address shortly. The report is on its way to your inbox.']);
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        @ob_flush();
        flush();
        if(session_id()) session_write_close();

        $payload = [];
        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            $vendorFromDb = $data[0];
            $ipCoreNameFromDb = $data[1];
            $shortDescriptionFromDb = $data[2];
            $featuresFromDb = $data[4];
            $urlFromDb = $data[18];

            $performanceFromDb = validate($data[7]);
            $numberOfBitsFromDb = validate($data[6]);
            $isaTypeFromDb = validate($data[5]);

            if ($vendorFromDb !== null && $vendorFromDb !== '' && $vendorFromDb !== 'Vendor' && $vendorFromDb !== '<END>') {
                if ($isaType === 'All') {
                    if ((strpos($numberOfBitsFromDb, $numberOfBits) !== FALSE) && ($performanceFromDb === $performance)) {
                        $payload[] = [
                            'vendor' => $vendorFromDb,
                            'ip_core_name' => $ipCoreNameFromDb,
                            'short_description' => $shortDescriptionFromDb,
                            'features' => $featuresFromDb,
                            'url' => $urlFromDb
                        ];
                    }
                } else {
                    if (($isaTypeFromDb === $isaType) && (strpos($numberOfBitsFromDb, $numberOfBits) !== FALSE) && ($performanceFromDb === $performance)) {
                        $payload[] = [
                            'vendor' => $vendorFromDb,
                            'ip_core_name' => $ipCoreNameFromDb,
                            'short_description' => $shortDescriptionFromDb,
                            'features' => $featuresFromDb,
                            'url' => $urlFromDb
                        ];
                    }
                }

                if ($keyword !== null && $keyword !== '' && $data[18] !== null && $data[18] !== '' && substr($data[18], -4) !== '.pdf') {
                    $siteContent = file_get_contents($data[18]);
                    $pattern = '/' . $keyword . '/i';
                    if(preg_match($pattern, $siteContent) === 1) {
                        $payload[] = [
                            'vendor' => $vendorFromDb,
                            'ip_core_name' => $ipCoreNameFromDb,
                            'short_description' => $shortDescriptionFromDb,
                            'features' => $featuresFromDb,
                            'url' => $urlFromDb
                        ];
                    }
                }
            }
        }
        fclose($handle);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'press@anysilicon.com';
        $mail->Password   = 'ILOVEASRA';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('press@anysilicon.com', 'AnySilicon');
        $mail->addAddress($email);
        if ((int)$getPriceQuote === 1) {
            $mail->addAddress('info@anysilicon.com');
        }
        $mail->isHTML(true);
        $mail->Subject = 'CPU IP Core Search Result';
        if (count($payload) > 0) {
            $resultBody = '<html><head><style>table, th, td {border: 1px solid #cdcdcd;border-collapse: collapse;} .search_parameters th, td {padding-top: 5px;padding-bottom: 5px;padding-left: 30px;padding-right: 30px;} .search_results th, td {padding-top: 10px;padding-bottom: 10px;padding-left: 30px;padding-right: 30px;}</style></head><body>';
            $resultBody .= '<div style="font-weight: bold; font-size: 18px; margin-bottom: 15px;">Search Parameters:</div>';
            $resultBody .= '<table style="width: 100%;" class="search_parameters"><tr><td style="font-weight: bold;">Performance</td><td>' . $performance . '</td></tr><tr><td style="font-weight: bold;">Number of Bits</td><td>' . $numberOfBits . '</td></tr><tr><td style="font-weight: bold;">ISA Type</td><td>' . $isaType . '</td></tr>';
            if ($keyword !== '' & $keyword !== null) {
                $resultBody .=  '<tr><td style="font-weight: bold;">Keyword</td><td>' . $keyword . '</td></tr>';
            }
            $resultBody .= (int)$getPriceQuote === 1 ? '<tr><td style="font-weight: bold;">Wants Price</td><td>Yes</td></tr></table>' : '</table>';
            $resultBody .= '<div style="font-weight: bold; font-size: 18px; margin-top: 15px; margin-bottom: 15px;">Search Results:</div>';
            $resultBody .= '<table style="width: 100%;" class="search_results"><tr><th>Vendor</th><th>IP Core Name</th><th>Short Description</th><th>Features</th><th>URL</th></tr>';
            foreach ($payload as $key => $value) {
                $resultBody .= '<tr><td>' . $value["vendor"] . '</td><td>' . $value["ip_core_name"] . '</td><td>' . $value["short_description"] . '</td><td>' . $value["features"] . '</td><td>' . $value["url"] . '</td></tr>';
            }
            $resultBody .= '</table></body></html>';
        } else {
            $resultBody = '<html><head><style>table, th, td {border: 1px solid #cdcdcd;border-collapse: collapse;} .search_parameters th, td {padding-top: 5px;padding-bottom: 5px;padding-left: 30px;padding-right: 30px;}</style></head><body><div style="text-align: center; font-weight: bold; font-size: 20px;">No results found using your search input.</div>';
            $resultBody .= '<div style="font-weight: bold; font-size: 18px; margin-bottom: 15px; margin-top: 15px;">Search Parameters:</div>';
            $resultBody .= '<table style="width: 100%;" class="search_parameters"><tr><td style="font-weight: bold;">Performance</td><td>' . $performance . '</td></tr><tr><td style="font-weight: bold;">Number of Bits</td><td>' . $numberOfBits . '</td></tr><tr><td style="font-weight: bold;">ISA Type</td><td>' . $isaType . '</td></tr>';
            if ($keyword !== '' & $keyword !== null) {
                $resultBody .=  '<tr><td style="font-weight: bold;">Keyword</td><td>' . $keyword . '</td></tr>';
            }
            $resultBody .= (int)$getPriceQuote === 1 ? '<tr><td style="font-weight: bold;">Wants Price</td><td>Yes</td></tr></table>' : '</table>';
            $resultBody .= '</body></html>';
        }
        $mail->Body    = $resultBody;
        $mail->send();
    } else {
        echo json_encode(['success' => 0, 'message' => 'CSV file reading failed.']);
    }
} else {
    echo json_encode(['success' => 0, 'messages' => $validationErrors]);
}

function validate($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

