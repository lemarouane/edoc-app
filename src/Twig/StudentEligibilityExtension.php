<?php

namespace App\Twig;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StudentEligibilityExtension extends AbstractExtension
{
    private Security $security;
    private Connection $etudiantConnection;

    public function __construct(Security $security, Connection $etudiantConnection)
    {
        $this->security = $security;
        $this->etudiantConnection = $etudiantConnection;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_student_eligible_for_convention', [$this, 'isStudentEligibleForConvention']),
        ];
    }

    public function isStudentEligibleForConvention(): bool
    {
        $user = $this->security->getUser();
        if (!$user || !$user->getCode()) {
            return false;
        }

        $code = $user->getCode();
        $individu = $this->etudiantConnection->executeQuery(
            'SELECT COD_IND FROM individu WHERE COD_ETU = :cod_etu',
            ['cod_etu' => $code]
        )->fetchAssociative();

        if (!$individu || !isset($individu['COD_IND'])) {
            return false;
        }

        $insAdmEtp = $this->etudiantConnection->executeQuery(
            'SELECT COD_ETP FROM ins_adm_etp WHERE COD_IND = :cod_ind ORDER BY DAT_CRE_IAE DESC LIMIT 1',
            ['cod_ind' => $individu['COD_IND']]
        )->fetchAssociative();

        if (!$insAdmEtp || !isset($insAdmEtp['COD_ETP'])) {
            return false;
        }

        $codEtp = trim($insAdmEtp['COD_ETP']);
        return preg_match('/2$/', $codEtp) && $codEtp !== 'IIAP2';
    }
}