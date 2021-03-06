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
 * @package    Shopware_Plugins
 * @subpackage CronStock
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * todo@all: Documentation
 */
class Shopware_Plugins_Core_CronStock_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_ArticleStock',
            'onRun'
        );
        return true;
    }

    public function onRun(Enlight_Components_Cron_EventArgs $args)
    {
        $sql = '
			SELECT
			    d.ordernumber,
				d.id,
				a.id as `articleID`,
				a.name,
				a.description,
				a.description_long,
				a.shippingtime,
				a.datum as `added`,
				a.shippingfree,
				a.releasedate,
				a.topseller,
				a.keywords,
				a.minpurchase,
				a.purchasesteps,
				a.maxpurchase,
				a.purchaseunit,
				a.referenceunit,
				a.taxID,
				a.supplierID,
				a.unitID,
				a.changetime as `changed`,
				d.id as `articledetailsID`,
				d.ordernumber,
				d.suppliernumber,
				d.kind,
				d.additionaltext,
				d.impressions,
				d.sales,
				d.active,
				d.instock,
				d.stockmin,
				d.weight,
				d.position,
				at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10, 
				at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20,
				s.name as supplier,
				u.unit,
				t.tax
			FROM s_articles a
			INNER JOIN s_articles_details as d
			INNER JOIN s_articles_attributes as at
			LEFT JOIN s_articles_supplier as s
			ON a.supplierID = s.id
			LEFT JOIN s_core_units as u
			ON a.unitID = u.id
			LEFT JOIN s_core_tax as t
			ON a.taxID = t.id
			WHERE d.articleID = a.id
			AND d.id = at.articledetailsID
			AND stockmin > instock
		';
        $articles = Shopware()->Db()->fetchAssoc($sql);
        $data = array(
            'count' => count($articles),
            'numbers' => array_keys($articles),
        );

        if (empty($articles)) {
            return $data;
        }

        $job = $args->getJob();

        $context = array(
            'sData' => $data,
            'sJob' => array(
                'articles' => $articles
            )
        );

        $mail = Shopware()->TemplateMail()->createMail(
            $job->get('inform_template'), $context
        );
        $mail->addTo($job->get('inform_mail'));
        $mail->send();

        return $data;
    }

}