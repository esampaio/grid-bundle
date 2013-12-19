<?php
namespace PedroTeixeira\Bundle\GridBundle\Grid\Encoder;

use DoctrineExtensions\Paginate\Paginate;
use Doctrine\ORM\Query;

use PedroTeixeira\Bundle\GridBundle\Grid\Encoder\EncoderInterface;

class CsvEncoder implements EncoderInterface
{
    protected $queryBuilder;
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
    }

    public function getColumns()
    {
        return $this->columns;
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
        return false;
    }

    public function getMimeType()
    {
        return 'text/csv';
    }

    public function encode()
    {
        $data = $this->getQueryBuilder()->getQuery()->getResult(Query::HYDRATE_SCALAR);

        ob_start();
        $fileHandler = fopen('php://output', 'w');

        $columnsHeader = array();
        foreach ($this->getColumns() as $column) {
            if (!$column->getTwig()) {
                $columnsHeader[$column->getField()] = $column->getName();
            }
        }
        fputcsv($fileHandler, $columnsHeader);

        foreach ($data as $row) {
            $rowValue = array();

            foreach ($this->columns as $column) {
                $rowColumn = ' ';

                if ($column->getTwig()) {
                    continue;
                }

                if (array_key_exists($column->getField(), $row)) {
                    $rowColumn = $row[$column->getField()];
                } else if (array_key_exists('r_' . $column->getField(), $row)) {
                    $rowColumn = $row['r_' . $column->getField()];
                }

                $rowValue[$column->getField()] = $column->getRender()
                    ->setValue($rowColumn)
                    ->setStringOnly(true)
                    ->render();
            }

            fputcsv($fileHandler, $rowValue);
        }

        fclose($fileHandler);
        return ob_get_clean();
    }
}
