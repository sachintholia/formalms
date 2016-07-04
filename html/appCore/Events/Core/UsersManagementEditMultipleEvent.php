<?php
namespace appCore\Events\Core;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UsersManagementShowEvent
 * @package appLms\Events\Core
 */
class UsersManagementEditMultipleEvent extends Event
{
    const EVENT_NAME = 'core.usersmanagementeditmultiple.event';
    
    /** @var array */
    protected $users;

    /**
     * UsersManagementShowEvent constructor.
     */
    public function __construct()
    {
        
        $this->users = array();
    }

    /**
     * @param $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

}