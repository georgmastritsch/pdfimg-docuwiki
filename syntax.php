<?php
/**
 * PDF Image Link plugin - links to a PDF file with a thumbnail.
 *
 * Version 0.2 trial version.
 *
 * Syntax:	[PDFIMG filename.pdf?size&option&option|optional subtitle]
 *
 * size:	standard image size options  (not currently supported)
 * options: nodownload - suppress download link
 * 			noonline - suppress view-online link (default)
 *
 * filname.pdf will be searched for in ./data/media/<current namespace>/
  * alternatively it can be specified as namespace:filename.pdf
 *
 * should check image exists, generate if needed, and return html.
 *
 *
 * @license Simplified BSD License
 * @author Rob O'Donnell <robert@irrelevant.com>
 * Based on skeleton plugin by Christopher Smith <chris@jalakai.co.uk>
 */

if (!defined('DOKU_INC')) define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_pdfimg extends DokuWiki_Syntax_Plugin {

	/**
     * Get an associative array with plugin info.
     *
     * <p>
     * The returned array holds the following fields:
     * <dl>
     * <dt>author</dt><dd>Author of the plugin</dd>
     * <dt>email</dt><dd>Email address to contact the author</dd>
     * <dt>date</dt><dd>Last modified date of the plugin in
     * <tt>YYYY-MM-DD</tt> format</dd>
     * <dt>name</dt><dd>Name of the plugin</dd>
     * <dt>desc</dt><dd>Short description of the plugin (Text only)</dd>
     * <dt>url</dt><dd>Website with more information on the plugin
     * (eg. syntax description)</dd>
     * </dl>
     *
     * @param none $
     * @return Array Information about this plugin class.
     * @public
     * @static
     */
    function getInfo()
    {
        return array(
            'author' => 'Rob O\'Donnell',
            'email' => 'robert@irrelevant.com',
            'date' => '2011-11-28',
            'name' => 'PDF thumbnail link',
            'desc' => 'Creates an image-link for a PDF, generating thumbnail image if necessary.',
            'url' => 'http://www.irrelevant.com/dokuwiki/',
            );
    }

    /**
     * Get the type of syntax this plugin defines.
     *
     * @param none $
     * @return String <tt>'substition'</tt> (i.e. 'substitution').
     * @public
     * @static
     */
    function getType()
    {
        return 'substition';
    }

    /**
     * What kind of syntax do we allow (optional)
     */
    // function getAllowedTypes() {
    // return array();
    // }
    /**
     * Define how this plugin is handled regarding paragraphs.
     *
     * <p>
     * This method is important for correct XHTML nesting. It returns
     * one of the following values:
     * </p>
     * <dl>
     * <dt>normal</dt><dd>The plugin can be used inside paragraphs.</dd>
     * <dt>block</dt><dd>Open paragraphs need to be closed before
     * plugin output.</dd>
     * <dt>stack</dt><dd>Special case: Plugin wraps other paragraphs.</dd>
     * </dl>
     *
     * @param none $
     * @return String <tt>'block'</tt>.
     * @public
     * @static
     */
/*	function getPType() {
     return 'normal';
    }
*/
    /**
     * Where to sort in?
     *
     * @param none $
     * @return Integer <tt>6</tt>.
     * @public
     * @static
     */
    function getSort()
    {
        return 399;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param  $aMode String The desired rendermode.
     * @return none
     * @public
     * @see render
     */
    function connectTo($mode)
    {

        $this->Lexer->addSpecialPattern('\[PDFIMG.+?\]', $mode, 'plugin_pdfimg');

    }

/*    function postConnect()
    {
        // $this->Lexer->addExitPattern('</PDFIMG>','plugin_pdfimg');
    }
*/
    /**
     * Handler to prepare matched data for the rendering process.
     *
     * <p>
     * The <tt>$aState</tt> parameter gives the type of pattern
     * which triggered the call to this method:
     * </p>
     * <dl>
     * <dt>DOKU_LEXER_ENTER</dt>
     * <dd>a pattern set by <tt>addEntryPattern()</tt></dd>
     * <dt>DOKU_LEXER_MATCHED</dt>
     * <dd>a pattern set by <tt>addPattern()</tt></dd>
     * <dt>DOKU_LEXER_EXIT</dt>
     * <dd> a pattern set by <tt>addExitPattern()</tt></dd>
     * <dt>DOKU_LEXER_SPECIAL</dt>
     * <dd>a pattern set by <tt>addSpecialPattern()</tt></dd>
     * <dt>DOKU_LEXER_UNMATCHED</dt>
     * <dd>ordinary text encountered within the plugin's syntax mode
     * which doesn't match any pattern.</dd>
     * </dl>
     *
     * @param  $aMatch String The text matched by the patterns.
     * @param  $aState Integer The lexer state for the match.
     * @param  $aPos Integer The character position of the matched text.
     * @param  $aHandler Object Reference to the Doku_Handler object.
     * @return Integer The current lexer state for the match.
     * @public
     * @see render
     * @static
     */
    function handle($match, $state, $pos, &$handler)
    {

        global $conf;
        global $INFO;
    	global $ID;

    	$nodownload = FALSE;
    	$noonline = FALSE;

        $match = substr($match, 8, -1); //strip markup
        $params = preg_split('/(?<!\\\\)\|/', $match);
        $pdf = $params[0];
    	if (($of = strpos($pdf, '?'))!== FALSE) {		// yes, that's an asignment in there!!!
    		$options = explode('&', strtolower(substr($pdf, $of + 1 )));
    		$pdf = substr($pdf, 0, $of);
			$nodownload = in_array("nodownload",$options);
    		$noonline = in_array("noonline",$options);

		}
        if (count($params) > 1) {
            $subtitle = $params[1];
        } else {
            $subtitle = "";
        }



        /*
        switch ($state) {
          case DOKU_LEXER_ENTER :
          	$subtitle = substr($match, 8, -1);
          	$stuff = "heebiejeebies";
          	return array($state, array($subtitle,$stuff));
            break;
          case DOKU_LEXER_MATCHED :
            break;
          case DOKU_LEXER_UNMATCHED :
//          	$path = $this->getConf('mediadir') . "/" . $INFO['namespace'] . "/" . $match;
*/
		$pdf = str_replace(':','/',$pdf);				// doc requested
    	$ns = dirname(str_replace(':','/',$ID));		// current namespace?
    	if($ns == '.') $ns ='';							// or none
    	$ns  = utf8_encodeFN(str_replace(':','/',$ns));

    	if ($pdf{0} == '/') {
    		$pdf = substr($pdf,1);
    	} else if ($ns != '') $pdf = $ns . "/" . $pdf;

    	$rob_file = $conf['mediadir']."/".$pdf;

		/*		list($ns, $fn) = preg_split("/\:/u", $pdf, 2);
        if (empty($fn)) {
            $fn = $ns;
            $ns = $INFO['namespace'];
        }
        $rob_file = $path . "/" . $ns . "/" . $fn;
 */
        if (!file_exists($rob_file)) return array($state, array('', '', '', '', "Missing PDF - $rob_file"));

        $rob_ptime = filectime($rob_file);

        $rob_out = $rob_file . ".png";
        if (file_exists($rob_out)) {
            $rob_ctime = filectime($rob_out);
        } else {
            $rob_ctime = 0;
        }
        if ($rob_ctime == 0 || $rob_ptime > $rob_ctime) {
            $exec = '/usr/bin/convert -append -colorspace rgb  -quality 100 -thumbnail 150 -bordercolor grey -border 1 ';
            // temp file
            $tmp = tempnam(sys_get_temp_dir(), 'php');
            rename($tmp, "$tmp.pdf");
            $tmp = "$tmp.pdf";
            $out = tempnam(sys_get_temp_dir(), 'php');
            unlink($out);
            $out = "$out.png";
            // copy
            copy($rob_file, $tmp);
            // execute the command
            // echo sprintf('%s %s %s',$exec,$tmp."[0]",$out);
            $result = Array();
            exec(sprintf('%s %s %s', $exec, $tmp . "[0]", $out), $result, $retval);
            // putenv("TMPDIR=" . $oldtempdir);
            if (!file_exists($out)) {
                return array($state, array('', '', '', '', "Unable to create thumbnail"));
            } else {
                // echo "->$rob_out";
                copy ($out, $rob_out);
                unlink($tmp);
                unlink($out);
            }
        }
        // $content = "{{:$ns:$fn|{{:$ns:$fn.png|}}}}";
        // $handler->media($content, $state, $pos);
        // break; //
        return array($state, array($rob_file, $rob_out, '', $pdf, $subtitle, $nodownload, $noonline));
        /*
          case DOKU_LEXER_EXIT :
          	return array($state, '');
          case DOKU_LEXER_SPECIAL :
            break;
        }
        return array();
*/
    }

    /**
     * Handle the actual output creation.
     *
     * <p>
     * The method checks for the given <tt>$aFormat</tt> and returns
     * <tt>FALSE</tt> when a format isn't supported. <tt>$aRenderer</tt>
     * contains a reference to the renderer object which is currently
     * handling the rendering. The contents of <tt>$aData</tt> is the
     * return value of the <tt>handle()</tt> method.
     * </p>
     *
     * @param  $aFormat String The output format to generate.
     * @param  $aRenderer Object A reference to the renderer object.
     * @param  $aData Array The data created by the <tt>handle()</tt>
     * method.
     * @return Boolean <tt>TRUE</tt> if rendered successfully, or
     * <tt>FALSE</tt> otherwise.
     * @public
     * @see handle
     */
    function render($mode, &$renderer, $data)
    {

        if ($mode == 'xhtml') {
            list($state, $match) = $data;
            /*
        	switch ($state) {
        		case DOKU_LEXER_ENTER :
        			list($subtitle,$stuff) = $match;
        			$renderer->doc .= "<small>" . $renderer->_xmlEntities($subtitle) . "</small>";
        			break;

        		case DOKU_LEXER_UNMATCHED :
*/
            list($fnpdf, $fnimg, $ns, $pdf, $subtitle, $nodownload, $noonline) = $match;

        	if ($fnpdf == '') {
        		$content = "<div class=\"pdferror\">Error: $subtitle</div>";
        	} else {

            // $content = "[[this>_media/$ns:$fn|{{"."$ns:$fn.png?size}}]]";
	            $dl = "/lib/exe/fetch.php/$pdf"; //$ns/$fn";
        		$vw= "/lib/exe/pdfview.php/$pdf"; //$ns/$fn";

	            $content = "<div class=\"pdfimg__main\"><table><tr><td><img src=\"$dl.png\"></td></tr>";
	            if ($subtitle != "") {
	                $content .= "<tr><td class=\"pdfimg__caption\">$subtitle</td></tr>";
	            }
	            if (!$nodownload || !$noonline) 	$content .= "<tr><td class=\"pdfimg__links\"><small>";
				if (!$nodownload) 					$content .= "<a href=\"$dl\">Download PDF</a>";
        		if (!$nodownload && !$noonline) 	$content .= " - ";
        		if (!$noonline) 					$content .= "<a href=\"$vw\">View Online</a>";
				if (!$nodownload || !$noonline) 	$content .= "</small></td></tr>";

	            $content .= "</table></div>";
        	}

            $renderer->doc .= $content;
            /*
   					break;
        		case DOKU_LEXER_EXIT :
//        		    $renderer->doc .= "</span>";
        			break;
        	}
*/
            return true;
            // $renderer->doc .= "Hello World!";            // ptype = 'normal'
            // $renderer->doc .= "<p>Hello World!</p>";     // ptype = 'block'
            // return true;
        }
        return false;
    }
}
// Setup VIM: ex: et ts=4 enc=utf-8 :

?>