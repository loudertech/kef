<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category	Kumbia
 * @package		Generator
 * @subpackage	GeneratorReport
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Pdf.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * PDFGenerator
 *
 * Generador de Reportes a PDF
 *
 * @category	Kumbia
 * @package		Generator
 * @subpackage	GeneratorReport
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class PDF extends PdfDocument {

	/**
	 * Cabecera de la pagina
	 *
	 */
	public function Header(){
		$this->lineFeed(10);
	}

	/**
	 * Pie de Pagina
	 *
	 */
	public function Footer(){

		$config = CoreConfig::readAppConfig();
		$active_app = Router::getApplication();

		//Posición: a 1,5 cm del final
		$this->setY(-21);
		//Arial italic 8
		$this->setFont('Arial', '', 7);

		//Posicion: a 1,5 cm del final
		$this->setY(-18);
		//Arial italic 8
		$this->setFont('Arial', '', 7);
		//Número de página
		$this->writeCell(0,10, $config->application->name, 0, 0, 'C');

		//Posición: a 1,5 cm del final
		$this->setY(-10);
		//Arial italic 8
		$this->setFont('Arial', '', 8);
		//Número de página
		$this->writeCell(0,10,'-- '.$this->getPageNumber().' --', 0, 0, 'C');

	}

}

/**
 * Genera un reporte en PDF
 *
 * @param array $result
 * @param array $sumArray
 * @param string $title
 * @param array $weightArray
 * @param array $headerArray
 */
function pdf($result, $sumArray, $title, $weightArray, $headerArray){

	$config = CoreConfig::readAppConfig();
	$active_app = Router::getApplication();

	//Orientación
	if($sumArray>200) {
		$orientation = PdfDocument::ORI_LANDSCAPE;
	} else {
		$orientation = PdfDocument::ORI_PORTRAIT;
	}

	$numRows = 140;
	//Tipo de Papel
	if($sumArray>250){
		$paper = PdfDocument::PAPER_LEGAL;
	} else {
		$paper = PdfDocument::PAPER_LETTER;
	}

	if($paper==PdfDocument::PAPER_LETTER&&$orientation==PdfDocument::ORI_PORTRAIT){
		$widthPage = 220;
		$numRows = 42;
	}

	if($paper==PdfDocument::PAPER_LEGAL&&$orientation==PdfDocument::ORI_LANDSCAPE){
		$widthPage = 355;
		$numRows = 30;
	}

	if($paper==PdfDocument::PAPER_LETTER&&$orientation==PdfDocument::ORI_LANDSCAPE){
		$widthPage = 270;
		$numRows = 30;
	}

	//Crear Documento PDF
	$pdf = new PDF($orientation, PdfDocument::UNIT_MM, PdfDocument::PAPER_LETTER);

	$pdf->Open();
	$pdf->AddPage();

	//Nombre del Listado
	$pdf->setFillColor(255, 255, 255);
	$pdf->addFont('Verdana','','verdana.php');
	$pdf->setFont('Verdana','', 14);
	$pdf->setY(20);
	$pdf->setX(0);

	$pdf->lineFeed();

	if($config->application->name){
		$pdf->writeMultiCell(0, 6, strtoupper($config->application->name), 0, "C", 0);
	}
	$pdf->writeMultiCell(0, 6, 'REPORTE DE '.strtoupper($title), 0, 'C', 0);
	$pdf->setFont('Verdana','', 12);
	if(isset($_SESSION['fecsis'])){
		$pdf->writeMultiCell(0, 6, 'FECHA '.Date::getCurrentDate(), 0, 'C', 0);
	}
	$pdf->lineFeed();

	//Colores, ancho de línea y fuente en negrita
	$pdf->setFillColor(0xF2, 0xF2, 0xF2);
	$pdf->setTextColor(0);
	$pdf->setDrawColor(0, 0, 0);
	$pdf->setLineWidth(.2);
	$pdf->setFont('Arial', 'B', 10);

	if($weightArray[0]<11){
		$weightArray[0] = 11;
	}

	//Parametros del Reporte
	$pos = floor(($widthPage/2)-($sumArray/2));
	$pdf->setX($pos);
	$numberColumns = count($headerArray);
	for($i=0;$i<$numberColumns;++$i){
		if(!isset($weightArray[$i])){
			$weightArray[$i] = 25;
		}
		$pdf->writeCell($weightArray[$i], 7, $headerArray[$i], 1, 0, 'C', 1);
	}
	$pdf->lineFeed();

	//Restauración de colores y fuentes
	$pdf->setFillColor(224, 235, 255);
	$pdf->setTextColor(0);
	$pdf->setFont('Arial', 'B', 7);

	//Buscamos y listamos
	$n = 1;
	$p = 1;
	$t = 0;
	foreach($result as $row){
		//$pdf->Cell(Ancho, Alto, contenido, ?, ?, Align)
		if($n>$numRows||($p==1&&($n>$numRows-3))){
			$pdf->addPage($orientation);
			$pdf->setY(30);
			$pdf->setX($pos);
			$pdf->setFillColor(0xF2, 0xF2, 0xF2);
			$pdf->setTextColor(0);
			$pdf->setDrawColor(0,0,0);
			$pdf->setLineWidth(.2);
			$pdf->setFont('Arial', 'B', 10);
			for($i=0;$i<count($headerArray);++$i){
				$pdf->writeCell($weightArray[$i], 7, $headerArray[$i], 1, 0, 'C', 1);
			}
			$pdf->LineFeed();
			$pdf->SetFillColor(224, 235, 255);
			$pdf->SetTextColor(0);
			$pdf->SetFont('Arial', 'B', 7);
			$n = 1;
			++$p;
		}
		$pdf->setX($pos);
		$numberColumns = count($row)-1;
		for($i=0;$i<=$numberColumns;$i++){
			if(is_numeric($row[$i])){
				$pdf->writeCell($weightArray[$i], 5, trim($row[$i]),'LRTB', 0, 'C');
			} else {
				$pdf->writeCell($weightArray[$i], 5, trim($row[$i]),'LRTB', 0, 'L');
			}
		}
		++$n;
		++$t;
		$pdf->lineFeed();
	}

	$pdf->setX($pos);
	$pdf->setFont('Arial', 'B', 7);
	$pdf->setFillColor(0xF2,0xF2, 0xF2);
	if(isset($weightArray[1])){
		$pdf->writeCell($weightArray[0], 5, 'TOTAL','LRTB', 0, 'R');
		$pdf->writeCell($weightArray[1], 5, $t,'LRTB', 0, 'L');
	}

	$file = md5(mt_rand(0, 10000));
	$pdf->outputDocument('public/temp/'.$file .'.pdf', 'F');
	if(isset($raw_output)){
		echo "<script type='text/javascript'> window.open('".Core::getInstancePath()."temp/".$file.".pdf', null); </script>";
	} else {
		Generator::formsPrint("<script type='text/javascript'> window.open('".Core::getInstancePath()."temp/".$file.".pdf', null); </script>");
	}

}
