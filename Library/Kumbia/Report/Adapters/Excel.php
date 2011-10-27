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
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Excel.php,v 9a22443c227c 2011/10/27 00:03:34 andres $
 */

/**
 * ExcelReport
 *
 * Adaptador que permite generar reportes en Excel 2007
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @abstract
 */
class ExcelReport extends ReportAdapter implements ReportInterface {

	/**
	 * Salida HTML
	 *
	 * @var string
	 */
	private $_output;

	/**
	 * Tamaño de texto predeterminado
	 *
	 * @var int
	 * @static
	 */
	private static $_defaultFontSize = 12;


	/**
	 * Fuente de texto predeterminado
	 *
	 * @var int
	 * @static
	 */
	private static $_defaultFontFamily = 'Arial';

	/**
	 * Totales de columnas
	 *
	 * @var array
	 */
	protected $_totalizeValues = array();

	/**
	 * Formatos de Columnas
	 *
	 * @var array
	 */
	private $_columnFormats = array();

	/**
	 * Número de columnas del reporte
	 *
	 * @var int
	 */
	private $_numberColumns = null;

	/**
	 * Indica si el volcado del reporte ha sido iniciado
	 *
	 * @var boolean
	 */
	private $_started = false;

	/**
	 * Objeto PHPExcel
	 *
	 * @var PHPExcel
	 */
	private $_excel;

	/**
	 * Objeto PHPExcel_Worksheet
	 *
	 * @var PHPExcel_Worksheet
	 */
	private $_worksheet;

	/**
	 * Columna Actual a escribir
	 *
	 * @var int
	 */
	private $_column;

	/**
	 * Fila Actual a escribir
	 *
	 * @var int
	 */
	private $_row;

	/**
	 * Estilos preparados
	 *
	 * @var array
	 */
	private $_preparedStyles = array();

