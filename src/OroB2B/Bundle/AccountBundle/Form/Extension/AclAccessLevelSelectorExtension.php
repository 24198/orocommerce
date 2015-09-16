<?php

namespace OroB2B\Bundle\AccountBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Oro\Bundle\SecurityBundle\Form\Type\AclAccessLevelSelectorType;

use OroB2B\Bundle\AccountBundle\Form\Type\FrontendAccountUserRoleType;
use OroB2B\Bundle\AccountBundle\Form\Type\AccountUserRoleType;

class AclAccessLevelSelectorExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return AclAccessLevelSelectorType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $permissionForm = $form->getParent();
        if (!$permissionForm) {
            return;
        }

        $permissionsForm = $permissionForm->getParent();
        if (!$permissionsForm) {
            return;
        }

        $privilegeForm = $permissionsForm->getParent();
        if (!$privilegeForm) {
            return;
        }

        $privilegesForm = $privilegeForm->getParent();
        if (!$privilegesForm) {
            return;
        }

        $roleForm = $privilegesForm->getParent();
        if (!$roleForm) {
            return;
        }

        if (in_array(
            $roleForm->getConfig()->getType()->getName(),
            [AccountUserRoleType::NAME, FrontendAccountUserRoleType::NAME]
        )) {
            $view->vars['translation_prefix'] = 'orob2b.account.security.access-level.';
        }
    }
}
