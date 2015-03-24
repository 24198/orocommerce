<?php

namespace OroB2B\Component\Tree\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;

abstract class AbstractTreeHandler
{
    const SUCCESS_STATUS = true;
    const ERROR_STATUS   = false;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param string $entityClass
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct($entityClass, ManagerRegistry $managerRegistry)
    {
        $this->entityClass     = $entityClass;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return array
     */
    public function createTree()
    {
        $tree = $this->getEntityRepository()
            ->getChildren(null, false, 'left', 'ASC');

        return $this->formatTree($tree);
    }

    /**
     * @param Object[] $entities
     * @return array
     */
    protected function formatTree($entities)
    {
        $formattedTree = [];

        foreach ($entities as $entity) {
            $formattedTree[] = $this->formatEntity($entity);
        }

        return $formattedTree;
    }

    /**
     * Move a entity to another parent entity
     *
     * @param int $entityId
     * @param int $parentId
     * @param int $position
     * @return array
     */
    public function moveNode($entityId, $parentId, $position)
    {
        $status = ['status' => self::SUCCESS_STATUS];

        /** @var EntityManager $em */
        $em = $this->managerRegistry->getManagerForClass($this->entityClass);
        $connection = $em->getConnection();

        $connection->beginTransaction();

        try {
            $this->moveProcessing($entityId, $parentId, $position);

            $em->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $status['status'] = self::ERROR_STATUS;
            $status['error'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * Move node processing
     *
     * @param int $entityId
     * @param int $parentId
     * @param int $position
     */
    abstract protected function moveProcessing($entityId, $parentId, $position);

    /**
     * Returns an array formatted as:
     * array(
     *     'id'     => int,    // tree item id
     *     'parent' => int,    // tree item parent id
     *     'text'   => string  // tree item label
     * )
     *
     * @param object $entity
     * @return array
     */
    abstract protected function formatEntity($entity);

    /**
     * @return ObjectRepository
     */
    protected function getEntityRepository()
    {
        return $this->managerRegistry->getRepository($this->entityClass);
    }
}
