<?php
namespace OroB2B\Bundle\AccountBundle\Form\Type;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use OroB2B\Bundle\AccountBundle\Entity\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrontendAccountUserType extends AbstractType
{
    const NAME = 'orob2b_account_frontend_account_user';

    /** @var  SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('account');
        /** @var $account Account */
        $account = $this->securityFacade->getLoggedUser()->getAccount();
        $builder->add('account', AccountSelectType::NAME, ['data' => $account, 'empty_data' => $account->getId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AccountUserType::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['skip_role_acl_check' => true]);
    }
}
