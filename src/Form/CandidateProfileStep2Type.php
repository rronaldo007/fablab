<?php

namespace App\Form;

use App\Entity\CandidateProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class CandidateProfileStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_of_birth', BirthdayType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre date de naissance']),
                ],
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Adresse complète',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre adresse complète']),
                ],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone']),
                ],
            ])
            ->add('nationality', CountryType::class, [
                'label' => 'Nationalité',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre nationalité']),
                ],
            ])
            ->add('school', TextType::class, [
                'label' => 'École actuelle',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer le nom de votre école actuelle']),
                ],
            ])
            ->add('course_name', TextType::class, [
                'label' => 'Nom du cursus',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer le nom de votre cursus']),
                ],
            ])
            ->add('specialization', TextType::class, [
                'label' => 'Spécialité',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre spécialité']),
                ],
            ])
            ->add('course_start_date', DateType::class, [
                'label' => 'Date d\'entrée dans le cursus',
                'widget' => 'single_text',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer la date d\'entrée dans votre cursus']),
                ],
            ])
            ->add('course_year', IntegerType::class, [
                'label' => 'Année dans le cursus',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer votre année dans le cursus']),
                ],
            ])
            ->add('studentCardFile', FileType::class, [
                'label' => 'Carte d\'étudiant (JPEG ou PDF)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez télécharger votre carte d\'étudiant']),
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF ou une image JPEG/PNG valide',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CandidateProfile::class,
        ]);
    }
}