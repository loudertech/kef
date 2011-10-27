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
 * @package 	PdfDocument
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: PdfDocument.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * @see PdfColor
 */
require KEF_ABS_PATH.'Library/Kumbia/PdfDocument/Color/PdfColor.php';

/**
 * PdfDocument
 *
 * El objetivo del componente PDFDocument es la generación de documentos PDF.
 * Está basado en la estable  librería FPDF pero adaptado y mejorado para
 * ser integrado como parte del framework y hacerlo parte de su “garantía”.
 *
 * @category 	Kumbia
 * @package 	PdfDocument
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) Olivier Platei
 * @license 	New BSD License
 */
class PdfDocument {

	/**
	 * Version del formato PDF usada por el documento
	 *
	 * @var float
	 */
	private $_pdfVersion;

	/**
	 * Pagina activa del documento
	 *
	 * @var int
	 */
	private $_activePage;

	/**
	 * Factor de escalamiento
	 *
	 * @var int
	 */
	private $_scaleFactor;

	/**
	 * Orientación por defecto
	 *
	 * @var string
	 */
	private $_defaultOrientation;

	/**
	 * Orientación actual
	 *
	 * @var string
	 */
	private $_currentOrientation;

	/**
	 * Establece puntos para saber cuando cambia la orientacion del documento
	 *
	 * @var array
	 */
	private $_orientationChanges = array();

	/**
	 * Ancho del documento
	 *
	 * @var string
	 */
	private $_width;

	/**
	 * Alto del documento
	 *
	 * @var string
	 */
	private $_height;

	/**
	 * Posicion horizontal
	 *
	 * @var string
	 */
	private $_x;

	/**
	 * Posicion vertical
	 *
	 * @var string
	 */
	private $_y;

	/**
	 * fWidthPoint
	 *
	 * @var int
	 */
	private $_fWidthPoint;

	/**
	 * fHeightPoint
	 *
	 * @var int
	 */
	private $_fHeightPoint;

	/**
	 * heightPoint
	 *
	 * @var int
	 */
	private $_heightPoint;

	/**
	 * widthPoint
	 *
	 * @var int
	 */
	private $_widthPoint;

	/**
	 * Ancho de Linea
	 *
	 * @var int
	 */
	private $_lineWidth;

	/**
	 * Ultima altura calculada
	 *
	 * @var int
	 */
	private $_lastHeight;

	/**
	 * Margen interior de Celda
	 *
	 * @var int
	 */
	private $_cellMargin;

	/**
	 * Margen superior
	 *
	 * @var int
	 */
	private $_topMargin;

	/**
	 * Margen izquierdo
	 *
	 * @var int
	 */
	private $_leftMargin;

	/**
	 * Margen derecho
	 *
	 * @var int
	 */
	private $_rightMargin;

	/**
	 * Margen inferior
	 *
	 * @var int
	 */
	private $_bottomMargin;

	/**
	 * Indica si se debe saltar automaticamente a la pagina siguiente al terminar
	 *
	 * @var boolean
	 */
	private $_autoPageBreak = true;

	/**
	 * Indica el limite en el que debe lanzar el auto-break
	 *
	 * @var int
	 */
	private $_pageBreakTrigger;

	/**
	 * Modo de visualizacion del documento
	 *
	 * @var string
	 */
	private $_zoomMode;

	/**
	 * Modo de distribución del documento
	 *
	 * @var string
	 */
	private $_layoutMode;

	/**
	 * Establece si se debe comprimir el documento
	 *
	 * @var boolean
	 */
	private $_compress;

	/**
	 * Titulo del Documento
	 *
	 * @var string
	 */
	private $_title;

	/**
	 * Asunto del Documento
	 *
	 * @var string
	 */
	private $_subject;

	/**
	 * Autor del documento
	 *
	 * @var string
	 */
	private $_author;

	/**
	 * Palabras clave del documento
	 *
	 * @var string
	 */
	private $_keywords;

	/**
	 * Creador del documento
	 *
	 * @var string
	 */
	private $_creator;

	/**
	 * Estado de construcción del documento
	 *
	 * @var int
	 */
	private $_state;

	/**
	 * Alias para el total de paginas
	 *
	 * @var string
	 */
	private $_aliasNbPages;

	/**
	 * Indica si se encuentra en el pie de pagina
	 *
	 * @var boolean
	 */
	private $_inFooter;

	/**
	 * Path a la ubicacion estandar de fuentes
	 *
	 * @var string
	 */
	private $_fontPath;

	/**
	 * Tipo de fuente activa
	 *
	 * @var string
	 */
	private $_fontFamily;

	/**
	 * Tamaño de fuente activa
	 *
	 * @var int
	 */
	private $_fontSize;

	/**
	 * Tamaño de fuente activa en Pts
	 *
	 * @var int
	 */
	private $_fontSizePt;

	/**
	 * Estilo de la fuente a utilizar
	 *
	 * @var string
	 */
	private $_fontStyle;

	/**
	 * Ajuste a Metricas de Fuentes
	 *
	 * @var int
	 */
	private $_metricAdjusment;

	/**
	 * Indica si el estilo es subrayado
	 *
	 * @var boolean
	 */
	private $_underline;

	/**
	 * Color para dibujar lineas
	 *
	 * @var string
	 */
	private $_drawColor;

	/**
	 * Color de relleno de celdas
	 *
	 * @var string
	 */
	private $_fillColor;

	/**
	 * Color del Texto
	 *
	 * @var string
	 */
	private $_textColor;

	/**
	 * Bandera que indica si se debe aplicar color o no
	 *
	 * @var boolean
	 */
	private $_colorFlag;

	/**
	 * Indica la fuente actual
	 *
	 * @var array
	 */
	private $_currentFont;

	/**
	 * Fuentes agregadas al documento
	 *
	 * @var array
	 */
	private $_fonts = array();

	/**
	 * Archivos de fuentes
	 *
	 * @var array
	 */
	private $_fontFiles = array();

	/**
	 * Imagenes del documento PDF
	 *
	 * @var array
	 */
	private $_images = array();

	/**
	 * Codificaciones de fuentes
	 *
	 * @var array
	 */
	private $_diffs = array();

	/**
	 * Posicion de los enlaces del documento
	 *
	 * @var array
	 */
	private $_links = array();

	/**
	 * Enlaces del documento
	 *
	 * @var array
	 */
	private $_pageLinks = array();

	/**
	 * Cada una de las paginas del documento PDF
	 *
	 * @var array
	 */
	private $_pages = array();

	/**
	 * Buffer temporal con el contenido del documento
	 *
	 * @var string
	 */
	private $_buffer = "";

	/**
	 * Crea espacios temporales de memoria para guardar segmentos del buffer
	 *
	 * @var array
	 */
	private $_offsets = array();

	/**
	 * WS
	 *
	 * @var int
	 */
	private $_ws;

	/**
	 * Puntero temporal
	 *
	 * @var int
	 */
	private $_n;

	/**
	 * Indica si esta en modo debug
	 *
	 * @var boolean
	 */
	private $_debug = false;

	/**
	 * Códificación del documento
	 *
	 * @var int
	 */
	private $_encoding = 1;

	/**
	 * Fuentes del Núcleo del Componente
	 *
	 */
	const FONT_TYPE_CORE = 0;

	/**
	 * Fuentes Externas TrueType
	 *
	 */
	const FONT_TYPE_TRUETYPE = 1;

	/**
	 * Fuentes Externas Type1
	 *
	 */
	const FONT_TYPE_TYPE1 = 2;

	/**
	 * Alineacion justifificada
	 *
	 */
	const ALIGN_LEFT = 0;

	/**
	 * Alineacion centrada
	 *
	 */
	const ALIGN_CENTER = 1;

	/**
	 * Alineacion a la derecha
	 *
	 */
	const ALIGN_RIGHT = 2;

	/**
	 * Alineacion justifificada
	 *
	 */
	const ALIGN_JUSTIFY = 3;

	/**
	 * Unidad en milimetros
	 *
	 */
	const UNIT_MM = 1;

	/**
	 * Unidad en Centimetros
	 *
	 */
	const UNIT_CM = 2;

	/**
	 * Unidad en pulgadas
	 *
	 */
	const UNIT_IN = 3;

	/**
	 * Unidad en Pixeles por Punto
	 *
	 */
	const UNIT_PT = 4;

	/**
	 * Orientacion Vertical
	 *
	 */
	const ORI_PORTRAIT = 1;

	/**
	 * Orientacion Horizontal
	 *
	 */
	const ORI_LANDSCAPE = 2;

	/**
	 * Zoom Predeterminado
	 *
	 */
	const ZOOM_DEFAULT = 0;

