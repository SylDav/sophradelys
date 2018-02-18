<?php

namespace SophrologieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstAppointmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', TextType::class, array('label' => "Nouveau thème"))
            ->add('user_commentary', TextType::class, array('label' => "Commentaire sur le client (visible que par toi)"))
            ->add('commentary_for_current_appointment', TextType::class, array('label' => "Commentaire sur la séance actuelle"))
            ->add('commentary_for_next_appointment', TextType::class, array('label' => "Commentaire à faire pour la prochaine séance"));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sophrologiebundle_user';
    }


}
