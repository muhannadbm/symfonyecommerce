<?php

namespace App\Form\Admin;

use App\Entity\Admin\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('keywords')
            ->add('company')
            ->add('address')
            ->add('fax')
            ->add('phone')
            ->add('email')
            ->add('smtpserver')
            ->add('smtpemail')
            ->add('smptppassword')
            ->add('smtpport')
            ->add('aboutus')
            ->add('aboutustr')
            ->add('aboutusar')
            ->add('contact')
            ->add('referances')
            ->add('referancestr')
            ->add('referancesar')
            ->add('status')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Setting::class,
            'csrf_protection' =>false,
        ]);
    }
}