	/**
	 * Zoom Pagina Completa
	 *
	 */
	const ZOOM_FULLPAGE = 1;

	/**
	 * Zoom Ancho Completo
	 */
	const ZOOM_FULLWIDTH = 2;

	/**
	 * Zoom Real
	 */
	const ZOOM_REAL = 3;

	/**
	 * Layout Predeterminado
	 */
	const LAYOUT_DEFAULT = 0;

	/**
	 * Layout Pagina Sencilla
	 */
	const LAYOUT_SINGLE = 1;

	/**
	 * Layout a pagina continua
	 */
	const LAYOUT_CONTINUOUS = 1;

	/**
	 * Layout a doble pagina
	 */
	const LAYOUT_TWO = 3;

	/**
	 * Tipo de Papel A3
	 *
	 */
	const PAPER_A3 = 1;

	/**
	 * Tipo de Papel A4
	 *
	 */
	const PAPER_A4 = 2;

	/**
	 * Tipo de Papel A5
	 *
	 */
	const PAPER_A5 = 3;

	/**
	 * Tipo de Papel Oficio
	 *
	 */
	const PAPER_LEGAL = 4;

	/**
	 * Tipo de Papel Carta
	 *
	 */
	const PAPER_LETTER = 5;

	/**
	 * Codificación ISO-8859-1
	 *
	 */
	const ENC_ISO88591 = 1;

	/**
	 * Codificación UTF-8
	 *
	 */
	const ENC_UTF8 = 2;

	/**
	 * Codificación Japonesa JP
	 *
	 */
	const ENC_ISO2022JP = 3;

	/**
	 * Fuentes Estandar
	 *
	 * @var array
	 */
	static private $_coreFonts = array(
		'courier' => 'Courier',
		'courierB' => 'Courier-Bold',
		'courierI' => 'Courier-Oblique',
		'courierBI' => 'Courier-BoldOblique',
		'helvetica' => 'Helvetica',
		'helveticaB' => 'Helvetica-Bold',
		'helveticaI' => 'Helvetica-Oblique',
		'helveticaBI' => 'Helvetica-BoldOblique',
		'times' => 'Times-Roman',
		'timesB' => 'Times-Bold',
		'timesI' => 'Times-Italic',
		'timesBI' => 'Times-BoldItalic',
		'symbol' => 'Symbol',
		'zapfdingbats' => 'ZapfDingbats'
	);

	/**
	 * Constructor de la clase PdfDocument
	 *
	 * @param int $orientation
	 * @param int $unit
	 * @param int $format
	 * @return PdfDocument
	 */
	public function __construct($orientation=self::ORI_PORTRAIT, $unit=self::UNIT_MM, $format=self::PAPER_A4){
		//Some checks
		$this->doLocaleChecks();
		//Initialization of properties
		$this->_activePage = 0;
		$this->_n = 2;
		$this->_buffer = '';
		$this->_pages = array();
		$this->_orientationChanges = array();
		$this->_state = 0;
		$this->_fonts = array();
		$this->_fontFiles = array();
		$this->_diffs = array();
		$this->_images = array();
		$this->_links = array();
		$this->_inFooter = false;
		$this->_lastHeight = 0;
		$this->_fontFamily = '';
		$this->_fontStyle = '';
		$this->_fontSizePt = 12;
		$this->_underline = false;
		$this->_drawColor = '0 G';
		$this->_fillColor = '0 g';
		$this->_textColor = '0 g';
		$this->_colorFlag = false;
		$this->_ws = 0;

		//Scale factor
		if($unit==self::UNIT_PT){
			$this->_scaleFactor = 1;
		} else {
			if($unit==self::UNIT_MM){
				$this->_scaleFactor = 72/25.4;
			} else {
				if($unit==self::UNIT_CM){
					$this->_scaleFactor = 72/2.54;
				} else {
					if($unit==self::UNIT_IN){
						$this->_scaleFactor = 72;
					} else {
						throw new PdfDocumentException('Unidad incorrecta: '.$unit);
					}
				}
			}
		}

		//Page format
		if(is_int($format)){
			switch($format){
				case self::PAPER_A3:
					$format = array(841.89, 1190.55);
					break;
				case self::PAPER_A4:
					$format = array(595.28, 841.89);
					break;
				case self::PAPER_A5:
					$format = array(420.94, 595.28);
					break;
				case self::PAPER_LETTER:
					$format = array(612, 792);
					break;
				case self::PAPER_LEGAL:
					$format = array(612, 1008);
					break;
				default:
					throw new PdfDocumentException('Tipo de papel desconocido: '.$format);
			}
			$this->_fWidthPoint = $format[0];
			$this->_fHeightPoint = $format[1];
		} else {
			if(is_array($format)){
				$this->_fWidthPoint = $format[0]*$this->_scaleFactor;
				$this->_fHeightPoint = $format[1]*$this->_scaleFactor;
			} else {
				throw new PdfDocumentException('Tipo de papel desconocido: '.$format);
			}
		}
		$this->_fWidth = $this->_fWidthPoint/$this->_scaleFactor;
		$this->_fHeight = $this->_fHeightPoint/$this->_scaleFactor;
		//Page orientation
		if($orientation==self::ORI_PORTRAIT){
			$this->_defaultOrientation = $orientation;
			$this->_widthPoint=$this->_fWidthPoint;
			$this->_heightPoint=$this->_fHeightPoint;
		} else {
			if($orientation==self::ORI_LANDSCAPE){
				$this->_defaultOrientation = $orientation;
				$this->_widthPoint=$this->_fHeightPoint;
				$this->_heightPoint=$this->_fWidthPoint;
			} else {
				throw new PdfDocumentException('Orientación de Página incorrecta: '.$orientation);
			}
		}
		$this->_currentOrientation = $this->_defaultOrientation;
		$this->_width = $this->_widthPoint/$this->_scaleFactor;
		$this->_height = $this->_heightPoint/$this->_scaleFactor;
		//Page margins (1 cm)
		$margin = 28.35/$this->_scaleFactor;
		$this->setMargins($margin,$margin);
		//Interior cell margin (1 mm)
		$this->_cellMargin = $margin/10;
		//Line width (0.2 mm)
		$this->_lineWidth = 0.567/$this->_scaleFactor;
		//Automatic page break
		$this->setAutoPageBreak(true, 2*$margin);
		//Full width display mode
		$this->setDisplayMode(self::ZOOM_FULLWIDTH);
		//Enable compression
		$this->setCompression(true);
		//Set default PDF version number
		$this->_pdfVersion = '1.4';
		//Set de font PATH
		$this->_fontPath = 'Library/Kumbia/PdfDocument/Fonts/';
	}

	/**
	 * Establece si el documento se genera el modo debug
	 *
	 * @param boolean $debug
	 */
	public function setDebug($debug){
		$this->_debug = $debug;
	}

	/**
	 * Establece la códificación de los textos recibidos
	 *
	 * @param int $encoding
	 */
	public function setEncoding($encoding){
		switch($encoding){
			case self::ENC_ISO88591:
			case self::ENC_UTF8:
			case self::ENC_ISO2022JP:
				break;
			default:
				throw new PdfDocumentException('Tipo de Codificación incorrecta');
		}
		$this->_encoding = $encoding;
	}

	/**
	 * Set left, top and right margins
	 *
	 * @param integer $left
	 * @param integer $top
	 * @param integer $right
	 */
	public function setMargins($left, $top, $right=-1){
		$this->_leftMargin = $left;
		$this->_topMargin = $top;
		if($right==-1){
			$right = $left;
		}
		$this->_rightMargin = $right;
	}

	/**
	 * Set left margin
	 *
	 * @param integer $margin
	 */
	public function setLeftMargin($margin){
		$this->_leftMargin = $margin;
		if($this->_activePage>0 && $this->_x<$margin){
			$this->_x = $margin;
		}
	}

	/**
	 * Set top margin
	 *
	 * @param integer $margin
	 */
	public function setTopMargin($margin){
		$this->_topMargin = $margin;
	}

	/**
	 * Establece la margen derecha
	 *
	 * @param integer $margin
	 */
	public function setRightMargin($margin){
		//Set right margin
		$this->_rightMargin = $margin;
	}

	/**
	 * Establece el modo auto-break y lanza la maregn
	 *
	 * @param boolean $auto
	 * @param integer $margin
	 */
	public function setAutoPageBreak($auto, $margin=0){
		//Set auto page break mode and triggering margin
		$this->_autoPageBreak = $auto;
		$this->_bottomMargin = $margin;
		$this->_activePageBreakTrigger = $this->_height-$margin;
	}