	/**
	 * Constructor de ExcelReport
	 *
	 */
	public function __construct(){
		if(class_exists('PHPExcel', false)===false){

			require KEF_ABS_PATH.'Library/PHPExcel/Classes/PHPExcel.php';

			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_discISAM;
			$cacheSettings = array(
				'memoryCacheSize' => '256MB'
			);
			if(!PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings)){
				throw new ReportException('No se pudo crear el cache del reporte');
			}

			$locale = Locale::getApplication();
			PHPExcel_Settings::setLocale((string)$locale);

			$this->_excel = new PHPExcel();
			$this->_worksheet = $this->_excel->setActiveSheetIndex(0);
			/*if(PHP_OS=='Darwin'){
				$this->_worksheet->getSheetView()->setZoomScale(150);
			}*/

			/*$title = i18n::substr($this->getDocumentTitle(), 0, 31);
			$title = preg_replace('/[^a-zA-Z0-9 ]/', '', $title);
			$this->_worksheet->setTitle($title);*/

			$this->_row = 1;
			$this->_column = 0;
		}
	}

	/**
	 * Genera la salida del reporte
	 *
	 * @return string
	 */
	public function getOutput(){
		$this->_prepareHead();
		$this->_renderPages();
		$this->_prepareFooter();
		return $this->_output;
	}

	/**
	 * Inicia el reporte
	 *
	 * @param boolean $implicitFlush
	 */
	protected function _start($implicitFlush=false){
		$this->_implicitFlush = $implicitFlush;
		if($implicitFlush==true){
			$this->_prepareHead();
			$this->_renderHeader();
			$this->_renderColumnHeaders();
		}
		$this->_started = true;
	}

	/**
	 * Finalizar el reporte
	 *
	 */
	protected function _finish(){
		$this->_renderTotals();
		$this->_prepareFooter();
	}

	/**
	 * Genera el encabezado del reporte
	 *
	 */
	private function _prepareHead(){
		if($this->_started==false){
			$this->_prepareColumnStyles();
			$this->_prepareColumnFormats();
			$this->_prepareTotalizedColumns();
		}
	}

	protected function _prepareFooter(){

	}

	/**
	 * Escribe los estilos de los encabezados del reporte
	 *
	 * @access protected
	 */
	protected  function _prepareCellHeaderStyle(){

	}

	/**
	 * Obtiene los formatos asignados a las columnas del reporte
	 *
	 * @access protected
	 */
	protected function _prepareColumnFormats(){
		$this->_columnFormats = $this->getColumnFormats();
	}

	/**
	 * Obtiene los valores base de totales de las columnas
	 *
	 * @access protected
	 */
	protected function _prepareTotalizedColumns(){
		$this->_totalizeValues = $this->getTotalizeValues();
	}

	/**
	 * Agrega un borde a la celda
	 *
	 * @param PHPExcel_Style $cellStyle
	 * @param array $preparedStyle
	 */
	private function _extendBorder(&$preparedStyle=array()){
		if(!isset($preparedStyle['borders']['allborders'])){
			$preparedStyle['borders']['allborders'] = array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
				'color' => array(
					'rgb' => '111111'
				)
			);
		}
	}

	/**
	 * Escribe las columnas encabezados del reporte
	 * Combina las celdas del encabezado del reporte
	 *
	 */
	private function _renderColumnHeaders(){
		$style = $this->getCellHeaderStyle();
		if($style!==null){
			$preparedStyle = $this->_prepareStyle($style->getStyles());
		} else {
			$preparedStyle = null;
		}
		$headers = $this->getColumnHeaders();
		$numberColumns = count($headers);
		if($numberColumns>0){
			for($i=1;$i<$this->_row;$i++){
				$this->_worksheet->mergeCellsByColumnAndRow(0, $i, $numberColumns-1, $i);
			}
			for($i=0;$i<$numberColumns;$i++){
				$this->_worksheet->getColumnDimensionByColumn($i)->setAutoSize(true);
			}
		}
		foreach($headers as $header){
			$cellStyle = $this->_appendToOutput($header);
			if($preparedStyle!==null){
				$this->_extendBorder($preparedStyle);
				$cellStyle->applyFromArray($preparedStyle);
			}
		}
		$this->_numberColumns = $numberColumns;
		$this->_lineFeed();
	}

	/**
	 * Escribe los estilos de las columnas del reporte
	 *
	 * @access protected
	 */
	protected function _prepareColumnStyles(){
		$styles = $this->getColumnStyles();
		for($i=0;$i<$this->_numberColumns;$i++){
			if(isset($styles[$i])){
				$style = $styles[$i];
				if($style!==null){
					$this->_preparedStyles[$i] = $this->_prepareStyle($style->getStyles());
				} else {
					$this->_preparedStyles[$i] = array();
				}
			} else {
				$this->_preparedStyles[$i] = array();
			}
			$this->_extendBorder($this->_preparedStyles[$i]);
		}
	}

	/**
	 * Convierte una definición de estilo en un estilo de Excel
	 *
	 * @param 	array $attributes
	 * @return 	array
	 */
	public function _prepareStyle($attributes){
		$style = array();
		foreach($attributes as $attributeName => $value){
			switch($attributeName){
				case 'fontSize':
					$style['font']['size'] = $value;
					break;
				case 'fontWeight':
					if($value=='bold'){
						$style['font']['bold'] = true;
					} else {
						$style['font']['bold'] = false;
					}
					break;
				case 'textAlign':
					$style['alignment']['horizontal'] = $value;
					break;
				case 'color':
					$style['fill']['color']['rgb'] = strtoupper(substr($value, 1));
					break;
				case 'borderColor':
					$style['borders']['allborders'] = array(
						'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
						'color' => array(
							'rgb' => strtoupper(substr($value, 1))
						)
					);
					break;
				case 'backgroundColor':
					$style['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
					$style['fill']['startcolor'] = array(
						'rgb' => strtoupper(substr($value, 1))
					);
					break;
				/*case 'paddingRight':
					$style[] = 'padding-right:'.$value;
					break;*/
			}
		}
		return $style;
	}

	/**
	 * Renderiza el encabezado del documento
	 *
	 */
	protected function _renderHeader(){
		$header = $this->getHeader();
		if(is_array($header)){
			foreach($header as $item){
				$style = $this->_renderItem($item);
				$this->_lineFeed();
			}
		} else {
			$style = $this->_renderItem($item);
			$this->_lineFeed();
		}
		$this->_lineFeed();
	}

	/**
	 * Renderiza un item
	 *
	 * @param mixed $item
	 * @return array
	 */
	protected function _renderItem($item){
		if(is_string($item)){
			$this->_appendToOutput($item);
		} else {
			if(is_object($item)==true){
				if(get_class($item)=='ReportText'){
					$cellStyle = $this->_appendToOutput($item->getText());
					$itemStyle = $item->getAttributes();
					$style = $this->_prepareStyle($itemStyle);
					if(count($style)){
						$cellStyle->applyFromArray($style);
					}
					unset($cellStyle);
				}
			}
		}
		unset($item);
	}

	/**
	 * Agrega una celda al Worksheet
	 *
	 * @param	string $value
	 * @param 	int $type
	 * @return  PHPExcel_Style
	 */
	private function _appendToOutput($value, $type=PHPExcel_Cell_DataType::TYPE_STRING){
		$column = $this->_column++;
		$this->_worksheet->setCellValueExplicitByColumnAndRow($column, $this->_row, (string) $value, $type);
		return $this->_worksheet->getStyleByColumnAndRow($column, $this->_row);
	}

	/**
	 * Pasa a la siguiente fila del Worksheet
	 *
	 */
	private function _lineFeed(){
		$this->_row++;
		$this->_column = 0;
	}

	/**
	 * Escribe las páginas del reporte
	 *
	 * @param array $rows
	 */
	private function _renderRows($rows){
		foreach($rows as $row){
			$this->_renderRow($row);
		}
	}

	/**
	 * Agrega una fila al reporte en implicit flush
	 *
	 * @param array $row
	 */
	protected function _addRow($row){
		$this->_renderRow($row);
	}

	/**
	 * Escribe una fila del reporte
	 *
	 * @param array $row
	 */
	private function _renderRow($row){
		if($row['_type']=='normal'){
			unset($row['_type']);
			foreach($row as $numberColumn => $value){
				if(isset($this->_totalizeColumns[$numberColumn])){
					if(!isset($this->_totalizeValues[$numberColumn])){
						$this->_totalizeValues[$numberColumn] = 0;
					}
					$this->_totalizeValues[$numberColumn]+=$value;
				}
				if(isset($this->_columnFormats[$numberColumn])){
					$stdType = $this->_columnFormats[$numberColumn]->getStdType();
					switch($stdType){
						case 'number':
							$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
							break;
						default:
							$type = PHPExcel_Cell_DataType::TYPE_STRING;
							break;
					}
					$cellStyle = $this->_appendToOutput($value, $type);
					switch($stdType){
						case 'number':
							$cellStyle->getNumberFormat()->setFormatCode('#,##0.00');
							break;
					}
					unset($type);
					unset($stdType);
				} else {
					$cellStyle = $this->_appendToOutput($value);
				}
				if(isset($this->_preparedStyles[$numberColumn])){
					$cellStyle->applyFromArray($this->_preparedStyles[$numberColumn]);
				}
				unset($cellStyle);
				unset($value);
			}
		} else {
			if($row['_type']=='raw'){
				unset($row['_type']);
				foreach($row as $numberColumn => $rawColumn){
					$cellStyle = $this->_appendToOutput($rawColumn->getValue());
					$styles = $rawColumn->getStyle();
					if($styles){
						$cellStyle->applyFromArray($this->_prepareStyle($styles));
					}
					$this->_worksheet->mergeCellsByColumnAndRow($this->_column-1, $this->_row, $this->_column+$rawColumn->getSpan()-2, $this->_row);
					$this->_column+=($rawColumn->getSpan()-1);
					unset($rawColumn);
				}
			}
		}
		$this->_lineFeed();
	}

	/**
	 * Escribe las páginas del reporte
	 *
	 */
	private function _renderPages(){
		$data = $this->getRows();
		$this->_renderHeader();
		$this->_renderColumnHeaders();
		$this->_renderRows($data);
		$this->_renderTotals();
	}

	/**
	 * Renombra el archivo temporal del volcado al nombre dado por el usuario
	 *
	 * @param	string $path
	 * @return	boolean
	 */
	protected function _moveOutputTo($path){
		$writer = PHPExcel_IOFactory::createWriter($this->_excel, 'Excel2007');
		$writer->save($path);
		return basename('/'.$path);
	}

	/**
	 * Visualiza los totales del reporte
	 *
	 */
	private function _renderTotals(){
		if(count($this->_totalizeValues)>0){
			for($i=0;$i<$this->_numberColumns;++$i){
				if(isset($this->_totalizeValues[$i])){
					if(isset($this->_columnFormats[$i])){
						$stdType = $this->_columnFormats[$i]->getStdType();
						switch($stdType){
							case 'number':
								$type = PHPExcel_Cell_DataType::TYPE_NUMERIC;
								break;
							default:
								$type = PHPExcel_Cell_DataType::TYPE_STRING;
								break;
						}
						$cellStyle = $this->_appendToOutput($this->_totalizeValues[$i], $type);
						switch($stdType){
							case 'number':
								$cellStyle->getNumberFormat()->setFormatCode('#,##0.00');
								break;
						}
					} else {
						$this->_appendToOutput($this->_totalizeValues[$i], PHPExcel_Cell_DataType::TYPE_NUMERIC);
					}
				} else {
					$this->_appendToOutput('');
				}
			}
		}
	}

	/**
	 * Devuelve la extension del archivo recomendada
	 *
	 * @return string
	 */
	protected function getFileExtension(){
		return 'xlsx';
	}

}
