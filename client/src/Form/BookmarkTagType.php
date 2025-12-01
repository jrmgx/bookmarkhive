<?php

namespace App\Form;

use App\Model\Tag;
use App\Service\ApiService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array>
 */
class BookmarkTagType extends AbstractType
{
    public function __construct(
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $tags = $options['tagList'];
        $tagNames = array_keys($tags);
        $choices = array_combine($tagNames, $tagNames);
        dump($builder->getData());
        $builder
            ->add('tags', ChoiceType::class, [
                'choices' => $choices,
                'multiple' => true,
                'autocomplete' => true,
                'allow_options_create' => true,
                'tom_select_options' => [
                    'create' => true,
                ]
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                $isDirty = false;
                $choices = $form->get('tags')->getConfig()->getOption('choices');
                dump($data);
                $submittedOpts = $data['tags'];
                foreach ($submittedOpts as $opt) {
                    if (!\in_array($opt, $choices, true)) {
                        $choices[$opt] = $opt;
                        $isDirty = true;
                    }
                }

                if ($isDirty) {
                    dump($choices);
                    $form->add('tags', ChoiceType::class, [
                        'choices' => $choices,
                        'multiple' => true,
                        'autocomplete' => true,
                        'allow_options_create' => true,
                        'tom_select_options' => [
                            'create' => true,
                        ]
                    ]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // TODO
            //'data_class' => Tag::class,
        ])->setRequired(['tagList']);
    }
}
