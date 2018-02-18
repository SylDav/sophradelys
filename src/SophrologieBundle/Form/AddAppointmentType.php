<?php

namespace SophrologieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddAppointmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('day', TextType::class, array('label' => "Jour", 'attr' => array('placeholder' => "JJ/MM/YYYY")))
            ->add('begin', TextType::class, array('label' => "Heure de début", 'attr' => array('placeholder' => "HH:MM")))
            ->add('end', TextType::class, array('label' => "Heure de fin", 'attr' => array('placeholder' => "HH:MM")));
            //->add('duration', IntegerType::class, array('label' => "Durée du rendez-vous (minute)", 'attr' => array('value' => 60)));
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
        return 'sophrologiebundle_appointment';
    }


}
