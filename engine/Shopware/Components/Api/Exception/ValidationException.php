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
 * @package    Shopware_Components
 * @subpackage Api
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Benjamin Cremer
 * @author     $Author$
 */

namespace Shopware\Components\Api\Exception;

/**
 * Shopware API Component
 */
class ValidationException extends \Enlight_Exception
{
    /**
     * @var \Symfony\Component\Validator\ConstraintViolationList
     */
    protected $violations = null;

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationList $violations
     */
    public function __construct(\Symfony\Component\Validator\ConstraintViolationList $violations)
    {
        $this->setViolations($violations);

        parent::__construct();
    }

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationList $violations
     */
    public function setViolations($violations)
    {
        $this->violations = $violations;
    }

    /**
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    public function getViolations()
    {
        return $this->violations;
    }
}
