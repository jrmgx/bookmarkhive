<?php

namespace App\Form;

use App\Model\Bookmark;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Bookmark>
 */
class BookmarkTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $tags = $options['tagList'];
        $builder
            ->add('tags', ChoiceType::class, [
                'choices' => $tags,
                'multiple' => true,
                'autocomplete' => true,
                'allow_options_create' => true,
                'tom_select_options' => [
                    'create' => true,
                ],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                $isDirty = false;
                $choices = $form->get('tags')->getConfig()->getOption('choices');
                $submittedOpts = $data['tags'];
                foreach ($submittedOpts as $opt) {
                    if (!\in_array($opt, $choices, true)) {
                        $choices[$opt] = $opt;
                        $isDirty = true;
                    }
                }

                if ($isDirty) {
                    $form->add('tags', ChoiceType::class, [
                        'choices' => $choices,
                        'multiple' => true,
                        'autocomplete' => true,
                        'allow_options_create' => true,
                        'tom_select_options' => [
                            'create' => true,
                        ],
                    ]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // TODO
            'data_class' => Bookmark::class,
        ]);

        $resolver->setRequired(['tagList']);
    }
}
