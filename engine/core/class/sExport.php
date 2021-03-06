<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Shopware_Core
 * @subpackage Class
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */
/**
 * Deprecated Shopware Class to provide article export feeds
 *
 * todo@all: Documentation
 */
class	sExport
{
	var $sFeedID;
	var $sHash;
	var $sSettings;
	var $sDB;
	var $sApi;
	var $sSystem;
	var $sPath;
	var $sTemplates;

	var $sCurrency;
	var $sCustomergroup;
	var $sLanguage;
	var $sMultishop;

    /**
     * @var \Enlight_Controller_Request_RequestHttp
     */
    var $request;

    var $shop;


    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * @var \Shopware\Models\Media\Repository
     */
    protected $mediaRepository = null;

    /**
     * @var \Shopware\Models\Media\Album
     */
    protected $articleMediaAlbum = null;

	public function sGetCurrency ($currency)
	{
		static $cache = array();
		if(empty($currency))
			$currency = $this->sMultishop["defaultcurrency"];
		if(isset($cache[$currency]))
			return $cache[$currency];
		if(is_numeric($currency))
			$sql = "id=".$currency;
		elseif(is_string($currency))
			$sql = "currency=".$this->sDB->qstr(trim($currency));
		else
			return false;
		$sql = "
			SELECT *
			FROM s_core_currencies
			WHERE $sql
		";
		return $cache[$currency] = $this->sDB->GetRow($sql);
	}

	public function sGetCustomergroup ($customergroup)
	{
		static $cache = array();
		if(empty($customergroup))
			$customergroup = $this->sMultishop["defaultcustomergroup"];
		if(isset($cache[$customergroup]))
			return $cache[$customergroup];
		if(is_int($customergroup))
			$sql = "id=".$customergroup;
		elseif(is_string($customergroup))
			$sql = "groupkey=".$this->sDB->qstr(trim($customergroup));
		else
			return false;
		$sql = "
			SELECT *
			FROM s_core_customergroups
			WHERE $sql
		";
		return $cache[$customergroup] = $this->sDB->GetRow($sql);
	}

	public function sGetMultishop ($language)
	{
		static $cache = array();
		if(isset($cache[$language]))
			return $cache[$language];
		if(empty($language))
			$sql = "`default`=1";
		elseif(is_numeric($language))
			$sql = "id=".$language;
		elseif(is_string($language))
			$sql = "name=".$this->sDB->qstr(trim($language));

		$sql = "
			SELECT *
			FROM s_core_multilanguage
			WHERE $sql
		";
		return $cache[$language] = $this->sDB->GetRow($sql);
	}

	public function sGetLanguage ($language)
	{
		static $cache = array();
		if(isset($cache[$language]))
			return $cache[$language];
		if(empty($language))
			$sql = "default=1";
		elseif(is_numeric($language))
			$sql = "id=".$language;
		elseif(is_string($language))
			$sql = "isocode=".$this->sDB->qstr(trim($language));

		$sql = "
			SELECT *
			FROM s_core_multilanguage
			WHERE $sql
			ORDER BY skipbackend
		";
		return $cache[$language] = $this->sDB->GetRow($sql);
	}