	/**
	 * Set display mode in viewer
	 *
	 * @param string $zoom
	 * @param string $layout
	 */
	public function setDisplayMode($zoom, $layout=self::LAYOUT_CONTINUOUS){
		if($zoom==self::ZOOM_FULLPAGE || $zoom==self::ZOOM_FULLWIDTH || $zoom==self::ZOOM_REAL || $zoom==self::ZOOM_DEFAULT || !is_string($zoom)){
			$this->_zoomMode = $zoom;
		} else {
			throw new PdfDocumentException('Modo de zoom display incorrecto: '.$zoom);
		}
		if($layout==self::LAYOUT_SINGLE || $layout==self::LAYOUT_CONTINUOUS || $layout==self::LAYOUT_TWO || $layout==self::LAYOUT_DEFAULT){
			$this->_layoutMode = $layout;
		} else {
			throw new PdfDocumentException('Modo de distribución (layout) incorrecto: '.$layout);
		}
	}

	/**
	 * Set page compression
	 *
	 * @param boolean $compress
	 */
	public function setCompression($compress){
		if(function_exists('gzcompress')){
			$this->_compress = $compress;
		} else {
			$this->_compress = false;
		}
	}

	/**
	 * Title of document
	 *
	 * @param string $title
	 */
	public function setTitle($title){
		$this->_title = $title;
	}

	/**
	 * Subject of document
	 *
	 * @param string $subject
	 */
	public function setSubject($subject){
		$this->_subject = $subject;
	}

	/**
	 * Author of document
	 *
	 * @param string $author
	 */
	public function setAuthor($author){
		$this->_author = $author;
	}

	/**
	 * Keywords of document
	 *
	 * @param string $keywords
	 */
	public function setKeywords($keywords){
		$this->_keywords = $keywords;
	}

	/**
	 * Creator of document
	 *
	 * @param string $creator
	 */
	public function setCreator($creator){
		$this->_creator = $creator;
	}

	/**
	 * Define an alias for total number of pages
	 *
	 * @param string $alias
	 */
	public function aliasNbPages($alias='{nb}'){
		$this->_aliasNbPages = $alias;
	}

	/**
	 * Fatal error
	 *
	 * @param string $msg
	 */
	public function Error($msg){
		throw new Exception('<B>FPDF error: </B>'.$msg);
	}

	/**
	 * Begin document
	 *
	 */
	public function open(){
		$this->_state = 1;
	}

	/**
	 * Terminate document
	 *
	 */
	public function close(){
		if($this->_state==3){
			return;
		}
		if($this->_activePage==0){
			$this->AddPage();
		}
		//Page footer
		$this->_inFooter = true;
		$this->footer();
		$this->_inFooter = false;
		//Close page
		$this->_endpage();
		//Close document
		$this->_enddoc();
	}

	/**
	 * Start a new page
	 *
	 * @param string $orientation
	 */
	public function addPage($orientation=''){
		if($this->_state==0){
			$this->Open();
		}
		$family = $this->_fontFamily;
		$style = $this->_fontStyle.($this->_underline ? 'U' : '');
		$size = $this->_fontSizePt;
		$lw = $this->_lineWidth;
		$dc = $this->_drawColor;
		$fc = $this->_fillColor;
		$tc = $this->_textColor;
		$cf = $this->_colorFlag;
		if($this->_activePage>0){
			//Page footer
			$this->_inFooter = true;
			$this->footer();
			$this->_inFooter = false;
			//Close page
			$this->_endpage();
		}
		//Start new page
		$this->_beginpage($orientation);
		//Set line cap style to square
		$this->_out('2 J');
		//Set line width
		$this->_lineWidth = $lw;
		$this->_out(sprintf('%.2f w',$lw*$this->_scaleFactor));
		//Set font
		if($family){
			$this->setFont($family,$style,$size);
		}
		//Set colors
		$this->_drawColor = $dc;
		if($dc!='0 G'){
			$this->_out($dc);
		}
		$this->_fillColor = $fc;
		if($fc!='0 g'){
			$this->_out($fc);
		}
		$this->_textColor = $tc;
		$this->_colorFlag = $cf;
		//Page header
		$this->header();
		//Restore line width
		if($this->_lineWidth!=$lw){
			$this->_lineWidth = $lw;
			$this->_out(sprintf('%.2f w',$lw*$this->_scaleFactor));
		}
		//Restore font
		if($family){
			$this->setFont($family,$style,$size);
		}
		//Restore colors
		if($this->_drawColor!=$dc){
			$this->_drawColor = $dc;
			$this->_out($dc);
		}
		if($this->_fillColor!=$fc){
			$this->_fillColor = $fc;
			$this->_out($fc);
		}
		$this->_textColor = $tc;
		$this->_colorFlag = $cf;
	}

	/**
	 * To be implemented in your own inherited class
	 *
	 */
	public function Header(){

	}

	/**
	 * To be implemented in your own inherited class
	 *
	 */
	public function Footer(){

	}

	/**
	 * Get current page number
	 *
	 * @return integer
	 */
	public function getPageNumber(){
		return $this->_activePage;
	}

	/**
	 * Set color for all stroking operations
	 *
	 * @param integer $r
	 * @param integer $g
	 * @param integer $b
	 */
	public function setDrawColor($r, $g=-1, $b=-1){
		if(($r==0&&$g==0&&$b==0)||$g==-1){
			$this->_drawColor = sprintf('%.3f G',$r/255);
		} else {
			$this->_drawColor = sprintf('%.3f %.3f %.3f RG', $r/255, $g/255, $b/255);
		}
		if($this->_activePage>0){
			$this->_out($this->_drawColor);
		}
	}

	/**
	 * Lee un entero corto de 16bits
	 *
	 * @param resource $f
	 * @return int
	 */
	private function _readShort($f){
		$a = unpack('n1n', fread($f, 2));
		return $a['n'];
	}

	/**
	 * Lee un entero long de 32bits
	 *
	 * @param resource $f
	 * @return int
	 */
	private function _readLong($f){
		$a = unpack('N1N', fread($f, 4));
		return $a['N'];
	}

	/**
	 * Verifica si un TTF se puede embeber en el PDF
	 *
	 * @param string $file
	 */
	public function checkTTF($file){
		//Check if font license allows embedding
		$f = fopen($file, 'rb');
		if(!$f){
			throw new PdfDocumentException('<B>Error:</B> Can\'t open '.$file);
		}
		//Extract number of tables
		fseek($f, 4, SEEK_CUR);
		$nb = $this->_readShort($f);
		fseek($f, 6, SEEK_CUR);
		//Seek OS/2 table
		$found = false;
		for($i=0;$i<$nb;++$i){
			if(fread($f, 4)=='OS/2'){
				$found = true;
				break;
			}
			fseek($f, 12, SEEK_CUR);
		}
		if(!$found){
			fclose($f);
			return;
		}
		fseek($f, 4, SEEK_CUR);
		$offset = $this->_readLong($f);
		fseek($f, $offset, SEEK_SET);
		//Extract fsType flags
		fseek($f, 8, SEEK_CUR);
		$fsType = $this->_readShort($f);
		$rl = ($fsType & 0x02)!=0;
		$pp = ($fsType & 0x04)!=0;
		$e = ($fsType & 0x08)!=0;
		fclose($f);
		if($rl && !$pp && !$e){
			throw new PdfDocumentException('Font license does not allow embedding');
		}
	}

	/**
	 * Establece el color para las operaciones de relleno
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 */
	public function setFillColor($red, $green=-1, $blue=-1){
		if($red instanceof PdfColor){
			$color = $red;
			$this->_fillColor = sprintf('%.3f %.3f %.3f rg', $color->getRed(), $color->getGreen(), $color->getBlue());
		} else {
			if(($red==0&&$green==0&&$blue==0)||$green==-1){
				$this->_fillColor = sprintf('%.3f g', $red/255);
			} else {
				$this->_fillColor = sprintf('%.3f %.3f %.3f rg', $red/255, $green/255, $blue/255);
			}
		}
		$this->_colorFlag = ($this->_fillColor!=$this->_textColor);
		if($this->_activePage>0){
			$this->_out($this->_fillColor);
		}
	}

	/**
	 * Set color for text
	 *
	 * @param integer $red
	 * @param integer $green
	 * @param integer $blue
	 */
	public function setTextColor($red, $green=-1, $blue=-1){
		if($red instanceof PdfColor){
			$color = $red;
			$this->_textColor = sprintf('%.3f %.3f %.3f rg', $color->getRed(), $color->getGreen(), $color->getBlue());
		} else {
			if(($red==0 && $green==0 && $blue==0) || $green==-1){
				$this->_textColor = sprintf('%.3f g', $red/255);
			} else {
				$this->_textColor = sprintf('%.3f %.3f %.3f rg', $red/255, $green/255, $blue/255);
			}
		}
		$this->_colorFlag = ($this->_fillColor!=$this->_textColor);
	}

