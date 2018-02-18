<?php

namespace SophrologieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array('label' => "Nom"))
            ->add('firstname', TextType::class, array('label' => "Prénom"))
            ->add('mail', TextType::class, array('label' => "Adresse mail"))
            ->add('phonenumber', TextType::class, array('label' => "Numéro de téléphone"));
            //->add('username', TextType::class, array('label' => "Nom de compte"))
            //->add('commentary', TextType::class, array('label' => "Commentaire"));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SophrologieBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sophrologiebundle_user';
    }


}
