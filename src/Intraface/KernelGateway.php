<?php
class Intraface_KernelGateway
{
    protected $translation;

    function __construct(Translation2 $translation)
    {
        $this->translation = $translation;
    }

    function findByUserobject($user)
    {
        $kernel = new Intraface_Kernel(session_id());
        $kernel->user = $user;
        if (!$intranet_id = $kernel->user->getActiveIntranetId()) {
            throw new Exception('no active intranet_id');
        }
        // hack to avoid having to set it everywhere
        Intraface_Doctrine_Intranet::singleton($intranet_id);

        $kernel->intranet = new Intraface_Intranet($intranet_id);
        $kernel->translation = $this->translation;
        $kernel->translation->setLang($kernel->user->getLanguage());

        // @todo why are we setting the id?
        $kernel->user->setIntranetId($kernel->intranet->get('id'));
        $kernel->setting = new Intraface_Setting($kernel->intranet->get('id'), $kernel->user->get('id'));
        return $kernel;
    }
}