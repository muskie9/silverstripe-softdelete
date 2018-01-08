<?php

namespace LeKoala\SoftDelete\Extension;

use LeKoala\SoftDelete\Forms\GridField\GridFieldSoftDeleteAction;
use LeKoala\SoftDelete\ORM\SoftDeletable;
use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;

/**
 * Add gridfield action to SecurityAdmin
 *
 * @author Koala
 * @property SecurityAdmin $owner
 */
class SoftDeleteSecurityAdmin extends Extension
{
    /**
     * @param Form $form
     */
    public function updateEditForm(Form $form)
    {
        /* @var $owner SecurityAdmin */
        $owner = $this->owner;

        $memberSingl = singleton(Member::class);
        $groupSingl = singleton(Group::class);

        if ($memberSingl->hasExtension(SoftDeletable::class)) {
            $gridfield = $form->Fields()->dataFieldByName('Members');
            $config = $gridfield->getConfig();

            $config->removeComponentsByType(GridFieldDeleteAction::class);
            if ($this->owner->config()->softdelete_from_list) {
                $config->addComponent(new GridFieldSoftDeleteAction());
            }

            // No caution because soft :-)
            $form->Fields()->removeByName('MembersCautionText');

            $bulkManager = $config->getComponentByType('GridFieldBulkManager');
            if ($bulkManager && $this->owner->config()->softdelete_from_bulk) {
                $bulkManager->removeBulkAction('delete');
                $bulkManager->addBulkAction(
                    'softDelete',
                    'delete (soft)',
                    'GridFieldBulkSoftDeleteEventHandler'
                );
            }
        }

        if ($groupSingl->hasExtension('Groups')) {
            $gridfield = $form->Fields()->dataFieldByName('Members');
            $config = $gridfield->getConfig();

            $config->removeComponentsByType(GridFieldDeleteAction::class);
            if ($this->owner->config()->softdelete_from_list) {
                $config->addComponent(new GridFieldSoftDeleteAction());
            }

            $bulkManager = $config->getComponentByType('GridFieldBulkManager');
            if ($bulkManager && $this->owner->config()->softdelete_from_bulk) {
                $bulkManager->removeBulkAction('delete');
                $bulkManager->addBulkAction(
                    'softDelete',
                    'delete (soft)',
                    'GridFieldBulkSoftDeleteEventHandler'
                );
            }
        }
    }
}
