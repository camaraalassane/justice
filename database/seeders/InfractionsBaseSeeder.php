<?php

namespace Database\Seeders;

use App\Models\InfractionBase;
use Illuminate\Database\Seeder;

class InfractionsBaseSeeder extends Seeder
{
    public function run(): void
    {
        $infractions = [
            // ==================== CRIMINELLES ====================
            // Code pénal malien - Livre II - Crimes contre les personnes
            [
                'code_infraction' => 'INF-CR01',
                'libelle' => 'Meurtre',
                'description' => 'Homicide volontaire commis avec intention de donner la mort (Art. 199 Code pénal)',
                'classification' => 'Criminelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 5,
            ],
            [
                'code_infraction' => 'INF-CR02',
                'libelle' => 'Assassinat',
                'description' => 'Meurtre commis avec préméditation ou guet-apens (Art. 200 Code pénal)',
                'classification' => 'Criminelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 5,
            ],
            [
                'code_infraction' => 'INF-CR03',
                'libelle' => 'Viol',
                'description' => 'Acte de pénétration sexuelle commis par violence, contrainte, menace ou surprise (Art. 226 Code pénal)',
                'classification' => 'Criminelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 5,
            ],
            [
                'code_infraction' => 'INF-CR04',
                'libelle' => 'Coups et blessures volontaires ayant entraîné la mort sans intention de la donner',
                'description' => 'Violences volontaires ayant entraîné la mort (Art. 207 Code pénal)',
                'classification' => 'Criminelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 5,
            ],

            // Code de justice militaire - Crimes militaires
            [
                'code_infraction' => 'INF-CR05',
                'libelle' => 'Trahison en temps de guerre',
                'description' => 'Intelligence avec une puissance étrangère en temps de guerre (Art. 38 Code justice militaire)',
                'classification' => 'Criminelle',
                'nature' => 'Trahison',
                'gravite' => 5,
            ],
            [
                'code_infraction' => 'INF-CR06',
                'libelle' => 'Désertion en temps de guerre',
                'description' => 'Abandon de poste ou de corps en présence de l\'ennemi (Art. 45 Code justice militaire)',
                'classification' => 'Criminelle',
                'nature' => 'Désertion',
                'gravite' => 5,
            ],
            [
                'code_infraction' => 'INF-CR07',
                'libelle' => 'Désertion à l\'étranger en temps de paix',
                'description' => 'Désertion avec passage à l\'étranger (Art. 46 Code justice militaire)',
                'classification' => 'Criminelle',
                'nature' => 'Désertion',
                'gravite' => 4,
            ],
            [
                'code_infraction' => 'INF-CR08',
                'libelle' => 'Rébellion armée',
                'description' => 'Rébellion militaire avec usage d\'armes (Art. 55 Code justice militaire)',
                'classification' => 'Criminelle',
                'nature' => 'Atteinte à l\'honneur',
                'gravite' => 5,
            ],
            [
                'code_infraction' => 'INF-CR09',
                'libelle' => 'Pillage en temps de guerre',
                'description' => 'Pillage ou destruction de biens en temps de guerre (Art. 60 Code justice militaire)',
                'classification' => 'Criminelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 5,
            ],

            // ==================== DÉLICTUELLES ====================
            // Code pénal malien - Délits contre les personnes
            [
                'code_infraction' => 'INF-DE01',
                'libelle' => 'Coups et blessures volontaires avec incapacité',
                'description' => 'Violences ayant entraîné une incapacité de travail (Art. 208 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE02',
                'libelle' => 'Menaces de mort',
                'description' => 'Menaces de mort réitérées (Art. 215 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 3,
            ],

            // Code pénal malien - Délits contre les biens
            [
                'code_infraction' => 'INF-DE03',
                'libelle' => 'Vol simple',
                'description' => 'Soustraction frauduleuse de la chose d\'autrui (Art. 275 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 2,
            ],
            [
                'code_infraction' => 'INF-DE04',
                'libelle' => 'Vol aggravé',
                'description' => 'Vol commis avec circonstances aggravantes (escalade, effraction, réunion) (Art. 279 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 4,
            ],
            [
                'code_infraction' => 'INF-DE05',
                'libelle' => 'Escroquerie',
                'description' => 'Usage de faux nom ou qualité pour escroquer des fonds (Art. 290 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE06',
                'libelle' => 'Abus de confiance',
                'description' => 'Détournement de biens remis à titre précaire (Art. 295 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE07',
                'libelle' => 'Destruction de biens publics',
                'description' => 'Destruction, dégradation ou détérioration volontaire de biens appartenant à l\'État (Art. 310 Code pénal)',
                'classification' => 'Délictuelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 3,
            ],

            // Code de justice militaire - Délits militaires
            [
                'code_infraction' => 'INF-DE08',
                'libelle' => 'Abandon de poste en temps de paix',
                'description' => 'Abandon de poste sans autorisation (Art. 43 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Manquement à la discipline',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE09',
                'libelle' => 'Désertion en temps de paix',
                'description' => 'Absence illégale de plus de 6 jours (Art. 44 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Désertion',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE10',
                'libelle' => 'Outrage à supérieur',
                'description' => 'Paroles, gestes ou menaces outrageantes envers un supérieur (Art. 30 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Manquement à la discipline',
                'gravite' => 2,
            ],
            [
                'code_infraction' => 'INF-DE11',
                'libelle' => 'Voies de fait envers un supérieur',
                'description' => 'Violences légères envers un supérieur (Art. 31 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Manquement à la discipline',
                'gravite' => 4,
            ],
            [
                'code_infraction' => 'INF-DE12',
                'libelle' => 'Refus d\'obéissance',
                'description' => 'Refus délibéré d\'exécuter un ordre légal (Art. 28 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Manquement à la discipline',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE13',
                'libelle' => 'Détournement de matériel militaire',
                'description' => 'Détournement ou utilisation frauduleuse de matériel militaire (Art. 65 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Atteinte aux biens',
                'gravite' => 3,
            ],
            [
                'code_infraction' => 'INF-DE14',
                'libelle' => 'Ivresse en service',
                'description' => 'État d\'ivresse manifeste pendant le service (Art. 33 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Manquement à la discipline',
                'gravite' => 2,
            ],
            [
                'code_infraction' => 'INF-DE15',
                'libelle' => 'Faux en écriture militaire',
                'description' => 'Falsification de documents administratifs militaires (Art. 68 Code justice militaire)',
                'classification' => 'Délictuelle',
                'nature' => 'Infraction au droit commun',
                'gravite' => 3,
            ],

            // ==================== CONTRAVENTIONS ====================
            // Code pénal malien - Contraventions
            [
                'code_infraction' => 'INF-CO01',
                'libelle' => 'Trouble à l\'ordre public',
                'description' => 'Tapage, bruit ou désordre troublant la tranquillité (Art. 450 Code pénal)',
                'classification' => 'Contravention',
                'nature' => 'Manquement à la discipline',
                'gravite' => 1,
            ],
            [
                'code_infraction' => 'INF-CO02',
                'libelle' => 'Absence irrégulière de moins de 6 jours',
                'description' => 'Absence non autorisée mais inférieure à 6 jours (Art. 42 Code justice militaire)',
                'classification' => 'Contravention',
                'nature' => 'Manquement à la discipline',
                'gravite' => 1,
            ],
            [
                'code_infraction' => 'INF-CO03',
                'libelle' => 'Négligence dans le service',
                'description' => 'Manquement aux obligations de service sans intention malveillante (Art. 35 Code justice militaire)',
                'classification' => 'Contravention',
                'nature' => 'Manquement à la discipline',
                'gravite' => 1,
            ],
            [
                'code_infraction' => 'INF-CO04',
                'libelle' => 'Tenue non réglementaire',
                'description' => 'Non-respect du port de l\'uniforme réglementaire (Art. 25 Règlement de discipline générale)',
                'classification' => 'Contravention',
                'nature' => 'Manquement à la discipline',
                'gravite' => 1,
            ],
            [
                'code_infraction' => 'INF-CO05',
                'libelle' => 'Retard répété au service',
                'description' => 'Retards fréquents et non justifiés aux appels ou services (Art. 24 Règlement de discipline générale)',
                'classification' => 'Contravention',
                'nature' => 'Manquement à la discipline',
                'gravite' => 1,
            ],
            [
                'code_infraction' => 'INF-CO06',
                'libelle' => 'Non-respect du règlement intérieur',
                'description' => 'Violation des dispositions du règlement intérieur de l\'unité (Art. 22 Règlement de discipline générale)',
                'classification' => 'Contravention',
                'nature' => 'Manquement à la discipline',
                'gravite' => 1,
            ],
            [
                'code_infraction' => 'INF-CO07',
                'libelle' => 'Détérioration légère de matériel',
                'description' => 'Détérioration involontaire ou par négligence de matériel militaire (Art. 63 Code justice militaire)',
                'classification' => 'Contravention',
                'nature' => 'Atteinte aux biens',
                'gravite' => 1,
            ],
        ];

        foreach ($infractions as $infraction) {
            InfractionBase::create($infraction);
        }
    }
}