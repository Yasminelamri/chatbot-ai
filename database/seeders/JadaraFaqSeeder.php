<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadaraFaqSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $faqs = [
            [
                'question' => 'Comment demander une prolongation de ma bourse ?',
                'answer' => "✅ Chaque année, un mail explicatif vous est envoyé fin décembre. Il détaille les modalités et la procédure de prolongation de votre bourse.",
            ],
            [
                'question' => 'Quand vais-je recevoir la réponse à ma demande de prolongation ?',
                'answer' => "✅ Les demandes sont étudiées par le comité de sélection. Vous serez informé(e) par mail dès qu’une décision est prise.",
            ],
            [
                'question' => 'À quelle date la bourse est-elle versée ?',
                'answer' => "✅ Les bourses sont généralement versées soit à la fin du mois en cours, soit au début du mois suivant.",
            ],
            [
                'question' => 'Je n’ai pas encore reçu ma bourse ce mois-ci, que faire ?',
                'answer' => "✅ Vérifiez d’abord votre compte bancaire et assurez-vous d’avoir transmis tous les documents nécessaires. Si le versement n’apparaît pas, contactez programmes@jadara.foundation pour vérifier la situation.",
            ],
            [
                'question' => 'Est-ce que la bourse est versée pendant les vacances d’été ?',
                'answer' => "✅ Conformément au règlement intérieur de JADARA FOUNDATION et à votre contrat de bourse, la dotation n’est accordée en juillet et août qu’aux boursiers ayant justifié d’un stage durant cette période.",
            ],
            [
                'question' => 'Quels sont les documents à envoyer chaque année ?',
                'answer' => "✅ La reconduction de la bourse est conditionnée par la validation de votre année universitaire précédente. Vous devez fournir :\n\nBulletin de notes de l’année précédente ou attestation de réussite\n\nAttestation de scolarité de l’année suivante\n\nEn mars, vous devez également nous transmettre vos résultats semestriels.",
            ],
            [
                'question' => 'Je suis en 2ᵉ année CPGE et je souhaite passer les concours français. Est-ce que JADARA prend en charge les frais liés aux concours ?',
                'answer' => "✅ Oui, JADARA prend en charge les frais de concours. Les modalités de prise en charge vous seront communiquées par mail durant le mois de décembre.",
            ],
            [
                'question' => 'Que faire si je change de compte bancaire ?',
                'answer' => "✅ Vous devez mettre à jour votre RIB sur la plateforme Impact Social, y déposer l’attestation correspondante, puis nous renvoyer le document par mail.",
            ],
            [
                'question' => 'Que se passe-t-il si je redouble une année, est-ce que je garde ma bourse ?',
                'answer' => "✅ Selon le règlement intérieur de JADARA FOUNDATION, le redoublement est strictement interdit et n’ouvre pas droit à la bourse.",
            ],
            [
                'question' => 'Si je change d’établissement, est-ce que je garde ma bourse ?',
                'answer' => "✅ En principe, les changements d’établissement ne sont pas autorisés. Toutefois, certaines demandes exceptionnelles peuvent être étudiées en fin d’année académique. Les critères pris en compte sont :\n\nLa validation de l’année en cours\n\nL’établissement d’accueil\n\nLes motivations justifiant le transfert",
            ],
            [
                'question' => 'À qui dois-je m’adresser en cas de problème urgent ?',
                'answer' => "✅ Vous pouvez écrire directement à programmes@jadara.foundation",
            ],
            [
                'question' => 'Est-ce que je peux contacter directement les partenaires de JADARA ?',
                'answer' => "✅ Non, c’est strictement interdit.",
            ],
            [
                'question' => 'Est-ce que je peux demander un rendez-vous physique avec l’équipe JADARA ?',
                'answer' => "✅ Oui, il suffit de prendre rendez-vous",
            ],
        ];

        foreach ($faqs as $f) {
            DB::table('faq')->updateOrInsert(
                ['question' => $f['question']],
                ['answer' => $f['answer'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
