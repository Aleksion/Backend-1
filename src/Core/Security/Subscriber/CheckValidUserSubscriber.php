<?php

namespace Persona\Hris\Core\Security\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Persona\Hris\Core\Security\Model\UserAwareInterface;
use Persona\Hris\Core\Security\Model\UserInterface;
use Persona\Hris\Core\Security\Model\UserRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@personahris.com>
 */
final class CheckValidUserSubscriber implements EventSubscriber
{
    /**
     * @var UserRepositoryInterface
     */
    private $repository;

    /**
     * @param UserRepositoryInterface $repository
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof UserAwareInterface) {
            $this->isValidUserOrException($entity->getUser());
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if ($entity instanceof UserAwareInterface) {
            $this->isValidUserOrException($entity->getUser());
        }
    }

    /**
     * @param string $userId
     *
     * @return bool|null
     */
    private function isValidUserOrException(string $userId): ? bool
    {
        if (!$this->repository->find($userId) instanceof UserInterface) {
            throw new NotFoundHttpException(sprintf('User with id %s is not found.', $userId));
        }

        return true;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [Events::prePersist, Events::preUpdate];
    }
}