    /**
     * Helper function to get access to the article repository.
     * @return \Shopware\Models\Article\Repository
     */
    private function getArticleRepository() {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        }
        return $this->articleRepository;
    }

    /**
     * Helper function to get access to the media repository.
     * @return \Shopware\Models\Media\Repository
     */
    private function getMediaRepository() {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        }
        return $this->mediaRepository;
    }


	public function sInitSettings ()
	{
		$hash = $this->sDB->qstr($this->sHash);

		$sql = "
			SELECT
				id as feedID, s_export.*
			FROM
				s_export
			WHERE
				id = {$this->sFeedID}
			AND
				hash = $hash
			AND
				`active`=1
		";
		$this->sSettings = $this->sDB->GetRow($sql);

		if(empty($this->sSettings))
			die();

		$this->sSettings["dec_separator"] = ",";
		if($this->sSettings["formatID"]==1)
		{
			$this->sSettings["fieldmark"] = "\"";
			$this->sSettings["escaped_fieldmark"] = "\"\"";
			$this->sSettings["separator"] = ";";
			$this->sSettings["escaped_separator"] = ";";
			$this->sSettings["line_separator"] = "\r\n";
			$this->sSettings["escaped_line_separator"] = "\r\n";
		}
		elseif ($this->sSettings["formatID"]==2)
		{
			$this->sSettings["fieldmark"] = "";
			$this->sSettings["escaped_fieldmark"] = "";
			$this->sSettings["separator"] = "\t";
			$this->sSettings["escaped_separator"] = "";
			$this->sSettings["line_separator"] = "\r\n";
			$this->sSettings["escaped_line_separator"] = "";
		}
		elseif ($this->sSettings["formatID"]==4)
		{
			$this->sSettings["fieldmark"] = "";
			$this->sSettings["escaped_fieldmark"] = "";
			$this->sSettings["separator"] = "|";
			$this->sSettings["escaped_separator"] = "";
			$this->sSettings["line_separator"] = "\r\n";
			$this->sSettings["escaped_line_separator"] = "";
		}

		if(!empty($this->sSettings['encodingID']) && $this->sSettings['encodingID']==2) {
			$this->sSettings['encoding'] = 'UTF-8';
		} else {
			$this->sSettings['encoding'] = 'ISO-8859-1';
		}

		$this->sMultishop = $this->sGetMultishop($this->sSettings["multishopID"]);

		if(empty($this->sSettings["categoryID"]))
		{
			$this->sSettings["categoryID"] = $this->sMultishop["parentID"];
		}
		if(empty($this->sSettings["customergroupID"]))
		{
			$this->sSettings["customergroupID"] = $this->sMultishop["defaultcustomergroup"];
		}
		else
		{
			$this->sSettings["customergroupID"] = (int) $this->sSettings["customergroupID"];
		}
		if(empty($this->sSettings["currencyID"]))
		{
			$this->sSettings["currencyID"] = $this->sMultishop["defaultcurrency"];
		}
		if(empty($this->sSettings["languageID"]))
		{
			$this->sSettings["languageID"] = $this->sSettings["multishopID"];
		}
		$this->sLanguage = $this->sGetMultishop($this->sSettings["languageID"]);

		$this->sCurrency = $this->sGetCurrency($this->sSettings["currencyID"]);

		$this->sCustomergroup = $this->sGetCustomergroup($this->sSettings["customergroupID"]);


        $this->articleMediaAlbum = $this->getMediaRepository()
                ->getAlbumWithSettingsQuery(-1)
                ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);


        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $shop = $repository->getActiveById($this->sSettings['multishopID']);

        //$shop = $repository->getActiveById($this->sLanguage['id']);

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Currency');
        $shop->setCurrency($repository->find($this->sCurrency['id']));

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale');

        $shop->setLocale($repository->find($this->sMultishop['locale']));
        $shop->registerResources(Shopware()->Bootstrap());

        $this->shop = $shop;

		$this->sSystem->sCONFIG = Shopware()->Config();
	}

	public function sInitSmarty ()
	{
		$this->sSystem->sSMARTY->compile_id = "export_".$this->sFeedID;

		$this->sSystem->sSMARTY->cache_lifetime = 0;
		$this->sSystem->sSMARTY->debugging = 0;
		$this->sSystem->sSMARTY->caching = 0;

		$this->sSmarty->registerPlugin('modifier', 'format', array(&$this,'sFormatString'));
		$this->sSmarty->registerPlugin('modifier', 'escape', array(&$this,'sEscapeString'));
		$this->sSmarty->registerPlugin('modifier', 'category', array(&$this,'sGetArticleCategoryPath'));
		$this->sSmarty->registerPlugin('modifier', 'link', array(&$this,'sGetArticleLink'));
		$this->sSmarty->registerPlugin('modifier', 'image', array(&$this,'sGetImageLink'));
		$this->sSmarty->registerPlugin('modifier', 'shippingcost', array(&$this,'sGetArticleShippingcost'));

		$this->sSmarty->assign("sConfig",$this->sSystem->sCONFIG);
		$this->sSmarty->assign("sLanguage",$this->sLanguage);
		$this->sSmarty->assign("sMultishop",$this->sMultishop);
		$this->sSmarty->assign("sCurrency",$this->sCurrency);
		$this->sSmarty->assign("sCustomergroup",$this->sCustomergroup);
		$this->sSmarty->assign("sSettings",$this->sSettings);

		$this->sSmarty->config_vars["F"] = $this->sSettings["fieldmark"];
		$this->sSmarty->config_vars["EF"] = $this->sSettings["escaped_separator"];
		$this->sSmarty->config_vars["S"] = $this->sSettings["separator"];
		$this->sSmarty->config_vars["ES"] = $this->sSettings["escaped_fieldmark"];
		$this->sSmarty->config_vars["L"] = $this->sSettings["line_separator"];
		$this->sSmarty->config_vars["EL"] = $this->sSettings["escaped_line_separator"];
		if($this->sSettings['encoding'] == 'UTF-8') {
			$this->sSmarty->config_vars['BOM'] = "\xEF\xBB\xBF";
		} else {
			$this->sSmarty->config_vars['BOM'] = '';
		}
		$this->sSmarty->config_vars['EN'] = $this->sSettings['encoding'];
	}

	public function sFormatString($string, $esc_type = '', $char_set = null)
	{
		return $this->sEscapeString($string, $esc_type, $char_set);
	}

	public function sEscapeString($string, $esc_type = '', $char_set = null)
	{
		if(empty($esc_type)) {
			if(!empty($this->sSettings["formatID"]) && $this->sSettings["formatID"]==3) {
				$esc_type = "html";
			} else {
				$esc_type = "csv";
			}
		}

		if(empty($char_set)) {
			$char_set = $this->sSettings['encoding'];
		}

		switch ($esc_type) {
	        case 'number':
	            return number_format($string,2,$this->sSettings["dec_separator"],'');
	        case 'csv':
	        	if(empty($this->sSettings["escaped_line_separator"])) {
	        		$string = preg_replace('#[\r\n]+#m', ' ', $string);
	        	} elseif ($this->sSettings["escaped_line_separator"]!=$this->sSettings["line_separator"]) {
	        		$string = str_replace($this->sSettings["line_separator"],$this->sSettings['escaped_line_separator'],$string);
	        	}
	        	if(!empty($this->sSettings["fieldmark"])) {
					$string = str_replace($this->sSettings["fieldmark"],$this->sSettings['escaped_fieldmark'],$string);
	        	} else {
					$string = str_replace($this->sSettings['separator'],$this->sSettings['escaped_separator'],$string);
	        	}

                if ($char_set != 'UTF-8') {
                    $string = utf8_decode($string);
                }
                $string = html_entity_decode($string, ENT_NOQUOTES, $char_set);
	            return $this->sSettings["fieldmark"].$string.$this->sSettings["fieldmark"];
            case 'xml':
                 if ($char_set != 'UTF-8') {
                     $string = utf8_decode($string);
                 }
                return $string;
	       	case 'html':
                $string = html_entity_decode($string, ENT_NOQUOTES, $char_set);
           		return htmlspecialchars($string, ENT_QUOTES, $char_set, false);
	        case 'htmlall':
                return htmlentities($string, ENT_QUOTES, $char_set);
	        case 'url':
	            return rawurlencode($string);
	        case 'urlpathinfo':
	            return str_replace('%2F','/',rawurlencode($string));

	        case 'quotes':
	            // escape unescaped single quotes
	            return preg_replace("%(?<!\\\\)'%", "\\'", $string);

	        case 'hex':
	            // escape every character into hex
	            $return = '';
	            for ($x=0; $x < strlen($string); $x++) {
	                $return .= '%' . bin2hex($string[$x]);
	            }
	            return $return;

	        case 'hexentity':
	            $return = '';
	            for ($x=0; $x < strlen($string); $x++) {
	                $return .= '&#x' . bin2hex($string[$x]) . ';';
	            }
	            return $return;

	        case 'decentity':
	            $return = '';
	            for ($x=0; $x < strlen($string); $x++) {
	                $return .= '&#' . ord($string[$x]) . ';';
	            }
	            return $return;

	        case 'javascript':
	            // escape quotes and backslashes, newlines, etc.
	            return strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));

	        case 'mail':
	            // safe way to display e-mail address on a web page
	            return str_replace(array('@', '.'),array(' [AT] ', ' [DOT] '), $string);

	        case 'nonstd':
	           // escape non-standard chars, such as ms document quotes
	           $_res = '';
	           for($_i = 0, $_len = strlen($string); $_i < $_len; $_i++) {
	               $_ord = ord(substr($string, $_i, 1));
	               // non-standard char, escape it
	               if($_ord >= 126){
	                   $_res .= '&#' . $_ord . ';';
	               }
	               else {
	                   $_res .= substr($string, $_i, 1);
	               }
	           }
	           return $_res;
	    }
	}

	public function sGetArticleLink ($articleID, $title="")
	{
		return $this->sSystem->rewriteLink(array(2=>$this->sSYSTEM->sCONFIG["sBASEFILE"]."?sViewport=detail&sArticle=$articleID",3=>$title),true).(empty($this->sSettings["partnerID"])?"":"?sPartner=".urlencode($this->sSettings["partnerID"]));
	}

    public function sGetImageLink($hash, $imageSize = null)
    {

        if (!empty($hash)) {
            $sql = "SELECT articleID FROM s_articles_img WHERE img =?";
            $articleId = Shopware()->Db()->fetchOne($sql, array($hash));

            $imageSize = intval($imageSize);
            $image = $this->getArticleRepository()->getArticleCoverImageQuery($articleId)->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            if(empty($image)) {
                return "";
            }
            //first we get all thumbnail sizes of the article album
            $sizes = $this->articleMediaAlbum->getSettings()->getThumbnailSize();

            //now we get the configured image and thumbnail dir.
            $imageDir = 'http://'. $this->shop->getHost() . $this->request->getBasePath()  . '/media/image/';

            $thumbDir = $imageDir . 'thumbnail/';

            foreach ($sizes as $key => $size) {
                if (strpos($size, 'x') === 0) {
                    $size = $size . 'x' . $size;
                }
                $imageData[$key] = $thumbDir . $image['path'] . '_' . $size . '.' . $image['extension'];
            }


            if (!empty($imageData)) {
                return $imageData[$imageSize];
            }
        }
        return "";
    }

	public function sMapTranslation($object,$objectdata)
	{

		switch ($object){
			case "detail":
			case "article":
				$map = array("txtshortdescription"=>"description","txtlangbeschreibung"=>"description_long","txtArtikel"=>"name","txtzusatztxt"=>"additionaltext");
				for ($i=1;$i<=20;$i++)
					$map["attr$i"] = "attr$i";
				break;
			case "link":
				$map = array("linkname"=>"description");
				break;
			case "download":
				$map = array("downloadname"=>"description");
				break;
		}
		if (empty($objectdata))
			return array();
		$objectdata = unserialize($objectdata);
		if (empty($objectdata))
			return array();
		$result = array();
		foreach ($map as $key=>$value)
		{
			if(isset($objectdata[$key]))
				$result[$value] = $objectdata[$key];
		}
		return $result;
	}

	public function _decode_line($line)
	{
		$separator = ";";
		$fieldmark = "\"";
	    $elements = explode($separator, $line);
	    $tmp_elements = array();
	    for ($i = 0; $i < count($elements); $i++)
	    {
	        $nquotes = substr_count($elements[$i], $fieldmark);
	        if ($nquotes %2 == 1) {
	            if(isset($elements[$i+1]))
	           		$elements[$i+1] = $elements[$i].$separator.$elements[$i+1];
	        }
	        else
	        {
	        	if ($nquotes > 0)
	        	{
		            if(substr($elements[$i],0,1)==$fieldmark)
		            	$elements[$i] = substr($elements[$i],1);
		            if(substr($elements[$i],-1,1)==$fieldmark)
		            	$elements[$i] = substr($elements[$i],0,-1);
		            $elements[$i] = str_replace($fieldmark.$fieldmark, $fieldmark, $elements[$i]);
		        }
		        $tmp_elements[] = $elements[$i];
	        }
	    }
	    return $tmp_elements;
	}

	public function sCreateSql ()
	{
		$sql_add_join   = array();
		$sql_add_select = array();
		$sql_add_where  = array();

		if(empty($this->sLanguage["skipbackend"]) && !empty($this->sLanguage["isocode"]))
		{
			$sql_isocode = $this->sDB->qstr($this->sLanguage["isocode"]);
			$sql_add_join[] = "
				LEFT JOIN s_core_translations as ta
				ON ta.objectkey=a.id AND ta.objecttype='article' AND ta.objectlanguage=$sql_isocode

				LEFT JOIN s_core_translations as td
				ON td.objectkey=d.id AND td.objecttype='variant' AND td.objectlanguage=$sql_isocode
			";
			$sql_add_select[] = "ta.objectdata as article_translation";
			$sql_add_select[] = "td.objectdata as detail_translation";
		}
		if(!empty($this->sSettings["categoryID"]))
		{
			$sql_add_join[] = "
				JOIN s_categories c
					ON c.id = {$this->sSettings["categoryID"]}
				LEFT JOIN s_categories c2
					ON c2.left > c.left
					AND c2.right <= c.right
				JOIN s_articles_categories act
					ON act.articleID = a.id
					AND (
						act.categoryID = c.id
						OR act.categoryID = c2.id
					)
			";
		}
		if(empty($this->sSettings["image_filter"]))
		{
			$sql_add_join[] = "
				LEFT JOIN s_articles_img as i
				ON i.articleID = a.id AND i.main=1 AND i.article_detail_id IS NULL
			";
		}
		else
		{
			$sql_add_join[] = "
				JOIN s_articles_img as i
				ON i.articleID = a.id AND i.main=1 AND i.article_detail_id IS NULL
			";
		}

		if(!empty($this->sCustomergroup["groupkey"])&&empty($this->sCustomergroup["mode"])&&$this->sCustomergroup["groupkey"]!="EK")
		{
			$sql_add_join[] = "
				LEFT JOIN s_articles_prices as p2
				ON p2.articledetailsID = d.id AND p2.`from`=1
				AND p2.pricegroup='{$this->sCustomergroup["groupkey"]}'
				AND p2.price!=0
			";
			$pricefield = "IFNULL(p2.price, p.price)";
			$pseudoprice = "IFNULL(p2.pseudoprice, p.pseudoprice)";
			$baseprice = "IFNULL(p2.baseprice, p.baseprice)";
		}
		else
		{
			$pricefield = "p.price";
			$pseudoprice = "p.pseudoprice";
			$baseprice = "p.baseprice";
		}


		if(empty($this->sSettings["variant_export"])||$this->sSettings["variant_export"]==1)
		{

			$sql_add_select[] = "IF(COUNT(d.ordernumber)<=1,'',GROUP_CONCAT(CONCAT('\"',REPLACE(d.ordernumber,'\"','\"\"'),'\"') SEPARATOR ';')) as group_ordernumber";
			$sql_add_select[] = "IF(COUNT(d.additionaltext)<=1,'',GROUP_CONCAT(CONCAT('\"',REPLACE(d.additionaltext,'\"','\"\"'),'\"') SEPARATOR ';')) as group_additionaltext";
			$sql_add_select[] = "IF(COUNT($pricefield)<=1,'',GROUP_CONCAT(ROUND($pricefield*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]},2) SEPARATOR ';')) as group_pricenet";
			$sql_add_select[] = "IF(COUNT($pricefield)<=1,'',GROUP_CONCAT(ROUND($pricefield*(100+t.tax-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]},2) SEPARATOR ';')) as group_price";
			$sql_add_select[] = "IF(COUNT(d.active)<=1,'',GROUP_CONCAT(d.active SEPARATOR ';')) as group_active";
			$sql_add_select[] = "IF(COUNT(d.instock)<=1,'',GROUP_CONCAT(d.instock SEPARATOR ';')) as group_instock";

		}

		$grouppricefield = "gp.price";
		if(empty($this->sSettings["variant_export"])||$this->sSettings["variant_export"]==2||$this->sSettings["variant_export"]==1)
		{
			$sql_add_join[] = "
				JOIN (SELECT NULL as `articleID` , NULL as `valueID` , NULL as `attr1` , NULL as `attr2` , NULL as `attr3` , NULL as `attr4` , NULL as `attr5` , NULL as `attr6` , NULL as `attr7` , NULL as `attr8` , NULL as `attr9` , NULL as `attr10` , NULL as `standard` , NULL as `active` , NULL as `ordernumber` , NULL as `instock`) as v
			";

			$sql_add_join[] = "
				JOIN (SELECT NULL as articleID, NULL as valueID, NULL as groupkey, NULL as price, NULL as optionID) as gp
			";
		}

		if(empty($this->sSettings["variant_export"])||$this->sSettings["variant_export"]==1)
		{
			$sql_add_group_by = "a.id";
		}
		elseif($this->sSettings["variant_export"]==2)
		{
			$sql_add_group_by = "d.id";
		}
		else
		{
			//$sql_add_group_by = "d.id, v.valueID";
		}

		if(!empty($this->sSettings["active_filter"]))
		{
			$sql_add_where[] = "(v.active=1 OR (v.active IS NULL AND a.active=1))";
		}
		if(!empty($this->sSettings["stockmin_filter"]))
		{
			$sql_add_where[] ="(v.instock>=d.stockmin OR (v.instock IS NULL AND d.instock>=d.stockmin))";
		}
		if(!empty($this->sSettings["instock_filter"]))
		{
			$sql_add_where[] ="(v.instock>={$this->sSettings["instock_filter"]} OR (v.instock IS NULL AND d.instock>={$this->sSettings["instock_filter"]}))";
		}
		if(!empty($this->sSettings["price_filter"]))
		{
			$sql_add_where[] = "ROUND(IFNULL($grouppricefield,$pricefield)*(100+t.tax-IF(pd.discount IS NULL,0,pd.discount)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]},2)>=".$this->sSettings["price_filter"];
		}
		if(!empty($this->sSettings["own_filter"])&&trim($this->sSettings["own_filter"]))
		{
			$sql_add_where[] = "(".$this->sSettings["own_filter"].")";
		}

		$sql_add_join = implode(" ",$sql_add_join);
		if(!empty($sql_add_select))
			$sql_add_select = ", ".implode(", ",$sql_add_select);
		else
			$sql_add_select = "";
		if(!empty($sql_add_where))
			$sql_add_where = " AND ".implode(" AND ",$sql_add_where);
		else
			$sql_add_where = "";
		if(!empty($sql_add_group_by))
			$sql_add_group_by = "GROUP BY ($sql_add_group_by)";
		else
			$sql_add_group_by = "";

		$sql = "
			SELECT
				a.id as `articleID`,
				a.name,
				a.description,
				a.description_long,
				d.shippingtime,
				d.shippingfree,
				a.topseller,
				a.keywords,
				d.minpurchase,
				d.purchasesteps,
				d.maxpurchase,
				d.purchaseunit,
				d.referenceunit,
				a.taxID,
				a.supplierID,
				d.unitID,
				IF(a.changetime!='0000-00-00 00:00:00',a.changetime,'') as `changed`,
				IF(a.datum!='0000-00-00',a.datum,'') as `added`,
				IF(d.releasedate!='0000-00-00',d.releasedate,'') as `releasedate`,
				a.active as active,

				d.id as `articledetailsID`,
				IF(v.ordernumber IS NOT NULL,v.ordernumber,d.ordernumber) as ordernumber,

				d.suppliernumber,
				d.ean,
				d.width,
				d.height,
				d.length,
				d.kind,
				IF(v.standard=1||kind=1,1,0) as standard,
				d.additionaltext,
				d.impressions,
				d.sales,


				IF(v.active IS NOT NULL,IF(a.active=0,0,v.active),a.active) as active,
				IF(v.instock IS NOT NULL,v.instock,d.instock) as instock,
                (
				   SELECT AVG(av.points)
				   FROM s_articles_vote as av WHERE active=1
				   AND articleID=a.id
				) as sVoteAverage,
			    (
				   SELECT COUNT(*)
				   FROM s_articles_vote as av WHERE active=1
				   AND articleID=a.id
				) as sVoteCount,
				d.stockmin,
				d.weight,
				d.position,

				at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10,
				at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20,

				s.name as supplier,
				u.unit,
				u.description as unit_description,
				t.tax,
				i.img as image,

				a.configurator_set_id as configurator,

				ROUND(IFNULL($grouppricefield, $pricefield)*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]},2) as netprice,
				ROUND(IFNULL($grouppricefield, $pricefield)*(100+t.tax)/100*(100-IF(pd.discount,pd.discount,0)-{$this->sCustomergroup["discount"]})/100*{$this->sCurrency["factor"]},2) as price,
				pd.discount,
				ROUND($pseudoprice*{$this->sCurrency["factor"]},2) as netpseudoprice,
				ROUND($pseudoprice*(100+t.tax)*{$this->sCurrency["factor"]}/100,2) as pseudoprice,
				$baseprice,
				IF(file IS NULL,0,1) as esd
				$sql_add_select

			FROM s_articles a
			INNER JOIN s_articles_details d
			ON d.articleID = a.id
			LEFT JOIN s_articles_attributes at
			ON d.id = at.articledetailsID

			LEFT JOIN `s_core_units` as `u`
			ON d.unitID = u.id
			LEFT JOIN `s_core_tax` as `t`
			ON a.taxID = t.id
			LEFT JOIN `s_articles_supplier` as `s`
			ON a.supplierID = s.id


			LEFT JOIN s_core_pricegroups_discounts pd
			ON a.pricegroupActive=1
			AND	a.pricegroupID=groupID
			AND customergroupID = 1
			AND discountstart=1

			LEFT JOIN s_articles_esd e ON e.articledetailsID=d.id

			LEFT JOIN (
				SELECT articleID
				FROM
					s_export_categories as ec,
					s_articles_categories as ac
				WHERE feedID={$this->sFeedID}
				AND ec.categoryID=ac.categoryID
				GROUP BY articleID
			) AS bc
			ON bc.articleID=a.id

			LEFT JOIN s_export_suppliers AS bs
			ON (bs.supplierID=s.id AND bs.feedID={$this->sFeedID})

			LEFT JOIN s_export_articles AS ba
			ON (ba.articleID=a.id AND ba.feedID={$this->sFeedID})

			LEFT JOIN s_articles_prices as p
			ON p.articledetailsID = d.id
			AND p.`from`=1
			AND p.pricegroup='EK'

			$sql_add_join

			WHERE bc.articleID IS NULL
			AND bs.supplierID IS NULL
			AND a.mode = 0
			AND d.kind != 3
			AND ba.articleID IS NULL
			$sql_add_where

			$sql_add_group_by
		";

		if(!empty($this->sSettings["count_filter"]))
		{
			$sql .= "LIMIT ".$this->sSettings["count_filter"];
		}
		return  $sql;
	}

	public function sGetArticleCategoryPath ($articleID, $separator = " > ", $categoryID=null)
	{
		if(empty($categoryID))
			$categoryID = $this->sSettings["categoryID"];
		$path = "";
		while (!empty($categoryID))
		{
			$sql = "
			    SELECT c.id as categoryID, c.description
			    FROM s_articles_categories a, s_categories c
				WHERE a.articleID=$articleID
				AND c.parent=$categoryID
				AND a.categoryID = c.id
				ORDER BY a.id ASC LIMIT 1
			";
			$category = $this->sDB->GetRow($sql);
			if(empty($category))
				break;
			$path = empty($path) ? $category["description"] : $path . $separator . $category["description"];
			$categoryID = $category["categoryID"];
		}
		return htmlspecialchars_decode($path);
	}

	public function sGetCountry ($country)
	{
		static $cache = array();
		if(empty($country))
			return false;
		if(isset($cache[$country]))
			return $cache[$country];
		if(is_numeric($country))
			$sql = "c.id=".$country;
		elseif(is_string($country))
			$sql = "c.countryiso=".$this->sDB->qstr($country);
		else
			return false;
		$sql = "
			SELECT
			  c.id, c.id as countryID, countryname, countryiso,
			  countryen, c.position, notice,
			  c.shippingfree as shippingfree
			FROM s_core_countries c
			WHERE $sql
		";
		return $cache[$country] = $this->sDB->GetRow($sql);
	}

	public function sGetPaymentmean ($payment)
	{
		static $cache = array();
		if(empty($payment))
			return false;
		if(isset($cache[$payment]))
			return $cache[$payment];
		if(is_numeric($payment))
			$sql = "id=".$payment;
		elseif(is_string($payment))
			$sql = "name=".$this->sDB->qstr($payment);
		else
			return false;
		$sql = "
			SELECT * FROM s_core_paymentmeans
			WHERE $sql
		";
		$cache[$payment] = $this->sDB->GetRow($sql);

		$cache[$payment]["country_surcharge"] = array();
		if (!empty($cache[$payment]["surchargestring"])){
			foreach(explode(";",$cache[$payment]["surchargestring"]) as $countrySurcharge){
				list($key,$value) = explode(":",$countrySurcharge);
				$value = floatval(str_replace(",",".",$value));
				if (!empty($value)){
					$cache[$payment]["country_surcharge"][$key] = $value;
				}
			}
		}
		$cache[$payment]["surcharge"] = $cache[$payment]["surcharge"];
		return $cache[$payment];
	}

	public function sGetDispatch ($dispatch = null, $country = null)
	{
		if(empty($dispatch))
			$sql_order = "";
		elseif(is_numeric($dispatch))
			$sql_order = "IF(sd.id=".(int)$dispatch.",0,1),";
		elseif(is_string($dispatch))
			$sql_order = "IF(name=".$this->sDB->qstr($dispatch).",0,1),";
		else
			$sql_order = "";

		if(empty($country))
			$sql_where = "";
		elseif(is_numeric($country))
			$sql_where = "c.id=".$country;
		elseif(is_string($country))
			$sql_where = "c.countryiso=".$this->sDB->qstr($country);
		else
			$sql_where = "";

		static $cache = array();
		if(isset($cache[$sql_order."|".$sql_where]))
			return $cache[$sql_order."|".$sql_where];

		if(!empty($sql_where))
		{
			$sql_from = " s_premium_dispatch_countries sc,	s_core_countries c";
			$sql_where = "AND $sql_where AND c.id=sc.countryID";
		}
		else
		{
			$sql_from = "";
		}
		$sql = "
			SELECT sd.id, name, sd.description, sd.shippingfree
			FROM
				s_premium_dispatch sd,
				$sql_from
			WHERE sd.active = 1
			AND	sd.id = sc.dispatchID
			$sql_where
			ORDER BY $sql_order sd.position ASC LIMIT 1
		";
		return $cache[$sql_order."|".$sql_where] = $this->sDB->GetRow($sql);
	}

	public function sGetDispatchBasket ($article, $countryID=null, $paymentID = null)
	{
		$sql_select = '';
		if(!empty($this->sSystem->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT']))
		{
			$sql_select .= ', '.$this->sSystem->sCONFIG['sPREMIUMSHIPPIUNGASKETSELECT'];
		}
		$sql = 'SELECT id, calculation_sql FROM s_premium_dispatch WHERE calculation=3';
		$calculations = $this->sDB->GetAssoc($sql);
		if(!empty($calculations))
		foreach ($calculations as $dispatchID => $calculation)
		{
			if(empty($calculation)) $calculation = $this->sSYSTEM->sDB_CONNECTION->qstr($calculation);
			$sql_select .= ', ('.$calculation.') as calculation_value_'.$dispatchID;
		}

		$sql = "
			SELECT
				MIN(IFNULL(g.instock,d.instock)>=b.quantity) as instock,
				MIN(IFNULL(g.instock,d.instock)>=(b.quantity+d.stockmin)) as stockmin,
				MIN(a.laststock) as laststock,
				SUM(d.weight*b.quantity) as weight,
				SUM(IF(a.id,b.quantity,0)) as count_article,
				MAX(b.shippingfree) as shippingfree,
				SUM(IF(b.modus=0,b.quantity*b.price/b.currencyFactor,0)) as amount,
				MAX(t.tax) as max_tax, u.id as userID
				$sql_select
				, b.articleID
			FROM (
				SELECT
					NULL as sessionID,
					? as articleID,
					? as ordernumber,
					? as shippingfree,
					1 as quantity,
					? as price,
					? as netprice,
					0 as modus,
					? as esdarticle,
					'' as config,
					? as currencyFactor,
					'' as ob_attr1,
					'' as ob_attr2,
					'' as ob_attr3,
					'' as ob_attr4,
					'' as ob_attr5,
					'' as ob_attr6
			) as b

			LEFT JOIN s_articles a
			ON b.articleID=a.id
			AND b.modus=0
			AND b.esdarticle=0

			LEFT JOIN s_articles_groups_value g
			ON g.ordernumber=b.ordernumber
			AND g.articleID=a.id

			LEFT JOIN s_articles_details d
			ON (d.ordernumber=b.ordernumber OR g.valueID IS NOT NULL)
			AND d.articleID=a.id

			LEFT JOIN s_articles_attributes at
			ON at.articledetailsID=d.id

			LEFT JOIN s_core_tax t
			ON t.id=a.taxID

			LEFT JOIN s_user u
			ON u.id=NULL

			LEFT JOIN s_user_billingaddress ub
			ON ub.userID=u.id

			LEFT JOIN s_user_shippingaddress us
			ON us.userID=u.id

			GROUP BY b.sessionID
		";
		$basket = $this->sDB->GetRow($sql,array(
			$article["articleID"],
			$article["ordernumber"],
			$article["shippingfree"],
			$article["price"],
			$article["netprice"],
			$article["esd"],
			$this->sCurrency["factor"]
		));
		if(empty($basket))
		{
			return false;
		}
		$basket['countryID'] = $countryID;
		$basket['paymentID'] = $paymentID;
		$basket['customergroupID'] = $this->sCustomergroup['id'];
		$basket['multishopID'] = $this->sMultishop['id'];
		$basket['sessionID'] = null;
		return $basket;
	}

	public function sGetArticleShippingcost($article, $payment, $country, $dispatch = null)
	{
		if(empty($article)||!is_array($article)) return false;
		$country = $this->sGetCountry($country);
		if(empty($country)) return false;
		$payment = $this->sGetPaymentmean($payment);
		if(empty($payment)) return false;
		if (!empty($payment["country_surcharge"][$country["countryiso"]]))
			$payment["surcharge"] += $payment["country_surcharge"][$country["countryiso"]];
		$payment['surcharge'] = round($payment['surcharge']*$this->sCurrency["factor"],2);

		if (!empty($this->sSystem->sCONFIG['sPREMIUMSHIPPIUNG']))
		{
			return $this->sGetArticlePremiumShippingcosts($article,$payment,$country,$dispatch);
		}
		if(!empty($article["esd"])||(!empty($article["shippingfree"])&&!empty($dispatch["shippingfree"])))
			return 0;

		$dispatch = $this->sGetDispatch($dispatch,(int)$country["id"]);
		if(empty($dispatch)) return false;

		if (!empty($country["shippingfree"]) && !empty($dispatch["shippingfree"]) && $article["price"]>=$country["shippingfree"]*$this->sCurrency["factor"])
			return 0;


        return 0;
	}

	public function sGetPremiumDispatch ($basket, $dispatch = null)
	{
		if(empty($dispatch))
			$sql_order = "";
		elseif(is_numeric($dispatch))
			$sql_order = "IF(d.id=".(int)$dispatch.",0,1),";
		elseif(is_string($dispatch))
			$sql_order = "IF(d.name=".$this->sDB->qstr($dispatch).",0,1),";
		else
			$sql_order = "";

		$sql_add_join = "";
		if(!empty($basket['paymentID']))
		{
			$sql_add_join .= "
				JOIN s_premium_dispatch_paymentmeans dp
				ON d.id = dp.dispatchID
				AND dp.paymentID={$basket['paymentID']}
			";
		}
		if(!empty($basket['countryID']))
		{
			$sql_add_join .= "
				JOIN s_premium_dispatch_countries dc
				ON d.id = dc.dispatchID
				AND dc.countryID={$basket['countryID']}
			";
		}

		$sql = "SELECT id, bind_sql FROM s_premium_dispatch WHERE type IN (0) AND bind_sql IS NOT NULL";
		$statements = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);
		$sql_where = "";
		foreach ($statements as $dispatchID => $statement)
		{
			$sql_where .= "
			AND ( d.id!=$dispatchID OR ($statement))
			";
		}

		$sql_basket = array();
		foreach ($basket as $key => $value)
		{
			$sql_basket[] = $this->sDB->qstr($value)." as `$key`";
		}
		$sql_basket = implode(', ',$sql_basket);

		$sql = "
			SELECT d.id, d.name, d.description, d.calculation, d.status_link, d.surcharge_calculation, d.bind_shippingfree, tax_calculation, t.tax as tax_calculation_value, d.shippingfree
			FROM s_premium_dispatch d

			JOIN ( SELECT $sql_basket ) b

			$sql_add_join
			LEFT JOIN (
				SELECT dc.dispatchID
				FROM s_articles_categories ac,
				s_premium_dispatch_categories dc
				WHERE ac.articleID={$basket['articleID']}
				AND dc.categoryID=ac.categoryID
				GROUP BY dc.dispatchID
			) as dk
			ON d.id = dk.dispatchID

			LEFT JOIN s_core_tax t
			ON t.id=d.tax_calculation

			LEFT JOIN s_user u
			ON u.id=0
			AND u.active=1

			LEFT JOIN s_user_billingaddress ub
			ON ub.userID=u.id

			LEFT JOIN s_user_shippingaddress us
			ON us.userID=u.id

			WHERE d.active = 1
			AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
			AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
			AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
			AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
			AND (bind_laststock=0 OR (bind_laststock=1 AND b.instock) OR (bind_laststock=2 AND b.stockmin))
			AND (bind_shippingfree!=1 OR NOT b.shippingfree)
			AND (d.multishopID IS NULL OR d.multishopID= b.multishopID)
			AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
			AND dk.dispatchID IS NULL
			AND d.type IN (0)
			$sql_where
			ORDER BY $sql_order d.position, d.name
			LIMIT 1
		";
		$dispatch = $this->sDB->GetRow($sql);
		if(empty($dispatch))
		{
			$sql = "
				SELECT
					d.id, d.name,
					d.description,
					d.calculation,
					d.status_link,
					d.surcharge_calculation,
					d.bind_shippingfree,
					tax_calculation,
					t.tax as tax_calculation_value
				FROM s_premium_dispatch d
				LEFT JOIN s_core_tax t
				ON t.id=d.tax_calculation
				WHERE d.active=1
				AND d.type=1
				ORDER BY d.position, d.name
				LIMIT 1
			";
			$dispatch = $this->sDB->GetRow($sql);
		}
		return $dispatch;
	}

	public function sGetPremiumDispatchSurcharge ($basket)
	{
		if(empty($basket)) return false;

		$sql = 'SELECT id, bind_sql FROM s_premium_dispatch WHERE type=2 AND bind_sql IS NOT NULL';
		$statements = $this->sSYSTEM->sDB_CONNECTION->GetAssoc($sql);
		$sql_where = '';
		foreach ($statements as $dispatchID => $statement)
		{
			$sql_where .= "
			AND ( d.id!=$dispatchID OR ($statement))
			";
		}
		$sql_basket = array();
		foreach ($basket as $key => $value)
		{
			$sql_basket[] = $this->sSYSTEM->sDB_CONNECTION->qstr($value)." as `$key`";
		}
		$sql_basket = implode(', ',$sql_basket);

				$sql_add_join = "";
		if(!empty($basket['paymentID']))
		{
			$sql_add_join .= "
				JOIN s_premium_dispatch_paymentmeans dp
				ON d.id = dp.dispatchID
				AND dp.paymentID={$basket['paymentID']}
			";
		}
		if(!empty($basket['countryID']))
		{
			$sql_add_join .= "
				JOIN s_premium_dispatch_countries dc
				ON d.id = dc.dispatchID
				AND dc.countryID={$basket['countryID']}
			";
		}

		$sql = "
			SELECT d.id, d.calculation
			FROM s_premium_dispatch d

			JOIN ( SELECT $sql_basket ) b

			$sql_add_join

			LEFT JOIN (
				SELECT dc.dispatchID
				FROM s_articles_categories ac,
				s_premium_dispatch_categories dc
				WHERE ac.articleID={$basket['articleID']}
				AND dc.categoryID=ac.categoryID
				GROUP BY dc.dispatchID
			) as dk
			ON dk.dispatchID=d.id

			LEFT JOIN s_user u
			ON u.id=b.userID
			AND u.active=1

			LEFT JOIN s_user_billingaddress ub
			ON ub.userID=u.id

			LEFT JOIN s_user_shippingaddress us
			ON us.userID=u.id

			WHERE d.active=1
			AND (bind_weight_from IS NULL OR bind_weight_from <= b.weight)
			AND (bind_weight_to IS NULL OR bind_weight_to >= b.weight)
			AND (bind_price_from IS NULL OR bind_price_from <= b.amount)
			AND (bind_price_to IS NULL OR bind_price_to >= b.amount)
			AND (bind_instock=0 OR bind_instock IS NULL OR (bind_instock=1 AND b.instock) OR (bind_instock=2 AND b.stockmin))
			AND (bind_laststock=0 OR (bind_laststock=1 AND b.laststock))
			AND (bind_shippingfree=2 OR NOT b.shippingfree)

			AND (d.multishopID IS NULL OR d.multishopID=b.multishopID)
			AND (d.customergroupID IS NULL OR d.customergroupID=b.customergroupID)
			AND dk.dispatchID IS NULL
			AND d.type = 2
			AND (d.shippingfree IS NULL OR d.shippingfree > b.amount)
			$sql_where
			GROUP BY d.id
		";
		$dispatches = $this->sSYSTEM->sDB_CONNECTION->GetAll($sql);
		$surcharge = 0;
		if(!empty($dispatches))
		foreach ($dispatches as $dispatch)
		{
			if(empty($dispatch['calculation']))
				$from = round($basket['weight'],3);
			elseif($dispatch['calculation']==1)
				$from = round($basket['amount'],2);
			elseif($dispatch['calculation']==2)
				$from = round($basket['count_article']);
			elseif($dispatch['calculation']==3)
				$from = round($basket['calculation_value_'.$dispatch['id']]);
			else
				continue;
			$sql = "
				SELECT `value` , `factor`
				FROM `s_premium_shippingcosts`
				WHERE `from` <= $from
				AND `dispatchID` = {$dispatch['id']}
				ORDER BY `from` DESC
				LIMIT 1
			";
			$result = $this->sSYSTEM->sDB_CONNECTION->GetRow($sql);
			if(empty($result)) continue;
			$surcharge += $result['value'];
			if(!empty($result['factor']))
				$surcharge +=  $result['factor']/100*$from;
		}
		return $surcharge;
	}

	public function sGetArticlePremiumShippingcosts($article, $payment, $country, $dispatch = null)
	{
		$basket = $this->sGetDispatchBasket($article,$country['id'],$payment['id']);
		if(empty($basket)) return false;
		$dispatch = $this->sGetPremiumDispatch($basket, $dispatch);
		if(empty($dispatch)) return false;

		if ((!empty($dispatch['shippingfree'])&&$dispatch['shippingfree']<=$basket['amount'])
			||empty($basket['count_article'])
			||(!empty($basket['shippingfree'])&&empty($dispatch['bind_shippingfree']))
		)
		{
			if(empty($dispatch['surcharge_calculation']))
				return $payment['surcharge'];
			else
				return 0;
		}

		if(empty($dispatch['calculation']))
			$from = round($basket['weight'],3);
		elseif($dispatch['calculation']==1)
			$from = round($basket['amount'],2);
		elseif($dispatch['calculation']==2)
			$from = round($basket['count_article']);
		elseif($dispatch['calculation']==3)
			$from = round($basket['calculation_value_'.$dispatch['id']]);
		else
			return false;

		$sql = "
			SELECT `value` , `factor`
			FROM `s_premium_shippingcosts`
			WHERE `from`<=$from
			AND `dispatchID`={$dispatch['id']}
			ORDER BY `from` DESC
			LIMIT 1
		";
		$result = $this->sDB->GetRow($sql);
		if(empty($result)) return false;

		$result['shippingcosts'] = $result['value'];
		if(!empty($result['factor']))
			$result['shippingcosts'] +=  $result['factor']/100*$from;
		$result['surcharge'] = $this->sGetPremiumDispatchSurcharge($basket);
		if(!empty($result['surcharge']))
			$result['shippingcosts'] += $result['surcharge'];
		$result['shippingcosts'] *= $this->sCurrency["factor"];
		$result['shippingcosts'] = round($result['shippingcosts'],2);
		if(!empty($payment['surcharge'])&&$dispatch['surcharge_calculation']!=2&&(empty($article['shippingfree'])||empty($dispatch['surcharge_calculation'])))
		{
			$result['shippingcosts'] += $payment['surcharge'];
		}
		return $result['shippingcosts'];
	}
}
?>