	/**
	 * Get width of a string in the current font
	 *
	 * @param string $s
	 * @return integer
	 */
	public function GetStringWidth($s){
		$s = (string)$s;
		$cw = &$this->_currentFont['cw'];
		$w = 0;
		$l = strlen($s);
		for($i=0;$i<$l;++$i){
			$w+=$cw[$s{$i}];
		}
		return $w*$this->_fontSize/1000;
	}

	/**
	 * Set line width
	 *
	 * @param integer $width
	 */
	public function setLineWidth($width){
		$this->_lineWidth = $width;
		if($this->_activePage>0){
			$this->_out(sprintf('%.2f w',$width*$this->_scaleFactor));
		}
	}

	/**
	 * Draw a line
	 *
	 * @param integer $x1
	 * @param integer $y1
	 * @param integer $x2
	 * @param integer $y2
	 */
	public function drawLine($x1, $y1, $x2, $y2){
		$this->_out(sprintf('%.2f %.2f m %.2f %.2f l S',$x1*$this->_scaleFactor,($this->_height-$y1)*$this->_scaleFactor,$x2*$this->_scaleFactor,($this->_height-$y2)*$this->_scaleFactor));
	}

	/**
	 * Draw a rectangle
	 *
	 * @param integer $x
	 * @param integer $y
	 * @param integer $w
	 * @param integer $h
	 * @param string $style
	 */
	public function drawRect($x, $y, $w, $h, $style=''){
		if($style=='F'){
			$op='f';
		} else {
			if($style=='FD' || $style=='DF'){
				$op='B';
			} else {
				$op='S';
			}
		}
		$this->_out(sprintf('%.2f %.2f %.2f %.2f re %s',$x*$this->_scaleFactor,($this->_height-$y)*$this->_scaleFactor,$w*$this->_scaleFactor,-$h*$this->_scaleFactor,$op));
	}

	/**
	 * Add a TrueType or Type1 font
	 *
	 * @param string $family
	 * @param string $style
	 * @param string $file
	 */
	public function addFont($family, $style='', $file=''){
		$family = strtolower($family);
		if($file==''){
			$file = str_replace(' ','',$family).strtolower($style).'.php';
		}
		if($family=='arial'){
			$family = 'helvetica';
		}
		$style = strtoupper($style);
		if($style=='IB'){
			$style = 'BI';
		}
		$fontkey = $family.$style;
		if(isset($this->_fonts[$fontkey])){
			throw new PdfDocumentException('Font already added: '.$family.' '.$style);
		}
		include($this->_getfontpath().$file);
		if(!isset($name)){
			throw new PdfDocumentException('Could not include font definition file');
		}
		$i = count($this->_fonts)+1;
		$this->_fonts[$fontkey] = array(
			'i' => $i,
			'type' => $type,
			'name' => $name,
			'desc' => $desc,
			'up' => $up,
			'ut' => $ut,
			'cw' => $cw,
			'enc' => $enc,
			'file' => $file
		);
		if($diff){
			//Search existing encodings
			$d = 0;
			$nb = count($this->_diffs);
			for($i=1;$i<=$nb;++$i){
				if($this->_diffs[$i]==$diff){
					$d=$i;
					break;
				}
			}
			if($d==0){
				$d = $nb+1;
				$this->_diffs[$d] = $diff;
			}
			$this->_fonts[$fontkey]['diff'] = $d;
		}
		if($file){
			if($type=='TrueType'){
				$this->_fontFiles[$file] = array(
					'length1' => $originalsize
				);
			} else {
				$this->_fontFiles[$file] = array(
					'length1' => isset($size1)  ? $size1 : 0,
					'length2' => isset($size2)  ? $size2 : 0
				);
			}
		}
	}

	/**
	 * Select a font; size given in points
	 *
	 * @param string $family
	 * @param string $style
	 * @param integer $size
	 */
	public function setFont($family, $style='', $size=0){

		$family = strtolower($family);
		if($family==''){
			$family = $this->_fontFamily;
		}
		if($family=='arial'){
			$family = 'helvetica';
		} else {
			if($family=='symbol' || $family=='zapfdingbats'){
				$style = '';
			}
		}
		$style = strtoupper($style);
		if(strpos($style,'U')!==false){
			$this->_underline = true;
			$style=str_replace('U', '', $style);
		} else {
			$this->_underline = false;
		}
		if($style=='IB'){
			$style = 'BI';
		}
		if($size==0){
			$size = $this->_fontSizePt;
		}
		//Test if font is already selected
		if($this->_fontFamily==$family && $this->_fontStyle==$style && $this->_fontSizePt==$size){
			return;
		}
		//Test if used for the first time
		$fontkey = $family.$style;
		if(!isset($this->_fonts[$fontkey])){
			//Check if one of the standard fonts
			if(isset(self::$_coreFonts[$fontkey])){
				if(!isset($fpdfCharWidths[$fontkey])){
					//Load metric file
					$file = $family;
					if($family=='times'||$family=='helvetica'){
						$file.=strtolower($style);
					}
					require $this->_getFontPath().$file.'.php';
					if(!isset($pdfCharWidths[$fontkey])){
						throw new PdfDocumentException('Could not include font metric file');
					}
				}
				$i = count($this->_fonts)+1;
				$this->_fonts[$fontkey] = array(
					'i' => $i,
					'type' => 'core',
					'name' => self::$_coreFonts[$fontkey],
					'up' => -100,
					'ut' => 50,
					'cw' => $pdfCharWidths[$fontkey]
				);
			} else {
				throw new PdfDocumentException('Undefined font: '.$family.' '.$style);
			}
		}
		//Select it
		$this->_fontFamily = $family;
		$this->_fontStyle = $style;
		$this->_fontSizePt = $size;
		$this->_fontSize = $size/$this->_scaleFactor;
		$this->_currentFont = &$this->_fonts[$fontkey];
		if($this->_activePage>0){
			$this->_out(sprintf('BT /F%d %.2f Tf ET', $this->_currentFont['i'], $this->_fontSizePt));
		}
	}

	/**
	 * Set font size in points
	 *
	 * @param integer $size
	 */
	public function setFontSize($size){
		if($this->_fontSizePt==$size){
			return;
		}
		$this->_fontSizePt = $size;
		$this->_fontSize = $size/$this->_scaleFactor;
		if($this->_activePage>0){
			$this->_out(sprintf('BT /F%d %.2f Tf ET', $this->_currentFont['i'], $this->_fontSizePt));
		}
	}

	/**
	 * Create a new internal link
	 *
	 * @return integer
	 */
	public function addLink(){
		$n = count($this->_links)+1;
		$this->_links[$n] = array(0,0);
		return $n;
	}

	/**
	 * Set destination of internal link
	 *
	 * @param string $link
	 * @param integer $y
	 * @param integer $page
	 */
	public function setLink($link, $y=0, $page=-1){
		if($y==-1){
			$y = $this->_y;
		}
		if($page==-1){
			$page = $this->_activePage;
		}
		$this->_links[$link] = array($page, $y);
	}

	/**
	 * Put a link on the page
	 *
	 * @param integer $x
	 * @param integer $y
	 * @param integer $w
	 * @param integer $h
	 * @param string $link
	 */
	public function Link($x, $y, $w, $h, $link){
		$this->_activePageLinks[$this->_activePage][] = array($x*$this->_scaleFactor, $this->_heightPoint-$y*$this->_scaleFactor, $w*$this->_scaleFactor, $h*$this->_scaleFactor,$link);
	}

	/**
	 * Output a string
	 *
	 * @param integer $x
	 * @param integer $y
	 * @param integer $txt
	 */
	public function Text($x, $y, $txt){
		$s = sprintf('BT %.2f %.2f Td (%s) Tj ET', $x*$this->_scaleFactor, ($this->_height-$y)*$this->_scaleFactor, $this->_escape($txt));
		if($this->_underline && $txt!=''){
			$s.=' '.$this->_dounderline($x,$y,$txt);
		}
		if($this->_colorFlag){
			$s='q '.$this->_textColor.' '.$s.' Q';
		}
		$this->_out($s);
	}

	/**
	 * Accept automatic page break or not
	 *
	 * @return integer
	 */
	public function acceptPageBreak(){
		return $this->_autoPageBreak;
	}

