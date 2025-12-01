<?php

namespace App\Form;

use App\Model\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Tag>
 */
class TagEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('pinned', CheckboxType::class, [
                'required' => false,
            ])
            ->add('layout', ChoiceType::class, [
                'choices' => [
                    'Default' => Tag::LAYOUT_DEFAULT,
                    'Video Embed' => Tag::LAYOUT_EMBEDDED,
                    'Big Images' => Tag::LAYOUT_IMAGE,
                    // 'Read Later' => Tag::LAYOUT_POST,
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
