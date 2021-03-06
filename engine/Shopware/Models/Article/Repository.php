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
 * @package    Shopware_Models
 * @subpackage Article
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

namespace Shopware\Models\Article;
use Shopware\Components\Model\ModelRepository;
/**
 * todo@all: Documentation
 */
class Repository extends ModelRepository
{
    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects all data about a single article.
     * The query selects the article, main detail of the article, assigned categories, assigned similar and related articles,
     * links and downloads of the article, selected tax, associated article images and the attributes for the different models.
     * The query is used for the article detail page of the article backend module to load the article data into the view.
     * @param $articleId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleQuery($articleId)
    {
        $builder = $this->getArticleQueryBuilder($articleId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array(
                      'article', 'mainDetail',
                      'categories', 'similar',
                      'accessories', 'accessoryDetail',
                      'similarDetail', 'images',
                      'links', 'downloads',
                      'tax', 'linkAttribute', 'customerGroups',
                      'imageAttribute', 'downloadAttribute', 'propertyValues',
                      'imageMapping', 'mappingRule', 'ruleOption'
                ))
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.mainDetail', 'mainDetail')
                ->leftJoin('article.tax', 'tax')
                ->leftJoin('article.categories', 'categories', null, null, 'categories.id')
                ->leftJoin('article.links', 'links')
                ->leftJoin('article.images', 'images')
                ->leftJoin('article.downloads', 'downloads')
                ->leftJoin('article.related', 'accessories')
                ->leftJoin('article.propertyValues', 'propertyValues')
                ->leftJoin('accessories.mainDetail', 'accessoryDetail')
                ->leftJoin('article.similar', 'similar')
                ->leftJoin('similar.mainDetail', 'similarDetail')
                ->leftJoin('links.attribute', 'linkAttribute')
                ->leftJoin('images.attribute', 'imageAttribute')
                ->leftJoin('images.mappings', 'imageMapping')
                ->leftJoin('imageMapping.rules', 'mappingRule')
                ->leftJoin('mappingRule.option', 'ruleOption')
                ->leftJoin('downloads.attribute', 'downloadAttribute')
                ->leftJoin('article.customerGroups', 'customerGroups')
                ->where('article.id = ?1')
                ->andWhere('images.parentId IS NULL')
                ->setParameter(1, $articleId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @param $options
     * @return \Doctrine\ORM\Query
     */
    public function getDetailsForOptionIdsQuery($articleId, $options) {
    	$builder = $this->getDetailsForOptionIdsQueryBuilder($articleId, $options);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailsForOptionIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @param $options
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailsForOptionIdsQueryBuilder($articleId, $options) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('details'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.articleId = :articleId')
                ->setParameter('articleId', $articleId);

        foreach($options as $key => $option) {
            $alias = 'o' . $key;
            $builder->innerJoin('details.configuratorOptions', $alias, \Doctrine\ORM\Query\Expr\Join::WITH, $alias . '.id = :' . $alias);
            $builder->setParameter($alias, $option->getId());
        }

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $imageId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImageDataQuery($imageId) {
    	$builder = $this->getArticleImageDataQueryBuilder($imageId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImageDataQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $imageId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImageDataQueryBuilder($imageId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('image'))
                ->from('Shopware\Models\Article\Image', 'image')
                ->where('image.id = ?1')
                ->setParameter(1, $imageId);
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $imageId
     * @return \Doctrine\ORM\Query
     */
    public function getDeleteImageChildrenQuery($imageId) {
    	$builder = $this->getDeleteImageChildrenQueryBuilder($imageId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDeleteImageChildrenQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $imageId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDeleteImageChildrenQueryBuilder($imageId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Image', 'images')
                ->andWhere('images.parentId = ?1')
                ->setParameter(1, $imageId);

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $imageId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImageQuery($imageId) {
        $builder = $this->getArticleImageQueryBuilder($imageId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $imageId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImageQueryBuilder($imageId) {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('image', 'mappings', 'rules', 'option'))
                ->from('Shopware\Models\Article\Image', 'image')
                ->leftJoin('image.mappings', 'mappings')
                ->leftJoin('mappings.rules', 'rules')
                ->leftJoin('rules.option', 'option')
                ->where('mappings.imageId = ?1')
                ->setParameter(1, $imageId);
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the defined configurator set
     * with the groups and options for the passed article id.
     * @param $articleId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleConfiguratorSetByArticleIdQuery($articleId) {
    	$builder = $this->getArticleConfiguratorSetByArticleIdQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleConfiguratorSetByArticleIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleConfiguratorSetByArticleIdQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('PARTIAL article.{id}', 'configuratorSet', 'groups', 'options'))
                ->from('Shopware\Models\Article\Article', 'article')
                ->innerJoin('article.configuratorSet', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('article.id = ?1')
                ->setParameter(1, $articleId)
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleConfiguratorSetByArticleIdIndexedByIdsQuery($articleId) {
    	$builder = $this->getArticleConfiguratorSetByArticleIdIndexedByIdsQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleConfiguratorSetByArticleIdIndexedByIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleConfiguratorSetByArticleIdIndexedByIdsQueryBuilder($articleId) {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('article', 'configuratorSet', 'groups', 'options'))
                        ->from('Shopware\Models\Article\Article', 'article')
                        ->innerJoin('article.configuratorSet', 'configuratorSet')
                        ->innerJoin('configuratorSet.groups', 'groups', null, null, 'groups.id')
                        ->innerJoin('configuratorSet.options', 'options')
                        ->where('article.id = ?1')
                        ->setParameter(1, $articleId)
                        ->addOrderBy('groups.position', 'ASC')
                        ->addOrderBy('options.position', 'ASC');
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @param $optionId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleDetailByConfiguratorOptionIdQuery($articleId, $optionId) {
    	$builder = $this->getArticleDetailByConfiguratorOptionIdQueryBuilder($articleId, $optionId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleDetailByConfiguratorOptionIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @param $optionId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleDetailByConfiguratorOptionIdQueryBuilder($articleId, $optionId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('details', 'prices'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->innerJoin('details.prices', 'prices')
                ->innerJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('details.configuratorOptions', 'configuratorOptions')
                ->innerJoin('configuratorOptions.group', 'groups')
                ->where('details.articleId = ?1')
                ->andWhere('configuratorOptions.id = ?2')
                ->setParameter(1, $articleId)
                ->setParameter(2, $optionId)
                ->orderBy('details.kind', 'ASC')
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('configuratorOptions.position', 'ASC')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $optionsIds
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQuery($optionsIds) {
    	$builder = $this->getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQueryBuilder($optionsIds);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $optionsIds
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQueryBuilder($optionsIds) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('groups', 'options'))
                ->from('Shopware\Models\Article\Configurator\Group', 'groups')
                ->innerJoin('groups.options', 'options', \Doctrine\ORM\Query\Expr\Join::WITH, 'options.id IN (?1)', 'options.id')
                ->setParameter(1, $optionsIds);
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @param $firstOptionId
     * @param $secondOptionId
     * @param $article
     * @param $customerGroupKey
     * @return \Doctrine\ORM\Query
     */
    public function getArticleDetailForTableConfiguratorOptionCombinationQuery($articleId, $firstOptionId, $secondOptionId, $article, $customerGroupKey) {
    	$builder = $this->getArticleDetailForTableConfiguratorOptionCombinationQueryBuilder($articleId, $firstOptionId, $secondOptionId, $article, $customerGroupKey);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleDetailForTableConfiguratorOptionCombinationQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @param $firstOptionId
     * @param $secondOptionId
     * @param $article Article
     * @param $customerGroupKey
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleDetailForTableConfiguratorOptionCombinationQueryBuilder($articleId, $firstOptionId, $secondOptionId, $article, $customerGroupKey) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('details', 'prices'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->leftJoin('details.prices', 'prices')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('details.configuratorOptions', 'options1')
                ->innerJoin('details.configuratorOptions', 'options2')
                ->where('details.articleId = ?1')
                ->andWhere('details.active = 1')
                ->andWhere('options1.id = ?2')
                ->andWhere('options2.id = ?3')
                ->andWhere('customerGroup.key = :key')
                ->setParameter('key', $customerGroupKey)
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC')
                ->setParameter(1, $articleId)
                ->setParameter(2, $firstOptionId)
                ->setParameter(3, $secondOptionId);

        if ($article->getLastStock()) {
            $builder->andWhere('details.inStock > 0');
        }

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleWithVariantsAndOptionsQuery($articleId) {
    	$builder = $this->getArticleWithVariantsAndOptionsQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleWithVariantsAndOptionsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleWithVariantsAndOptionsQueryBuilder($articleId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('article', 'details', 'options'))
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.details', 'details')
                ->leftJoin('details.configuratorOptions', 'options')
                ->where('article.id = ?1')
                ->setParameter(1, $articleId);
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $article Article
     * @param $customerGroupKey
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorTablePreSelectionItemQuery($article, $customerGroupKey) {
    	$builder = $this->getConfiguratorTablePreSelectionItemQueryBuilder($article, $customerGroupKey);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorTablePreSelectionQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $article Article
     * @param $customerGroupKey
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorTablePreSelectionItemQueryBuilder($article, $customerGroupKey) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('details', 'prices', 'options'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->leftJoin('details.prices', 'prices')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('details.configuratorOptions', 'options', null, null, 'options.groupId')
                ->where('details.articleId = ?1')
                ->andWhere('details.kind = 1')
                ->andWhere('prices.customerGroupKey = :key')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC')
                ->setParameter('key', $customerGroupKey)
                ->setParameter(1, $article->getId());

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $ids
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorSetsWithExcludedIdsQuery($ids) {
    	$builder = $this->getConfiguratorSetsWithExcludedIdsQueryBuilder($ids);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorSetsWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $ids
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorSetsWithExcludedIdsQueryBuilder($ids) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('configuratorSet', 'groups', 'options'))
                ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('configuratorSet.public = ?1')
                ->setParameter(1, 1)
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');

        if (!empty($ids)) {
            $builder->andWhere($builder->expr()->notIn('configuratorSet.id', $ids));
        }

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $configuratorSetId
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorSetQuery($configuratorSetId) {
    	$builder = $this->getConfiguratorSetQueryBuilder($configuratorSetId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorSetQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $configuratorSetId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorSetQueryBuilder($configuratorSetId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('configuratorSet', 'groups', 'options'))
                ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('configuratorSet.id = ?1')
                ->setParameter(1, $configuratorSetId)
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $configuratorSetId
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorDependenciesQuery($configuratorSetId) {
    	$builder = $this->getConfiguratorDependenciesQueryBuilder($configuratorSetId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorDependenciesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $configuratorSetId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorDependenciesQueryBuilder($configuratorSetId) {
    	$builder = $this->getEntityManager()->createQueryBuilder($configuratorSetId);
        $builder->select(array('dependencies', 'dependencyParent', 'dependencyChild'))
                ->from('Shopware\Models\Article\Configurator\Dependency', 'dependencies')
                ->leftJoin('dependencies.parentOption', 'dependencyParent')
                ->leftJoin('dependencies.childOption', 'dependencyChild')
                ->where('dependencies.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId);
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $configuratorSetId
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorDependenciesIndexedByParentIdQuery($configuratorSetId) {
    	$builder = $this->getConfiguratorDependenciesIndexedByParentIdQueryBuilder($configuratorSetId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorDependenciesIndexedByParentIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $configuratorSetId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorDependenciesIndexedByParentIdQueryBuilder($configuratorSetId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('dependencies'))
                ->from('Shopware\Models\Article\Configurator\Dependency', 'dependencies', 'dependencies.parentId')
                ->where('dependencies.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId)
                ->orderBy('dependencies.parentId', 'ASC');
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $configuratorSetId
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorPriceSurchargesQuery($configuratorSetId) {
    	$builder = $this->getConfiguratorPriceSurchargesQueryBuilder($configuratorSetId);
    	return $builder->getQuery();
    }


    /**
     * Helper function to create the query builder for the "getConfiguratorPriceSurchargesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $configuratorSetId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorPriceSurchargesQueryBuilder($configuratorSetId) {

    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('priceSurcharges', 'priceSurchargesParent', 'priceSurchargesChild'))
                ->from('Shopware\Models\Article\Configurator\PriceSurcharge', 'priceSurcharges')
                ->leftJoin('priceSurcharges.parentOption', 'priceSurchargesParent')
                ->leftJoin('priceSurcharges.childOption', 'priceSurchargesChild')
                ->where('priceSurcharges.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId);
    	return $builder;
    }

    /**
     * Internal helper function to optimize the performance of the "getArticleConfiguratorSetQueryBuilder"  function.
     * Without this function, the getArticleConfiguratorSetQueryBuilder needs to join the s_articles_details
     * to filter the articleId.
     * @param $articleId
     * @return array
     */
    private function getArticleConfiguratorSetOptionIds($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $ids = $builder->select('DISTINCT options.id')
                       ->from('Shopware\Models\Article\Detail', 'detail')
                       ->innerJoin('detail.configuratorOptions', 'options')
                       ->where('detail.articleId = ?1')
                       ->setParameter(1, $articleId)
                       ->getQuery()->getArrayResult();

        $optionIds = array();
        foreach($ids as $id) {
            $optionIds[] = $id['id'];
        }
        return $optionIds;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all defined configurator groups.
     * Used for the backend module to display all groups for the article, even the inactive groups.
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorGroupsQuery() {
    	$builder = $this->getConfiguratorGroupsQueryBuilder();
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorGroupsQueryBuilder() {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('groups', 'options'))
                ->from('Shopware\Models\Article\Configurator\Group', 'groups')
                ->leftJoin('groups.options', 'options')
                ->orderBy('groups.position');

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @return \Doctrine\ORM\Query
     */
    public function getFirstArticleDetailWithKindTwoQuery($articleId) {
    	$builder = $this->getFirstArticleDetailWithKindTwoQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFirstArticleDetailWithKindTwoQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFirstArticleDetailWithKindTwoQueryBuilder($articleId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('details'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.kind = 2')
                ->andWhere('details.articleId = ?1')
                ->setParameter(1, $articleId)
                ->setFirstResult(0)
                ->setMaxResults(1);

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @return \Doctrine\ORM\Query
     */
    public function getAllConfiguratorOptionsIndexedByIdQuery() {
    	$builder = $this->getAllConfiguratorOptionsIndexedByIdQueryBuilder();
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAllConfiguratorOptionsIndexedByIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllConfiguratorOptionsIndexedByIdQueryBuilder() {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('options'))
                ->from('Shopware\Models\Article\Configurator\Option', 'options', 'options.id');
    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the id for the listing query
     * of the configurator. To display the options in the listing the configurator listing needs
     * an id query to allow an store paging.
     *
     * @param      $articleId
     * @param null $filter
     * @param null $sort
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorListIdsQuery($articleId, $filter = null, $sort = null, $offset = null, $limit = null) {
    	$builder = $this->getConfiguratorListIdsQueryBuilder($articleId, $filter, $sort);
        if ($limit != null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorListIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param      $articleId
     * @param null $filter
     * @param null $sort
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorListIdsQueryBuilder($articleId, $filter = null, $sort = null)
    {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('details.id')
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.articleId = ?1')
                ->setParameter(1, $articleId);

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'details.number LIKE ?2',
                'configuratorOptions.name LIKE ?2'
            ));
            $builder->setParameter(2, '%' . $filter[0]['value'] . '%');
            $builder->leftJoin('details.configuratorOptions', 'configuratorOptions');
        }

        if ($sort !== null && !empty($sort)) {
            $builder->addOrderBy($sort);
        } else {
            $builder->addOrderBy('details.id', 'ASC');
        }

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the article details
     * @param      $ids
     * @param null $sort
     * @return \Doctrine\ORM\Query
     */
    public function getDetailsByIdsQuery($ids, $sort = null) {
    	$builder = $this->getDetailsByIdsQueryBuilder($ids, $sort);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailsByArticleIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param      $ids
     * @param null $sort
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailsByIdsQueryBuilder($ids, $sort = null) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('details','attribute','prices','customerGroup','configuratorOptions'))
                ->from('Shopware\Models\Article\Detail', 'details')
                ->leftJoin('details.configuratorOptions', 'configuratorOptions')
                ->leftJoin('details.prices', 'prices')
                ->leftJoin('details.attribute', 'attribute')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->where('details.id IN (?1)')
                ->setParameter(1, $ids);

        if ($sort !== null && !empty($sort)) {
            $builder->addOrderBy($sort);
        } else {
            $builder->addOrderBy('details.id', 'ASC');
        }
        $builder->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');
    	return $builder;
    }


    /**
     * Returns a list of all defined price groups. Used for the article
     * detail page in the backend module to assign the article to a price group.
     * @return \Doctrine\ORM\Query
     */
    public function getPriceGroupQuery()
    {
        $builder = $this->getPriceGroupQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPriceGroupQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     * @access private
     */
    public function getPriceGroupQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('price_groups'))
                       ->from('Shopware\Models\Price\Group', 'price_groups')
                       ->orderBy('price_groups.name', 'ASC');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined taxes. Used for the tax combo box on the article detail page in the article backend module.
     * @return \Doctrine\ORM\Query
     */
    public function getTaxesQuery()
    {
        $builder = $this->getTaxesQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTaxesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTaxesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('taxes'))
                       ->from('Shopware\Models\Tax\Tax', 'taxes')
                       ->orderBy('taxes.name', 'ASC');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined pack units. Used for the unit combo box on the article detail page in the article backend module.
     * @return \Doctrine\ORM\Query
     */
    public function getUnitsQuery()
    {
        $builder = $this->getUnitsQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUnitsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUnitsQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('units'))
                       ->from('Shopware\Models\Article\Unit', 'units')
                       ->orderBy('units.name', 'ASC');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all article property groups. Used for the property combo box on the article detail page in the article backend module.
     * @return \Doctrine\ORM\Query
     */
    public function getPropertiesQuery()
    {
        $builder = $this->getPropertiesQueryBuilder();
        return $builder->getQuery();
    }
    /**
     * Helper function to create the query builder for the "getPropertiesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPropertiesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('properties'))
                       ->from('Shopware\Models\Property\Group', 'properties')
                       ->orderBy('properties.name', 'ASC');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the prices for the passed article detail id.
     * Used for the article detail page in the article backend module.
     * @param $articleDetailId
     * @return \Doctrine\ORM\Query
     */
    public function getPricesQuery($articleDetailId)
    {
        $builder = $this->getPricesQueryBuilder($articleDetailId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPricesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleDetailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPricesQueryBuilder($articleDetailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('prices', 'customerGroup', 'attribute'))
                ->from('Shopware\Models\Article\Price', 'prices')
                ->join('prices.customerGroup', 'customerGroup')
                ->leftJoin('prices.attribute', 'attribute')
                ->where('prices.articleDetailsId = ?1')
                ->setParameter(1, $articleDetailId)
                ->orderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the image attributes for the passed
     * image id. Used for the article backend module in the save article function.
     * @param $imageId
     * @return \Doctrine\ORM\Query
     */
    public function getImageAttributesQuery($imageId)
    {
        $builder = $this->getImageAttributesQueryBuilder($imageId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getImageAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $imageId
     * @internal param $articleDetailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getImageAttributesQueryBuilder($imageId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('attribute'))
                       ->from('Shopware\Models\Attribute\ArticleImage', 'attribute')
                       ->where('attribute.articleImageId = ?1')
                       ->setParameter(1, $imageId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article link id. Used for the article backend module in the save article function.
     * @param $linkId
     * @return \Doctrine\ORM\Query
     */
    public function getLinkAttributesQuery($linkId)
    {
        $builder = $this->getLinkAttributesQueryBuilder($linkId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getLinkAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $linkId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLinkAttributesQueryBuilder($linkId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('attribute'))
                       ->from('Shopware\Models\Attribute\ArticleLink', 'attribute')
                       ->where('attribute.articleLinkId = ?1')
                       ->setParameter(1, $linkId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article download id. Used for the article backend module in the save article function.
     * @param $downloadId
     * @return \Doctrine\ORM\Query
     */
    public function getDownloadAttributesQuery($downloadId)
    {
        $builder = $this->getDownloadAttributesQueryBuilder($downloadId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDownloadAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $downloadId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDownloadAttributesQueryBuilder($downloadId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('attribute'))
                         ->from('Shopware\Models\Attribute\ArticleDownload', 'attribute')
                         ->where('attribute.articleDownloadId = ?1')
                         ->setParameter(1, $downloadId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article detail id. Used for the article backend module in the save article function.
     * @param $articleDetailId
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($articleDetailId)
    {
        $builder = $this->getAttributesQueryBuilder($articleDetailId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleDetailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($articleDetailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('attribute'))
                      ->from('Shopware\Models\Attribute\Article', 'attribute')
                      ->where('attribute.articleDetailId = ?1')
                      ->setParameter(1, $articleDetailId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article price id. Used for the article backend module in the save article function.
     *
     * @param $priceId
     * @return \Doctrine\ORM\Query
     */
    public function getPriceAttributesQuery($priceId)
    {
        $builder = $this->getPriceAttributesQueryBuilder($priceId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPriceAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $priceId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPriceAttributesQueryBuilder($priceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('attribute'))
                ->from('Shopware\Models\Attribute\ArticlePrice', 'attribute')
                ->where('attribute.articlePriceId = ?1')
                ->setParameter(1, $priceId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for article details with the same
     * article oder number like the passed number.
     * @param $number
     * @param $articleDetailId
     * @return \Doctrine\ORM\Query
     */
    public function getValidateNumberQuery($number, $articleDetailId)
    {
        $builder = $this->getValidateNumberQueryBuilder($number, $articleDetailId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateNumberQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $number
     * @param $articleDetailId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateNumberQueryBuilder($number, $articleDetailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('details'))
                       ->from('Shopware\Models\Article\Detail', 'details')
                       ->where('details.number = ?1')
                       ->andWhere('details.id != ?2')
                       ->setParameter(1, $number)
                       ->setParameter(2, $articleDetailId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select article ids and names.
     * The passed article ids are excluded.
     *
     * @param null $ids
     * @param null $filter
     * @param null $offset
     * @param null $limit
     * @return \Doctrine\ORM\Query
     */
    public function getArticlesWithExcludedIdsQuery($ids = null, $filter = null, $offset = null, $limit = null)
    {
        $builder = $this->getArticlesWithExcludedIdsQueryBuilder($ids, $filter);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticlesWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param null $ids
     * @param null $filter
     * @internal param null $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticlesWithExcludedIdsQueryBuilder($ids = null, $filter = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array('articles', 'mainDetail', 'supplier'));
        $builder->from($this->getEntityName(), 'articles');
        $builder->leftJoin('articles.mainDetail', 'mainDetail')
                ->leftJoin('articles.supplier', 'supplier');

        if (!empty($ids)) {
            $ids = implode(",",$ids);
            $builder->where($builder->expr()->notIn("articles.id",$ids));
        }

        if (!empty($filter) && $filter[0]["property"] == "filter" && !empty($filter[0]["value"])) {
            $builder->andWhere('articles.name LIKE ?1')
                    ->orWhere('mainDetail.number LIKE ?1')
                    ->orWhere('supplier.name LIKE ?1')
                    ->setParameter(1, '%'.$filter[0]["value"].'%');
        }
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of all mails.
     * @param $supplierId
     * @return \Doctrine\ORM\Query
     */
    public function getSupplierQuery($supplierId)
    {
        $builder = $this->getSupplierQueryBuilder($supplierId);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $supplierId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSupplierQueryBuilder($supplierId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('supplier', 'attribute'))
                ->from('Shopware\Models\Article\Supplier', 'supplier')
                ->leftJoin('supplier.attribute', 'attribute')
                ->where('supplier.id = ?1')
                ->setParameter(1, $supplierId);

        return $builder;
    }

    /**
     * Returns a list of all defined article suppliers as array, ordered by the supplier name.
     * Used for the article detail page in the backend module.
     * @return \Doctrine\ORM\Query
     */
    public function getSuppliersQuery()
    {
        $builder = $this->getSuppliersQueryBuilder();
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSupplierQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSuppliersQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        return $builder->select(array('suppliers'))
                       ->from('Shopware\Models\Article\Supplier', 'suppliers')
                       ->orderBy('suppliers.name', 'ASC');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects supplier ids, names and
     * description. The passed supplier ids are excluded.
     *
     * @param $ids
     * @param $filter
     * @param $offset
     * @param $limit
     * @return \Doctrine\ORM\Query
     */
    public function getSuppliersWithExcludedIdsQuery($ids = null, $filter = null, $offset = null, $limit = null)
    {
        $builder = $this->getSuppliersWithExcludedIdsQueryBuilder($ids, $filter);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSuppliersWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $ids
     * @param $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSuppliersWithExcludedIdsQueryBuilder($ids = null, $filter = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'suppliers.id as id',
            'suppliers.name as name',
            'suppliers.description as description'
        ));
        $builder->from('Shopware\Models\Article\Supplier', 'suppliers');
        if (!empty($ids)) {
            $ids = implode(",",$ids);
            $builder->where($builder->expr()->notIn("suppliers.id",$ids));
        }
        if (!empty($filter) && $filter[0]["property"] == "filter" && !empty($filter[0]["value"])) {
            $builder->andWhere('suppliers.name LIKE ?1')
                    ->setParameter(1, '%'.$filter[0]["value"].'%');
        }
        return $builder;
    }

    /**
     * Retrieves the following fields
     * - id             Integer
     * - name           String
     * - image          String
     * - link           String
     * - description    String
     *
     * The filter param will be uses to search for a part of the suppliers name or description.
     *
     *
     * @param array $filter
     * @param   array $orderBy
     * @param   null $limit
     * @param   null $offset
     * @return  \Doctrine\ORM\Query
     */
    public function getSupplierListQuery($filter = null, array $orderBy, $limit = null, $offset = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'supplier.id as id',
            'supplier.name as name',
            'supplier.image as image',
            'supplier.link as link',
            'supplier.description as description',
            $builder->expr()->count("articles.id") . ' as articleCounter')
        );
        $builder->from('Shopware\Models\Article\Supplier', 'supplier');
        $builder->leftJoin('supplier.articles', 'articles');
        $builder->groupBy('supplier.id');

        if (is_array($filter) && ('name' == $filter[0]['property'])) {
            //filter the displayed columns with the passed filter
            $builder->where("supplier.name LIKE ?1") //Search only the beginning of the customer number.
                    ->orWhere("supplier.description LIKE ?1"); //Full text search for the first name of the customer

            //set the filter parameter for the different columns.
            $builder->setParameter(1, '%' . $filter[0]['value'] . '%');
        }

        $builder->addOrderBy($orderBy);

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        return $builder->getQuery();
    }

	/**
	 * Returns an instance of the \Doctrine\ORM\Query object which select a list of article votes.
	 * @param null $filter
	 * @param null $offset
	 * @param null $limit
	 * @param null $order
	 * @return \Doctrine\ORM\Query
	 */
    public function getVoteListQuery($filter = null, $offset = null, $limit = null, $order = null)
    {
        $builder = $this->getVoteListQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

	/**
	 * Helper function to create the query builder for the "getVoteListQuery" function.
	 * This function can be hooked to modify the query builder of the query object.
	 * @param $filter
	 * @param $order
	 * @return \Doctrine\ORM\QueryBuilder
	 */
    public function getVoteListQueryBuilder($filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'vote.id as id',
            'vote.articleId as articleId',
            'vote.name as name',
            'vote.headline as headline',
            'vote.comment as comment',
            'vote.points as points',
            'vote.datum as datum',
            'vote.active as active',
            'vote.answer as answer',
            'article.name as articleName'
        ));
        $builder->from('Shopware\Models\Article\Vote', 'vote');
        $builder->join('vote.article','article');
        if (!empty($filter)) {
            $builder->where('article.name LIKE ?1')
                    ->setParameter(1, '%'.$filter.'%');
        }
		if($order == null){
        	$builder->addOrderBy('vote.datum', 'DESC');
		}else{
			$builder->addOrderBy($order);
		}
        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all articles with registered notifications
     *
     * @param null $filter
     * @param null $offset
     * @param null $limit
     * @param null $order
     * @param null $summarize
     * @return \Doctrine\ORM\Query
     */
    public function getArticlesWithRegisteredNotificationsQuery($filter = null, $offset = null, $limit = null, $order = null, $summarize = null)
    {
        $builder = $this->getArticlesWithRegisteredNotificationsBuilder($filter, $order, $summarize);
        if (empty($summarize) && !empty($limit) && !empty($offset)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticlesWithRegisteredNotificationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $filter
     * @param $order
     * @param $summarize
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticlesWithRegisteredNotificationsBuilder($filter, $order, $summarize)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array(
            'notification.articleNumber as number',
            'COUNT(notification.articleNumber) as registered',
            '( COUNT(notification.articleNumber) ) - ( SUM(notification.send) ) as notNotified',
            'article.name as name',
        ))
        ->from('Shopware\Models\Article\Notification', 'notification')
        ->leftJoin('notification.articleDetail', 'articleDetail')
        ->leftJoin('articleDetail.article', 'article');

        if(!empty($summarize)) {
            return $builder;
        }

        $builder->groupBy("notification.articleNumber");

        //search part
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'search') {
            $builder->where('notification.articleNumber LIKE :search')
                    ->orWhere('article.name LIKE :search')
                    ->setParameter('search', $filter[0]['value']);
        }

        if($order == null){
            $builder->addOrderBy('registered', 'DESC');
        }else{
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all notification customers by the given articleOrderNumber
     *
     * @param $articleOrderNumber
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     * @internal param $articleOrderNumber
     * @return \Doctrine\ORM\Query
     */
    public function getNotificationCustomerByArticleQuery($articleOrderNumber, $filter, $offset, $limit, $order)
    {
        $builder = $this->getNotificationCustomerByArticleBuilder($articleOrderNumber, $filter, $order);
        if (!empty($limit) && !empty($offset)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getNotificationCustomerByArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleOrderNumber
     * @param $filter
     * @param $order
     * @internal param $articleOrderNumber
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNotificationCustomerByArticleBuilder($articleOrderNumber, $filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->setAlias('notification');
        $builder->select(array(
            'notification.mail as mail',
            'notification.date as date',
            'notification.send as notified',
            'notification.articleNumber as orderNumber',
            'billing.customerId as customerId',
            "CONCAT(CONCAT(billing.firstName, ' '), billing.lastName) as name",
        ))
        ->from('Shopware\Models\Article\Notification', 'notification')
        ->leftJoin('notification.customer', 'customer', 'with', 'customer.accountMode = 0 AND customer.languageIso = notification.language')
        ->leftJoin('customer.billing', 'billing')
        ->where('notification.articleNumber = :orderNumber')
        ->setParameter("orderNumber",$articleOrderNumber);

         //search part
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'search') {
            $builder->andWhere('(
                        notification.mail LIKE :search
                        OR notification.articleNumber LIKE :search
                        OR billing.lastName LIKE :search
                        OR billing.firstName LIKE :search
                    )')
                    ->setParameter('search', $filter[0]['value']);
        }

        if($order == null){
            $builder->addOrderBy('date', 'DESC');
        }else{
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all ESD by the given articleId
     *
     * @param $articleId
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     * @return \Doctrine\ORM\Query
     */
    public function getEsdByArticleQuery($articleId, $filter, $offset, $limit, $order)
    {
        $builder = $this->getEsdByArticleQueryBuilder($articleId, $filter, $order);
        if (!empty($limit) && !empty($offset)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getEsdByArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     * @param $filter
     * @param $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEsdByArticleQueryBuilder($articleId, $filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array(
                    'esd.id as id',
                    'esd.date as date',
                    'esd.file as file',
                    'esd.hasSerials as hasSerials',
                    'COUNT(serials) as serialsTotal',
                    'COUNT(esdOrder.id) as serialsUsed',
                    'COUNT(esdOrder.id) as downloads',
                    'article.name as name',
                    'articleDetail.id as articleDetailId',
                    'articleDetail.additionalText as additionalText',
                    'article.id as articleId',
                ))
                ->from('Shopware\Models\Article\Esd', 'esd')
                ->leftJoin('esd.serials', 'serials')
                ->leftJoin('serials.esdOrder', 'esdOrder')
                ->leftJoin('esd.article', 'article')
                ->leftJoin('esd.articleDetail', 'articleDetail')
                ->leftJoin('esd.attribute', 'attribute')
                ->groupBy('esd.id')
                ->where('esd.article = :articleId')
                ->setParameter('articleId', $articleId);

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'article.name LIKE :search',
                'articleDetail.additionalText LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        if ($order == null){
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }


    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all serials by the given esdId
     *
     * @param $esdId
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     * @return \Doctrine\ORM\Query
     */
    public function getSerialsByEsdQuery($esdId, $filter, $offset, $limit, $order)
    {
        $builder = $this->getSerialsByEsdQueryBuilder($esdId, $filter, $order);
        if (!empty($limit)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSerialsByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $esdId
     * @param $filter
     * @param $order
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSerialsByEsdQueryBuilder($esdId, $filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array(
                    'serial.id as id',
                    'serial.serialnumber as serialnumber',
                    'esdOrder.date as date',
                    'customer.id as customerId',
                    'customer.accountMode as accountMode',
                    'customer.email as customerEmail'
                ))
                ->from('Shopware\Models\Article\EsdSerial', 'serial')
                ->leftJoin('serial.esdOrder', 'esdOrder')
                ->leftJoin('esdOrder.customer', 'customer')
                ->where('serial.esd = :esdId')
                ->setParameter('esdId', $esdId);


        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'customer.email LIKE :search',
                'serial.serialnumber LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        if ($order == null){
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

       return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all free serials by the given esdId
     *
     * @param $esdId
     * @return \Doctrine\ORM\Query
     */
    public function getFreeSerialsCountByEsdQuery($esdId)
    {
        $builder = $this->getFreeSerialsCountByEsdQueryBuilder($esdId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFreeSerialsCountByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $esdId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFreeSerialsCountByEsdQueryBuilder($esdId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
                ->select('COUNT(serials) - COUNT(esdOrder.id) as serialsFree')
                ->from('Shopware\Models\Article\Esd', 'esd')
                ->leftJoin('esd.serials', 'serials')
                ->leftJoin('serials.esdOrder', 'esdOrder')
                ->where('esd.id = :esdId')
                ->setParameter('esdId', $esdId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all unused serials by the given esdId
     *
     * @param $esdId
     * @return \Doctrine\ORM\Query
     */
    public function getUnusedSerialsByEsdQuery($esdId)
    {
        $builder = $this->getUnusedSerialsByEsdQueryBuilder($esdId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUnusedSerialsByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $esdId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUnusedSerialsByEsdQueryBuilder($esdId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
                ->select('serial')
                ->from('Shopware\Models\Article\EsdSerial', 'serial')
                ->leftJoin('serial.esdOrder', 'esdOrder')
                ->where('serial.esd = :esdId')
                ->andWhere('esdOrder IS NULL')
                ->setParameter('esdId', $esdId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @internal param int $main
     * @return \Doctrine\ORM\Query
     */
    public function getArticleCoverImageQuery($articleId) {
    	$builder = $this->getArticleCoverImageQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleCoverImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleCoverImageQueryBuilder($articleId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('images', 'attribute'))
                ->from('Shopware\Models\Article\Image', 'images')
                ->leftJoin('images.attribute', 'attribute')
                ->leftJoin('images.children', 'children')
                ->where('images.articleId = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->andWhere('children.id IS NULL')
                ->setParameter('articleId', $articleId)
                ->orderBy('images.main', 'ASC')
                ->addOrderBy('images.position', 'ASC')
				->setFirstResult(0)
				->setMaxResults(1);

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @internal param $article
     * @return \Doctrine\ORM\Query
     */
    public function getArticleFallbackCoverQuery($articleId) {
    	$builder = $this->getArticleFallbackCoverQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleFallbackCoverQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @internal param $article
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleFallbackCoverQueryBuilder($articleId) {
        $builder = $this->getEntityManager()->createQueryBuilder();
           $builder->select(array('images', 'attribute'))
                   ->from('Shopware\Models\Article\Image', 'images')
                   ->leftJoin('images.attribute', 'attribute')
                   ->where('images.articleId = :articleId')
                   ->andWhere('images.parentId IS NULL')
                   ->andWhere('images.main = :main')
                   ->setParameter('main', 1)
                   ->setParameter('articleId', $articleId)
   				   ->setFirstResult(0)
   				   ->setMaxResults(1);

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $number
     * @param $offset
     * @param $limit
     * @return \Doctrine\ORM\Query
     */
    public function getVariantImagesByArticleNumberQuery($number, $offset = null, $limit = null) {
    	$builder = $this->getVariantImagesByArticleNumberQueryBuilder($number);
        if($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVariantImagesByArticleNumberQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $number
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVariantImagesByArticleNumberQueryBuilder($number) {
    	$builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array(
                    'imageParent.id',
                    'imageParent.articleId',
                    'imageParent.articleDetailId',
                    'imageParent.description',
                    'imageParent.path',
                    'imageParent.main',
                    'imageParent.position',
                    'imageParent.width',
                    'imageParent.height',
                    'imageParent.extension',
                    'imageParent.parentId',
                    'attribute.attribute1',
                    'attribute.attribute2',
                    'attribute.attribute3',
                 ))
                ->from('Shopware\Models\Article\Image', 'images')
                ->innerJoin('images.articleDetail', 'articleDetail')
                ->innerJoin('images.parent', 'imageParent')
                ->leftJoin('imageParent.attribute', 'attribute')
                ->where('articleDetail.number = ?1')
                ->setParameter(1, $number)
                ->orderBy('imageParent.main', 'ASC')
                ->addOrderBy('imageParent.position', 'ASC');

    	return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     * @param $articleId
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImagesQuery($articleId) {
    	$builder = $this->getArticleImagesQueryBuilder($articleId);
    	return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImagesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImagesQueryBuilder($articleId) {
    	$builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(array('images'))
                ->from('Shopware\Models\Article\Image', 'images')
                ->leftJoin('images.children','children')
                ->where('images.articleId = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->andWhere('images.articleDetailId IS NULL')
                ->andWhere('children.id IS NULL')
                ->setParameter('articleId', $articleId)
                ->addOrderBy('images.position', 'ASC');

    	return $builder;
    }


}




