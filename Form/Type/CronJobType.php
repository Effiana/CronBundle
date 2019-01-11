<?php
/**
 * This file is part of the BrandOriented package.
 *
 * (c) Brand Oriented sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Dominik Labudzinski <dominik@labudzinski.com>
 */

namespace Effiana\CronBundle\Form\Type;


use Effiana\CronBundle\Entity\CronJob;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CronJobType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'detail.name',
                'required' => true,
            ]
        );
        $builder->add(
            'command',
            CommandChoiceType::class,
            [
                'label' => 'detail.command',
                'required' => true,
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label' => 'detail.description',
                'required' => true,
            ]
        );
        $builder->add(
            'schedule',
            TextType::class,
            [
                'label' => 'detail.cronExpression',
                'required' => true,
            ]
        );

        $builder->add(
            'enabled',
            CheckboxType::class,
            [
                'label' => 'detail.disabled',
                'required' => false,
            ]
        );
        $builder->add(
            'save',
            SubmitType::class,
            [
                'label' => 'detail.save',
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CronJob::class,
        ]);
    }
}