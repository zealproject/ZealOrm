<?php
/**
 * Zeal ORM
 *
 * @link      http://github.com/zealproject
 * @copyright Copyright (c) 2010-2018 Tim Fountain (http://tfountain.co.uk/)
 * @license   http://tfountain.co.uk/license New BSD License
 */

namespace Zeal\Orm\Mapper\Association;

use Zeal\Orm\Association\AbstractAssociation;
use Zeal\Orm\Mapper\Collection;

class HasMany extends AbstractAssociation
{
    protected $sourceMapper;

    protected $targetMapper;

    protected $options;

    public function __construct($sourceMapper, $targetMapper, $options)
    {
        $this->sourceMapper = $sourceMapper;
        $this->targetMapper = $targetMapper;
        $this->options = $options;
    }

    public function buildQuery($sourceModel)
    {
        $sourcePrimaryKey = $this->sourceMapper->getAdapterOption('primaryKey');

        $foreignKey = $this->getOption('foreignKey', $sourcePrimaryKey);
        $primaryKey = $this->getOption('primaryKey', $sourcePrimaryKey);

        $query = $this->targetMapper->buildQuery();
        $query->where([$foreignKey => $sourceModel->$primaryKey]);

        return $query;
    }

    public function buildCollection($sourceModel)
    {
        return new Collection($this->targetMapper, $this->buildQuery($sourceModel));
    }
}
