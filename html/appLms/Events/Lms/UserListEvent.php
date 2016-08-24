<?php
namespace appLms\Events\Lms;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UserListEvent
 * @package appLms\Events\Lms
 */
class UserListEvent extends Event
{
    const EVENT_NAME = 'lms.userlist.event';

    /** @var null */
    protected $lang;

    /** @var null */
    protected $out;

    /** @var null */
    protected $exportLink;

    /** @var null */
    protected $idEvent;

    /**
     * UserProfileShowEvent constructor.
     */
    public function __construct($out, $lang)
    {
        $this->out = $out;

        $this->lang = $lang;

        $this->idEvent = NULL;
    }

    /**
     * @param null $idEvent
     */
    public function setIdEvent($idEvent)
    {
        $this->idEvent = $idEvent;

        $this->exportLink = '<a href="index.php?modname=reservation&amp;op=excel&amp;id_event='.$this->idEvent.'" target="_blank">'.$this->lang->def('_EXPORT_XLS').'</a>';
    }

    /**
     * @return null
     */
    public function getIdEvent()
    {
        return $this->idEvent;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return mixed
     */
    public function getExportLink()
    {
        return $this->exportLink;
    }

    /**
     * @param mixed $exportLink
     */
    public function setExportLink($exportLink)
    {
        $this->exportLink = $exportLink;
    }

    /**
     * @return null
     */
    public function getOut()
    {
        return $this->out;
    }
}