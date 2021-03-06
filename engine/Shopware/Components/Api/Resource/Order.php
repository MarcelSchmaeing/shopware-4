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
 */

namespace Shopware\Components\Api\Resource;

use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Order\Order as OrderModel;


/**
 * Order API Resource
 */
class Order extends Resource
{
    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Order\Order');
    }

    /**
     * @param int $id
     * @return array|\Shopware\Models\Order\Order
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->getOrdersQueryBuilder($id);

        /** @var $order \Shopware\Models\Order\Order */
        $order = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$order) {
            throw new ApiException\NotFoundException("Order by id $id not found");
        }

        if (is_array($order)) {
            $order['paymentStatusId'] = $order['cleared'];
            $order['orderStatusId'] = $order['status'];
            unset($order['cleared']);
            unset($order['status']);
        }

        return $order;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('orders');

        $builder->addFilter($criteria);
        $builder->addOrderBy($orderBy);
        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the order data
        $orders = $paginator->getIterator()->getArrayCopy();

        foreach ($orders as &$order) {
            if (is_array($order)) {
                $order['paymentStatusId'] = $order['cleared'];
                $order['orderStatusId'] = $order['status'];
                unset($order['cleared']);
                unset($order['status']);
            }
        }

        return array('data' => $orders, 'total' => $totalResult);
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Order\Order
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $order \Shopware\Models\Order\Order */
        $order = $this->getRepository()->find($id);

        if (!$order) {
            throw new ApiException\NotFoundException("Order by id $id not found");
        }

        $whitelist = array(
            'paymentStatusId',
            'orderStatusId',
            'trackingCode',
            'comment',
            'customerComment',
            'internalComment',
            'transactionId',
            'clearedDate',
            'attribute',
        );

        $params = array_intersect_key($params, array_flip($whitelist));

        if (isset($params['orderStatusId'])) {
            $params['orderStatus'] = Shopware()->Models()->getRepository('Shopware\Models\Order\Status')->findOneBy(array(
                'id'    => $params['orderStatusId'],
                'group' => 'state',
            ));

            if (empty($params['orderStatus'])) {
                throw new ApiException\NotFoundException(sprintf("OrderStatus by id %s not found", $params['orderStatusId']));
            }
        }

        if (isset($params['paymentStatusId'])) {
            $params['paymentStatus'] = Shopware()->Models()->getRepository('Shopware\Models\Order\Status')->findOneBy(array(
                'id'    => $params['paymentStatusId'],
                'group' => 'payment',
            ));

            if (empty($params['paymentStatus'])) {
                throw new ApiException\NotFoundException(sprintf("PaymentStatus by id %s not found", $params['paymentStatusId']));
            }
        }

        $order->fromArray($params);

        $violations = $this->getManager()->validate($order);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $order;
    }
}
