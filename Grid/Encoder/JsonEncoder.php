<?php
namespace PedroTeixeira\Bundle\GridBundle\Grid\Encoder;

use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\Query;

use PedroTeixeira\Bundle\GridBundle\Grid\Encoder\EncoderInterface;

class JsonEncoder implements EncoderInterface
{
    protected $queryBuilder;
    protected $templating;
    protected $container;
    protected $columns;

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     *
     * @return \PedroTeixeira\Bundle\GridBundle\Grid\Encoder\JsonEncoder
     */
    public function __construct(\Symfony\Component\DependencyInjection\Container $container)
    {
        $this->container = $container;
        $this->templating = $container->get('templating');
    }

    public function setColumns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function setQueryBuilder(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function supportsPagination()
    {
        return true;
    }

    public function getMimeType()
    {
        return 'application/json';
    }

    public function encode()
    {
        $data = $this->getQueryBuilder()->getQuery()->getResult(Query::HYDRATE_SCALAR);
        $rows = array();

        foreach ($data as $key => $row) {

            $rowValue = array();

            foreach ($this->columns as $column) {

                if ($column->getExportOnly() && !$this->isExport()) {
                    continue;
                }

                $rowColumn = ' ';

                if (array_key_exists($column->getField(), $row)) {
                    $rowColumn = $row[$column->getField()];
                } else if (array_key_exists('r_' . $column->getField(), $row)) {
                    $rowColumn = $row['r_' . $column->getField()];
                } else if ($column->getTwig()) {
                    $rowColumn = $this->templating->render(
                        $column->getTwig(),
                        array(
                            'row' => $row
                        )
                    );
                }

                $rowValue[$column->getField()] = $column->getRender()
                    ->setValue($rowColumn)
                    ->setStringOnly(false)
                    ->render();
            }

            $rows[$key] = $rowValue;
        }

        return $rows;
    }
}
