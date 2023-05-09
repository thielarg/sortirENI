<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie* :'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label'=> 'Date de debut de la sortie* :'
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label'=> 'Date de fin d\'inscription* :'
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places* :'
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (minutes)* :'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos :'
            ])
            ->add('ville', EntityType::class,[
                    'class' => 'App\Entity\Ville',
                    'label'=>'Ville :',
                    'mapped' => false, //à ne pas oublier car la vile n'est pas referencée dans l'entité sortie
                    'choice_label' => 'nom',
                    'placeholder' => 'Selectionner une ville',
                    'required' => false
                ]
            )
        ;

        /* Evenement declenché après avoir rempli l'entité */
        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event){
                $form = $event->getForm();
                $this->addLieuField($form->getParent(), $form->getData());
            }
        );

        /* Evenement déclenché juste avant la fin du chargement des données dans le formulaire */
        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event){
                $data = $event->getData();
                /* @var $lieu \App\Entity\Lieu */
                $lieu = $data->getLieu();
                $form = $event->getForm();
                if($lieu){
                    $ville = $lieu->getVille();
                    $this->addLieuField($form, $ville);
                    $form->get('ville')->setData($ville);
                }else{
                    $this->addLieuField($form, null);
                }

            }
        );
    }

    //ajout du select lieu au formulaire
    private function addLieuField(FormInterface $form, ?Ville $ville){
        $builder = $form->add('lieu', EntityType::class,[
            'class' => Lieu::class,
            'label'=> 'Lieu :',
            'choice_label' => 'nom',
            'placeholder' => $ville ? 'Selectionnez votre lieu' : 'Selectionnez votre ville',
            'required' => true,
            'auto_initialize' => false,
            'choices' => $ville ? $ville->getLieux() : []
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
