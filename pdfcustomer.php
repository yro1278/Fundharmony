<?php
session_start();
if (!isset($_SESSION['admin']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function generateRow()
{
    include_once 'database/db_connection.php';
    $contents = '';
    $sql = "SELECT * FROM customers ORDER BY registration_date DESC";
    $num = 1;
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $fullname = $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['surname'];
        $contents .= "
         <tr>
             <td>" . $num++ . "</td>
             <td>" . $row['customer_number'] . "</td>
             <td>" . $fullname . "</td>
             <td>" . ($row['email'] ?? '-') . "</td>
             <td>" . ($row['phone'] ?? '-') . "</td>
             <td>" . ($row['gender'] == 'M' ? 'Male' : ($row['gender'] == 'F' ? 'Female' : $row['gender'])) . "</td>
             <td>" . ($row['date_of_birth'] ? date('M d, Y', strtotime($row['date_of_birth'])) : '-') . "</td>
             <td>" . ($row['nationality'] ?? '-') . "</td>
             <td>" . date('M d, Y', strtotime($row['registration_date'])) . "</td>
         </tr>
        ";
    }

    return $contents;
}

require_once 'tcpdf/tcpdf.php';
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("Customer Detail");
$pdf->SetHeaderData('Netasatta Technologies', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN, 12));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, 'Times new roman', PDF_FONT_SIZE_DATA, 12));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT, 0);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(true, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();
$content = '';
$content .= '
      	<h2 align="center">Customer Report</h2>
      	<h4>All Customers</h4>
      	<table border="1" cellspacing="0" cellpadding="3">
           <tr>
                <th width="5%">SN</th>
				<th width="12%">Customer No.</th>
				<th width="18%">Name</th>
             <th width="18%">Email</th>
             <th width="12%">Phone</th>
             <th width="8%">Gender</th>
             <th width="10%">DOB</th>
             <th width="10%">Nationality</th>
				<th width="12%">Date Added</th>
           </tr>
      ';
$content .= generateRow();
$content .= '</table>';
$pdf->writeHTML($content);
$pdf->Output('customer detail.pdf', 'I');
