<?php 

require("phpix-libs/fpdf/fpdf.php");

$image = dirname(__FILE__).DIRECTORY_SEPARATOR.’my_image.png’;
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image($image,20,40,170,170);
$pdf->Output();


 ?>