	/**
	 * Output a cell
	 *
	 * @param integer $w
	 * @param integer $h
	 * @param string $txt
	 * @param integer $border
	 * @param integer $ln
	 * @param string $align
	 * @param integer $fill
	 * @param string $link
	 */
	public function writeCell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link=''){
		$k = $this->_scaleFactor;
		if($this->_y+$h>$this->_activePageBreakTrigger && !$this->_inFooter && $this->AcceptPageBreak()){
			//Automatic page break
			$x = $this->_x;
			$ws = $this->_ws;
			if($ws>0){
				$this->_ws = 0;
				$this->_out('0 Tw');
			}
			$this->addPage($this->_currentOrientation);
			$this->_x = $x;
			if($ws>0){
				$this->_ws = $ws;
				$this->_out(sprintf('%.3f Tw', $ws*$k));
			}
		}
		if($w==0){
			$w = $this->_width-$this->_rightMargin-$this->_x;
		}
		$s='';
		if($fill==1||$border==1){
			if($fill==1){
				$op = ($border==1) ? 'B' : 'f';
			} else {
				$op = 'S';
			}
			$s = sprintf('%.2f %.2f %.2f %.2f re %s ',$this->_x*$k,($this->_height-$this->_y)*$k,$w*$k,-$h*$k,$op);
		}
		if(is_string($border)){
			$x = $this->_x;
			$y = $this->_y;
			if(strpos($border, 'L')!==false){
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->_height-$y)*$k,$x*$k,($this->_height-($y+$h))*$k);
			}
			if(strpos($border, 'T')!==false){
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->_height-$y)*$k,($x+$w)*$k,($this->_height-$y)*$k);
			}
			if(strpos($border, 'R')!==false){
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->_height-$y)*$k,($x+$w)*$k,($this->_height-($y+$h))*$k);
			}
			if(strpos($border, 'B')!==false){
				$s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->_height-($y+$h))*$k,($x+$w)*$k,($this->_height-($y+$h))*$k);
			}
		}
		if($txt!==''){
			if($align==self::ALIGN_RIGHT){
				$dx = $w-$this->_cellMargin-$this->GetStringWidth($txt);
			} else {
				if($align==self::ALIGN_CENTER){
					$dx = ($w-$this->GetStringWidth($txt))/2;
				} else {
					$dx = $this->_cellMargin;
				}
			}
			$txt = $this->_getEncodedString($txt);
			if($this->_colorFlag){
				$s.= 'q '.$this->_textColor.' ';
				$txt2 = str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
				$s.= sprintf('BT %.2f %.2f Td (%s) Tj ET',($this->_x+$dx)*$k,($this->_height-($this->_y+.5*$h+.3*$this->_fontSize))*$k,$txt2);
			}
			if($this->_underline){
				$s.=' '.$this->_doUnderline($this->_x+$dx, $this->_y+.5*$h+.3*$this->_fontSize, $txt);
			}
			if($this->_colorFlag){
				$s.=' Q';
			}
			if($link){
				$this->Link($this->_x+$dx,$this->_y+.5*$h-.5*$this->_fontSize,$this->GetStringWidth($txt),$this->_fontSize,$link);
			}
		}
		if($s){
			$this->_out($s);
			$this->_lastHeight=$h;
		}
		if($ln>0){
			//Go to next line
			$this->_y+=$h;
			if($ln==1){
				$this->_x = $this->_leftMargin;
			}
		} else {
			$this->_x+=$w;
		}
	}

	/**
	 * Devuelve una cadena codificada en ISO8859-1
	 *
	 * @param string $txt
	 * @return string
	 */
	private function _getEncodedString($txt){
		if($this->_encoding==self::ENC_ISO88591){
			return utf8_encode($txt);
		} else {
			if($this->_encoding==self::ENC_UTF8){
				return utf8_decode($txt);
			}
		}
		return $txt;
	}

	/**
	 * Output text with automatic or explicit line breaks
	 *
	 * @param integer $w
	 * @param integer $h
	 * @param string $txt
	 * @param integer $border
	 * @param string $align
	 * @param integer $fill
	 */
	public function writeMultiCell($w, $h, $txt, $border=0, $align=self::ALIGN_JUSTIFY, $fill=0){
		$cw = &$this->_currentFont['cw'];
		if($w==0){
			$w = $this->_width-$this->_rightMargin-$this->_x;
		}
		$wmax = ($w-2*$this->_cellMargin)*1000/$this->_fontSize;
		$s = str_replace("\r", '', $txt);
		$nb = strlen($s);
		if($nb>0 && $s[$nb-1]=="\n"){
			$nb--;
		}
		$b = 0;
		if($border){
			if($border==1){
				$border = 'LTRB';
				$b = 'LRT';
				$b2 = 'LR';
			} else {
				$b2 = '';
				if(strpos($border, 'L')!==false){
					$b2.='L';
				}
				if(strpos($border, 'R')!==false){
					$b2.='R';
				}
				$b = (strpos($border, 'T')!==false) ? $b2.'T' : $b2;
			}
		}
		$sep =-1;
		$i = 0;
		$j = 0;
		$l = 0;
		$ns = 0;
		$nl = 1;
		while($i<$nb){
			//Get next character
			$c = $s{$i};
			if($c=="\n"){
				//Explicit line break
				if($this->_ws>0){
					$this->_ws=0;
					$this->_out('0 Tw');
				}
				$this->writeCell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
				++$i;
				$sep =-1;
				$j = $i;
				$l = 0;
				$ns = 0;
				++$nl;
				if($border && $nl==2){
					$b = $b2;
				}
				continue;
			}
			if($c==' '){
				$sep = $i;
				$ls = $l;
				++$ns;
			}
			$l+= $cw[$c];
			if($l>$wmax){
				//Automatic line break
				if($sep==-1){
					if($i==$j){
						++$i;
					}
					if($this->_ws>0){
						$this->_ws = 0;
						$this->_out('0 Tw');
					}
					$this->writeCell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
				} else {
					if($align==self::ALIGN_JUSTIFY){
						$this->_ws = ($ns>1) ? ($wmax-$ls)/1000*$this->_fontSize/($ns-1) : 0;
						$this->_out(sprintf('%.3f Tw',$this->_ws*$this->_scaleFactor));
					}
					$this->writeCell($w, $h, substr($s, $j, $sep-$j), $b, 2, $align, $fill);
					$i = $sep+1;
				}
				$sep=-1;
				$j = $i;
				$l = 0;
				$ns = 0;
				++$nl;
				if($border && $nl==2){
					$b = $b2;
				}
			} else {
				$i++;
			}
		}
		//Last chunk
		if($this->_ws>0){
			$this->_ws=0;
			$this->_out('0 Tw');
		}
		if($border && strpos($border,'B')!==false){
			$b.='B';
		}
		$this->writeCell($w, $h, substr($s,$j,$i-$j), $b, 2, $align, $fill);
		$this->_x = $this->_leftMargin;
	}

	/**
	 * Output text in flowing mode
	 *
	 * @param integer $h
	 * @param string $txt
	 * @param string $link
	 */
	public function Write($h, $txt, $link=''){
		$cw = &$this->_currentFont['cw'];
		$w = $this->_width-$this->_rightMargin-$this->_x;
		$wmax = ($w-2*$this->_cellMargin)*1000/$this->_fontSize;
		$s = str_replace("\r",'',$txt);
		$nb = strlen($s);
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;
		while($i<$nb){
			//Get next character
			$c = $s{$i};
			if($c=="\n"){
				//Explicit line break
				$this->writeCell($w, $h, substr($s, $j, $i-$j), 0, 2, '', 0, $link);
				++$i;
				$sep=-1;
				$j = $i;
				$l = 0;
				if($nl==1){
					$this->_x = $this->_leftMargin;
					$w = $this->_width-$this->_rightMargin-$this->_x;
					$wmax = ($w-2*$this->_cellMargin)*1000/$this->_fontSize;
				}
				++$nl;
				continue;
			}
			if($c==' '){
				$sep = $i;
			}
			$l+=$cw[$c];
			if($l>$wmax){
				//Automatic line break
				if($sep==-1){
					if($this->_x>$this->_leftMargin){
						//Move to next line
						$this->_x = $this->_leftMargin;
						$this->_y+=$h;
						$w = $this->_width-$this->_rightMargin-$this->_x;
						$wmax = ($w-2*$this->_cellMargin)*1000/$this->_fontSize;
						++$i;
						++$nl;
						continue;
					}
					if($i==$j){
						++$i;
					}
					$this->writeCell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
				} else {
					$this->writeCell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
					$i = $sep+1;
				}
				$sep=-1;
				$j = $i;
				$l = 0;
				if($nl==1){
					$this->_x = $this->_leftMargin;
					$w = $this->_width-$this->_rightMargin-$this->_x;
					$wmax = ($w-2*$this->_cellMargin)*1000/$this->_fontSize;
				}
				++$nl;
			} else {
				++$i;
			}
		}
		//Last chunk
		if($i!=$j){
			$this->writeCell($l/1000*$this->_fontSize,$h,substr($s,$j),0,0,'',0,$link);
		}
	}

	/**
	 * Put an image on the page
	 *
	 * @param string $file
	 * @param integer $x
	 * @param integer $y
	 * @param integer $w
	 * @param integer $h
	 * @param string $type
	 * @param string $link
	 */
	public function addImage($file, $x, $y, $w=0, $h=0, $type='', $link=''){
		if(!isset($this->_images[$file])){
			//First use of image, get info
			if($type==''){
				$pos=strrpos($file,'.');
				if(!$pos){
					throw new PdfDocumentException('Image file has no extension and no type was specified: '.$file);
				}
				$type = substr($file,$pos+1);
			}
			$type = strtolower($type);
			$mqr = get_magic_quotes_runtime();
			set_magic_quotes_runtime(0);
			if($type=='jpg' || $type=='jpeg'){
				$info = $this->_parsejpg($file);
			} else {
				if($type=='png'){
					$info = $this->_parsepng($file);
				} else {
					//Allow for additional formats
					$mtd = '_parse'.$type;
					if(!method_exists($this, $mtd)){
						throw new PdfDocumentException('Unsupported image type: '.$type);
					}
					$info=$this->$mtd($file);
				}
			}
			set_magic_quotes_runtime($mqr);
			$info['i'] = count($this->_images)+1;
			$this->_images[$file]=$info;
		} else {
			$info = $this->_images[$file];
		}
		//Automatic width and height calculation if needed
		if($w==0 && $h==0){
			//Put image at 72 dpi
			$w = $info['w']/$this->_scaleFactor;
			$h = $info['h']/$this->_scaleFactor;
		}
		if($w==0){
			$w = $h*$info['w']/$info['h'];
		}
		if($h==0){
			$h = $w*$info['h']/$info['w'];
		}
		$this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q',$w*$this->_scaleFactor,$h*$this->_scaleFactor,$x*$this->_scaleFactor,($this->_height-($y+$h))*$this->_scaleFactor,$info['i']));
		if($link){
			$this->Link($x,$y,$w,$h,$link);
		}
	}

	/**
	 * Line feed; default value is last cell height
	 *
	 * @param string $h
	 */
	public function lineFeed($h=''){
		$this->_x = $this->_leftMargin;
		if(is_string($h)){
			$this->_y+=$this->_lastHeight;
		} else 	{
			$this->_y+=$h;
		}
	}

	/**
	 * Get x position
	 *
	 * @return integer
	 */
	public function getX(){
		return $this->_x;
	}

	/**
	 * Set x position
	 *
	 * @param integer $x
	 */
	public function setX($x){
		if($x>=0){
			$this->_x = $x;
		} else {
			$this->_x = $this->_width+$x;
		}
	}

	/**
	 * Get y position
	 *
	 * @return integer
	 */
	public function getY(){
		return $this->_y;
	}

	/**
	 * Set y position and reset x
	 *
	 * @param integer $y
	 */
	public function setY($y){
		$this->_x = $this->_leftMargin;
		if($y>=0){
			$this->_y = $y;
		} else {
			$this->_y = $this->_height+$y;
		}
	}

	/**
	 * Set x and y positions
	 *
	 * @param integer $x
	 * @param integer $y
	 */
	public function setXY($x, $y){
		$this->setY($y);
		$this->setX($x);
	}

	/**
	 * Output PDF to some destination
	 *
	 * @param string $name
	 * @param string $dest
	 * @return string
	 */
	public function outputDocument($name='', $dest=''){

		//Finish document if necessary
		if($this->_state<3){
			$this->close();
		}
		//Normalize parameters
		if(is_bool($dest)){
			$dest = $dest ? 'D' : 'F';
		}
		$dest = strtoupper($dest);
		if($dest==''){
			if($name==''){
				$name='doc.pdf';
				$dest='I';
			} else {
				$dest='F';
			}
		}
		switch($dest){
			case 'I':
				//Send to standard output
				if($this->_debug==false){
					if(ob_get_contents()){
						throw new PdfDocumentException('Some data has already been output, can\'t send PDF file');
					}
					if(php_sapi_name()!='cli'){
						//We send to a browser
						if($this->_debug==false){
							header('Content-Type: application/pdf');
							if(headers_sent()){
								throw new PdfDocumentException('Some data has already been output to browser, can\'t send PDF file');
							}
							header('Content-Length: '.strlen($this->_buffer));
							header('Content-disposition: inline; filename="'.$name.'"');
						}
					}
				}
				echo $this->_buffer;
				break;
			case 'D':
				//Download file
				if(ob_get_contents()){
					throw new PdfDocumentException('Some data has already been output, can\'t send PDF file');
				}
				if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')){
					header('Content-Type: application/force-download');
				} else {
					header('Content-Type: application/octet-stream');
				}
				if(headers_sent()){
					throw new PdfDocumentException('Some data has already been output to browser, can\'t send PDF file');
				}
				header('Content-Length: '.strlen($this->_buffer));
				header('Content-disposition: attachment; filename="'.$name.'"');
				echo $this->_buffer;
				break;
			case 'F':
				//Save to local file
				$f = fopen($name,'wb');
				if(!$f){
					throw new PdfDocumentException('Unable to create output file: '.$name);
				}
				fwrite($f,$this->_buffer,strlen($this->_buffer));
				fclose($f);
				break;
			case 'S':
				//Return as a string
				return $this->_buffer;
			default:
				throw new PdfDocumentException('Incorrect output destination: '.$dest);
		}
		return '';
	}

	/**
	 * Envia el documento PDF a la salida estándar
	 *
	 */
	public function outputToBrowser(){
		$this->outputDocument('', 'I');
	}

	/**
	 * Devuelve el ancho disponible para utilizar (sin margenes)
	 *
	 * @return int
	 */
	public function getAvailableWidth(){
		return $this->_width-($this->_leftMargin+$this->_rightMargin);
	}

	/*******************************************************************************
	*                                                                              *
	*                              Protected methods                               *
	*                                                                              *
	*******************************************************************************/
	/**
	 * Check for locale-related bug
	 *
	 */
	protected function doLocaleChecks(){
		if(1.1==1){
			throw new PdfDocumentException('Don\'t alter the locale before including class file');
		}
		//Check for decimal separator
		if(sprintf('%.1f',1.0)!='1.0'){
			setlocale(LC_NUMERIC, 'C');
		}
	}

	/**
	 * Obtener el path a las fuentes
	 *
	 * @return string
	 */
	protected function _getFontPath(){
		return $this->_fontPath;
	}

	/**
	 * Establece el PATH donde se encuentran las fuentes
	 *
	 * @param string $path
	 */
	public function setFontPath($path){
		$this->_fontPath = $path;
	}

	/**
	 * Generar Paginas
	 *
	 */
	protected function _putPages(){
		$nb = $this->_activePage;
		if(!empty($this->_aliasNbPages)){
			//Replace number of pages
			for($n=1;$n<=$nb;++$n){
				$this->_pages[$n] = str_replace($this->_aliasNbPages, $nb, $this->_pages[$n]);
			}
		}
		if($this->_defaultOrientation==self::ORI_PORTRAIT){
			$wPt = $this->_fWidthPoint;
			$hPt = $this->_fHeightPoint;
		} else {
			$wPt = $this->_fHeightPoint;
			$hPt = $this->_fWidthPoint;
		}
		$filter = ($this->_compress) ? '/Filter /FlateDecode ' : '';
		for($n=1;$n<=$nb;++$n){
			//Page
			$this->_newobj();
			$this->_out('<</Type /Page');
			$this->_out('/Parent 1 0 R');
			if(isset($this->_orientationChanges[$n])){
				$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$hPt,$wPt));
			}
			$this->_out('/Resources 2 0 R');
			if(isset($this->_activePageLinks[$n])){
				//Links
				$annots='/Annots [';
				foreach($this->_activePageLinks[$n] as $pl){
					$rect = sprintf('%.2f %.2f %.2f %.2f', $pl[0], $pl[1], $pl[0]+$pl[2], $pl[1]-$pl[3]);
					$annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
					if(is_string($pl[4])){
						$annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
					} else {
						$l =$this->_links[$pl[4]];
						$h = isset($this->_orientationChanges[$l[0]]) ? $wPt : $hPt;
						$annots.= sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]>>', 1+2*$l[0], $h-$l[1]*$this->_scaleFactor);
					}
				}
				$this->_out($annots.']');
			}
			$this->_out('/Contents '.($this->_n+1).' 0 R>>');
			$this->_out('endobj');
			//Page content
			$p = ($this->_compress) ? gzcompress($this->_pages[$n]) : $this->_pages[$n];
			$this->_newobj();
			$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
			$this->_putstream($p);
			$this->_out('endobj');
		}
		//Pages root
		$this->_offsets[1] = strlen($this->_buffer);
		$this->_out('1 0 obj');
		$this->_out('<</Type /Pages');
		$kids='/Kids [';
		for($i=0;$i<$nb;$i++){
			$kids.=(3+2*$i).' 0 R ';
		}
		$this->_out($kids.']');
		$this->_out('/Count '.$nb);
		$this->_out(sprintf('/MediaBox [0 0 %.2f %.2f]',$wPt,$hPt));
		$this->_out('>>');
		$this->_out('endobj');
	}

	/**
	 * Colocar fuentes
	 *
	 */
	protected function _putFonts(){
		$nf = $this->_n;
		foreach($this->_diffs as $diff){
			//Encodings
			$this->_newobj();
			$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
			$this->_out('endobj');
		}
		$mqr = get_magic_quotes_runtime();
		set_magic_quotes_runtime(0);
		foreach($this->_fontFiles as $file=>$info){
			//Font file embedding
			$this->_newobj();
			$this->_fontFiles[$file]['n']=$this->_n;
			$font='';
			$f=fopen($this->_getfontpath().$file,'rb',1);
			if(!$f){
				throw new PdfDocumentException('Font file not found');
			}
			while(!feof($f)){
				$font.=fread($f,8192);
			}
			fclose($f);
			$compressed=(substr($file,-2)=='.z');
			if(!$compressed && isset($info['length2'])){
				$header=(ord($font{0})==128);
				if($header){
					//Strip first binary header
					$font = substr($font,6);
				}
				if($header && ord($font{$info['length1']})==128){
					//Strip second binary header
					$font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
				}
			}
			$this->_out('<</Length '.strlen($font));
			if($compressed){
				$this->_out('/Filter /FlateDecode');
			}
			$this->_out('/Length1 '.$info['length1']);
			if(isset($info['length2'])){
				$this->_out('/Length2 '.$info['length2'].' /Length3 0');
			}
			$this->_out('>>');
			$this->_putstream($font);
			$this->_out('endobj');
		}
		set_magic_quotes_runtime($mqr);
		foreach($this->_fonts as $k => $font){
			//Font objects
			$this->_fonts[$k]['n'] = $this->_n+1;
			$type = $font['type'];
			$name = $font['name'];
			if($type=='core'){
				//Standard font
				$this->_newobj();
				$this->_out('<</Type /Font');
				$this->_out('/BaseFont /'.$name);
				$this->_out('/Subtype /Type1');
				if($name!='Symbol' && $name!='ZapfDingbats'){
					$this->_out('/Encoding /WinAnsiEncoding');
				}
				$this->_out('>>');
				$this->_out('endobj');
			} else {
				if($type==PdfDocument::FONT_TYPE_TYPE1||$type==PdfDocument::FONT_TYPE_TRUETYPE){
					//Additional Type1 or TrueType font
					$this->_newobj();
					$this->_out('<</Type /Font');
					$this->_out('/BaseFont /'.$name);
					$this->_out('/Subtype /'.$type);
					$this->_out('/FirstChar 32 /LastChar 255');
					$this->_out('/Widths '.($this->_n+1).' 0 R');
					$this->_out('/FontDescriptor '.($this->_n+2).' 0 R');
					if($font['enc']){
						if(isset($font['diff'])){
							$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
						} else {
							$this->_out('/Encoding /WinAnsiEncoding');
						}
					}
					$this->_out('>>');
					$this->_out('endobj');
					//Widths
					$this->_newobj();
					$cw = &$font['cw'];
					$s = '[';
					for($i=32;$i<=255;++$i){
						$s.=$cw[chr($i)].' ';
					}
					$this->_out($s.']');
					$this->_out('endobj');
					//Descriptor
					$this->_newobj();
					$s = '<</Type /FontDescriptor /FontName /'.$name;
					foreach($font['desc'] as $k => $v){
						$s.=' /'.$k.' '.$v;
					}
					$file = $font['file'];
					if($file){
						$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->_fontFiles[$file]['n'].' 0 R';
					}
					$this->_out($s.'>>');
					$this->_out('endobj');
				} else {
					//Allow for additional types
					$mtd = '_put'.strtolower($type);
					if(!method_exists($this, $mtd)){
						throw new PdfDocumentException('Unsupported font type: '.$type);
					}
					$this->$mtd($font);
				}
			}
		}
	}

	private function _putImages(){
		$filter = ($this->_compress) ? '/Filter /FlateDecode ' : '';
		reset($this->_images);
		while(list($file,$info)=each($this->_images)){
			$this->_newobj();
			$this->_images[$file]['n']=$this->_n;
			$this->_out('<</Type /XObject');
			$this->_out('/Subtype /Image');
			$this->_out('/Width '.$info['w']);
			$this->_out('/Height '.$info['h']);
			if($info['cs']=='Indexed'){
				$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->_n+1).' 0 R]');
			} else {
				$this->_out('/ColorSpace /'.$info['cs']);
				if($info['cs']=='DeviceCMYK'){
					$this->_out('/Decode [1 0 1 0 1 0 1 0]');
				}
			}
			$this->_out('/BitsPerComponent '.$info['bpc']);
			if(isset($info['f'])){
				$this->_out('/Filter /'.$info['f']);
			}
			if(isset($info['parms'])){
				$this->_out($info['parms']);
			}
			if(isset($info['trns']) && is_array($info['trns'])){
				$trns = '';
				for($i=0;$i<count($info['trns']);++$i){
					$trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
				}
				$this->_out('/Mask ['.$trns.']');
			}
			$this->_out('/Length '.strlen($info['data']).'>>');
			$this->_putstream($info['data']);
			unset($this->_images[$file]['data']);
			$this->_out('endobj');
			//Palette
			if($info['cs']=='Indexed'){
				$this->_newobj();
				$pal=($this->_compress) ? gzcompress($info['pal']) : $info['pal'];
				$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
				$this->_putstream($pal);
				$this->_out('endobj');
			}
		}
	}

	private function _putxobjectdict(){
		foreach($this->_images as $image){
			$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
		}
	}

	private function _putresourcedict(){
		$this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
		$this->_out('/Font <<');
		foreach($this->_fonts as $font){
			$this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
		}
		$this->_out('>>');
		$this->_out('/XObject <<');
		$this->_putxobjectdict();
		$this->_out('>>');
	}

	private function _putResources(){
		$this->_putfonts();
		$this->_putimages();
		//Resource dictionary
		$this->_offsets[2] = strlen($this->_buffer);
		$this->_out('2 0 obj');
		$this->_out('<<');
		$this->_putResourceDict();
		$this->_out('>>');
		$this->_out('endobj');
	}

	/**
	 * Imprime la información del encabezado del documento
	 *
	 */
	private function _putInfo(){
		$this->_out('/Producer '.$this->_textstring('KEF '.Core::FRAMEWORK_VERSION));
		if(!empty($this->_title)){
			$this->_out('/Title '.$this->_textstring($this->_title));
		}
		if(!empty($this->_subject)){
			$this->_out('/Subject '.$this->_textstring($this->_subject));
		}
		if(!empty($this->_author)){
			$this->_out('/Author '.$this->_textstring($this->_author));
		}
		if(!empty($this->_keywords)){
			$this->_out('/Keywords '.$this->_textstring($this->_keywords));
		}
		if(!empty($this->_creator)){
			$this->_out('/Creator '.$this->_textstring($this->_creator));
		}
		$this->_out('/CreationDate '.$this->_textstring('D:'.date('YmdHis')));
	}

	private function _putCatalog(){
		$this->_out('/Type /Catalog');
		$this->_out('/Pages 1 0 R');
		if($this->_zoomMode==self::ZOOM_FULLPAGE){
			$this->_out('/OpenAction [3 0 R /Fit]');
		} else {
			if($this->_zoomMode==self::ZOOM_FULLWIDTH){
				$this->_out('/OpenAction [3 0 R /FitH null]');
			} else {
				if($this->_zoomMode==self::ZOOM_REAL){
					$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
				} else {
					if(!is_string($this->_zoomMode)){
						$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->_zoomMode/100).']');
					}
				}
			}
		}
		if($this->_layoutMode==self::LAYOUT_SINGLE){
			$this->_out('/PageLayout /SinglePage');
		} else {
			if($this->_layoutMode==self::LAYOUT_CONTINUOUS){
				$this->_out('/PageLayout /OneColumn');
			} else {
				if($this->_layoutMode==self::LAYOUT_TWO){
					$this->_out('/PageLayout /TwoColumnLeft');
				}
			}
		}
	}

	/**
	 * Imprime el encabezado del documento PDF
	 *
	 */
	private function _putHeader(){
		$this->_out('%PDF-'.$this->_pdfVersion);
	}

	/**
	 * Imprime el pie de pagina del document PDF
	 *
	 */
	private function _putTrailer(){
		$this->_out('/Size '.($this->_n+1));
		$this->_out('/Root '.$this->_n.' 0 R');
		$this->_out('/Info '.($this->_n-1).' 0 R');
	}

	private function _endDoc(){
		$this->_putHeader();
		$this->_putPages();
		$this->_putResources();
		//Info
		$this->_newobj();
		$this->_out('<<');
		$this->_putinfo();
		$this->_out('>>');
		$this->_out('endobj');
		//Catalog
		$this->_newobj();
		$this->_out('<<');
		$this->_putcatalog();
		$this->_out('>>');
		$this->_out('endobj');
		//Cross-ref
		$o=strlen($this->_buffer);
		$this->_out('xref');
		$this->_out('0 '.($this->_n+1));
		$this->_out('0000000000 65535 f ');
		for($i=1;$i<=$this->_n;++$i){
			$this->_out(sprintf('%010d 00000 n ', $this->_offsets[$i]));
		}
		//Trailer
		$this->_out('trailer');
		$this->_out('<<');
		$this->_putTrailer();
		$this->_out('>>');
		$this->_out('startxref');
		$this->_out($o);
		$this->_out('%%EOF');
		$this->_state = 3;
	}

	/**
	 * Inicia una pagina
	 *
	 * @param string $orientation
	 */
	protected function _beginPage($orientation){
		++$this->_activePage;
		$this->_pages[$this->_activePage] = '';
		$this->_state = 2;
		$this->_x = $this->_leftMargin;
		$this->_y = $this->_topMargin;
		$this->_fontFamily='';
		//Page orientation
		if(!$orientation){
			$orientation=$this->_defaultOrientation;
		} else {
			$orientation=strtoupper($orientation{0});
			if($orientation!=$this->_defaultOrientation){
				$this->_orientationChanges[$this->_activePage]=true;
			}
		}
		if($orientation!=$this->_currentOrientation){
			//Change orientation
			if($orientation==self::ORI_PORTRAIT){
				$this->_widthPoint = $this->_fWidthPoint;
				$this->_heightPoint = $this->_fHeightPoint;
				$this->_width = $this->_fWidth;
				$this->_height = $this->_fHeight;
			} else {
				$this->_widthPoint = $this->_fHeightPoint;
				$this->_heightPoint = $this->_fWidthPoint;
				$this->_width = $this->_fHeight;
				$this->_height = $this->_fWidth;
			}
			$this->_activePageBreakTrigger = $this->_height-$this->_bottomMargin;
			$this->_currentOrientation = $orientation;
		}
	}

	protected function _endpage(){
		//End of page contents
		$this->_state = 1;
	}

	protected function _newobj(){
		//Begin a new object
		++$this->_n;
		$this->_offsets[$this->_n] = strlen($this->_buffer);
		$this->_out($this->_n.' 0 obj');
	}

	/**
	 * Underline text
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $txt
	 * @return string
	 */
	protected function _doUnderline($x, $y, $txt){
		//
		$up = $this->_currentFont['up'];
		$ut = $this->_currentFont['ut'];
		$w = $this->GetStringWidth($txt)+$this->_ws*substr_count($txt,' ');
		return sprintf('%.2f %.2f %.2f %.2f re f',$x*$this->_scaleFactor,($this->_height-($y-$up/1000*$this->_fontSize))*$this->_scaleFactor,$w*$this->_scaleFactor,-$ut/1000*$this->_fontSizePt);
	}

	/**
	 * Extract info from a JPEG file
	 *
	 * @param string $file
	 * @return string
	 */
	protected function _parseJpg($file){
		$a = GetImageSize($file);
		if(!$a){
			throw new PdfDocumentException('Missing or incorrect image file: '.$file);
		}
		if($a[2]!=2){
			throw new PdfDocumentException('Not a JPEG file: '.$file);
		}
		if(!isset($a['channels']) || $a['channels']==3){
			$colspace='DeviceRGB';
		} else {
			if($a['channels']==4){
				$colspace='DeviceCMYK';
			}else {
				$colspace='DeviceGray';
			}
		}
		$bpc = isset($a['bits']) ? $a['bits'] : 8;
		//Read whole file
		$f = fopen($file,'rb');
		$data = '';
		while(!feof($f)){
			$data.=fread($f,4096);
		}
		fclose($f);
		return array(
			'w' => $a[0],
			'h' => $a[1],
			'cs' => $colspace,
			'bpc' => $bpc,
			'f' => 'DCTDecode',
			'data' => $data
		);
	}

	/**
	 * Extract info from a PNG file
	 *
	 * @param string $file
	 * @return string
	 */
	protected function _parsePNG($file){
		$f = fopen($file,'rb');
		if(!$f){
			throw new PdfDocumentException('Can\'t open image file: '.$file);
		}
		//Check signature
		if(fread($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10)){
			throw new PdfDocumentException('Not a PNG file: '.$file);
		}
		//Read header chunk
		fread($f,4);
		if(fread($f,4)!='IHDR'){
			throw new PdfDocumentException('Incorrect PNG file: '.$file);
		}
		$w = $this->_freadint($f);
		$h = $this->_freadint($f);
		$bpc = ord(fread($f, 1));
		if($bpc>8){
			throw new PdfDocumentException('16-bit depth not supported: '.$file);
		}
		$ct = ord(fread($f,1));
		if($ct==0){
			$colspace='DeviceGray';
		} else{
			if($ct==2){
				$colspace='DeviceRGB';
			} else {
				if($ct==3){
					$colspace='Indexed';
				} else {
					throw new PdfDocumentException('Alpha channel not supported: '.$file);
				}
			}
		}
		if(ord(fread($f, 1))!=0){
			throw new PdfDocumentException('Unknown compression method: '.$file);
		}
		if(ord(fread($f, 1))!=0){
			throw new PdfDocumentException('Unknown filter method: '.$file);
		}
		if(ord(fread($f, 1))!=0){
			throw new PdfDocumentException('Interlacing not supported: '.$file);
		}
		fread($f, 4);
		$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
		//Scan chunks looking for palette, transparency and image data
		$pal = '';
		$trns = '';
		$data = '';
		do {
			$n = $this->_freadint($f);
			$type = fread($f,4);
			if($type=='PLTE'){
				//Read palette
				$pal = fread($f, $n);
				fread($f,4);
			}
			elseif($type=='tRNS')
			{
				//Read transparency info
				$t=fread($f,$n);
				if($ct==0)
				$trns=array(ord(substr($t,1,1)));
				elseif($ct==2)
				$trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
				else
				{
					$pos=strpos($t,chr(0));
					if($pos!==false)
					$trns=array($pos);
				}
				fread($f,4);
			}
			elseif($type=='IDAT')
			{
				//Read image data block
				$data.=fread($f,$n);
				fread($f,4);
			}
			elseif($type=='IEND')
			break;
			else {
			fread($f,$n+4);
			}
		} while($n);
		if($colspace=='Indexed' && empty($pal)){
			throw new PdfDocumentException('Missing palette in '.$file);
		}
		fclose($f);
		return array(
			'w' => $w,
			'h' => $h,
			'cs' => $colspace,
			'bpc' => $bpc,
			'f' => 'FlateDecode',
			'parms' => $parms,
			'pal' => $pal,
			'trns' => $trns,
			'data' => $data
		);
	}

	/**
	 * Read a 4-byte integer from file
	 *
	 * @param string $f
	 * @return int
	 */
	protected function _fReadInt($f){
		$a = unpack('Ni', fread($f,4));
		return $a['i'];
	}

	/**
	 * Format a text string
	 *
	 * @param string $s
	 * @return string
	 */
	protected function _textstring($s){
		return '('.$this->_escape($s).')';
	}

	/**
	 * Add \ before \, ( and )
	 *
	 * @param string $s
	 * @return string
	 */
	private function _escape($s){
		return str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$s)));
	}

	/**
	 * Enter description here...
	 *
	 * @param string $s
	 */
	private function _putstream($s){
		$this->_out('stream');
		$this->_out($s);
		$this->_out('endstream');
	}

	/**
	 * Add a line to the document
	 *
	 * @param string $s
	 */
	private function _out($s){
		if($this->_state==2){
			$this->_pages[$this->_activePage].=$s."\n";
		} else {
			$this->_buffer.=$s."\n";
		}
	}
}
