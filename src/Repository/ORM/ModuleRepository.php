<?php

namespace Persona\Hris\Repository\ORM;

use Persona\Hris\Core\Manager\ManagerFactory;
use Persona\Hris\Core\Security\Model\ModuleInterface;
use Persona\Hris\Core\Security\Model\ModuleRepositoryInterface;
use Persona\Hris\Repository\AbstractRepository;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@personahris.com>
 */
final class ModuleRepository extends AbstractRepository implements ModuleRepositoryInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $recall;

    /**
     * @param ManagerFactory $managerFactory
     * @param string         $class
     */
    public function __construct(ManagerFactory $managerFactory, string  $class)
    {
        parent::__construct($managerFactory);
        $this->class = $class;
        $this->recall = true;
    }

    /**
     * @param string $id
     *
     * @return ModuleInterface|null
     */
    public function find(string $id): ? ModuleInterface
    {
        $cache = $this->managerFactory->getCacheDriver();
        if ($cache->contains($this->class)) {
            $data = $cache->fetch($this->class);
            $this->managerFactory->merge([$data]);

            return $data;
        }

        $data = $this->managerFactory->getWriteManager()->getRepository($this->class)->findOneBy(['id' => $id, 'deletedAt' => null]);
        if ($data) {
            $cache->save($this->class, $data);
        }

        return $data;
    }

    /**
     * @param string $path
     *
     * @return ModuleInterface|null
     */
    public function findByPath(string $path): ? ModuleInterface
    {
        $data = $this->managerFactory->getReadManager()->getRepository($this->class)->findOneBy(['path' => $path, 'deletedAt' => null]);
        if (!$data && $this->recall) {
            $path = explode('/', $path);
            array_pop($path);
            $this->recall = false;

            $data = $this->findByPath(implode('/', $path));
        }

        return $data;
    }
}
