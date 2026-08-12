<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_cdb_role_label_is_observateur(): void
    {
        $user = new User(['role' => 'CDB']);
        $this->assertEquals('Observateur', $user->role_label);
        $this->assertTrue($user->estLectureSeule());
    }

    public function test_cdb_permissions_are_read_only(): void
    {
        $user = new User(['role' => 'CDB']);

        // Must NOT be able to write/create/modify/delete
        $this->assertFalse($user->peutCreerProcedure());
        $this->assertFalse($user->peutModifierProcedure());
        $this->assertFalse($user->peutSupprimerProcedure());
        $this->assertFalse($user->peutGererMilitaires());
        $this->assertFalse($user->peutCreerRapports());

        // Must BE able to read/consult/export
        $this->assertTrue($user->peutConsulterTout());
        $this->assertTrue($user->peutExporter());
        $this->assertTrue($user->peutVoirStatistiques());
        $this->assertTrue($user->peutVoirRapports());
    }

    public function test_admin_permissions(): void
    {
        $user = new User(['role' => 'ADMIN']);

        $this->assertTrue($user->peutCreerProcedure());
        $this->assertTrue($user->peutModifierProcedure());
        $this->assertTrue($user->peutSupprimerProcedure());
        $this->assertTrue($user->peutGererUtilisateurs());
        $this->assertFalse($user->estLectureSeule());
    }
}
